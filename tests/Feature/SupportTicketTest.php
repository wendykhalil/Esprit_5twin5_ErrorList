<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_support_ticket_creation_requires_valid_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('support.tickets.create'))->post(route('support.tickets.store'), [
            'subject' => 'Hi',
            'message' => 'Trop court',
            'priority' => 'normal',
        ]);

        $response
            ->assertRedirect(route('support.tickets.create'))
            ->assertSessionHasErrors(['subject', 'message']);
    }

    public function test_authenticated_user_can_create_support_ticket(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('support.tickets.store'), [
            'subject' => 'Problème de facturation',
            'message' => 'Je ne comprends pas ma dernière facture.',
            'priority' => 'normal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'subject' => 'Problème de facturation',
        ]);

        $ticket = SupportTicket::first();
        $this->assertMatchesRegularExpression('/^TCK-\d{6}$/', $ticket->reference);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->get(route('support.tickets.show', $ticket))
            ->assertForbidden();
    }

    public function test_admin_can_reply_to_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->create(['status' => 'open']);

        $response = $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => 'Nous traitons votre demande.',
            'is_internal' => '0',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'body' => 'Nous traitons votre demande.',
            'is_internal' => false,
        ]);

        $ticket->refresh();
        $this->assertSame('in_progress', $ticket->status);
    }

    public function test_closed_ticket_cannot_receive_client_reply(): void
    {
        $client = User::factory()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $client->id,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->actingAs($client)
            ->post(route('support.tickets.replies.store', $ticket), ['body' => 'Encore un mot'])
            ->assertForbidden();
    }

    public function test_closed_ticket_cannot_receive_admin_reply_until_reopened(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->create([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.replies.store', $ticket), ['body' => 'Réponse'])
            ->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'normal',
        ]);

        $ticket->refresh();
        $this->assertNull($ticket->closed_at);

        $this->actingAs($admin)
            ->post(route('admin.tickets.replies.store', $ticket), ['body' => 'Après réouverture'])
            ->assertRedirect();
    }

    public function test_resolved_status_sets_closed_at(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->create(['status' => 'in_progress', 'closed_at' => null]);

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'resolved',
            'priority' => 'normal',
        ]);

        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_internal_notes_are_hidden_from_client_view(): void
    {
        $client = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $ticket = SupportTicket::factory()->create(['user_id' => $client->id]);

        TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'body' => 'Note secrète admin',
            'is_internal' => true,
        ]);

        TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'body' => 'Réponse publique',
            'is_internal' => false,
        ]);

        $response = $this->actingAs($client)->get(route('support.tickets.show', $ticket));

        $response->assertOk();
        $response->assertSee('Réponse publique');
        $response->assertDontSee('Note secrète admin');
    }

    public function test_admin_can_update_ticket_status(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->create(['status' => 'open', 'priority' => 'normal']);

        $response = $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'closed',
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $ticket->refresh();
        $this->assertSame('closed', $ticket->status);
        $this->assertSame('high', $ticket->priority);
        $this->assertNotNull($ticket->closed_at);
    }
}

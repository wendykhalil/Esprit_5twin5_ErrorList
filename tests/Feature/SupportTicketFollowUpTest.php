<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_creating_a_ticket_sets_the_first_response_deadline_from_its_priority(): void
    {
        Carbon::setTestNow('2026-10-20 08:00:00');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('support.tickets.store'), [
            'subject' => 'Panne de chargeur solaire',
            'message' => 'Le chargeur ne démarre plus depuis ce matin malgré une batterie pleine.',
            'priority' => 'high',
        ])->assertRedirect();

        $ticket = SupportTicket::first();
        $this->assertSame('2026-10-20 12:00:00', $ticket->first_response_due_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'created',
        ]);
    }

    public function test_a_similar_open_ticket_is_signalled_without_blocking_creation(): void
    {
        $user = User::factory()->create();
        $existing = SupportTicket::factory()->open()->create([
            'user_id' => $user->id,
            'subject' => 'Probleme de facture du mois',
            'message' => 'La facture affiche un montant different de ma reservation solaire.',
        ]);

        $this->actingAs($user)->post(route('support.tickets.store'), [
            'subject' => 'Probleme de facture du mois',
            'message' => 'La facture affiche un montant different de ma reservation solaire.',
            'priority' => 'normal',
        ])->assertRedirect()->assertSessionHas('warning');

        $this->assertSame(2, SupportTicket::query()->where('user_id', $user->id)->count());
        $this->assertStringContainsString($existing->reference, session('warning'));
    }

    public function test_client_cannot_reply_on_a_resolved_ticket_without_the_reopen_reason(): void
    {
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $owner->id,
            'status' => 'resolved',
            'resolved_at' => now(),
            'closed_at' => null,
        ]);

        $this->actingAs($owner)
            ->post(route('support.tickets.replies.store', $ticket), [
                'body' => 'Le probleme est revenu sans autre explication utile.',
            ])
            ->assertForbidden();

        $this->assertSame(0, $ticket->replies()->count());
        $this->assertSame('resolved', $ticket->fresh()->status);
    }

    public function test_only_a_public_admin_reply_stamps_the_first_response(): void
    {
        Carbon::setTestNow('2026-10-20 09:30:00');
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create();

        $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => 'Note interne de suivi.',
            'is_internal' => '1',
        ])->assertRedirect();

        $this->assertNull($ticket->fresh()->first_response_at);

        $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => 'Nous avons identifie la cause.',
            'is_internal' => '0',
        ])->assertRedirect();

        $this->assertSame('2026-10-20 09:30:00', $ticket->fresh()->first_response_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2026-10-20 11:00:00');
        $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => 'Complement public qui ne doit pas ecraser l horaire.',
            'is_internal' => '0',
        ])->assertRedirect();

        $this->assertSame('2026-10-20 09:30:00', $ticket->fresh()->first_response_at->format('Y-m-d H:i:s'));
    }

    public function test_overdue_tickets_are_escalated_once_per_level(): void
    {
        Carbon::setTestNow('2026-10-20 18:00:00');
        $ticket = SupportTicket::factory()->open()->create([
            'priority' => 'low',
            'first_response_due_at' => '2026-10-20 08:00:00',
        ]);

        $this->artisan('support:escalate-sla')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame('normal', $ticket->priority);
        $this->assertNotNull($ticket->sla_breached_at);
        $this->assertTrue($ticket->first_response_due_at->greaterThan(now()));
        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'sla_escalated',
        ]);

        $this->artisan('support:escalate-sla')->assertSuccessful();
        $this->assertSame('normal', $ticket->fresh()->priority);
    }

    public function test_owner_can_rate_a_resolved_ticket_once(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $owner->id,
            'status' => 'resolved',
            'resolved_at' => now(),
            'closed_at' => null,
        ]);

        $this->actingAs($other)->post(route('support.tickets.satisfaction', $ticket), [
            'score' => 5,
        ])->assertForbidden();

        $this->actingAs($owner)->post(route('support.tickets.satisfaction', $ticket), [
            'score' => 4,
            'comment' => 'La reponse a regle le probleme.',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame(4, $ticket->satisfaction_score);
        $this->assertNotNull($ticket->satisfaction_rated_at);

        $this->actingAs($owner)->post(route('support.tickets.satisfaction', $ticket), [
            'score' => 1,
        ])->assertForbidden();
        $this->assertSame(4, $ticket->fresh()->satisfaction_score);
    }

    public function test_a_duplicate_public_reply_is_kept_and_signalled(): void
    {
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create(['user_id' => $owner->id]);
        TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $owner->id,
            'body' => 'Le chargeur reste bloque.',
            'is_internal' => false,
        ]);

        $this->actingAs($owner)->post(route('support.tickets.replies.store', $ticket), [
            'body' => "  le   chargeur reste bloque.  ",
        ])->assertRedirect()->assertSessionHas('warning');

        $this->assertSame(2, $ticket->publicReplies()->count());
    }

    public function test_a_reply_can_carry_an_image_or_a_pdf_but_not_another_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->post(route('support.tickets.replies.store', $ticket), [
            'body' => 'Voici le document demande pour la facture contestee.',
            'attachment' => UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $reply = $ticket->replies()->first();
        $this->assertNotNull($reply->attachment);
        Storage::disk('public')->assertExists($reply->attachment);

        $this->actingAs($owner)->from(route('support.tickets.show', $ticket))->post(route('support.tickets.replies.store', $ticket), [
            'body' => 'Ce fichier ne devrait pas etre accepte sur le ticket.',
            'attachment' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ])->assertRedirect(route('support.tickets.show', $ticket))->assertSessionHasErrors('attachment');
    }

    public function test_silent_tickets_receive_one_internal_reminder(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'created_at' => now()->subDays(3),
        ]);

        $this->artisan('support:remind-silent')->assertSuccessful();
        $this->artisan('support:remind-silent')->assertSuccessful();

        $this->assertSame(1, $ticket->replies()->count());
        $note = $ticket->replies()->first();
        $this->assertTrue($note->is_internal);
        $this->assertSame($admin->id, $note->user_id);

        $this->actingAs($owner)
            ->get(route('support.tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Relance automatique');
    }

    public function test_closing_a_ticket_does_not_erase_the_resolution_time(): void
    {
        Carbon::setTestNow('2026-10-20 10:00:00');
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->create([
            'status' => 'resolved',
            'resolved_at' => '2026-10-18 09:00:00',
            'closed_at' => null,
        ]);

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'closed',
            'priority' => $ticket->priority,
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame('closed', $ticket->status);
        $this->assertSame('2026-10-18 09:00:00', $ticket->resolved_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-20 10:00:00', $ticket->closed_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'status_changed',
        ]);
    }
};

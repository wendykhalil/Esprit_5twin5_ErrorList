<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupportTicketAssistantAndSolutionTest extends TestCase
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

    public function test_admin_analysis_stores_a_suggestion_without_changing_the_ticket(): void
    {
        config(['ollama.enabled' => true]);
        Http::fake([
            '*' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'category' => 'paiement',
                        'priority' => 'high',
                        'summary' => 'Le client ne parvient pas à finaliser son paiement.',
                        'suggested_reply' => 'Bonjour, nous vérifions votre paiement et revenons vers vous.',
                    ]),
                ],
            ]),
        ]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'priority' => 'low',
            'status' => 'open',
            'closed_at' => null,
        ]);
        $repliesBefore = TicketReply::count();

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('low', $ticket->priority);
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->closed_at);
        $this->assertNull($ticket->accepted_reply_id);
        $this->assertSame('paiement', $ticket->ai_category);
        $this->assertSame('high', $ticket->ai_suggested_priority);
        $this->assertSame('Le client ne parvient pas à finaliser son paiement.', $ticket->ai_summary);
        $this->assertNotNull($ticket->ai_analyzed_at);
        $this->assertSame($repliesBefore, TicketReply::count());

        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/api/chat')
                && ($request->data()['format'] ?? null) === 'json';
        });
    }

    public function test_analysis_does_not_send_internal_notes_to_ollama(): void
    {
        config(['ollama.enabled' => true]);
        Http::fake([
            '*' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'category' => 'technique',
                        'priority' => 'normal',
                        'summary' => 'Le client décrit une panne après une réponse publique.',
                        'suggested_reply' => 'Bonjour, nous examinons la panne signalée dans votre message.',
                    ]),
                ],
            ]),
        ]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'subject' => 'Panne du materiel',
            'message' => 'Le materiel ne demarre plus depuis hier matin.',
        ]);
        TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'is_internal' => true,
            'body' => 'NOTE-INTERNE-SECRETE',
        ]);
        TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'is_internal' => false,
            'body' => 'REPONSE-PUBLIQUE-VISIBLE',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect();

        Http::assertSent(function ($request) use ($ticket) {
            $payload = json_encode($request->data());

            return str_contains($payload, $ticket->subject)
                && str_contains($payload, $ticket->message)
                && str_contains($payload, 'REPONSE-PUBLIQUE-VISIBLE')
                && ! str_contains($payload, 'NOTE-INTERNE-SECRETE');
        });
    }

    public function test_invalid_ai_payload_keeps_the_existing_ticket_data(): void
    {
        config(['ollama.enabled' => true]);
        $analyzedAt = Carbon::parse('2026-10-18 09:00:00');
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'priority' => 'normal',
            'status' => 'in_progress',
            'ai_category' => 'compte',
            'ai_suggested_priority' => 'low',
            'ai_summary' => 'Ancien résumé encore valable.',
            'ai_suggested_reply' => 'Ancien brouillon encore valable.',
            'ai_analyzed_at' => $analyzedAt,
        ]);

        Http::fake([
            '*' => Http::response([
                'message' => ['content' => 'pas du json'],
            ]),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.tickets.show', $ticket))
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect(route('admin.tickets.show', $ticket))
            ->assertSessionHas('error');

        $ticket->refresh();
        $this->assertSame('normal', $ticket->priority);
        $this->assertSame('in_progress', $ticket->status);
        $this->assertSame('compte', $ticket->ai_category);
        $this->assertSame('low', $ticket->ai_suggested_priority);
        $this->assertSame('Ancien résumé encore valable.', $ticket->ai_summary);
        $this->assertSame('2026-10-18 09:00:00', $ticket->ai_analyzed_at->format('Y-m-d H:i:s'));
    }

    public function test_schema_invalid_ai_json_keeps_the_existing_ticket_data(): void
    {
        config(['ollama.enabled' => true]);
        Http::fake([
            '*' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'category' => 'inconnue',
                        'priority' => 'urgent',
                        'summary' => 'court',
                        'suggested_reply' => 'non',
                    ]),
                ],
            ]),
        ]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'priority' => 'low',
            'status' => 'open',
            'ai_summary' => 'Résumé précédent à conserver.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect()
            ->assertSessionHas('error');

        $ticket->refresh();
        $this->assertSame('low', $ticket->priority);
        $this->assertSame('open', $ticket->status);
        $this->assertSame('Résumé précédent à conserver.', $ticket->ai_summary);
        $this->assertNull($ticket->ai_analyzed_at);
    }

    public function test_ollama_http_error_keeps_the_existing_ticket_data(): void
    {
        config(['ollama.enabled' => true]);
        Http::fake([
            '*' => Http::response('unavailable', 500),
        ]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'priority' => 'high',
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.tickets.show', $ticket))
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect()
            ->assertSessionHas('error');

        $ticket->refresh();
        $this->assertSame('high', $ticket->priority);
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->ai_category);
        $this->assertNull($ticket->ai_analyzed_at);
    }

    public function test_ollama_connection_failure_keeps_the_existing_ticket_data(): void
    {
        config(['ollama.enabled' => true]);
        Http::fake(function () {
            throw new ConnectionException('Ollama is down');
        });

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create(['priority' => 'low']);

        $this->actingAs($admin)
            ->from(route('admin.tickets.show', $ticket))
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect()
            ->assertSessionHas('error');

        $ticket->refresh();
        $this->assertSame('low', $ticket->priority);
        $this->assertNull($ticket->ai_summary);
    }

    public function test_client_cannot_run_the_support_analysis(): void
    {
        config(['ollama.enabled' => true]);
        Http::fake();

        $client = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create(['user_id' => $client->id]);

        $this->actingAs($client)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertForbidden();

        Http::assertNothingSent();
        $this->assertNull($ticket->fresh()->ai_analyzed_at);
    }

    public function test_owner_can_accept_a_public_reply_as_the_solution(): void
    {
        Carbon::setTestNow('2026-10-19 10:00:00');
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'priority' => 'normal',
            'closed_at' => null,
        ]);
        $reply = TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'is_internal' => false,
            'body' => 'Nous avons corrigé le point signalé.',
        ]);

        $this->actingAs($owner)
            ->post(route('support.tickets.replies.accept', [$ticket, $reply]))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertSame($reply->id, $ticket->accepted_reply_id);
        $this->assertSame('normal', $ticket->priority);
        $this->assertSame('2026-10-19 10:00:00', $ticket->closed_at->format('Y-m-d H:i:s'));
    }

    public function test_a_second_acceptance_replaces_the_solution_without_overwriting_closed_at(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'closed_at' => null,
        ]);
        $first = TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
        ]);
        $second = TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
        ]);

        Carbon::setTestNow('2026-10-19 10:00:00');
        $this->actingAs($owner)->post(route('support.tickets.replies.accept', [$ticket, $first]));

        Carbon::setTestNow('2026-10-19 12:00:00');
        $this->actingAs($owner)->post(route('support.tickets.replies.accept', [$ticket, $second]))->assertRedirect();

        $ticket->refresh();
        $this->assertSame($second->id, $ticket->accepted_reply_id);
        $this->assertSame('resolved', $ticket->status);
        $this->assertSame('2026-10-19 10:00:00', $ticket->closed_at->format('Y-m-d H:i:s'));
    }

    public function test_internal_note_cannot_be_accepted(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'closed_at' => null,
        ]);
        $note = TicketReply::factory()->internal()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($owner)
            ->post(route('support.tickets.replies.accept', [$ticket, $note]))
            ->assertForbidden();

        $ticket->refresh();
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->accepted_reply_id);
        $this->assertNull($ticket->closed_at);
    }

    public function test_reply_from_another_ticket_cannot_be_accepted(): void
    {
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'closed_at' => null,
        ]);
        $foreignReply = TicketReply::factory()->create();

        $this->actingAs($owner)
            ->post(route('support.tickets.replies.accept', [$ticket, $foreignReply]))
            ->assertForbidden();

        $ticket->refresh();
        $foreignReply->refresh();
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->accepted_reply_id);
        $this->assertNull($foreignReply->acceptedForTicket);
    }

    public function test_closed_ticket_cannot_accept_a_solution(): void
    {
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $owner->id,
            'status' => 'closed',
            'closed_at' => '2026-10-01 08:00:00',
        ]);
        $reply = TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('support.tickets.replies.accept', [$ticket, $reply]))
            ->assertForbidden();

        $ticket->refresh();
        $this->assertSame('closed', $ticket->status);
        $this->assertNull($ticket->accepted_reply_id);
        $this->assertSame('2026-10-01 08:00:00', $ticket->closed_at->format('Y-m-d H:i:s'));
    }

    public function test_only_the_owner_can_accept_a_solution(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create(['user_id' => $owner->id]);
        $reply = TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->post(route('support.tickets.replies.accept', [$ticket, $reply]))
            ->assertForbidden();

        $this->assertNull($ticket->fresh()->accepted_reply_id);
    }

    public function test_owner_can_reopen_a_resolved_ticket_with_a_required_reason(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'closed_at' => null,
        ]);
        $reply = TicketReply::factory()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
        ]);

        Carbon::setTestNow('2026-10-19 10:00:00');
        $this->actingAs($owner)->post(route('support.tickets.replies.accept', [$ticket, $reply]));

        $this->actingAs($owner)
            ->from(route('support.tickets.show', $ticket))
            ->post(route('support.tickets.reopen', $ticket), ['motif' => 'court'])
            ->assertRedirect(route('support.tickets.show', $ticket))
            ->assertSessionHasErrors('motif');

        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertSame($reply->id, $ticket->accepted_reply_id);
        $this->assertSame('2026-10-19 10:00:00', $ticket->closed_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2026-10-19 15:00:00');
        $this->actingAs($owner)->post(route('support.tickets.reopen', $ticket), [
            'motif' => 'Le problème est revenu dès le lendemain.',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->accepted_reply_id);
        $this->assertNull($ticket->closed_at);
        $this->assertDatabaseHas('ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $owner->id,
            'body' => 'Le problème est revenu dès le lendemain.',
            'is_internal' => false,
        ]);
    }

    public function test_reopen_is_refused_unless_the_ticket_is_resolved(): void
    {
        $owner = User::factory()->create();

        foreach (['open', 'in_progress', 'closed'] as $status) {
            $ticket = SupportTicket::factory()->create([
                'user_id' => $owner->id,
                'status' => $status,
                'closed_at' => $status === 'closed' ? '2026-10-01 08:00:00' : null,
                'accepted_reply_id' => null,
            ]);
            $before = TicketReply::query()->where('support_ticket_id', $ticket->id)->count();

            $this->actingAs($owner)->post(route('support.tickets.reopen', $ticket), [
                'motif' => 'Je souhaite rouvrir cette demande maintenant.',
            ])->assertForbidden();

            $ticket->refresh();
            $this->assertSame($status, $ticket->status);
            $this->assertNull($ticket->accepted_reply_id);
            $this->assertSame($before, TicketReply::query()->where('support_ticket_id', $ticket->id)->count());

            if ($status === 'closed') {
                $this->assertSame('2026-10-01 08:00:00', $ticket->closed_at->format('Y-m-d H:i:s'));
            } else {
                $this->assertNull($ticket->closed_at);
            }
        }
    }
};

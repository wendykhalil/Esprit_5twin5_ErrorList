<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupportTicketAssistantReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['ollama.enabled' => true]);
    }

    public function test_a_valid_analysis_stores_the_reason_the_gaps_and_the_confidence(): void
    {
        Http::fake(['*' => Http::response($this->ollama([
            'priority_reason' => 'Le client signale un debit sans confirmation de reservation.',
            'missing_information' => [
                'La reference de reservation, si elle existe',
                'La date approximative du paiement',
            ],
            'confidence' => 0.64,
        ]))]);

        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'priority' => 'low',
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect()
            ->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame('low', $ticket->priority);
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->resolved_at);
        $this->assertNull($ticket->closed_at);
        $this->assertSame(0, $ticket->replies()->count());
        $this->assertSame('Le client signale un debit sans confirmation de reservation.', $ticket->ai_priority_reason);
        $this->assertSame([
            'La reference de reservation, si elle existe',
            'La date approximative du paiement',
        ], $ticket->ai_missing_information);
        $this->assertEquals(0.64, $ticket->ai_confidence);
        $this->assertTrue($ticket->aiAnalysisIsCurrent());

        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'type' => 'ai_analysis_completed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Justification')
            ->assertSee('64 %')
            ->assertSee('La date approximative du paiement')
            ->assertSee('Suggestion uniquement')
            ->assertSee('type="button" id="use-ai-draft"', false);

        $this->actingAs($owner)
            ->get(route('support.tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Le client signale un debit sans confirmation de reservation.');
    }

    public function test_a_legacy_analysis_does_not_claim_missing_information_was_reviewed(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'ai_summary' => 'Ancien resume sans liste.',
            'ai_analyzed_at' => now(),
            'ai_context_hash' => null,
            'ai_missing_information' => null,
            'ai_confidence' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Non évaluées pour cette analyse.')
            ->assertSee('Confiance non évaluée')
            ->assertSee('Relancer l\'analyse')
            ->assertDontSee('Aucune information manquante signalée.');
    }

    public function test_invalid_confidence_is_not_stored_as_a_valid_estimate(): void
    {
        Http::fake(['*' => Http::response($this->ollama(['confidence' => 4]))]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create();

        $this->actingAs($admin)->post(route('admin.tickets.analyze', $ticket))->assertRedirect();

        $ticket->refresh();
        $this->assertNull($ticket->ai_confidence);
        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Confiance non évaluée')
            ->assertDontSee('400 %');
    }

    public function test_too_many_missing_questions_are_rejected(): void
    {
        Http::fake(['*' => Http::response($this->ollama([
            'missing_information' => ['Un', 'Deux', 'Trois', 'Quatre', 'Cinq', 'Six'],
        ]))]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'ai_summary' => 'Résumé précédent à conserver.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('Résumé précédent à conserver.', $ticket->fresh()->ai_summary);
        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'ai_analysis_failed',
        ]);
    }

    public function test_an_unknown_json_key_is_rejected(): void
    {
        $payload = $this->suggestion();
        $payload['refund_amount'] = '50 TND';

        Http::fake(['*' => Http::response($this->ollama($payload, false))]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'ai_summary' => 'Résumé précédent à conserver.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertSessionHas('error');

        $this->assertSame('Résumé précédent à conserver.', $ticket->fresh()->ai_summary);
    }

    public function test_a_public_reply_during_analysis_marks_the_result_as_stale(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => $owner->id,
            'priority' => 'normal',
        ]);

        Http::fake(function () use ($ticket, $owner) {
            TicketReply::factory()->create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $owner->id,
                'is_internal' => false,
                'body' => 'Le paiement a finalement ete confirme.',
            ]);

            return Http::response($this->ollama());
        });

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertRedirect()
            ->assertSessionHas('warning');

        $ticket->refresh();
        $this->assertSame('normal', $ticket->priority);
        $this->assertFalse($ticket->aiAnalysisIsCurrent());
        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertSee('Relancer l\'analyse')
            ->assertSee('Analyse IA obsolète')
            ->assertSee('Suggestion conservée, à ne pas utiliser telle quelle');
    }

    public function test_an_internal_note_during_analysis_does_not_make_it_stale(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create();

        Http::fake(function () use ($ticket, $admin) {
            TicketReply::factory()->internal()->create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $admin->id,
                'body' => 'NOTE-INTERNE-PENDANT-ANALYSE',
            ]);

            return Http::response($this->ollama());
        });

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertSessionHas('success');

        $this->assertTrue($ticket->fresh()->aiAnalysisIsCurrent());

        Http::assertSent(function ($request) {
            return ! str_contains(json_encode($request->data()), 'NOTE-INTERNE-PENDANT-ANALYSE');
        });
    }

    public function test_a_slower_analysis_does_not_overwrite_one_that_finished_first(): void
    {
        $frozen = Carbon::parse('2026-10-21 01:15:00');
        Carbon::setTestNow($frozen);

        try {
            $admin = User::factory()->admin()->create();
            $ticket = SupportTicket::factory()->open()->create([
                'priority' => 'low',
                'status' => 'open',
                'ai_summary' => 'Resultat deja present.',
                'ai_analyzed_at' => $frozen,
            ]);

            $phase = 'A';
            Http::fake(function () use (&$phase, $admin, $ticket) {
                if ($phase === 'A') {
                    $phase = 'B';
                    $this->actingAs($admin)->post(route('admin.tickets.analyze', $ticket));

                    return Http::response($this->ollama([
                        'summary' => 'Resultat A trop tardif pour remplacer B.',
                    ]));
                }

                return Http::response($this->ollama([
                    'summary' => 'Resultat B arrive en premier.',
                ]));
            });

            $this->actingAs($admin)
                ->post(route('admin.tickets.analyze', $ticket))
                ->assertRedirect()
                ->assertSessionHas('warning');

            $ticket->refresh();
            $this->assertSame('Resultat B arrive en premier.', $ticket->ai_summary);
            $this->assertSame(1, (int) $ticket->ai_analysis_generation);
            $this->assertSame('low', $ticket->priority);
            $this->assertSame('open', $ticket->status);
            $this->assertDatabaseHas('ticket_events', [
                'support_ticket_id' => $ticket->id,
                'type' => 'ai_analysis_discarded',
            ]);
            $this->assertSame(1, $ticket->events()->where('type', 'ai_analysis_completed')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_an_older_analysis_does_not_replace_a_newer_result(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'ai_summary' => 'Resultat recent a conserver.',
            'ai_analyzed_at' => now()->subHour(),
        ]);

        Http::fake(function () use ($ticket) {
            $ticket->forceFill([
                'ai_summary' => 'Resultat encore plus recent.',
                'ai_analyzed_at' => now(),
            ])->save();

            return Http::response($this->ollama([
                'summary' => 'Ancien resultat qui ne doit pas ecraser le recent.',
            ]));
        });

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertSessionHas('warning');

        $this->assertSame('Resultat encore plus recent.', $ticket->fresh()->ai_summary);
        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'ai_analysis_discarded',
        ]);
    }

    public function test_suggested_priority_is_saved_only_by_the_admin_update(): void
    {
        Http::fake(['*' => Http::response($this->ollama(['priority' => 'high']))]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create([
            'priority' => 'low',
            'status' => 'open',
        ]);

        $this->actingAs($admin)->post(route('admin.tickets.analyze', $ticket));
        $this->assertSame('low', $ticket->fresh()->priority);
        $this->assertDatabaseMissing('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'priority_changed',
        ]);

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'high',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame('high', $ticket->priority);
        $this->assertSame('open', $ticket->status);

        $event = $ticket->events()->where('type', 'priority_changed')->first();
        $this->assertNotNull($event);
        $this->assertTrue($event->payload['matches_ai_suggestion']);
        $this->assertSame('admin', $event->payload['source']);
    }

    public function test_a_draft_is_sent_only_when_the_admin_submits_the_reply_form(): void
    {
        $draft = 'Bonjour, nous allons verifier les informations de votre demande.';
        Http::fake(['*' => Http::response($this->ollama(['suggested_reply' => $draft]))]);

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create();

        $this->actingAs($admin)->post(route('admin.tickets.analyze', $ticket));
        $this->assertSame(0, $ticket->replies()->count());
        $this->assertDatabaseMissing('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'ai_draft_sent',
        ]);

        $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => 'Texte modifie par l administrateur avant envoi.',
            'is_internal' => '0',
        ])->assertRedirect();

        $this->assertSame(1, $ticket->replies()->count());
        $this->assertDatabaseMissing('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'ai_draft_sent',
        ]);

        $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => $draft,
            'is_internal' => '1',
        ])->assertRedirect();

        $this->assertDatabaseMissing('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'ai_draft_sent',
        ]);

        $this->actingAs($admin)->post(route('admin.tickets.replies.store', $ticket), [
            'body' => $draft,
            'is_internal' => '0',
        ])->assertRedirect();

        $this->assertSame(3, $ticket->replies()->count());
        $this->assertSame(1, $ticket->events()->where('type', 'ai_draft_sent')->count());
    }

    public function test_disabled_assistant_does_not_call_ollama_or_change_the_ticket(): void
    {
        config(['ollama.enabled' => false]);
        Http::fake();

        $admin = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->open()->create(['priority' => 'normal']);

        $this->actingAs($admin)
            ->post(route('admin.tickets.analyze', $ticket))
            ->assertSessionHas('error');

        Http::assertNothingSent();
        $this->assertSame('normal', $ticket->fresh()->priority);
        $this->assertDatabaseHas('ticket_events', [
            'support_ticket_id' => $ticket->id,
            'type' => 'ai_analysis_failed',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function suggestion(array $overrides = []): array
    {
        return array_replace([
            'category' => 'paiement',
            'priority' => 'high',
            'priority_reason' => 'Le client signale un debit sans confirmation de reservation.',
            'summary' => 'Le client indique avoir paye sans recevoir de confirmation.',
            'suggested_reply' => 'Bonjour, nous allons verifier les informations de votre demande.',
            'missing_information' => ['La date approximative du paiement'],
            'confidence' => 0.5,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function ollama(array $overrides = [], bool $merge = true): array
    {
        $content = $merge ? $this->suggestion($overrides) : $overrides;

        return [
            'message' => [
                'content' => json_encode($content),
            ],
        ];
    }
}

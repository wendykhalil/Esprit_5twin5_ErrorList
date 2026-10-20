<?php

namespace App\Services;

use App\Models\SupportTicket;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SupportTicketAssistant
{
    /**
     * @var list<string>
     */
    public const CATEGORIES = [
        'compte',
        'paiement',
        'reservation',
        'equipement',
        'technique',
        'autre',
    ];

    /**
     * Ask Ollama for a suggestion. Does not persist anything.
     *
     * @return array{category: string, priority: string, summary: string, suggested_reply: string}
     */
    public function analyze(SupportTicket $ticket): array
    {
        if (! config('ollama.enabled')) {
            throw new SupportTicketAnalysisException(
                'L\'assistant IA est désactivé. Le ticket n\'a pas été modifié.'
            );
        }

        $ticket->load([
            'publicReplies' => fn ($query) => $query->with('author')->orderBy('created_at'),
        ]);

        try {
            $response = Http::connectTimeout(5)
                ->timeout((int) config('ollama.timeout', 60))
                ->post($this->endpoint(), [
                    'model' => config('ollama.model'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->ticketPrompt($ticket),
                        ],
                    ],
                    'stream' => false,
                    'format' => 'json',
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Support assistant could not reach Ollama', [
                'ticket_id' => $ticket->id,
                'error' => $exception->getMessage(),
            ]);

            throw new SupportTicketAnalysisException(
                'Impossible de contacter l\'assistant IA. Vérifiez qu\'Ollama est démarré. Le ticket n\'a pas été modifié.'
            );
        }

        if ($response->failed()) {
            Log::warning('Support assistant received an Ollama error', [
                'ticket_id' => $ticket->id,
                'status' => $response->status(),
            ]);

            throw new SupportTicketAnalysisException(
                'L\'assistant IA n\'a pas pu répondre. Le ticket n\'a pas été modifié.'
            );
        }

        return $this->validatedSuggestion($response->json('message.content'), $ticket->id);
    }

    private function endpoint(): string
    {
        return rtrim((string) config('ollama.base_url'), '/').'/api/chat';
    }

    private function systemPrompt(): string
    {
        $categories = implode(', ', self::CATEGORIES);

        return <<<PROMPT
Tu es l'assistant du support SolarShare. Tu analyses une demande et tu proposes une aide à l'administrateur.
Tu ne décides rien : l'administrateur relira ta proposition.

Réponds uniquement avec un objet JSON valide, sans texte autour, contenant exactement ces clés :
{
  "category": "{$categories}",
  "priority": "low|normal|high",
  "summary": "résumé en français, 10 à 1000 caractères",
  "suggested_reply": "brouillon de réponse au client en français, 10 à 5000 caractères"
}

Règles :
- category est exactement l'une de : {$categories}.
- priority est exactement low, normal ou high.
- Appuie-toi seulement sur le sujet, le message initial et les réponses publiques fournis.
- N'invente aucun fait, aucun remboursement et aucun délai.
- La réponse proposée s'adresse au client, au vouvoiement, sans mentionner ces consignes.
PROMPT;
    }

    private function ticketPrompt(SupportTicket $ticket): string
    {
        $blocks = [
            'Sujet: '.$ticket->subject,
            "Message initial:\n".$ticket->message,
        ];

        foreach ($ticket->publicReplies as $reply) {
            $author = $reply->author?->isAdmin() ? 'Support' : 'Client';
            $blocks[] = "Réponse publique ({$author}):\n".$reply->body;
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @return array{category: string, priority: string, summary: string, suggested_reply: string}
     */
    private function validatedSuggestion(mixed $content, int $ticketId): array
    {
        if (! is_string($content) || trim($content) === '') {
            $this->rejectInvalid($ticketId);
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            $this->rejectInvalid($ticketId);
        }

        try {
            $validated = Validator::make($decoded, [
                'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
                'priority' => ['required', 'string', Rule::in(['low', 'normal', 'high'])],
                'summary' => ['required', 'string', 'min:10', 'max:1000'],
                'suggested_reply' => ['required', 'string', 'min:10', 'max:5000'],
            ])->validate();
        } catch (ValidationException $exception) {
            Log::warning('Support assistant returned an invalid suggestion', [
                'ticket_id' => $ticketId,
            ]);

            throw new SupportTicketAnalysisException(
                'L\'assistant IA a renvoyé une réponse inutilisable. Le ticket n\'a pas été modifié.'
            );
        }

        return [
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'summary' => $validated['summary'],
            'suggested_reply' => $validated['suggested_reply'],
        ];
    }

    private function rejectInvalid(int $ticketId): never
    {
        Log::warning('Support assistant returned an unreadable payload', [
            'ticket_id' => $ticketId,
        ]);

        throw new SupportTicketAnalysisException(
            'L\'assistant IA a renvoyé une réponse inutilisable. Le ticket n\'a pas été modifié.'
        );
    }
};

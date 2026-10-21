<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    /**
     * Délai de première réponse publique, en heures, selon la priorité.
     *
     * @var array<string, int>
     */
    public const SLA_HOURS = [
        'high' => 4,
        'normal' => 24,
        'low' => 72,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'attachment',
        'priority',
        'status',
        'closed_at',
        'resolved_at',
        'first_response_due_at',
        'first_response_at',
        'sla_breached_at',
        'reopen_count',
        'last_reopened_at',
        'satisfaction_score',
        'satisfaction_comment',
        'satisfaction_rated_at',
        'last_reminded_at',
        'ai_category',
        'ai_suggested_priority',
        'ai_summary',
        'ai_suggested_reply',
        'ai_priority_reason',
        'ai_missing_information',
        'ai_confidence',
        'ai_context_hash',
        'ai_analysis_generation',
        'ai_analyzed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            if (blank($ticket->reference)) {
                $ticket->reference = 'TCK-PENDING-'.Str::lower(Str::random(12));
            }

            if ($ticket->first_response_due_at === null) {
                $hours = self::SLA_HOURS[$ticket->priority ?? 'normal'] ?? self::SLA_HOURS['normal'];
                $ticket->first_response_due_at = now()->addHours($hours);
            }
        });

        static::created(function (SupportTicket $ticket) {
            $ticket->forceFill([
                'reference' => sprintf('TCK-%06d', $ticket->id),
            ])->saveQuietly();
        });

        static::deleting(function (SupportTicket $ticket) {
            if ($ticket->accepted_reply_id === null) {
                return;
            }

            $ticket->newQuery()->whereKey($ticket->id)->update([
                'accepted_reply_id' => null,
            ]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'first_response_due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'sla_breached_at' => 'datetime',
            'last_reopened_at' => 'datetime',
            'satisfaction_rated_at' => 'datetime',
            'last_reminded_at' => 'datetime',
            'reopen_count' => 'integer',
            'satisfaction_score' => 'integer',
            'ai_analyzed_at' => 'datetime',
            'ai_missing_information' => 'array',
            'ai_confidence' => 'float',
            'ai_analysis_generation' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function acceptedReply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class, 'accepted_reply_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class)->orderBy('created_at');
    }

    /**
     * Réponses visibles par le client (jamais de notes internes).
     */
    public function publicReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', false);
    }

    /**
     * Empreinte du contexte public réellement analysable.
     * Les notes internes n'entrent pas dans ce calcul.
     */
    public function publicContextFingerprint(): string
    {
        $this->refresh();

        $replies = $this->publicReplies()
            ->where('support_ticket_id', $this->id)
            ->where('is_internal', false)
            ->orderBy('id')
            ->get(['id', 'support_ticket_id', 'body', 'is_internal']);

        $payload = [
            'subject' => $this->subject,
            'message' => $this->message,
            'replies' => $replies
                ->map(fn (TicketReply $reply) => $reply->id.'|'.$reply->body)
                ->values()
                ->all(),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public function aiAnalysisIsCurrent(): bool
    {
        if ($this->ai_analyzed_at === null || $this->ai_context_hash === null) {
            return false;
        }

        return hash_equals($this->ai_context_hash, $this->publicContextFingerprint());
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function acceptsReplies(): bool
    {
        return ! $this->isClosed();
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment);
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->hasAttachment()) {
            return null;
        }

        return asset('storage/'.$this->attachment);
    }

    /**
     * Sépare la résolution de la fermeture.
     * resolved_at marque la solution. closed_at marque uniquement le statut closed.
     */
    public function syncClosedTimestampForStatus(string $status): void
    {
        if ($status === 'resolved') {
            $this->resolved_at = $this->resolved_at ?? now();
            $this->closed_at = null;

            return;
        }

        if ($status === 'closed') {
            $this->closed_at = $this->closed_at ?? now();

            return;
        }

        $this->resolved_at = null;
        $this->closed_at = null;
    }

    public function recordEvent(string $type, ?int $userId = null, array $payload = []): void
    {
        $this->events()->create([
            'user_id' => $userId,
            'type' => $type,
            'payload' => $payload === [] ? null : $payload,
        ]);
    }

    public function hasDuplicatePublicBody(string $body): bool
    {
        $normalized = TicketReply::normalizeBody($body);

        if ($normalized === '') {
            return false;
        }

        return $this->publicReplies()
            ->pluck('body')
            ->contains(fn (string $existing) => TicketReply::normalizeBody($existing) === $normalized);
    }

    public static function findSimilarOpen(int $userId, string $subject, string $message): ?self
    {
        $needle = TicketReply::normalizeBody($subject.' '.$message);

        if ($needle === '') {
            return null;
        }

        return self::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['open', 'in_progress'])
            ->latest('id')
            ->limit(20)
            ->get()
            ->first(function (self $candidate) use ($needle) {
                $percent = 0.0;
                similar_text(
                    $needle,
                    TicketReply::normalizeBody($candidate->subject.' '.$candidate->message),
                    $percent
                );

                return $percent >= 70;
            });
    }

    /**
     * Après réponse admin : open → in_progress.
     */
    public function markInProgressIfOpen(): void
    {
        if ($this->status === 'open') {
            $this->update(['status' => 'in_progress']);
        }
    }

    /**
     * @param  Builder<SupportTicket>  $query
     * @return Builder<SupportTicket>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * @param  Builder<SupportTicket>  $query
     * @return Builder<SupportTicket>
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * @param  Builder<SupportTicket>  $query
     * @return Builder<SupportTicket>
     */
    public function scopeByPriority(Builder $query, ?string $priority): Builder
    {
        if ($priority === null || $priority === '') {
            return $query;
        }

        return $query->where('priority', $priority);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

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
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            if (blank($ticket->reference)) {
                $ticket->reference = 'TCK-PENDING-'.Str::lower(Str::random(12));
            }
        });

        static::created(function (SupportTicket $ticket) {
            $ticket->forceFill([
                'reference' => sprintf('TCK-%06d', $ticket->id),
            ])->saveQuietly();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
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

    /**
     * Réponses visibles par le client (jamais de notes internes).
     */
    public function publicReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', false);
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
     * Met à jour closed_at selon le statut (resolved / closed vs réouverture).
     */
    public function syncClosedTimestampForStatus(string $status): void
    {
        if (in_array($status, ['closed', 'resolved'], true)) {
            $this->closed_at = $this->closed_at ?? now();

            return;
        }

        $this->closed_at = null;
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

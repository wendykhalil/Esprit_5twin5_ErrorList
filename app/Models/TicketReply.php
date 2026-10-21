<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TicketReply extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'body',
        'is_internal',
        'attachment',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function acceptedForTicket(): HasOne
    {
        return $this->hasOne(SupportTicket::class, 'accepted_reply_id');
    }

    /**
     * @param  Builder<TicketReply>  $query
     * @return Builder<TicketReply>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }

    public static function normalizeBody(string $body): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim(mb_strtolower($body)));

        return $normalized ?? '';
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

    public function attachmentIsPdf(): bool
    {
        return $this->hasAttachment() && str_ends_with(strtolower((string) $this->attachment), '.pdf');
    }
}

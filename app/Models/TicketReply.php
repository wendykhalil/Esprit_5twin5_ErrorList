<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @param  Builder<TicketReply>  $query
     * @return Builder<TicketReply>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }
}

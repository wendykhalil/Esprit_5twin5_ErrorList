<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'equipment_id',
        'contract_number',
        'start_date',
        'end_date',
        'amount',
        'status',
        'terms_and_conditions',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'amount' => 'decimal:2',
            'accepted_at' => 'datetime',
        ];
    }

    // Constants for contract statuses
    public const STATUTS = [
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Get the reservation this contract belongs to.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user who owns this contract (the client).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the equipment this contract is for.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Scope to get contracts for a specific user (client access).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get active contracts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Generate a unique contract number.
     */
    public static function generateContractNumber(): string
    {
        $lastContract = self::orderBy('id', 'desc')->first();
        $nextNumber = ($lastContract?->id ?? 0) + 1;
        return 'CONT-' . now()->format('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Accept the contract and record the acceptance timestamp.
     * This triggers the delivery creation workflow.
     *
     * @return self
     */
    public function accept(): self
    {
        $this->update(['accepted_at' => now()]);
        return $this;
    }

    /**
     * Check if the contract has been accepted by the client.
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}

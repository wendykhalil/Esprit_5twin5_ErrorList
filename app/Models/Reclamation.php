<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reclamation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'title',
        'description',
        'status',
        'refund_amount',
        'refund_approved_at',
        'admin_notes',
        'rejected_reason',
        'rejected_at',
    ];

    protected $casts = [
        'refund_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Statuts possibles d'une réclamation
     */
    public const STATUSES = [
        'en_attente',
        'en_cours',
        'approuvee',
        'rejetee',
        'fermee',
    ];

    /**
     * Get the reservation associated with this reclamation.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user (client) who created this reclamation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all incidents related to this reclamation.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get the compensation code assigned to this reclamation (if approved).
     */
    public function compensationCode(): HasOne
    {
        return $this->hasOne(CompensationCode::class);
    }

    /**
     * Get the associated payment (convenience method).
     */
    public function payment()
    {
        return $this->reservation?->payments()
            ->where('status', 'paid')
            ->first();
    }

    /**
     * Calculate total refund from all incidents.
     *
     * @deprecated This method is kept for backward compatibility with existing tests/code.
     * New code should use compensation codes instead.
     */
    public function calculateTotalRefund(): float
    {
        return (float) $this->incidents->sum('proposed_refund_amount');
    }

    /**
     * Check if reclamation can be approved (has payment and within limits).
     *
     * @deprecated This method is kept for backward compatibility with existing tests/code.
     * New code should use canReceiveCompensation() instead.
     */
    public function canBeApproved(): bool
    {
        $payment = $this->payment();
        if (!$payment) {
            return false;
        }

        $totalRefunded = $payment->transactions()
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->sum('amount');

        $remainingRefundable = $payment->amount - $totalRefunded;
        return $this->calculateTotalRefund() <= $remainingRefundable;
    }

    /**
     * Check if reclamation can receive a compensation code.
     * A reclamation can receive compensation if:
     * - It's in a state that can be approved (en_attente or en_cours)
     * - It doesn't already have a compensation code assigned
     */
    public function canReceiveCompensation(): bool
    {
        // Only en_attente or en_cours can be approved
        if (!in_array($this->status, ['en_attente', 'en_cours'])) {
            return false;
        }

        // Must not already have a compensation code
        return $this->compensationCode === null;
    }
}

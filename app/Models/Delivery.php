<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    /**
     * Delivery statuses.
     * Business rule: Delivery lifecycle for equipment hand-over and return
     * 
     * Initial status on creation is now 'prete' (Ready) instead of 'a_preparer'
     * because delivery is created after client accepts the contract, meaning
     * equipment is ready for handover.
     */
    public const STATUTS = [
        'prete' => 'Prête',                     // Initial state: Ready for pickup
        'remise_au_client' => 'Remise au client', // Handed over to client
        'retour_recu' => 'Retour reçu',         // Equipment returned
        'terminee' => 'Terminée',               // Delivery/return complete
    ];

    protected $fillable = [
        'reservation_id',
        'user_id',
        'equipment_id',
        'planned_delivery_date',
        'actual_delivery_date',
        'planned_return_date',
        'actual_return_date',
        'status',
        'notes',
        'delivery_notes',
        'return_notes',
    ];

    protected $casts = [
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'datetime',
        'planned_return_date' => 'date',
        'actual_return_date' => 'datetime',
    ];

    /**
     * Get the reservation this delivery belongs to.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the client (user) for this delivery.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the equipment being delivered.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Check if delivery has been handed over to client.
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, ['remise_au_client', 'retour_recu', 'terminee']);
    }

    /**
     * Check if return has been received.
     */
    public function isReturned(): bool
    {
        return in_array($this->status, ['retour_recu', 'terminee']);
    }

    /**
     * Check if delivery is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'terminee';
    }

    /**
     * Get valid status transitions from current status.
     * 
     * State machine:
     * - prete (initial) → remise_au_client
     * - remise_au_client → retour_recu
     * - retour_recu → terminee
     * - terminee → (terminal, no transitions)
     */
    public function getValidTransitions(): array
    {
        return match ($this->status) {
            'prete' => ['remise_au_client'],
            'remise_au_client' => ['retour_recu'],
            'retour_recu' => ['terminee'],
            'terminee' => [],
            default => [],
        };
    }

    /**
     * Check if a status transition is valid.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, $this->getValidTransitions());
    }
}

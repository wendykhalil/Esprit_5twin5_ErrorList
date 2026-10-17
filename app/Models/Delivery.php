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
     * Workflow stages:
     * 1. a_preparer - Equipment being prepared (admin only)
     * 2. prete - Equipment ready for pickup (admin only)
     * 3. remise_au_client - Client has confirmed receipt
     * 4. retour_recu - Equipment returned, admin confirmed receipt
     * 5. terminee - Transaction complete
     */
    public const STATUTS = [
        'a_preparer' => 'À préparer',           // Being prepared
        'prete' => 'Prête',                     // Ready for pickup
        'remise_au_client' => 'Remise au client', // Handed over to client (client confirmed)
        'retour_recu' => 'Retour reçu',         // Equipment returned and received
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
        'prepared_at',
        'ready_at',
        'client_received_at',
    ];

    protected $casts = [
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'datetime',
        'planned_return_date' => 'date',
        'actual_return_date' => 'datetime',
        'prepared_at' => 'datetime',
        'ready_at' => 'datetime',
        'client_received_at' => 'datetime',
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
     * State machine (5-step workflow):
     * - a_preparer (initial) → prete
     * - prete → remise_au_client (client confirms receipt)
     * - remise_au_client → retour_recu (admin marks return received)
     * - retour_recu → terminee (admin completes)
     * - terminee → (terminal, no transitions)
     */
    public function getValidTransitions(): array
    {
        return match ($this->status) {
            'a_preparer' => ['prete'],
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

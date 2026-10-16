<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation $reservation;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Reservation $reservation, string $oldStatus, string $newStatus)
    {
        $this->reservation = $reservation->load(['equipment']);
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to both admin channel and the reservation owner (private channel)
        return [
            new PrivateChannel('admin.reservations'),
            new PrivateChannel('user.' . $this->reservation->user_id . '.reservations'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->reservation->id,
            'equipment_id' => $this->reservation->equipment_id,
            'equipment_name' => $this->reservation->equipment->name,
            'date_debut' => $this->reservation->date_debut->format('Y-m-d'),
            'date_fin' => $this->reservation->date_fin->format('Y-m-d'),
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'statut' => $this->reservation->statut,
            'prix_total' => $this->reservation->prix_total,
            'updated_at' => $this->reservation->updated_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'reservation-status-changed';
    }
}

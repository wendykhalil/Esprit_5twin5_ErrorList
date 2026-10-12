<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation $reservation;

    /**
     * Create a new event instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load(['user', 'equipment']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.reservations'),
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
            'user_id' => $this->reservation->user_id,
            'equipment_id' => $this->reservation->equipment_id,
            'date_debut' => $this->reservation->date_debut,
            'date_fin' => $this->reservation->date_fin,
            'statut' => $this->reservation->statut,
            'prix_total' => $this->reservation->prix_total,
            'user' => [
                'id' => $this->reservation->user->id,
                'name' => $this->reservation->user->name,
                'email' => $this->reservation->user->email,
            ],
            'equipment' => [
                'id' => $this->reservation->equipment->id,
                'name' => $this->reservation->equipment->name,
            ],
            'created_at' => $this->reservation->created_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'reservation-created';
    }
}

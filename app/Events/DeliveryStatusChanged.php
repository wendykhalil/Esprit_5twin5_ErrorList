<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;
    public string $oldStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Delivery $delivery, string $oldStatus)
    {
        $this->delivery = $delivery->load(['user', 'reservation', 'equipment']);
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.deliveries'),
        ];

        // Broadcast to the user's private channel for personal notifications
        if ($this->delivery->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->delivery->user_id . '.deliveries');
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->delivery->id,
            'reservation_id' => $this->delivery->reservation_id,
            'user_id' => $this->delivery->user_id,
            'equipment_id' => $this->delivery->equipment_id,
            'status' => $this->delivery->status,
            'old_status' => $this->oldStatus,
            'scheduled_date' => $this->delivery->scheduled_date,
            'delivered_date' => $this->delivery->delivered_date,
            'user' => [
                'id' => $this->delivery->user->id,
                'name' => $this->delivery->user->name,
            ],
            'updated_at' => $this->delivery->updated_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'delivery-status-changed';
    }
}

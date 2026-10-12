<?php

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Contract $contract;

    /**
     * Create a new event instance.
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract->load(['user', 'reservation', 'equipment']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.contracts'),
        ];

        // Broadcast to the user's private channel
        if ($this->contract->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->contract->user_id . '.contracts');
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
            'id' => $this->contract->id,
            'user_id' => $this->contract->user_id,
            'reservation_id' => $this->contract->reservation_id,
            'equipment_id' => $this->contract->equipment_id,
            'status' => $this->contract->status,
            'signed_at' => $this->contract->signed_at,
            'user' => [
                'id' => $this->contract->user->id,
                'name' => $this->contract->user->name,
            ],
            'reservation' => [
                'id' => $this->contract->reservation->id,
            ],
            'created_at' => $this->contract->created_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'contract-created';
    }
}

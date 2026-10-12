<?php

namespace App\Events;

use App\Models\Reclamation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReclamationApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reclamation $reclamation;

    /**
     * Create a new event instance.
     */
    public function __construct(Reclamation $reclamation)
    {
        $this->reclamation = $reclamation->load(['user', 'compensationCode']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.reclamations'),
        ];

        // Broadcast to the user's private channel for personal notification
        if ($this->reclamation->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->reclamation->user_id . '.reclamations');
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
        $compensationCode = null;
        if ($this->reclamation->compensationCode) {
            $compensationCode = [
                'id' => $this->reclamation->compensationCode->id,
                'code' => $this->reclamation->compensationCode->code,
                'type' => $this->reclamation->compensationCode->type,
                'value' => $this->reclamation->compensationCode->value,
                'valid_until' => $this->reclamation->compensationCode->valid_until,
            ];
        }

        return [
            'id' => $this->reclamation->id,
            'user_id' => $this->reclamation->user_id,
            'status' => $this->reclamation->status,
            'refund_amount' => $this->reclamation->refund_amount,
            'compensation_code' => $compensationCode,
            'updated_at' => $this->reclamation->updated_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'reclamation-approved';
    }
}

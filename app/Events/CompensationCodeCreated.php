<?php

namespace App\Events;

use App\Models\CompensationCode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompensationCodeCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CompensationCode $compensationCode;

    /**
     * Create a new event instance.
     */
    public function __construct(CompensationCode $compensationCode)
    {
        $this->compensationCode = $compensationCode->load(['user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.compensation-codes'),
        ];

        // Only send to the owner's personal channel if this code is assigned to a specific user
        if ($this->compensationCode->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->compensationCode->user_id . '.compensation-codes');
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
            'id' => $this->compensationCode->id,
            'code' => $this->compensationCode->code,
            'type' => $this->compensationCode->type,
            'value' => $this->compensationCode->value,
            'valid_from' => $this->compensationCode->valid_from,
            'valid_until' => $this->compensationCode->valid_until,
            'max_uses' => $this->compensationCode->max_uses,
            'uses_count' => $this->compensationCode->uses_count,
            'is_active' => $this->compensationCode->is_active,
            'user_id' => $this->compensationCode->user_id,
            'reclamation_id' => $this->compensationCode->reclamation_id,
            'created_at' => $this->compensationCode->created_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'compensation-code-created';
    }
}

<?php

namespace App\Events;

use App\Models\Reclamation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReclamationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reclamation $reclamation;

    /**
     * Create a new event instance.
     */
    public function __construct(Reclamation $reclamation)
    {
        $this->reclamation = $reclamation->load(['user', 'reservation', 'incidents']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.reclamations'),
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
            'id' => $this->reclamation->id,
            'user_id' => $this->reclamation->user_id,
            'reservation_id' => $this->reclamation->reservation_id,
            'title' => $this->reclamation->title,
            'description' => $this->reclamation->description,
            'status' => $this->reclamation->status,
            'incidents_count' => $this->reclamation->incidents->count(),
            'user' => [
                'id' => $this->reclamation->user->id,
                'name' => $this->reclamation->user->name,
                'email' => $this->reclamation->user->email,
            ],
            'created_at' => $this->reclamation->created_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'reclamation-created';
    }
}

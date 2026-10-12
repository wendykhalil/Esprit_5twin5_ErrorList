<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment->load(['user', 'reservation']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.payments'),
        ];

        // Also broadcast to the user's private channel for personal notifications
        if ($this->payment->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->payment->user_id . '.payments');
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
            'id' => $this->payment->id,
            'user_id' => $this->payment->user_id,
            'reservation_id' => $this->payment->reservation_id,
            'amount' => $this->payment->amount,
            'method' => $this->payment->method,
            'status' => $this->payment->status,
            'payment_date' => $this->payment->payment_date,
            'user' => [
                'id' => $this->payment->user->id,
                'name' => $this->payment->user->name,
            ],
            'reservation' => [
                'id' => $this->payment->reservation->id,
                'equipment_id' => $this->payment->reservation->equipment_id,
            ],
            'created_at' => $this->payment->created_at,
        ];
    }

    /**
     * Get the name of the channel to emit the broadcast on.
     */
    public function broadcastAs(): string
    {
        return 'payment-processed';
    }
}

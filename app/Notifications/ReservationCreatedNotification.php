<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Reservation $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load(['user', 'equipment']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Determine if the notification should be sent.
     * Prevents duplicate notifications for the same reservation.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        // Check if an unread notification for this reservation already exists
        $existingNotification = $notifiable
            ->unreadNotifications()
            ->whereJsonContains('data->reservation_id', $this->reservation->id)
            ->whereJsonContains('data->type', 'reservation_created')
            ->exists();

        return !$existingNotification;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->reservation->user_id,
            'user_name' => $this->reservation->user->name,
            'equipment_id' => $this->reservation->equipment_id,
            'equipment_name' => $this->reservation->equipment->name,
            'date_debut' => $this->reservation->date_debut->format('Y-m-d'),
            'date_fin' => $this->reservation->date_fin->format('Y-m-d'),
            'prix_total' => $this->reservation->prix_total,
            'type' => 'reservation_created',
        ];
    }
}

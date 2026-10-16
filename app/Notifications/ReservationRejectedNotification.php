<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Reservation $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load(['equipment']);
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
     * Prevents duplicate notifications for the same reservation rejection.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        // Check if an unread notification for this reservation rejection already exists
        $existingNotification = $notifiable
            ->unreadNotifications()
            ->whereJsonContains('data->reservation_id', $this->reservation->id)
            ->whereJsonContains('data->type', 'reservation_rejected')
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
            'equipment_name' => $this->reservation->equipment->name,
            'date_debut' => $this->reservation->date_debut->format('Y-m-d'),
            'date_fin' => $this->reservation->date_fin->format('Y-m-d'),
            'statut' => $this->reservation->statut,
            'type' => 'reservation_rejected',
            'message' => "Votre réservation pour {$this->reservation->equipment->name} a été refusée par l'administrateur.",
        ];
    }
}

<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\BadgeAttributionService;

class ReservationObserver
{
    public function updated(Reservation $reservation): void
    {
        if ($reservation->wasChanged('statut') && $reservation->statut === 'terminee') {
            $reservation->loadMissing('user');
            if ($reservation->user) {
                app(BadgeAttributionService::class)->evaluer($reservation->user, $reservation);
            }
        }
    }
}

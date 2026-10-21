<?php

namespace App\Services;

use App\Models\ReleveEnergie;

class DetectionAnomalieService
{
    public function analyser(ReleveEnergie $releve): ReleveEnergie
    {
        $releve->loadMissing('reservation.equipment', 'reservation.user');
        $reasons = [];
        $reservation = $releve->reservation;
        $duration = $reservation?->date_debut && $reservation?->date_fin
            ? (abs($reservation->date_debut->diffInDays($reservation->date_fin)) + 1) * 24
            : 0;
        if ((float) $releve->heures_utilisation > max(1, $duration)) {
            $reasons[] = 'La durée déclarée dépasse la durée de la réservation.';
        }
        $power = (float) ($releve->puissance_watts ?: $reservation?->equipment?->power);
        if ($power > 0 && (float) $releve->kwh_estimes > $power * (float) $releve->heures_utilisation / 1000 * 1.2) {
            $reasons[] = 'La production déclarée dépasse de 20 % la puissance disponible.';
        }
        if ($reservation?->user_id && $releve->date_releve) {
            $count = ReleveEnergie::whereHas('reservation', fn ($q) => $q->where('user_id', $reservation->user_id))
                ->whereDate('date_releve', $releve->date_releve)->where('id', '!=', $releve->getKey())->count();
            if ($count >= 2) {
                $reasons[] = 'Plus de deux relevés sont déclarés par cet utilisateur le même jour.';
            }
        }
        $previous = $reservation?->user_id
            ? ReleveEnergie::whereHas('reservation', fn ($q) => $q->where('user_id', $reservation->user_id))
                ->where('date_releve', '<', $releve->date_releve)->latest('date_releve')->get()
            : collect();
        if ($previous->take(2)->count() === 2 && $previous->take(2)->every(fn ($item) =>
            (float) $item->heures_utilisation === (float) $releve->heures_utilisation &&
            (float) $item->kwh_estimes === (float) $releve->kwh_estimes)) {
            $reasons[] = 'Les valeurs sont strictement identiques à plusieurs relevés précédents.';
        }
        $attributes = [
            'statut_verification' => $reasons ? 'suspect' : ($releve->statut_verification === 'suspect' ? 'normal' : $releve->statut_verification),
            'motif_suspicion' => $reasons ? implode(' ', $reasons) : null,
        ];
        if ($releve->getAttribute('statut_verification') !== $attributes['statut_verification']
            || $releve->getAttribute('motif_suspicion') !== $attributes['motif_suspicion']) {
            $releve->update($attributes);
        }
        return $releve->refresh();
    }
}

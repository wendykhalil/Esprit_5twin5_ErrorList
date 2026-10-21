<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Reservation;
use App\Models\User;

class BadgeAttributionService
{
    public function evaluer(User $user, ?Reservation $source = null): void
    {
        $user->loadMissing('badges');
        $badges = Badge::query()->where('actif', true)->get();
        $values = $this->valuesFor($user);

        foreach ($badges as $badge) {
            if ($user->badges->pluck($badge->getKeyName())->contains($badge->getKey()) || $values[$badge->critere] < $badge->seuil) {
                continue;
            }

            $user->badges()->attach($badge->getKey(), [
                'reservation_id' => $source?->getKey(),
                'date_obtention' => now(),
            ]);
            session()->flash('success', 'Nouveau badge débloqué : '.$badge->nom);
            $user->badges->push($badge);
        }
    }

    /**
     * @return array<string, float|int>
     */
    private function valuesFor(User $user): array
    {
        return [
            'kwh_total' => (float) $user->relevesEnergie()->whereIn('statut_verification', ['normal', 'valide'])->sum('kwh_estimes'),
            'co2_total' => (float) $user->relevesEnergie()->whereIn('statut_verification', ['normal', 'valide'])->sum('co2_evite_kg'),
            'reservations_terminees' => (int) $user->reservations()->where('statut', 'terminee')->count(),
            'retours_sans_dommage' => (int) $user->inspections()
                ->where('type', 'retour')
                ->where('etat', '!=', 'endommage')
                ->count(),
            'releves_declares' => (int) $user->relevesEnergie()->whereIn('statut_verification', ['normal', 'valide'])->count(),
        ];
    }
}

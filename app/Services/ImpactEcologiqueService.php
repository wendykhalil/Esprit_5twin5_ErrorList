<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\ReleveEnergie;
use App\Models\User;
use Illuminate\Support\Carbon;

class ImpactEcologiqueService
{
    public function calculate(float $puissanceWatts, float $heures): array
    {
        $kwh = round($puissanceWatts * $heures / 1000, 2);

        return [
            'kwh_estimes' => $kwh,
            'co2_evite_kg' => round($kwh * (float) config('solarshare.co2_kg_par_kwh'), 2),
        ];
    }

    public function syncBadges(User $user): void
    {
        $totalKwh = (float) $user->relevesEnergie()->sum('kwh_estimes');

        foreach (config('solarshare.badges', []) as $badgeDefinition) {
            if ($totalKwh >= $badgeDefinition['seuil_kwh']) {
                Badge::firstOrCreate(
                    ['user_id' => $user->id, 'nom' => $badgeDefinition['nom']],
                    [
                        'niveau' => $badgeDefinition['niveau'],
                        'seuil_kwh' => $badgeDefinition['seuil_kwh'],
                        'date_obtention' => Carbon::today(),
                    ]
                );
            }
        }
    }

    public function syncBadgesFor(ReleveEnergie $releve): void
    {
        $releve->loadMissing('reservation.user');
        if ($releve->reservation?->user) {
            $this->syncBadges($releve->reservation->user);
        }
    }
}

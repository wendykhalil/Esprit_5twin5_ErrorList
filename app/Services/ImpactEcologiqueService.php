<?php

namespace App\Services;

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

}

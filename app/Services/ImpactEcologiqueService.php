<?php

namespace App\Services;

class ImpactEcologiqueService
{
    public function calculate(float $puissanceWatts, float $heures): array
    {
        $kwh = $puissanceWatts * $heures / 1000;

        return [
            'kwh_estimes' => $kwh,
            'co2_evite_kg' => $kwh * (float) config('solarshare.co2_kg_par_kwh'),
        ];
    }

}

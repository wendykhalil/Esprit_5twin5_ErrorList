<?php

namespace Database\Seeders;

use App\Models\ReleveEnergie;
use App\Models\Reservation;
use App\Services\ImpactEcologiqueService;
use Illuminate\Database\Seeder;

class ReleveEnergieSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ImpactEcologiqueService::class);

        Reservation::with('equipment')
            ->where('statut', 'terminee')
            ->whereDoesntHave('releveEnergie')
            ->get()
            ->each(function (Reservation $reservation) use ($service): void {
                $power = (float) ($reservation->equipment?->power ?? 0);
                if ($power <= 0) {
                    return;
                }

                $maxHours = max(1, $reservation->date_debut->diffInDays($reservation->date_fin) * 24);
                $hours = min(1 + (($reservation->id % 4) * 0.5), $maxHours);
                $calculated = $service->calculate($power, $hours);

                ReleveEnergie::create([
                    'reservation_id' => $reservation->id,
                    'puissance_watts' => $power,
                    'heures_utilisation' => $hours,
                    ...$calculated,
                    'date_releve' => min($reservation->date_fin->toDateString(), now()->toDateString()),
                    'observations' => 'Relevé initial.',
                ]);
            });
    }
}

<?php

namespace Database\Factories;

use App\Models\ReleveEnergie;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReleveEnergieFactory extends Factory
{
    protected $model = ReleveEnergie::class;

    public function definition(): array
    {
        $reservation = Reservation::where('statut', 'terminee')->inRandomOrder()->first();
        $power = $reservation?->equipment?->power;
        $hours = fake()->randomFloat(2, 1, 24);
        $kwh = round((float) $power * $hours / 1000, 2);

        return [
            'reservation_id' => $reservation?->id ?? Reservation::factory(),
            'puissance_watts' => $power,
            'heures_utilisation' => $hours,
            'kwh_estimes' => $kwh,
            'co2_evite_kg' => round($kwh * (float) config('solarshare.co2_kg_par_kwh'), 2),
            'date_releve' => fake()->dateTimeBetween('-30 days', 'today'),
            'observations' => fake()->optional()->sentence(),
        ];
    }
}

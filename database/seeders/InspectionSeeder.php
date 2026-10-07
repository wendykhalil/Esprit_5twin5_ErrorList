<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        // Pour chaque réservation : une inspection à la remise et une au retour
        Reservation::all()->each(function (Reservation $reservation) {
            Inspection::factory()->create([
                'reservation_id' => $reservation->id,
                'type' => 'remise',
                'etat' => 'bon',
                'niveau_batterie' => 100,
            ]);
            Inspection::factory()->create([
                'reservation_id' => $reservation->id,
                'type' => 'retour',
            ]);
        });
    }
}

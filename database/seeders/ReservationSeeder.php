<?php

namespace Database\Seeders;

use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        Reservation::factory(4)->create();
        Reservation::factory(6)->create([
            'date_debut' => now()->subDays(10),
            'date_fin' => now()->subDays(3),
            'statut' => 'terminee',
        ]);
    }
}

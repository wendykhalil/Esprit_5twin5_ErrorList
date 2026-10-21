<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $equipments = Equipment::orderBy('id')->get();
        foreach ($equipments as $index => $equipment) {
            Reservation::create([
                'user_id' => $user->id,
                'equipment_id' => $equipment->id,
                'date_debut' => now()->subDays(20 + $index),
                'date_fin' => now()->subDays(18 + $index),
                'statut' => 'terminee',
                'prix_total' => 70,
            ]);
        }
    }
}

<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        // Create a new reservation for this delivery to avoid unique constraint conflicts
        $reservation = Reservation::factory()->create();

        return [
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'equipment_id' => $reservation->equipment_id,
            'planned_delivery_date' => $reservation->date_debut,
            'planned_return_date' => $reservation->date_fin,
            'actual_delivery_date' => null,
            'actual_return_date' => null,
            'status' => 'a_preparer',
            'notes' => null,
            'delivery_notes' => null,
            'return_notes' => null,
        ];
    }
}

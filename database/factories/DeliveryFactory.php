<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Closures run only when the test does not already supply the attribute.
            'reservation_id' => function () {
                return Reservation::factory()->create()->id;
            },
            'user_id' => function (array $attributes) {
                return Reservation::query()->findOrFail($attributes['reservation_id'])->user_id;
            },
            'equipment_id' => function (array $attributes) {
                return Reservation::query()->findOrFail($attributes['reservation_id'])->equipment_id;
            },
            'planned_delivery_date' => function (array $attributes) {
                return Reservation::query()->findOrFail($attributes['reservation_id'])->date_debut;
            },
            'planned_return_date' => function (array $attributes) {
                return Reservation::query()->findOrFail($attributes['reservation_id'])->date_fin;
            },
            'actual_delivery_date' => null,
            'actual_return_date' => null,
            'status' => 'a_preparer',
            'notes' => null,
            'delivery_notes' => null,
            'return_notes' => null,
        ];
    }
}

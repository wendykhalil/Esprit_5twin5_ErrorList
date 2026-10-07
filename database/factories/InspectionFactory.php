<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'type' => fake()->randomElement(['remise', 'retour']),
            'etat' => fake()->randomElement(['neuf', 'bon', 'use', 'endommage']),
            'niveau_batterie' => fake()->numberBetween(0, 100),
            'observations' => fake()->optional()->sentence(),
            'date_inspection' => fake()->dateTimeBetween('-10 days', '+10 days'),
        ];
    }
}

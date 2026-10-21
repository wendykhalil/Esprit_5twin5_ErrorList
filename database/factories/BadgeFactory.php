<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'critere' => fake()->randomElement(['kwh_total', 'co2_total', 'reservations_terminees', 'retours_sans_dommage', 'releves_declares']),
            'seuil' => fake()->numberBetween(1, 100),
            'niveau' => fake()->randomElement(['bronze', 'argent', 'or']),
            'actif' => true,
        ];
    }
}

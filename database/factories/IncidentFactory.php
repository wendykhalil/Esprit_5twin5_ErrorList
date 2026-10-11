<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Reclamation;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        $type = fake()->randomElement(Incident::TYPES);
        $damagePercentage = match ($type) {
            'endommage' => fake()->numberBetween(20, 100),
            'manquant' => fake()->numberBetween(10, 50),
            'malfonctionnant' => fake()->numberBetween(10, 30),
            default => 0,
        };

        return [
            'reclamation_id' => Reclamation::inRandomOrder()->value('id') ?? Reclamation::factory(),
            'equipment_id' => Equipment::inRandomOrder()->value('id') ?? Equipment::factory(),
            'type_probleme' => $type,
            'description' => fake()->paragraph(),
            'damage_percentage' => $damagePercentage,
            'proposed_refund_percentage' => fake()->numberBetween(10, 50),
            'proposed_refund_amount' => fake()->randomFloat(2, 10, 200),
        ];
    }
}

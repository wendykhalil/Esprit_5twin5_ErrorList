<?php

namespace Database\Factories;

use App\Models\Equipement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $debut = fake()->dateTimeBetween('+1 day', '+30 days');
        $fin = (clone $debut)->modify('+' . fake()->numberBetween(1, 7) . ' days');

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'equipement_id' => Equipement::inRandomOrder()->value('id') ?? Equipement::factory(),
            'date_debut' => $debut,
            'date_fin' => $fin,
            'statut' => fake()->randomElement(['en_attente', 'confirmee', 'en_cours', 'terminee']),
            'prix_total' => fake()->randomFloat(2, 10, 200),
        ];
    }
}

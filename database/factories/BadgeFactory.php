<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        $definition = fake()->randomElement(config('solarshare.badges'));

        return [
            'user_id' => User::factory(),
            'nom' => $definition['nom'],
            'niveau' => $definition['niveau'],
            'seuil_kwh' => $definition['seuil_kwh'],
            'date_obtention' => fake()->dateTimeBetween('-30 days', 'today'),
        ];
    }
}

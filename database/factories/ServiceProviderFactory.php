<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceProviderFactory extends Factory
{
    protected $model = ServiceProvider::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialty' => fake()->randomElement(['Solar Technician', 'Electrician', 'Battery Technician', 'Installer']),
            'description' => fake()->paragraph(),
            'experience_years' => fake()->numberBetween(1, 20),
            'phone' => fake()->phoneNumber(),
            'location' => fake()->city(),
            'hourly_rate' => fake()->randomFloat(2, 20, 150),
            'availability' => fake()->boolean(80),
            'status' => fake()->randomElement(['pending', 'approved', 'approved', 'approved']),
        ];
    }
}

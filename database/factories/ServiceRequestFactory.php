<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'equipment_id' => fake()->boolean(50) ? Equipment::factory() : null,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'requested_date' => fake()->dateTimeBetween('now', '+1 month'),
            'address' => fake()->address(),
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected', 'in_progress', 'completed', 'cancelled']),
            'estimated_price' => fake()->boolean(70) ? fake()->randomFloat(2, 50, 500) : null,
        ];
    }
}

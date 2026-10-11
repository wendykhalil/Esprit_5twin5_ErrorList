<?php

namespace Database\Factories;

use App\Models\CompensationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompensationCodeFactory extends Factory
{
    protected $model = CompensationCode::class;

    public function definition(): array
    {
        return [
            'code' => CompensationCode::generateCode(),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'value' => $this->faker->randomElement(['percentage' => rand(5, 50), 'fixed' => rand(10, 100)]),
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
            'max_uses' => $this->faker->randomElement([1, 3, 5, 10]),
            'uses_count' => 0,
            'is_active' => true,
            'user_id' => User::factory(),
            'reclamation_id' => null,
            'created_by' => User::factory(),
            'description' => $this->faker->sentence(),
            'last_used_at' => null,
        ];
    }

    public function percentage(int $value = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => $value,
        ]);
    }

    public function fixed(float $value = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'value' => $value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_until' => now()->subDay(),
        ]);
    }

    public function notYetStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => now()->addDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'uses_count' => $attributes['max_uses'],
        ]);
    }
}

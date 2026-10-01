<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']);
        $method = $this->faker->randomElement(['card', 'cash', 'bank_transfer']);
        
        return [
            'amount' => $this->faker->randomFloat(2, 10, 500), // 10 to 500 TND
            'method' => $method,
            'status' => $status,
            'payment_date' => $status === 'pending' ? null : $this->faker->dateTimeBetween('-30 days', 'now'),
            'description' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_date' => null,
        ]);
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the payment was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the payment method is card.
     */
    public function byCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'card',
        ]);
    }

    /**
     * Indicate that the payment method is bank transfer.
     */
    public function byBankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'bank_transfer',
        ]);
    }

    /**
     * Indicate that the payment method is cash.
     */
    public function byCash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'cash',
        ]);
    }
}

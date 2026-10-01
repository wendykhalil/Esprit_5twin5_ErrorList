<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']);
        $type = $this->faker->randomElement(['payment', 'refund']);
        
        return [
            'payment_id' => Payment::factory(),
            'reference' => 'TXN-' . strtoupper($this->faker->unique()->bothify('??-#####')),
            'type' => $type,
            'amount' => $this->faker->randomFloat(2, 10, 500), // 10 to 500 TND
            'status' => $status,
            'transaction_date' => $status === 'pending' ? null : $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'transaction_date' => null,
        ]);
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction is a payment.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment',
        ]);
    }

    /**
     * Indicate that the transaction is a refund.
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'refund',
        ]);
    }

    /**
     * Indicate that the transaction belongs to a specific payment.
     */
    public function forPayment(Payment $payment): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_id' => $payment->id,
        ]);
    }
}

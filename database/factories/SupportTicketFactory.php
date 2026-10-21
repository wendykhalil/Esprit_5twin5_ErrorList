<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']);

        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(6),
            'message' => fake()->paragraphs(2, true),
            'priority' => fake()->randomElement(['low', 'normal', 'high']),
            'status' => $status,
            'resolved_at' => $status === 'resolved' ? fake()->dateTimeBetween('-10 days', 'now') : null,
            'closed_at' => $status === 'closed' ? fake()->dateTimeBetween('-10 days', 'now') : null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => 'open',
            'closed_at' => null,
            'resolved_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReclamationFactory extends Factory
{
    protected $model = Reclamation::class;

    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::inRandomOrder()->value('id') ?? Reservation::factory(),
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(Reclamation::STATUSES),
            'refund_amount' => fake()->randomFloat(2, 0, 500),
            'admin_notes' => null,
            'rejected_reason' => null,
            'refund_approved_at' => null,
            'rejected_at' => null,
        ];
    }
}

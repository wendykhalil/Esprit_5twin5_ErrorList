<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        // Select up to 5 existing users who do not already have a ServiceProvider profile
        $users = User::doesntHave('serviceProvider')
            ->inRandomOrder()
            ->take(5)
            ->get();

        foreach ($users as $user) {
            ServiceProvider::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}

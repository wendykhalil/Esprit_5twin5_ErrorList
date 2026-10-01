<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        // Use an existing user, for example our "test" account.
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Utilisateur SolarShare',
                'email' => 'demo@solarshare.tn',
            ]);
        }

        // Make sure categories exist.
        if (Category::count() === 0) {
            $this->call(CategorySeeder::class);
        }

        $categories = Category::all();

        // Create 12 equipments.
        Equipment::factory()
            ->count(12)
            ->state(function () use ($user, $categories) {
                return [
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                ];
            })
            ->create();
    }
}
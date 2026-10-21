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

        $items = [
            ['name' => 'Chargeur solaire portable', 'power' => 20, 'brand' => 'SolarShare', 'category_id' => $categories->first()->id],
            ['name' => 'Panneau solaire pliable', 'power' => 120, 'brand' => 'SolarShare', 'category_id' => $categories->first()->id],
            ['name' => 'Station électrique portable', 'power' => 300, 'brand' => 'SolarShare', 'category_id' => $categories->first()->id],
            ['name' => 'Batterie portable', 'power' => 100, 'brand' => 'SolarShare', 'category_id' => $categories->first()->id],
            ['name' => 'Mini-éolienne portable', 'power' => 400, 'brand' => 'SolarShare', 'category_id' => $categories->last()->id],
        ];
        foreach ($items as $item) {
            Equipment::updateOrCreate(
                ['name' => $item['name'], 'user_id' => $user->id],
                $item + ['description' => 'Équipement énergétique fiable pour une production indicative.', 'price_per_day' => 35, 'location' => 'Tunis', 'availability' => true, 'status' => 'active']
            );
        }
    }
}
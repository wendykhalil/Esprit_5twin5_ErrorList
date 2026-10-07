<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $providers = ServiceProvider::all();
        $equipments = Equipment::all();
        $allUsers = User::all();

        if ($allUsers->isEmpty() || $providers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $provider = $providers->random();
            
            // Filter users to exclude the provider's own user account
            $eligibleUsers = $allUsers->where('id', '!=', $provider->user_id);
            
            if ($eligibleUsers->isEmpty()) {
                continue;
            }

            $user = $eligibleUsers->random();

            $equipmentId = fake()->boolean(50) && $equipments->isNotEmpty() 
                ? $equipments->random()->id 
                : null;

            ServiceRequest::factory()->create([
                'user_id' => $user->id,
                'service_provider_id' => $provider->id,
                'equipment_id' => $equipmentId,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\ImpactEcologiqueService;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ImpactEcologiqueService::class);
        User::query()->each(fn (User $user) => $service->syncBadges($user));
    }
}

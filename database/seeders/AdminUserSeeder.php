<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Compte administrateur local pour tester le back-office (/admin).
 * Relancer : php artisan db:seed --class=AdminUserSeeder
 */
class AdminUserSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@solarshare.tn';

    public const ADMIN_PASSWORD = 'password';

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Administrateur Backoffice',
                'password' => Hash::make(self::ADMIN_PASSWORD),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage equipment',
            'manage reservations',
            'view equipment',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $client = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'web',
        ]);

        $provider = Role::firstOrCreate([
            'name' => 'provider',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions($permissions);

        $client->syncPermissions([
            'view equipment',
        ]);

        $provider->syncPermissions([
            'view equipment',
            'manage equipment',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

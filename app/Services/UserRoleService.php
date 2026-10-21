<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ServiceProvider;
use App\Models\User;

class UserRoleService
{
    public function markAsProvider(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $user->update(['role' => UserRole::Provider->value]);

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::findOrCreate(UserRole::Provider->value, 'web');
            $user->syncRoles([UserRole::Provider->value]);
        }
    }

    public function markAsClient(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $user->update(['role' => UserRole::Client->value]);

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::findOrCreate(UserRole::Client->value, 'web');
            $user->syncRoles([UserRole::Client->value]);
        }
    }

    public function syncAfterProviderApproval(ServiceProvider $serviceProvider): void
    {
        $this->markAsProvider($serviceProvider->user);
    }

    public function syncAfterProviderRejection(ServiceProvider $serviceProvider): void
    {
        $this->markAsClient($serviceProvider->user);
    }

    public function syncAfterProviderProfileDeleted(User $user): void
    {
        $this->markAsClient($user);
    }
}

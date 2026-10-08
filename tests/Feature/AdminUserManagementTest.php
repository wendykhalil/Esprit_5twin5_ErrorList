<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_update_user_role_to_provider_when_profile_approved(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['role' => 'client']);
        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'specialty' => 'Technicien',
            'description' => 'Description suffisamment longue pour le profil.',
            'experience_years' => 2,
            'location' => 'Tunis',
            'hourly_rate' => 30,
            'availability' => true,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'provider',
            ])
            ->assertRedirect(route('admin.users.show', $user));

        $this->assertSame('provider', $user->fresh()->role);
    }

    public function test_admin_cannot_assign_provider_role_without_approved_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $user))
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'provider',
            ])
            ->assertRedirect(route('admin.users.edit', $user))
            ->assertSessionHasErrors('role');
    }
}

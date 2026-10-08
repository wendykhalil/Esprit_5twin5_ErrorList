<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_client_can_access_service_provider_creation_form(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('service-providers.create'))
            ->assertOk();
    }

    public function test_provider_cannot_access_service_provider_creation_form(): void
    {
        $user = User::factory()->provider()->create();
        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'specialty' => 'Technicien',
            'description' => 'Description suffisamment longue pour le profil.',
            'experience_years' => 3,
            'location' => 'Tunis',
            'hourly_rate' => 25,
            'availability' => true,
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('service-providers.create'))
            ->assertForbidden();
    }

    public function test_approved_provider_can_access_provider_requests(): void
    {
        $user = User::factory()->provider()->create();
        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'specialty' => 'Technicien',
            'description' => 'Description suffisamment longue pour le profil.',
            'experience_years' => 3,
            'location' => 'Tunis',
            'hourly_rate' => 25,
            'availability' => true,
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('provider.service-requests.index'))
            ->assertOk();
    }

    public function test_client_cannot_access_provider_requests_dashboard(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('provider.service-requests.index'))
            ->assertForbidden();
    }

    public function test_admin_approval_assigns_provider_role(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $profile = ServiceProvider::query()->create([
            'user_id' => $client->id,
            'specialty' => 'Installateur',
            'description' => 'Description suffisamment longue pour le profil.',
            'experience_years' => 5,
            'location' => 'Sfax',
            'hourly_rate' => 40,
            'availability' => true,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.service-providers.approve', $profile))
            ->assertRedirect();

        $this->assertSame('provider', $client->fresh()->role);
    }
}

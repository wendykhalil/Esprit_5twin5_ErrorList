<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminServiceRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(UserRole::Client->value, 'web');
        Role::findOrCreate(UserRole::Provider->value, 'web');
        Role::findOrCreate(UserRole::Admin->value, 'web');
    }

    public function test_guest_cannot_access_admin_interventions(): void
    {
        $this->get(route('admin.service-requests.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_interventions(): void
    {
        $client = $this->createClientUser();
        $this->actingAs($client)
            ->get(route('admin.service-requests.index'))
            ->assertForbidden();

        $provider = $this->createProviderUser();
        $this->actingAs($provider)
            ->get(route('admin.service-requests.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_all_service_requests_and_see_kpis(): void
    {
        $admin = $this->createAdminUser();

        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'title' => 'Panne onduleur urgente',
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'title' => 'Nettoyage panneaux solaire',
            'status' => 'completed',
            'estimated_price' => 350.00,
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.service-requests.index'))
            ->assertOk()
            ->assertViewIs('backend.service-requests.index')
            ->assertSee('Panne onduleur urgente')
            ->assertSee('Nettoyage panneaux solaire')
            ->assertSee('Montant estimé (terminées)')
            ->assertSee('350.00');
    }

    public function test_admin_can_filter_interventions_by_status(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'title' => 'Demande en attente Alpha',
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'title' => 'Demande terminee Beta',
            'status' => 'completed',
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.service-requests.index', ['status' => 'pending']))
            ->assertOk();

        $response->assertSee('Demande en attente Alpha');
        $response->assertDontSee('Demande terminee Beta');
    }

    public function test_admin_can_search_interventions_by_title_or_client(): void
    {
        $admin = $this->createAdminUser();
        $clientA = User::factory()->create([
            'name' => 'Foulen Ben Foulen',
            'role' => UserRole::Client->value,
        ]);
        $clientA->assignRole(UserRole::Client->value);

        $clientB = User::factory()->create([
            'name' => 'Autre Personne',
            'role' => UserRole::Client->value,
        ]);
        $clientB->assignRole(UserRole::Client->value);

        $provider = $this->createApprovedProvider();

        ServiceRequest::factory()->create([
            'user_id' => $clientA->id,
            'service_provider_id' => $provider->id,
            'title' => 'Installation spéciale',
            'equipment_id' => null,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $clientB->id,
            'service_provider_id' => $provider->id,
            'title' => 'Simple contrôle',
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.service-requests.index', ['search' => 'Foulen']))
            ->assertOk();

        $response->assertSee('Installation spéciale');
        $response->assertDontSee('Simple contrôle');
    }

    public function test_admin_can_view_intervention_details(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'title' => 'Intervention détaillée test',
            'description' => 'Description détaillée pour audit technique.',
            'address' => 'Avenue Habib Bourguiba, Tunis',
            'status' => 'accepted',
            'estimated_price' => 200.00,
            'equipment_id' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.service-requests.show', $request))
            ->assertOk()
            ->assertViewIs('backend.service-requests.show')
            ->assertSee('Intervention détaillée test')
            ->assertSee('Description détaillée pour audit technique.')
            ->assertSee('200.00');
    }

    public function test_admin_can_cancel_pending_intervention_with_reason(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.service-requests.cancel', $request), [
                'cancellation_reason' => 'Demande en doublon signalée par le client.',
            ]);

        $response->assertRedirect(route('admin.service-requests.show', $request));
        $response->assertSessionHas('success');

        $fresh = $request->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        $this->assertEquals('Demande en doublon signalée par le client.', $fresh->cancellation_reason);
        $this->assertNotNull($fresh->cancelled_at);
        $this->assertEquals($admin->id, $fresh->cancelled_by);
        $this->assertEquals($admin->name, $fresh->cancelledBy->name);
    }

    public function test_admin_can_cancel_accepted_and_in_progress_interventions(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $acceptedRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'accepted',
            'estimated_price' => 150.00,
            'equipment_id' => null,
        ]);

        $this->actingAs($admin)->patch(route('admin.service-requests.cancel', $acceptedRequest), [
            'cancellation_reason' => 'Client injoignable pour confirmation de date.',
        ])->assertRedirect();
        $this->assertEquals('cancelled', $acceptedRequest->fresh()->status);

        $inProgressRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'in_progress',
            'estimated_price' => 250.00,
            'equipment_id' => null,
        ]);

        $this->actingAs($admin)->patch(route('admin.service-requests.cancel', $inProgressRequest), [
            'cancellation_reason' => 'Conditions météo dangereuses sur le site.',
        ])->assertRedirect();
        $this->assertEquals('cancelled', $inProgressRequest->fresh()->status);
    }

    public function test_admin_cannot_cancel_completed_intervention(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $completedRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'completed',
            'estimated_price' => 500.00,
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.service-requests.cancel', $completedRequest), [
                'cancellation_reason' => 'Tentative d annulation invalide.',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals('completed', $completedRequest->fresh()->status);
    }

    public function test_cancellation_requires_a_valid_reason_min_5_chars(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.service-requests.cancel', $request), [
                'cancellation_reason' => 'abc', // trop court
            ]);

        $response->assertSessionHasErrors(['cancellation_reason']);
        $this->assertEquals('pending', $request->fresh()->status);
    }

    public function test_admin_can_emergency_complete_only_in_progress_intervention(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $inProgressRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'in_progress',
            'estimated_price' => 450.00,
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.service-requests.complete', $inProgressRequest));

        $response->assertRedirect(route('admin.service-requests.show', $inProgressRequest));
        $this->assertEquals('completed', $inProgressRequest->fresh()->status);
        $this->assertEquals(450.00, (float) $inProgressRequest->fresh()->estimated_price);
    }

    public function test_admin_cannot_complete_pending_or_accepted_intervention(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $pendingRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.service-requests.complete', $pendingRequest))
            ->assertSessionHas('error');
        $this->assertEquals('pending', $pendingRequest->fresh()->status);

        $acceptedRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'accepted',
            'estimated_price' => 120.00,
            'equipment_id' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.service-requests.complete', $acceptedRequest))
            ->assertSessionHas('error');
        $this->assertEquals('accepted', $acceptedRequest->fresh()->status);
    }

    public function test_cancelled_and_rejected_are_terminal_for_admin_actions(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $rejectedRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'rejected',
            'equipment_id' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.service-requests.cancel', $rejectedRequest), [
                'cancellation_reason' => 'Motif quelconque',
            ])
            ->assertSessionHas('error');
        $this->assertEquals('rejected', $rejectedRequest->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.service-requests.complete', $rejectedRequest))
            ->assertSessionHas('error');
        $this->assertEquals('rejected', $rejectedRequest->fresh()->status);
    }

    public function test_frontend_shows_cancellation_reason_when_cancelled(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();
        $provider = $this->createApprovedProvider();

        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Intervention impossible suite intempéries.',
            'cancelled_at' => now(),
            'cancelled_by' => $admin->id,
            'equipment_id' => null,
        ]);

        // Client voit le motif
        $this->actingAs($client)
            ->get(route('service-requests.show', $request))
            ->assertOk()
            ->assertSee('Intervention annulée')
            ->assertSee('Intervention impossible suite intempéries.');

        // Prestataire voit le motif
        $this->actingAs($provider->user)
            ->get(route('provider.service-requests.show', $request))
            ->assertOk()
            ->assertSee('Intervention annulée')
            ->assertSee('Intervention impossible suite intempéries.');
    }

    // --------------------------------------------------
    // HELPERS ISOLÉS
    // --------------------------------------------------

    private function createClientUser(): User
    {
        $user = User::factory()->create(['role' => UserRole::Client->value]);
        $user->assignRole(UserRole::Client->value);
        return $user;
    }

    private function createProviderUser(): User
    {
        $user = User::factory()->create(['role' => UserRole::Provider->value]);
        $user->assignRole(UserRole::Provider->value);
        return $user;
    }

    private function createAdminUser(): User
    {
        $user = User::factory()->create(['role' => UserRole::Admin->value]);
        $user->assignRole(UserRole::Admin->value);
        return $user;
    }

    private function createApprovedProvider(): ServiceProvider
    {
        $providerUser = $this->createProviderUser();
        return ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);
    }
}

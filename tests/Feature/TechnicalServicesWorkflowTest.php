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

class TechnicalServicesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(UserRole::Client->value, 'web');
        Role::findOrCreate(UserRole::Provider->value, 'web');
        Role::findOrCreate(UserRole::Admin->value, 'web');
    }

    // --------------------------------------------------
    // 1 & 2. ACCÈS À L'ESPACE PRESTATAIRE (provider.service-requests.index)
    // --------------------------------------------------

    public function test_non_provider_or_client_receives_403_when_accessing_provider_requests_index(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->get(route('provider.service-requests.index'))
            ->assertForbidden();
    }

    public function test_provider_with_pending_profile_receives_403_when_accessing_provider_requests_index(): void
    {
        $user = $this->createClientUser();
        ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('provider.service-requests.index'))
            ->assertForbidden();
    }

    public function test_provider_with_approved_profile_can_access_provider_requests_index(): void
    {
        $providerUser = $this->createProviderUser();
        ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $this->actingAs($providerUser)
            ->get(route('provider.service-requests.index'))
            ->assertOk()
            ->assertViewIs('frontend.provider-requests.index');
    }

    // --------------------------------------------------
    // 3 & 4. VALIDATION DU PRIX ESTIMÉ ET REJET DE DEMANDE
    // --------------------------------------------------

    public function test_negative_estimated_price_is_rejected_on_acceptance(): void
    {
        $providerUser = $this->createProviderUser();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client = $this->createClientUser();
        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'estimated_price' => null,
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.accept', $request), [
                'estimated_price' => -10.50,
            ]);

        $response->assertSessionHasErrors(['estimated_price']);
        $this->assertEquals('pending', $request->fresh()->status);
        $this->assertNull($request->fresh()->estimated_price);
    }

    public function test_provider_can_reject_pending_request_and_status_becomes_rejected(): void
    {
        $providerUser = $this->createProviderUser();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client = $this->createClientUser();
        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        $response = $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.reject', $request));

        $response->assertRedirect();
        $this->assertEquals('rejected', $request->fresh()->status);
    }

    // --------------------------------------------------
    // 5. TRANSITIONS D'ÉTAT INVALIDES INTERDITES
    // --------------------------------------------------

    public function test_invalid_state_transitions_are_rejected(): void
    {
        $providerUser = $this->createProviderUser();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client = $this->createClientUser();

        // 5.a : pending -> start (doit échouer)
        $pendingRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);
        $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.start', $pendingRequest))
            ->assertSessionHas('error');
        $this->assertEquals('pending', $pendingRequest->fresh()->status);

        // 5.b : pending -> complete (doit échouer)
        $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.complete', $pendingRequest))
            ->assertSessionHas('error');
        $this->assertEquals('pending', $pendingRequest->fresh()->status);

        // 5.c : rejected -> start (doit échouer)
        $rejectedRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'rejected',
            'equipment_id' => null,
        ]);
        $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.start', $rejectedRequest))
            ->assertSessionHas('error');
        $this->assertEquals('rejected', $rejectedRequest->fresh()->status);

        // 5.d : completed -> start (doit échouer)
        $completedRequest = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'completed',
            'equipment_id' => null,
        ]);
        $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.start', $completedRequest))
            ->assertSessionHas('error');
        $this->assertEquals('completed', $completedRequest->fresh()->status);
    }

    // --------------------------------------------------
    // 6. ISOLATION ENTRE PRESTATAIRES
    // --------------------------------------------------

    public function test_provider_cannot_manage_or_view_another_providers_request(): void
    {
        $providerUser1 = $this->createProviderUser();
        $provider1 = ServiceProvider::factory()->create([
            'user_id' => $providerUser1->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $providerUser2 = $this->createProviderUser();
        $provider2 = ServiceProvider::factory()->create([
            'user_id' => $providerUser2->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client = $this->createClientUser();
        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider1->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        // Provider 2 ne peut pas afficher la demande du Provider 1
        $this->actingAs($providerUser2)
            ->get(route('provider.service-requests.show', $request))
            ->assertForbidden();

        // Provider 2 ne peut pas accepter la demande du Provider 1
        $this->actingAs($providerUser2)
            ->patch(route('provider.service-requests.accept', $request), [
                'estimated_price' => 100,
            ])
            ->assertForbidden();

        // Provider 2 ne peut pas rejeter la demande du Provider 1
        $this->actingAs($providerUser2)
            ->patch(route('provider.service-requests.reject', $request))
            ->assertForbidden();

        $this->assertEquals('pending', $request->fresh()->status);
    }

    // --------------------------------------------------
    // 7. ISOLATION ENTRE CLIENTS
    // --------------------------------------------------

    public function test_client_cannot_show_or_delete_another_clients_request(): void
    {
        $providerUser = $this->createProviderUser();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client1 = $this->createClientUser();
        $client2 = $this->createClientUser();

        $request = ServiceRequest::factory()->create([
            'user_id' => $client1->id,
            'service_provider_id' => $provider->id,
            'status' => 'pending',
            'equipment_id' => null,
        ]);

        // Client 2 ne peut pas afficher la demande de Client 1
        $this->actingAs($client2)
            ->get(route('service-requests.show', $request))
            ->assertForbidden();

        // Client 2 ne peut pas annuler/supprimer la demande de Client 1
        $this->actingAs($client2)
            ->delete(route('service-requests.destroy', $request))
            ->assertForbidden();

        $this->assertDatabaseHas('service_requests', ['id' => $request->id]);
    }

    // --------------------------------------------------
    // 8. INTÉGRITÉ ET VERROUILLAGE DU PRIX ESTIMÉ
    // --------------------------------------------------

    public function test_estimated_price_remains_unchanged_after_acceptance(): void
    {
        $providerUser = $this->createProviderUser();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client = $this->createClientUser();
        $request = ServiceRequest::factory()->create([
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'status' => 'accepted',
            'estimated_price' => 175.50,
            'equipment_id' => null,
        ]);

        // Démarrage de l'intervention
        $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.start', $request));

        $this->assertEquals('in_progress', $request->fresh()->status);
        $this->assertEquals(175.50, (float) $request->fresh()->estimated_price);

        // Clôture de l'intervention
        $this->actingAs($providerUser)
            ->patch(route('provider.service-requests.complete', $request));

        $this->assertEquals('completed', $request->fresh()->status);
        $this->assertEquals(175.50, (float) $request->fresh()->estimated_price);
    }

    // --------------------------------------------------
    // CYCLE DE VIE GLOBAL & SYNCHRONISATION DES RÔLES
    // --------------------------------------------------

    public function test_admin_approval_synchronizes_both_role_column_and_spatie_role(): void
    {
        $admin = $this->createAdminUser();
        $client = $this->createClientUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $client->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.service-providers.approve', $provider))
            ->assertRedirect();

        $client->refresh();
        $this->assertTrue($client->isProvider());
        $this->assertTrue($client->hasRole(UserRole::Provider->value));
    }

    public function test_complete_valid_workflow_from_request_to_completion(): void
    {
        $providerUser = $this->createProviderUser();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $client = $this->createClientUser();
        $equipment = $this->createEquipmentFor($client);

        // 1. Client crée la demande
        $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => 'Maintenance annuelle onduleur',
            'description' => 'Contrôle complet des connectiques et de la production solaire.',
            'requested_date' => now()->addDays(3)->format('Y-m-d'),
            'address' => '10 Avenue de Carthage, Tunis',
            'equipment_id' => $equipment->id,
        ])->assertRedirect(route('service-requests.index'));

        $request = ServiceRequest::where('user_id', $client->id)->first();
        $this->assertEquals('pending', $request->status);

        // 2. Prestataire accepte avec prix estimé
        $this->actingAs($providerUser)->patch(route('provider.service-requests.accept', $request), [
            'estimated_price' => 180.00,
        ])->assertRedirect();
        $this->assertEquals('accepted', $request->fresh()->status);
        $this->assertEquals(180.00, (float) $request->fresh()->estimated_price);

        // 3. Prestataire démarre l'intervention
        $this->actingAs($providerUser)->patch(route('provider.service-requests.start', $request))->assertRedirect();
        $this->assertEquals('in_progress', $request->fresh()->status);

        // 4. Prestataire termine l'intervention
        $this->actingAs($providerUser)->patch(route('provider.service-requests.complete', $request))->assertRedirect();
        $this->assertEquals('completed', $request->fresh()->status);
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

    private function createEquipmentFor(User $user): Equipment
    {
        $category = Category::firstOrCreate(
            ['name' => 'Panneaux Solaires'],
            ['description' => 'Catégorie de test pour intervention']
        );

        return Equipment::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 400W',
            'description' => 'Équipement pour test d intervention.',
            'brand' => 'EcoSolar',
            'power' => 400,
            'capacity' => null,
            'condition' => 'good',
            'price_per_day' => 25.00,
            'location' => 'Tunis',
            'availability' => true,
            'status' => 'active',
        ]);
    }
}

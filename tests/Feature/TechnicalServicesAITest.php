<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\TechnicalServiceAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TechnicalServicesAITest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(UserRole::Client->value, 'web');
        Role::findOrCreate(UserRole::Provider->value, 'web');
        Role::findOrCreate(UserRole::Admin->value, 'web');
    }

    public function test_ai_pre_diagnosis_route_requires_authentication(): void
    {
        $response = $this->postJson(route('service-requests.ai-diagnose'), [
            'title' => 'Panne onduleur',
            'description' => 'Voyant rouge fixe sur mon onduleur solaire depuis ce matin.',
        ]);

        $response->assertUnauthorized();
    }

    public function test_ai_pre_diagnosis_validates_required_fields(): void
    {
        $client = $this->createClientUser();

        $response = $this->actingAs($client)->postJson(route('service-requests.ai-diagnose'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['service_provider_id', 'title', 'description']);
    }

    public function test_ai_pre_diagnosis_rejects_unapproved_provider(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'pending',
            'availability' => true,
        ]);

        $response = $this->actingAs($client)->postJson(route('service-requests.ai-diagnose'), [
            'service_provider_id' => $provider->id,
            'title' => 'Panne onduleur générale',
            'description' => 'L\'onduleur ne produit plus aucune énergie depuis hier soir.',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ce prestataire n\'est pas disponible pour de nouvelles interventions.',
            ]);
    }

    public function test_ai_pre_diagnosis_rejects_unavailable_provider(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => false,
        ]);

        $response = $this->actingAs($client)->postJson(route('service-requests.ai-diagnose'), [
            'service_provider_id' => $provider->id,
            'title' => 'Panne onduleur générale',
            'description' => 'L\'onduleur ne produit plus aucune énergie depuis hier soir.',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ce prestataire n\'est pas disponible pour de nouvelles interventions.',
            ]);
    }

    public function test_ai_pre_diagnosis_rejects_self_diagnosis_by_provider(): void
    {
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $response = $this->actingAs($providerUser)->postJson(route('service-requests.ai-diagnose'), [
            'service_provider_id' => $provider->id,
            'title' => 'Panne sur ma propre installation',
            'description' => 'Test de demande de diagnostic sur mon propre profil prestataire.',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Vous ne pouvez pas demander un diagnostic pour votre propre profil prestataire.',
            ]);
    }

    public function test_ai_pre_diagnosis_rejects_foreign_equipment_with_403(): void
    {
        $client = $this->createClientUser();
        $otherClient = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        // Équipement appartenant à un autre client
        $foreignEquipment = $this->createEquipmentFor($otherClient);

        $response = $this->actingAs($client)->postJson(route('service-requests.ai-diagnose'), [
            'service_provider_id' => $provider->id,
            'equipment_id' => $foreignEquipment->id,
            'title' => 'Panne onduleur',
            'description' => 'Description détaillée de panne pour équipement non autorisé.',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à utiliser cet équipement.',
            ]);
    }

    public function test_ai_pre_diagnosis_stores_fingerprinted_result_in_session_with_tnd(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $response = $this->actingAs($client)->postJson(route('service-requests.ai-diagnose'), [
            'service_provider_id' => $provider->id,
            'title' => 'Onduleur en alarme code E031',
            'description' => 'L\'onduleur bip en continu avec une erreur de surchauffe interne.',
        ]);

        $response->assertOk();
        $diagnosis = $response->json('diagnosis');

        // Vérification devise TND
        $this->assertStringContainsString('TND', $diagnosis['indicative_cost_range']);
        $this->assertStringNotContainsString('€', $diagnosis['indicative_cost_range']);

        // Vérification empreinte en session
        $this->assertTrue(session()->has('ts_ai_pre_diagnosis_' . $provider->id));
        $sessionData = session('ts_ai_pre_diagnosis_' . $provider->id);

        $this->assertArrayHasKey('fingerprint', $sessionData);
        $this->assertArrayHasKey('diagnosis', $sessionData);

        $expectedFingerprint = TechnicalServiceAIService::computeFingerprint(
            providerId: $provider->id,
            equipmentId: null,
            title: 'Onduleur en alarme code E031',
            description: 'L\'onduleur bip en continu avec une erreur de surchauffe interne.'
        );
        $this->assertEquals($expectedFingerprint, $sessionData['fingerprint']);
    }

    public function test_matching_form_data_persists_ai_diagnosis(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $title = 'Intervention sur onduleur défaillant';
        $desc = 'Description détaillée de plus de trente caractères pour validation valide.';

        // Mettre en session le diagnostic avec l'empreinte exacte
        $fingerprint = TechnicalServiceAIService::computeFingerprint($provider->id, null, $title, $desc);
        session()->put('ts_ai_pre_diagnosis_' . $provider->id, [
            'fingerprint' => $fingerprint,
            'diagnosis' => [
                'diagnostic_probable' => 'Défaut de conversion onduleur',
                'severity' => 'elevee',
                'estimated_duration' => '1h30 à 3h',
                'probable_components' => ['Onduleur central'],
                'safety_advice' => 'Éloignez-vous de l\'équipement.',
                'recommended_specialty' => 'Onduleurs',
                'short_summary' => 'Inspection nécessaire sur site.',
                'indicative_cost_range' => '90 TND - 220 TND',
                'source' => 'heuristic',
            ],
        ]);

        // Soumission du formulaire avec les données strictement identiques
        $response = $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => $title,
            'description' => $desc,
            'requested_date' => now()->addDays(2)->format('Y-m-d'),
            'address' => '123 Avenue des Oliviers, Tunis',
        ]);

        $response->assertRedirect(route('service-requests.index'));

        $createdRequest = ServiceRequest::latest('id')->first();
        $this->assertNotNull($createdRequest);
        $this->assertEquals('pending', $createdRequest->status);
        $this->assertNull($createdRequest->estimated_price);
        $this->assertNotNull($createdRequest->ai_pre_diagnosis);
        $this->assertEquals('Défaut de conversion onduleur', $createdRequest->ai_pre_diagnosis['diagnostic_probable']);
        $this->assertEquals('90 TND - 220 TND', $createdRequest->ai_pre_diagnosis['indicative_cost_range']);
    }

    public function test_stale_or_modified_form_data_discards_ai_diagnosis_without_rejecting_request(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        // Diagnostic généré pour un problème d'onduleur
        $fingerprint = TechnicalServiceAIService::computeFingerprint(
            $provider->id,
            null,
            'Ancien titre analysé',
            'Ancienne description analysée par l\'IA avec au moins trente caractères.'
        );

        session()->put('ts_ai_pre_diagnosis_' . $provider->id, [
            'fingerprint' => $fingerprint,
            'diagnosis' => [
                'diagnostic_probable' => 'Défaut onduleur obsolète',
                'severity' => 'elevee',
            ],
        ]);

        // L'utilisateur a modifié le titre et la description avant d'envoyer
        $response = $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => 'Nouveau titre modifié par l\'utilisateur',
            'description' => 'Nouvelle description différente qui ne correspond plus à l\'analyse IA initiale.',
            'requested_date' => now()->addDays(2)->format('Y-m-d'),
            'address' => '123 Avenue des Oliviers, Tunis',
        ]);

        // La demande DOIT être créée avec succès sans bloquer le client
        $response->assertRedirect(route('service-requests.index'));

        $createdRequest = ServiceRequest::latest('id')->first();
        // Mais l'ancien diagnostic obsolète DOIT être rejeté (null)
        $this->assertNull($createdRequest->ai_pre_diagnosis);
    }

    public function test_session_consumed_after_submission(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $title = 'Demande intervention test consommation session';
        $desc = 'Description détaillée valide de plus de trente caractères pour le test de session.';

        session()->put('ts_ai_pre_diagnosis_' . $provider->id, [
            'fingerprint' => TechnicalServiceAIService::computeFingerprint($provider->id, null, $title, $desc),
            'diagnosis' => ['diagnostic_probable' => 'Test'],
        ]);

        $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => $title,
            'description' => $desc,
            'requested_date' => now()->addDays(2)->format('Y-m-d'),
            'address' => '123 Avenue des Oliviers, Tunis',
        ]);

        // La session est obligatoirement vidée après soumission (one-time consumption)
        $this->assertFalse(session()->has('ts_ai_pre_diagnosis_' . $provider->id));
    }

    public function test_protected_fields_are_rejected_and_no_service_request_is_created(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();
        $otherProvider = ServiceProvider::factory()->create([
            'user_id' => $this->createProviderUser()->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        // Tentative de manipulation DevTools : forcer status=completed, estimated_price=500, etc.
        $response = $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => 'Demande avec tentative de contournement des statuts',
            'description' => 'Description détaillée de plus de trente caractères pour validation valide.',
            'requested_date' => now()->addDays(2)->format('Y-m-d'),
            'address' => '123 Avenue des Oliviers, Tunis',
            'status' => 'completed',
            'estimated_price' => 500,
            'user_id' => $client->id,
            'service_provider_id' => $otherProvider->id,
        ]);

        // Rejeté par la validation stricte de StoreServiceRequest
        $response->assertSessionHasErrors(['status', 'estimated_price', 'service_provider_id']);
        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_legitimate_submission_without_ai_creates_pending_request_with_null_price_and_null_diagnosis(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $response = $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => 'Demande légitime sans pré-diagnostic IA',
            'description' => 'Description détaillée valide de plus de trente caractères pour le test.',
            'requested_date' => now()->addDays(2)->format('Y-m-d'),
            'address' => '123 Avenue des Oliviers, Tunis',
        ]);

        $response->assertRedirect(route('service-requests.index'));

        $createdRequest = ServiceRequest::latest('id')->first();
        $this->assertNotNull($createdRequest);
        $this->assertEquals('pending', $createdRequest->status);
        $this->assertNull($createdRequest->estimated_price);
        $this->assertNull($createdRequest->ai_pre_diagnosis);
    }

    public function test_forged_client_payload_cannot_bypass_ai_or_persist_untrusted_diagnosis(): void
    {
        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        // Injection d'un champ non autorisé 'ai_pre_diagnosis' directement dans le corps de requête
        $response = $this->actingAs($client)->post(route('service-requests.store', $provider), [
            'title' => 'Demande avec faux JSON dans le formulaire',
            'description' => 'Description détaillée valide de plus de trente caractères pour le test.',
            'requested_date' => now()->addDays(2)->format('Y-m-d'),
            'address' => '123 Avenue des Oliviers, Tunis',
            'ai_pre_diagnosis' => json_encode(['diagnostic_probable' => 'Faux diagnostic injecté']),
        ]);

        // Rejeté par la politique des champs inattendus de StoreServiceRequest
        $response->assertSessionHasErrors(['form']);
        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_fallback_heuristic_returns_conservative_safety_advice_and_tnd_when_ollama_fails(): void
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $client = $this->createClientUser();
        $providerUser = $this->createProviderUser();

        $provider = ServiceProvider::factory()->create([
            'user_id' => $providerUser->id,
            'status' => 'approved',
            'availability' => true,
        ]);

        $response = $this->actingAs($client)->postJson(route('service-requests.ai-diagnose'), [
            'service_provider_id' => $provider->id,
            'title' => 'Batterie solaire en surchauffe',
            'description' => 'La batterie dégage une odeur anormale et semble gonflée.',
        ]);

        $response->assertOk();
        $diagnosis = $response->json('diagnosis');

        $this->assertEquals('heuristic', $diagnosis['source']);
        $this->assertStringContainsString('TND', $diagnosis['indicative_cost_range']);
        $this->assertStringNotContainsString('€', $diagnosis['indicative_cost_range']);
        $this->assertStringContainsString('Ne touchez pas aux bornes', $diagnosis['safety_advice']);
    }

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

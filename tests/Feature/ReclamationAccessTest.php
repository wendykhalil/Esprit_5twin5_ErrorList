<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\Category;
use App\Models\Reclamation;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReclamationAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $otherClient;
    private User $equipmentOwner;
    private Equipment $equipment;
    private Reservation $eligibleReservation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->client = User::factory()->create(['role' => 'client']);
        $this->otherClient = User::factory()->create(['role' => 'client']);
        $this->equipmentOwner = User::factory()->create(['role' => 'client']);

        // Create category and equipment
        $category = Category::firstOrCreate(['name' => 'Solar Panels']);
        $this->equipment = Equipment::create([
            'user_id' => $this->equipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Solar Panel Test',
            'description' => 'Test equipment',
            'price_per_day' => 100,
            'condition' => 'excellent',
            'power' => 100,
            'capacity' => 100,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);

        // Create eligible reservation (confirmee)
        $this->eligibleReservation = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->subDays(10),
            'date_fin' => now()->addDays(5),
            'statut' => 'confirmee',
            'prix_total' => 500,
        ]);
    }

    /**
     * Test: Client can access /reclamations/create with eligible reservation
     */
    public function test_client_can_access_create_form_with_eligible_reservation(): void
    {
        $this->actingAs($this->client);

        $response = $this->get(route('reclamations.create'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.reclamations.create');
        $response->assertViewHas('reservations');
        $response->assertSee($this->eligibleReservation->id);
    }

    /**
     * Test: Client sees helpful message when no eligible reservations
     */
    public function test_client_sees_message_when_no_eligible_reservations(): void
    {
        // Create another client without eligible reservations
        $clientNoRes = User::factory()->create(['role' => 'client']);

        $this->actingAs($clientNoRes);

        $response = $this->get(route('reclamations.create'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.reclamations.no-reservations');
        $response->assertSee('Aucune Réservation Éligible');
    }

    /**
     * Test: Create reclamation with valid incident
     */
    public function test_create_reclamation_with_valid_incident(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reclamations.store'), [
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Panel Endommagé',
            'description' => 'Le panneau solaire est endommagé après livraison.',
            'incidents' => [
                [
                    'equipment_id' => $this->equipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Crack visible sur le verre.',
                    'damage_percentage' => 25,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reclamations', [
            'user_id' => $this->client->id,
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Panel Endommagé',
            'status' => 'en_attente',
        ]);
        $this->assertDatabaseHas('incidents', [
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
        ]);
    }

    /**
     * Test: Reject reclamation for another user's reservation
     */
    public function test_reject_reclamation_for_other_users_reservation(): void
    {
        $this->actingAs($this->otherClient);

        $response = $this->post(route('reclamations.store'), [
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Try to hack',
            'description' => 'Trying to create reclamation for other user',
            'incidents' => [
                [
                    'equipment_id' => $this->equipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Fake damage',
                ],
            ],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('reclamations', [
            'user_id' => $this->otherClient->id,
        ]);
    }

    /**
     * Test: Reject incident equipment that doesn't match reservation
     */
    public function test_reject_incident_with_non_matching_equipment(): void
    {
        $this->actingAs($this->client);

        // Create different equipment
        $category = Category::firstOrCreate(['name' => 'Solar Panels']);
        $differentEquipment = Equipment::create([
            'user_id' => $this->equipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Different Equipment',
            'description' => 'Different',
            'price_per_day' => 50,
            'condition' => 'good',
            'power' => 50,
            'capacity' => 50,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);

        $response = $this->post(route('reclamations.store'), [
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Wrong Equipment',
            'description' => 'Trying to claim different equipment',
            'incidents' => [
                [
                    'equipment_id' => $differentEquipment->id,  // Wrong equipment!
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Fake',
                ],
            ],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('incidents', [
            'equipment_id' => $differentEquipment->id,
        ]);
    }

    /**
     * Test: Can only access reclamations for own reservations
     */
    public function test_client_can_only_view_own_reclamations(): void
    {
        // Create reclamation for this client
        $reclamation = Reclamation::create([
            'reservation_id' => $this->eligibleReservation->id,
            'user_id' => $this->client->id,
            'title' => 'Test Reclamation',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        // Other client tries to view
        $this->actingAs($this->otherClient);
        $response = $this->get(route('reclamations.show', $reclamation->id));

        $response->assertStatus(403);
    }

    /**
     * Test: Reclamation eligibility includes all correct statuses
     */
    public function test_reclamation_eligibility_includes_confirmee_en_cours_terminee(): void
    {
        $this->actingAs($this->client);

        // Create reservations with different statuses
        $confirmee = $this->eligibleReservation; // Already created

        $enCours = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->subDays(5),
            'date_fin' => now()->addDays(10),
            'statut' => 'en_cours',
            'prix_total' => 500,
        ]);

        $terminee = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->subDays(20),
            'date_fin' => now()->subDays(5),
            'statut' => 'terminee',
            'prix_total' => 500,
        ]);

        $response = $this->get(route('reclamations.create'));

        $response->assertStatus(200);
        $response->assertViewHas('reservations');
        $reservations = $response->viewData('reservations');
        $ids = $reservations->pluck('id')->toArray();

        $this->assertContains($confirmee->id, $ids);
        $this->assertContains($enCours->id, $ids);
        $this->assertContains($terminee->id, $ids);
    }

    /**
     * Test: Reclamation eligibility excludes ineligible statuses
     */
    public function test_reclamation_eligibility_excludes_ineligible_statuses(): void
    {
        $this->actingAs($this->client);

        // Create reservations with ineligible statuses
        $enAttente = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(5),
            'date_fin' => now()->addDays(10),
            'statut' => 'en_attente',
            'prix_total' => 500,
        ]);

        $annulee = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->subDays(20),
            'date_fin' => now()->subDays(5),
            'statut' => 'annulee',
            'prix_total' => 500,
        ]);

        $response = $this->get(route('reclamations.create'));

        $response->assertStatus(200);
        $response->assertViewHas('reservations');
        $reservations = $response->viewData('reservations');
        $ids = $reservations->pluck('id')->toArray();

        $this->assertNotContains($enAttente->id, $ids);
        $this->assertNotContains($annulee->id, $ids);
    }

    /**
     * Test: Incident form allows selecting equipment and problem type
     */
    public function test_create_form_displays_properly(): void
    {
        $this->actingAs($this->client);

        $response = $this->get(route('reclamations.create'));

        $response->assertStatus(200);
        $response->assertSee('Nouvelle Réclamation');
        $response->assertSee('Sélectionnez une réservation');
        $response->assertSee('Type de problème');
        $response->assertSee('Description');
    }

    /**
     * Test: Form submission with CORRECT equipment_id (reproduces bug 403).
     *
     * This test reproduces the exact bug flow:
     * 1. Client selects Reservation #1 (which has equipment_id = X)
     * 2. Form submits with incidents[0][equipment_id] = X (CORRECT)
     * 3. Should succeed (not 403)
     */
    public function test_form_submission_with_correct_equipment_id_succeeds(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reclamations.store'), [
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Equipment Damage',
            'description' => 'The equipment was damaged.',
            'incidents' => [
                [
                    'equipment_id' => $this->equipment->id,  // ✓ CORRECT: matches $eligibleReservation->equipment_id
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Visible damage on surface',
                    'damage_percentage' => 30,
                ],
            ],
        ]);

        // Should NOT be 403
        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertDatabaseHas('reclamations', [
            'user_id' => $this->client->id,
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Equipment Damage',
        ]);
    }

    /**
     * Test: Form submission with WRONG equipment_id (still fails correctly).
     *
     * This test verifies that if someone tries to use a mismatched equipment_id,
     * the 403 security check still works as intended.
     */
    public function test_form_submission_with_wrong_equipment_id_fails(): void
    {
        $this->actingAs($this->client);

        // Create a different equipment
        $category = Category::firstOrCreate(['name' => 'Other Category']);
        $wrongEquipment = Equipment::create([
            'user_id' => $this->equipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Wrong Equipment',
            'description' => 'Different equipment',
            'price_per_day' => 50,
            'condition' => 'good',
            'power' => 50,
            'capacity' => 50,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);

        $response = $this->post(route('reclamations.store'), [
            'reservation_id' => $this->eligibleReservation->id,
            'title' => 'Wrong Equipment Claim',
            'description' => 'Trying to claim wrong equipment',
            'incidents' => [
                [
                    'equipment_id' => $wrongEquipment->id,  // ✗ WRONG: doesn't match reservation
                    'type_probleme' => 'manquant', 'titre' => 'Incident signale',
                    'description' => 'Equipment is missing',
                ],
            ],
        ]);

        // Should be 403
        $response->assertStatus(403);
        $this->assertDatabaseMissing('reclamations', ['title' => 'Wrong Equipment Claim']);
    }
}

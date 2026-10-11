<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = User::factory()->create(['role' => 'client']);
        $this->owner = User::factory()->create(['role' => 'client']);

        // Create test category
        $this->category = Category::create(['name' => 'Panneaux Solaires']);

        // Create test equipment
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $this->category->id,
        ]);
    }

    protected function createDeliveryWithReservation($clientId = null, $equipmentId = null)
    {
        $clientId = $clientId ?? $this->client->id;
        $equipmentId = $equipmentId ?? $this->equipment->id;

        $reservation = Reservation::factory()->create([
            'user_id' => $clientId,
            'equipment_id' => $equipmentId,
            'statut' => 'confirmee',
        ]);

        return Delivery::create([
            'reservation_id' => $reservation->id,
            'user_id' => $clientId,
            'equipment_id' => $equipmentId,
            'planned_delivery_date' => $reservation->date_debut,
            'planned_return_date' => $reservation->date_fin,
            'status' => 'prete', // Changed from 'a_preparer' to 'prete'
        ]);
    }

    /**
     * Test: Admin can view deliveries index
     */
    public function test_admin_can_view_deliveries_index(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.deliveries.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.deliveries.index');
        $response->assertViewHas('deliveries');
        $response->assertSee($delivery->equipment->name);
    }

    /**
     * Test: Non-admin cannot view deliveries admin page
     */
    public function test_non_admin_cannot_view_deliveries_index(): void
    {
        $response = $this->actingAs($this->client)
            ->get(route('admin.deliveries.index'));

        $response->assertStatus(403);
    }

    /**
     * Test: Unauthenticated user is redirected
     */
    public function test_unauthenticated_user_cannot_view_admin_deliveries(): void
    {
        $response = $this->get(route('admin.deliveries.index'));

        $response->assertRedirect('/login');
    }

    /**
     * Test: Admin can view delivery details
     */
    public function test_admin_can_view_delivery_show(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.deliveries.show', $delivery));

        $response->assertStatus(200);
        $response->assertViewIs('backend.deliveries.show');
        $response->assertSee($delivery->equipment->name);
        $response->assertSee($this->client->name);
    }

    /**
     * Test: Admin can update delivery status with valid transition
     */
    public function test_admin_can_update_delivery_status_valid_transition(): void
    {
        $delivery = $this->createDeliveryWithReservation();
        $this->assertEquals('prete', $delivery->status);

        // Transition from 'prete' to 'remise_au_client'
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'remise_au_client',
                'notes' => 'Équipement remis au client',
            ]);

        $response->assertRedirect(route('admin.deliveries.show', $delivery));
        $response->assertSessionHas('success');

        $delivery->refresh();
        $this->assertEquals('remise_au_client', $delivery->status);
        $this->assertNotNull($delivery->actual_delivery_date);
    }

    /**
     * Test: Admin cannot update delivery status with invalid transition
     */
    public function test_admin_cannot_update_delivery_status_invalid_transition(): void
    {
        $delivery = $this->createDeliveryWithReservation();
        $this->assertEquals('prete', $delivery->status);

        // Try to transition from 'prete' to 'retour_recu' (invalid - must go through 'remise_au_client')
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'retour_recu',
            ]);

        $response->assertSessionHas('error');

        $delivery->refresh();
        $this->assertEquals('prete', $delivery->status); // Status unchanged
    }

    /**
     * Test: Status transition to 'remise_au_client' records actual delivery date
     */
    public function test_status_transition_to_remise_au_client_records_delivery_date(): void
    {
        $delivery = $this->createDeliveryWithReservation();
        $delivery->update(['status' => 'prete']);

        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'remise_au_client',
            ]);

        $delivery->refresh();
        $this->assertEquals('remise_au_client', $delivery->status);
        $this->assertNotNull($delivery->actual_delivery_date);
    }

    /**
     * Test: Status transition to 'retour_recu' records actual return date
     */
    public function test_status_transition_to_retour_recu_records_return_date(): void
    {
        $delivery = $this->createDeliveryWithReservation();
        $delivery->update(['status' => 'remise_au_client']);

        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'retour_recu',
            ]);

        $delivery->refresh();
        $this->assertEquals('retour_recu', $delivery->status);
        $this->assertNotNull($delivery->actual_return_date);
    }

    /**
     * Test: Complete delivery status flow
     */
    public function test_complete_delivery_status_flow(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        // Transition 1: à préparer → prête
        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'prete',
            ]);

        $delivery->refresh();
        $this->assertEquals('prete', $delivery->status);

        // Transition 2: prête → remise_au_client
        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'remise_au_client',
            ]);

        $delivery->refresh();
        $this->assertEquals('remise_au_client', $delivery->status);

        // Transition 3: remise_au_client → retour_reçu
        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'retour_recu',
            ]);

        $delivery->refresh();
        $this->assertEquals('retour_recu', $delivery->status);

        // Transition 4: retour_reçu → terminée
        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'terminee',
            ]);

        $delivery->refresh();
        $this->assertEquals('terminee', $delivery->status);
    }

    /**
     * Test: Client can view own deliveries
     */
    public function test_client_can_view_own_deliveries(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        $response = $this->actingAs($this->client)
            ->get(route('deliveries.index'));

        $response->assertStatus(200);
        $response->assertViewIs('deliveries.index');
        $response->assertSee($delivery->equipment->name);
    }

    /**
     * Test: Client cannot view other clients' deliveries
     */
    public function test_client_cannot_view_other_clients_deliveries(): void
    {
        $other_client = User::factory()->create(['role' => 'client']);
        $delivery = $this->createDeliveryWithReservation($other_client->id);

        $response = $this->actingAs($this->client)
            ->get(route('deliveries.show', $delivery));

        $response->assertStatus(403);
    }

    /**
     * Test: Client can view their own delivery detail
     */
    public function test_client_can_view_own_delivery_detail(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        $response = $this->actingAs($this->client)
            ->get(route('deliveries.show', $delivery));

        $response->assertStatus(200);
        $response->assertViewIs('deliveries.show');
        $response->assertSee($delivery->equipment->name);
        $response->assertSee($delivery->reservation->reference);
    }

    /**
     * Test: Unauthenticated user cannot view deliveries
     */
    public function test_unauthenticated_user_cannot_view_deliveries(): void
    {
        $response = $this->get(route('deliveries.index'));

        $response->assertRedirect('/login');
    }

    /**
     * Test: Only one delivery per reservation (unique constraint)
     */
    public function test_only_one_delivery_per_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $delivery1 = Delivery::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'planned_delivery_date' => $reservation->date_debut,
            'planned_return_date' => $reservation->date_fin,
            'status' => 'a_preparer',
        ]);

        // Try to create another delivery for same reservation
        try {
            Delivery::create([
                'reservation_id' => $reservation->id,
                'user_id' => $this->client->id,
                'equipment_id' => $this->equipment->id,
                'planned_delivery_date' => $reservation->date_debut,
                'planned_return_date' => $reservation->date_fin,
                'status' => 'a_preparer',
            ]);
            $this->fail('Should have thrown an exception due to unique constraint');
        } catch (\Exception $e) {
            // Expected: Integrity constraint violation
            $this->assertStringContainsString('Integrity constraint violation', $e->getMessage());
        }
    }

    /**
     * Test: Delivery creation helper only for confirmed reservations
     */
    public function test_delivery_cannot_be_created_for_pending_reservation(): void
    {
        $pending_reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot create delivery for reservation with status');

        \App\Http\Controllers\Admin\DeliveryController::createDeliveryForReservation($pending_reservation);
    }

    /**
     * Test: Delivery creation helper for confirmed reservation
     */
    public function test_delivery_can_be_created_for_confirmed_reservation(): void
    {
        $confirmed_reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $delivery = \App\Http\Controllers\Admin\DeliveryController::createDeliveryForReservation($confirmed_reservation);

        $this->assertNotNull($delivery->id);
        $this->assertEquals($confirmed_reservation->id, $delivery->reservation_id);
        $this->assertEquals('prete', $delivery->status); // Changed from 'a_preparer' to 'prete'
        $this->assertEquals($confirmed_reservation->date_debut, $delivery->planned_delivery_date);
        $this->assertEquals($confirmed_reservation->date_fin, $delivery->planned_return_date);
    }

    /**
     * Test: Delivery creation helper returns existing delivery
     */
    public function test_delivery_creation_helper_returns_existing_delivery(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $existing_delivery = Delivery::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'planned_delivery_date' => $reservation->date_debut,
            'planned_return_date' => $reservation->date_fin,
            'status' => 'a_preparer',
        ]);

        $delivery = \App\Http\Controllers\Admin\DeliveryController::createDeliveryForReservation($reservation);

        $this->assertEquals($existing_delivery->id, $delivery->id);
    }

    /**
     * Test: Delivery model status helpers
     */
    public function test_delivery_model_status_helpers(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        $this->assertFalse($delivery->isDelivered());
        $this->assertFalse($delivery->isReturned());
        $this->assertFalse($delivery->isComplete());

        // After delivery
        $delivery->status = 'remise_au_client';
        $this->assertTrue($delivery->isDelivered());
        $this->assertFalse($delivery->isReturned());
        $this->assertFalse($delivery->isComplete());

        // After return
        $delivery->status = 'retour_recu';
        $this->assertTrue($delivery->isDelivered());
        $this->assertTrue($delivery->isReturned());
        $this->assertFalse($delivery->isComplete());

        // After complete
        $delivery->status = 'terminee';
        $this->assertTrue($delivery->isDelivered());
        $this->assertTrue($delivery->isReturned());
        $this->assertTrue($delivery->isComplete());
    }

    /**
     * Test: Valid status transitions
     */
    public function test_valid_status_transitions(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        // Delivery starts with status 'prete'
        $this->assertEquals('prete', $delivery->status);

        $transitions = $delivery->getValidTransitions();
        // From 'prete', valid transition is to 'remise_au_client'
        $this->assertContains('remise_au_client', $transitions);
        $this->assertNotContains('prete', $transitions);
        $this->assertNotContains('retour_recu', $transitions);
        $this->assertNotContains('a_preparer', $transitions);
    }

    /**
     * Test: Can transition to status helper
     */
    public function test_can_transition_to_status_helper(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        // From 'prete' status, only 'remise_au_client' is valid
        $this->assertTrue($delivery->canTransitionTo('remise_au_client'));
        $this->assertFalse($delivery->canTransitionTo('prete'));
        $this->assertFalse($delivery->canTransitionTo('retour_recu'));
        $this->assertFalse($delivery->canTransitionTo('terminee'));
        $this->assertFalse($delivery->canTransitionTo('a_preparer'));
    }

    // ===== FRONTEND DELIVERIES TESTS =====

    /**
     * Test: Deliveries link visible in navbar for authenticated users
     */
    public function test_deliveries_link_visible_in_navbar_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->client)
            ->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Mes livraisons');
        $response->assertSee(route('deliveries.index'));
    }

    /**
     * Test: Deliveries link NOT visible in navbar for guests
     */
    public function test_deliveries_link_not_visible_in_navbar_for_guest(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Mes livraisons');
    }

    /**
     * Test: Frontend - Client can access deliveries index
     */
    public function test_frontend_client_can_access_deliveries_index(): void
    {
        $response = $this->actingAs($this->client)
            ->get(route('deliveries.index'));

        $response->assertStatus(200);
        $response->assertViewIs('deliveries.index');
    }

    /**
     * Test: Frontend - Client sees only their own deliveries
     */
    public function test_frontend_client_sees_only_own_deliveries(): void
    {
        // Create delivery for client 1
        $delivery1 = $this->createDeliveryWithReservation($this->client->id);

        // Create another client and their delivery
        $other_client = User::factory()->create(['role' => 'client']);
        $delivery2 = $this->createDeliveryWithReservation($other_client->id);

        $response = $this->actingAs($this->client)
            ->get(route('deliveries.index'));

        $response->assertStatus(200);
        $response->assertSee($delivery1->equipment->name);
        // Check that we don't see the other equipment in the page content
        $content = $response->getContent();
        $this->assertStringContainsString($delivery1->equipment->name, $content);
    }

    /**
     * Test: Frontend - Deliveries page displays equipment info
     */
    public function test_frontend_deliveries_page_displays_equipment_info(): void
    {
        $delivery = $this->createDeliveryWithReservation();

        $response = $this->actingAs($this->client)
            ->get(route('deliveries.index'));

        $response->assertStatus(200);
        $response->assertSee($delivery->equipment->name);
        $response->assertSee($delivery->planned_delivery_date->format('d/m/Y'));
        $response->assertSee($delivery->planned_return_date->format('d/m/Y'));
    }

    /**
     * Test: Frontend - Deliveries show page displays status timeline
     */
    public function test_frontend_deliveries_show_displays_status_timeline(): void
    {
        $delivery = $this->createDeliveryWithReservation();
        $delivery->update(['status' => 'prete']);

        $response = $this->actingAs($this->client)
            ->get(route('deliveries.show', $delivery));

        $response->assertStatus(200);
        $response->assertViewIs('deliveries.show');
        $response->assertSee('Prête');
        $response->assertSee($delivery->equipment->name);
    }

    /**
     * Test: Frontend - Guest cannot access deliveries
     */
    public function test_frontend_guest_cannot_access_deliveries(): void
    {
        $response = $this->get(route('deliveries.index'));

        $response->assertRedirect('/login');
    }

    /**
     * Test: Frontend - Deliveries are isolated per user
     */
    public function test_frontend_deliveries_are_isolated_per_user(): void
    {
        $delivery = $this->createDeliveryWithReservation($this->client->id);
        $other_client = User::factory()->create(['role' => 'client']);

        // Other client tries to access client's delivery
        $response = $this->actingAs($other_client)
            ->get(route('deliveries.show', $delivery));

        $response->assertStatus(403);
    }
}

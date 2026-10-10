<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test unified reservation workflow:
 * - Pathway A: Equipment detail page → Reservation form with equipment pre-selected
 * - Pathway B: Reservations page → Reservation form with interactive equipment selector
 * 
 * Both pathways should:
 * - Use the same reservation form
 * - Preserve all validation and business logic
 * - Calculate price correctly server-side
 * - Prevent self-reservations
 * - Check for date conflicts
 */
class UnifiedReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $owner;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client', 'email' => 'client@test.com']);
        $this->owner = User::factory()->create(['role' => 'client', 'email' => 'owner@test.com']);

        $category = Category::create(['name' => 'Équipement Solaire']);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 400W',
            'price_per_day' => 50.00,
            'availability' => true,
        ]);
    }

    /**
     * Test Pathway A: Access reservation form from equipment detail page with equipment_id query parameter
     * Equipment should be pre-selected and displayed as read-only
     */
    public function test_pathway_a_access_form_from_equipment_detail_with_prefilled_equipment(): void
    {
        $this->actingAs($this->client);

        // Simulate accessing from equipment detail page with equipment_id query parameter
        $response = $this->get(route('reservations.create', [
            'equipment_id' => $this->equipment->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('reservations.create');

        // Verify preFilledData is passed with equipment_id
        $preFilledData = $response->viewData('preFilledData');
        $this->assertNotNull($preFilledData);
        $this->assertEquals($this->equipment->id, $preFilledData['equipment_id']);

        // Verify equipment is in the equipments list for display
        $equipments = $response->viewData('equipments');
        $this->assertTrue($equipments->contains('id', $this->equipment->id));
    }

    /**
     * Test Pathway A: Form submission with equipment pre-filled from equipment detail
     * Should create reservation with correct price calculation
     */
    public function test_pathway_a_create_reservation_with_prefilled_equipment(): void
    {
        $this->actingAs($this->client);

        // First access form with equipment pre-filled
        $formResponse = $this->get(route('reservations.create', [
            'equipment_id' => $this->equipment->id,
            'start_date' => '2026-11-01',
            'days' => 3,
        ]));

        $formResponse->assertStatus(200);
        $preFilledData = $formResponse->viewData('preFilledData');
        $this->assertEquals($this->equipment->id, $preFilledData['equipment_id']);
        $this->assertEquals('2026-11-01', $preFilledData['start_date']);
        $this->assertEquals(3, $preFilledData['days']);

        // Now submit reservation form
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-01',
            'date_fin' => '2026-11-04', // 3 days
        ]);

        $response->assertRedirect();

        // Verify reservation created correctly
        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals($this->equipment->id, $reservation->equipment_id);
        $this->assertEquals('2026-11-01', $reservation->date_debut->format('Y-m-d'));
        $this->assertEquals('2026-11-04', $reservation->date_fin->format('Y-m-d'));
        
        // Verify price is calculated correctly: 3 days × 50 TND/day = 150 TND
        $this->assertEquals(150.00, $reservation->prix_total);
        
        // Verify status is 'en_attente'
        $this->assertEquals('en_attente', $reservation->statut);
    }

    /**
     * Test Pathway B: Access reservation form from reservations index page
     * Equipment selector should be interactive dropdown
     */
    public function test_pathway_b_access_form_from_reservations_page_with_interactive_selector(): void
    {
        $this->actingAs($this->client);

        // Access form without equipment_id (normal reservations page flow)
        $response = $this->get(route('reservations.create'));

        $response->assertStatus(200);
        $response->assertViewIs('reservations.create');

        // Verify preFilledData is empty or no equipment_id
        $preFilledData = $response->viewData('preFilledData');
        $this->assertNull($preFilledData['equipment_id'] ?? null);

        // Verify equipment list is provided for dropdown
        $equipments = $response->viewData('equipments');
        $this->assertGreaterThan(0, $equipments->count());
        $this->assertTrue($equipments->contains('id', $this->equipment->id));
    }

    /**
     * Test Pathway B: Form submission with interactive equipment selection
     * Should create reservation with correct price calculation
     */
    public function test_pathway_b_create_reservation_with_interactive_selector(): void
    {
        $this->actingAs($this->client);

        // Submit reservation with equipment selected from dropdown
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-12-01',
            'date_fin' => '2026-12-08', // 7 days
        ]);

        $response->assertRedirect();

        // Verify reservation created correctly
        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals($this->equipment->id, $reservation->equipment_id);
        
        // Verify price is calculated correctly: 7 days × 50 TND/day = 350 TND
        $this->assertEquals(350.00, $reservation->prix_total);
        
        // Verify status is 'en_attente'
        $this->assertEquals('en_attente', $reservation->statut);
    }

    /**
     * Test both pathways preserve validation rules
     * Should prevent self-reservations (user trying to reserve their own equipment)
     */
    public function test_both_pathways_prevent_self_reservation(): void
    {
        $this->actingAs($this->owner); // Login as equipment owner

        // Pathway A: Try to reserve own equipment from equipment detail
        $responseA = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-01',
            'date_fin' => '2026-11-03',
        ]);

        $responseA->assertSessionHasErrors('equipment_id');
        $this->assertEquals(
            'Vous ne pouvez pas réserver votre propre équipement.',
            session('errors')->getBag('default')->get('equipment_id')[0]
        );

        // Verify no reservation created
        $this->assertEquals(0, Reservation::where('user_id', $this->owner->id)->count());

        // Pathway B: Try to reserve own equipment from reservations page
        $responseB = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-12-01',
            'date_fin' => '2026-12-03',
        ]);

        $responseB->assertSessionHasErrors('equipment_id');

        // Verify still no reservation created
        $this->assertEquals(0, Reservation::where('user_id', $this->owner->id)->count());
    }

    /**
     * Test both pathways preserve date validation
     * Should require future dates and end date after start date
     */
    public function test_both_pathways_validate_dates(): void
    {
        $this->actingAs($this->client);

        // Try with end date before start date
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-12-05',
            'date_fin' => '2026-12-03', // Before start date
        ]);

        $response->assertSessionHasErrors('date_fin');

        // Verify no reservation created
        $this->assertEquals(0, Reservation::where('user_id', $this->client->id)->count());

        // Try with past dates
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2020-01-01', // Past date
            'date_fin' => '2020-01-05',
        ]);

        $response->assertSessionHasErrors('date_debut');

        // Verify still no reservation created
        $this->assertEquals(0, Reservation::where('user_id', $this->client->id)->count());
    }

    /**
     * Test both pathways check for date conflicts
     * Should prevent overlapping reservations
     */
    public function test_both_pathways_prevent_date_conflicts(): void
    {
        $this->actingAs($this->client);

        // Create first reservation
        $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-01',
            'date_fin' => '2026-11-05',
        ]);

        $firstReservation = Reservation::where('user_id', $this->client->id)->first();
        $this->assertNotNull($firstReservation);

        // Login as different client to try overlapping reservation
        $other_client = User::factory()->create(['role' => 'client', 'email' => 'other@test.com']);
        $this->actingAs($other_client);

        // Try to book overlapping period
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-03', // Overlaps with first reservation
            'date_fin' => '2026-11-07',
        ]);

        $response->assertSessionHasErrors('date_debut');

        // Verify no overlapping reservation created
        $this->assertEquals(0, Reservation::where('user_id', $other_client->id)->count());
    }

    /**
     * Test server-side price calculation (both pathways)
     * Price should be calculated on server, not trusted from browser
     */
    public function test_server_side_price_calculation_both_pathways(): void
    {
        $this->actingAs($this->client);

        // Submit with wrong price (should be ignored, server calculates)
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-10',
            'date_fin' => '2026-11-15', // 5 days
            // Note: No prix_total in request (client can't set it anyway since it's not in form)
        ]);

        $response->assertRedirect();

        // Verify price is calculated correctly by server
        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $this->assertEquals(250.00, $reservation->prix_total); // 5 days × 50 TND = 250 TND (not whatever client tried to send)
    }

    /**
     * Test form shows correct available equipment (excluding owner's own equipment)
     */
    public function test_equipment_selector_excludes_users_own_equipment(): void
    {
        $category = Category::create(['name' => 'Autre Équipement']);
        
        // Client's own equipment
        $clientEquipment = Equipment::factory()->create([
            'user_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Client Equipment',
            'price_per_day' => 30.00,
        ]);

        $this->actingAs($this->client);

        // Access form
        $response = $this->get(route('reservations.create'));

        $response->assertStatus(200);

        // Verify equipment list doesn't include client's own equipment
        $equipments = $response->viewData('equipments');
        $this->assertFalse($equipments->contains('id', $clientEquipment->id));
        
        // But includes other client's equipment
        $this->assertTrue($equipments->contains('id', $this->equipment->id));
    }
}

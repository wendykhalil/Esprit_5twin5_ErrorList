<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use App\Services\EquipmentAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilitySystemTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $client;
    private Equipment $equipment;
    private EquipmentAvailabilityService $service;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Equipment Owner']);
        $this->client = User::factory()->create(['name' => 'Client']);
        
        // Create a category for the equipment (direct creation, no factory)
        $this->category = Category::create(['name' => 'Solar Equipment']);
        
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $this->category->id,
            'name' => 'Solar Panel',
            'price_per_day' => 100.00,
            'availability' => true,
            'status' => 'active',
        ]);

        $this->service = new EquipmentAvailabilityService();
    }

    /**
     * Test 1: An approved reservation blocks overlapping dates
     */
    public function test_approved_reservation_blocks_overlapping_dates()
    {
        // Create an approved reservation
        $reservation = Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        // Check that dates in the period are blocked
        $this->assertTrue(
            $this->service->hasOverlap($this->equipment, '2027-02-05', '2027-02-10'),
            'Reservation blocking dates should return true for overlap'
        );

        // Check that a different period is not blocked
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-02-15', '2027-02-20'),
            'Non-overlapping period should return false'
        );
    }

    /**
     * Test 2: A pending (en_attente) reservation blocks overlapping dates
     */
    public function test_pending_reservation_blocks_overlapping_dates()
    {
        // Create a pending reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'en_attente',
            'prix_total' => 1000.00,
        ]);

        // Pending should block availability
        $this->assertTrue(
            $this->service->hasOverlap($this->equipment, '2027-02-05', '2027-02-10'),
            'Pending reservation should block dates'
        );
    }

    /**
     * Test 3: A rejected reservation does NOT block dates
     */
    public function test_rejected_reservation_does_not_block_dates()
    {
        // Create a rejected reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'refusee',
            'prix_total' => 1000.00,
        ]);

        // Rejected should NOT block availability
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-02-05', '2027-02-10'),
            'Rejected reservation should NOT block dates'
        );
    }

    /**
     * Test 4: A cancelled reservation does NOT block dates
     */
    public function test_cancelled_reservation_does_not_block_dates()
    {
        // Create a cancelled reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'annulea',
            'prix_total' => 1000.00,
        ]);

        // Cancelled should NOT block availability
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-02-05', '2027-02-10'),
            'Cancelled reservation should NOT block dates'
        );
    }

    /**
     * Test 5: A completed reservation does NOT block future periods
     */
    public function test_completed_reservation_does_not_block_future_periods()
    {
        // Create a completed (historical) reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'terminee',
            'prix_total' => 1000.00,
        ]);

        // Completed should NOT block future availability
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-02-15', '2027-02-20'),
            'Completed reservation should NOT block future dates'
        );
    }

    /**
     * Test 6: A non-overlapping period is accepted
     */
    public function test_non_overlapping_period_is_accepted()
    {
        // Create a reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        // Check a completely different period
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-03-01', '2027-03-10'),
            'Non-overlapping period should be available'
        );
    }

    /**
     * Test 7: Half-open interval convention - touching dates are allowed
     * Reservation 1: [2027-02-01, 2027-02-11)
     * Reservation 2: [2027-02-11, 2027-02-18) -- should be allowed, same end/start date
     */
    public function test_half_open_interval_touching_dates_are_allowed()
    {
        // Create first reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        // Check that same end/start date is allowed (half-open interval)
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-02-11', '2027-02-18'),
            'Reservation starting when another ends should be allowed (half-open interval)'
        );
    }

    /**
     * Test 8: Availability endpoint returns only selected equipment's blocked periods
     */
    public function test_availability_endpoint_returns_only_selected_equipment_periods()
    {
        // Create reservations for this equipment
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        // Create another equipment and reservation
        $other_equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $this->category->id,
            'price_per_day' => 50.00,
        ]);

        Reservation::create([
            'equipment_id' => $other_equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-03-01',
            'date_fin' => '2027-03-11',
            'statut' => 'confirmee',
            'prix_total' => 500.00,
        ]);

        // Call availability endpoint for first equipment
        $response = $this->get("/equipements/{$this->equipment->id}/availability");

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals($this->equipment->id, $data['equipment_id']);
        $this->assertCount(1, $data['blocked_periods']);
        $this->assertEquals('2027-02-01', $data['blocked_periods'][0]['start_date']);
        $this->assertEquals('2027-02-11', $data['blocked_periods'][0]['end_date']);

        // Other equipment's dates should NOT be included
        $blocked_dates = array_map(fn($p) => $p['start_date'], $data['blocked_periods']);
        $this->assertNotContains('2027-03-01', $blocked_dates);
    }

    /**
     * Test 9: Availability endpoint does NOT expose private data
     */
    public function test_availability_endpoint_does_not_expose_private_data()
    {
        // Create a reservation with identifiable info
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        $response = $this->get("/equipements/{$this->equipment->id}/availability");
        $data = $response->json();

        // Should NOT contain user info, names, emails, IDs
        $this->assertArrayNotHasKey('user_id', $data);
        $this->assertArrayNotHasKey('client_name', $data);
        $this->assertArrayNotHasKey('client_email', $data);

        // Each period should only have dates
        $period = $data['blocked_periods'][0];
        $this->assertArrayHasKey('start_date', $period);
        $this->assertArrayHasKey('end_date', $period);
        $this->assertArrayNotHasKey('reservation_id', $period);
        $this->assertArrayNotHasKey('user_id', $period);
    }

    /**
     * Test 10: Backend validation rejects overlapping reservation even when frontend is bypassed
     */
    public function test_backend_validation_rejects_overlapping_reservation()
    {
        // Create an existing reservation
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        // Try to create an overlapping reservation (7 days, valid duration)
        $response = $this->actingAs($this->client)->post('/reservations', [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2027-02-05',
            'date_fin' => '2027-02-12', // 7 days, which overlaps
            'compensation_code' => '',
        ]);

        // Should redirect with validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors('date_debut');
    }

    /**
     * Test 11: Invalid equipment and unsupported rental durations are rejected
     */
    public function test_invalid_equipment_and_unsupported_durations_rejected()
    {
        // Create another client to test with valid equipment
        $otherClient = User::factory()->create(['name' => 'Other Client']);
        
        // Test invalid equipment ID (via session error, not AJAX)
        $response = $this->actingAs($this->client)->post('/reservations', [
            'equipment_id' => 99999,
            'date_debut' => '2027-02-05',
            'date_fin' => '2027-02-12', // 7 days - valid duration
            'compensation_code' => '',
        ]);

        $response->assertSessionHasErrors('equipment_id');

        // Test unsupported duration (5 days is not supported: only 1, 3, 7, 14)
        $response = $this->actingAs($otherClient)->post('/reservations', [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2027-02-05',
            'date_fin' => '2027-02-10', // 5 days - unsupported
            'compensation_code' => '',
        ]);

        $response->assertSessionHasErrors('date_fin');
    }

    /**
     * Test 12: Compensation code and server-side price calculation behavior still works
     */
    public function test_compensation_code_and_price_calculation_works()
    {
        // This test verifies that price calculation still works correctly
        $this->actingAs($this->client)->post('/reservations', [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-08', // 7 days
            'compensation_code' => '',
        ]);

        $reservation = Reservation::where('equipment_id', $this->equipment->id)
            ->where('user_id', $this->client->id)
            ->first();

        // Price should be 7 days × 100 TND = 700 TND
        $this->assertEquals(700.00, $reservation->prix_total);
    }

    /**
     * Test 13: Admin rejection makes the period available again
     */
    public function test_admin_rejection_makes_period_available()
    {
        // Create a pending reservation
        $reservation = Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'en_attente',
            'prix_total' => 1000.00,
        ]);

        // Verify it blocks availability
        $this->assertTrue(
            $this->service->hasOverlap($this->equipment, '2027-02-05', '2027-02-10')
        );

        // Reject the reservation (admin action)
        $reservation->update(['statut' => 'refusee']);

        // Verify dates are now available
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2027-02-05', '2027-02-10')
        );
    }

    /**
     * Test 14: Availability service handles multiple blocked periods correctly
     */
    public function test_availability_service_returns_multiple_blocked_periods()
    {
        // Create multiple reservations
        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'confirmee',
            'prix_total' => 1000.00,
        ]);

        Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-20',
            'date_fin' => '2027-02-27',
            'statut' => 'en_attente',
            'prix_total' => 700.00,
        ]);

        $blockedPeriods = $this->service->getBlockedPeriods($this->equipment);

        $this->assertCount(2, $blockedPeriods);
        $this->assertEquals('2027-02-01', $blockedPeriods[0]['start_date']);
        $this->assertEquals('2027-02-11', $blockedPeriods[0]['end_date']);
        $this->assertEquals('2027-02-20', $blockedPeriods[1]['start_date']);
        $this->assertEquals('2027-02-27', $blockedPeriods[1]['end_date']);
    }

    /**
     * Test 15: Client cannot create reservation for own equipment
     */
    public function test_client_cannot_create_reservation_for_own_equipment()
    {
        // Create an equipment owned by the owner
        $ownEquipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $this->category->id,
            'price_per_day' => 100.00,
        ]);
        
        // Try to create a reservation for own equipment (as owner)
        $response = $this->actingAs($this->owner)->post('/reservations', [
            'equipment_id' => $ownEquipment->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-08', // 7 days
            'compensation_code' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('equipment_id');
    }

    /**
     * Test 16: Availability service excludes specific reservation when provided (for editing)
     */
    public function test_availability_service_excludes_reservation_for_editing()
    {
        // Create a reservation
        $reservation = Reservation::create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'date_debut' => '2027-02-01',
            'date_fin' => '2027-02-11',
            'statut' => 'en_attente',
            'prix_total' => 1000.00,
        ]);

        // When editing the same reservation, it should NOT be counted as an overlap
        $this->assertFalse(
            $this->service->hasOverlap(
                $this->equipment,
                '2027-02-01',
                '2027-02-11',
                $reservation // Exclude this reservation
            ),
            'Editing a reservation should not count as overlap with itself'
        );
    }
}

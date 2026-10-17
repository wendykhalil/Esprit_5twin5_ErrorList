<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\Delivery;
use App\Models\User;
use App\Services\EquipmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentAvailabilityCompletedDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private EquipmentAvailabilityService $service;
    private Equipment $equipment;
    private User $owner;
    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new EquipmentAvailabilityService();
        
        // Create owner and client users
        $this->owner = User::factory()->create();
        $this->client = User::factory()->create();
        
        // Create equipment
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'active',
        ]);
    }

    /**
     * Scenario A: Completed delivery should not block dates
     */
    public function test_completed_delivery_does_not_block_dates()
    {
        // Create a reservation with confirmed status
        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-18'),
            'date_fin' => Carbon::parse('2026-10-21'),
        ]);

        // Create a completed delivery for this reservation
        $delivery = Delivery::factory()->create([
            'reservation_id' => $reservation->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'terminee',
            'actual_return_date' => Carbon::parse('2026-10-16 14:34'),
            'planned_delivery_date' => Carbon::parse('2026-10-18'),
            'planned_return_date' => Carbon::parse('2026-10-21'),
        ]);

        // Reload reservation with delivery relationship
        $reservation->refresh();

        // Test: the original planned dates should NOT be blocked
        $hasOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-10-18',
            '2026-10-21'
        );

        $this->assertFalse($hasOverlap, 'Completed delivery should not block its original dates');
    }

    /**
     * Scenario B: Active reservation should still block dates
     */
    public function test_active_reservation_blocks_dates()
    {
        // Create an active reservation
        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-25'),
        ]);

        // Create a non-completed delivery
        Delivery::factory()->create([
            'reservation_id' => $reservation->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'remise_au_client', // Not yet returned
            'planned_delivery_date' => Carbon::parse('2026-10-20'),
            'planned_return_date' => Carbon::parse('2026-10-25'),
        ]);

        $reservation->refresh();

        // Test: the dates SHOULD be blocked
        $hasOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-10-20',
            '2026-10-25'
        );

        $this->assertTrue($hasOverlap, 'Active reservation should block its dates');
    }

    /**
     * Scenario C: Cancelled reservation should not block dates
     */
    public function test_cancelled_reservation_does_not_block_dates()
    {
        // Create a cancelled reservation
        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'annulea', // Cancelled
            'date_debut' => Carbon::parse('2026-11-01'),
            'date_fin' => Carbon::parse('2026-11-05'),
        ]);

        // Test: cancelled dates should NOT be blocked
        $hasOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-11-01',
            '2026-11-05'
        );

        $this->assertFalse($hasOverlap, 'Cancelled reservation should not block dates');
    }

    /**
     * Scenario D: Multiple reservations - completed + active
     */
    public function test_multiple_reservations_completed_and_active()
    {
        // First: completed reservation
        $completed = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-10'),
            'date_fin' => Carbon::parse('2026-10-15'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $completed->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'terminee',
            'actual_return_date' => Carbon::parse('2026-10-14'),
            'planned_delivery_date' => Carbon::parse('2026-10-10'),
            'planned_return_date' => Carbon::parse('2026-10-15'),
        ]);

        // Second: active reservation
        $active = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-25'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $active->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'remise_au_client',
            'planned_delivery_date' => Carbon::parse('2026-10-20'),
            'planned_return_date' => Carbon::parse('2026-10-25'),
        ]);

        // Test 1: completed dates NOT blocked
        $completedOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-10-10',
            '2026-10-15'
        );
        $this->assertFalse($completedOverlap, 'Completed reservation dates should not be blocked');

        // Test 2: active dates ARE blocked
        $activeOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-10-20',
            '2026-10-25'
        );
        $this->assertTrue($activeOverlap, 'Active reservation dates should be blocked');

        // Test 3: dates between reservations are available
        $betweenOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-10-15',
            '2026-10-20'
        );
        $this->assertFalse($betweenOverlap, 'Dates between reservations should be available');
    }

    /**
     * Scenario E: getBlockedPeriods should exclude completed deliveries
     */
    public function test_get_blocked_periods_excludes_completed_deliveries()
    {
        // Completed reservation
        $completed = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-10'),
            'date_fin' => Carbon::parse('2026-10-15'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $completed->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'terminee',
            'planned_delivery_date' => Carbon::parse('2026-10-10'),
            'planned_return_date' => Carbon::parse('2026-10-15'),
        ]);

        // Active reservation
        $active = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_attente',
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-22'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $active->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'prete',
            'planned_delivery_date' => Carbon::parse('2026-10-20'),
            'planned_return_date' => Carbon::parse('2026-10-22'),
        ]);

        // Get blocked periods
        $blockedPeriods = $this->service->getBlockedPeriods($this->equipment);

        // Should only have 1 blocked period (the active one)
        $this->assertCount(1, $blockedPeriods, 'Should have only 1 blocked period (active reservation)');

        // Verify it's the active one
        $this->assertEquals('2026-10-20', $blockedPeriods->first()['start_date']);
        $this->assertEquals('2026-10-22', $blockedPeriods->first()['end_date']);
    }

    /**
     * Scenario F: shouldBlockDates helper method
     */
    public function test_should_block_dates_helper_method()
    {
        // Test 1: completed delivery - should NOT block
        $completed = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-10'),
            'date_fin' => Carbon::parse('2026-10-15'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $completed->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'terminee',
            'planned_delivery_date' => Carbon::parse('2026-10-10'),
            'planned_return_date' => Carbon::parse('2026-10-15'),
        ]);

        $completed->refresh();
        $this->assertFalse($completed->shouldBlockDates(), 'Completed delivery reservation should not block');

        // Test 2: cancelled reservation - should NOT block
        $cancelled = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'annulea',
            'date_debut' => Carbon::parse('2026-11-01'),
            'date_fin' => Carbon::parse('2026-11-05'),
        ]);

        $this->assertFalse($cancelled->shouldBlockDates(), 'Cancelled reservation should not block');

        // Test 3: active reservation - SHOULD block
        $active = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-25'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $active->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'prete',
            'planned_delivery_date' => Carbon::parse('2026-10-20'),
            'planned_return_date' => Carbon::parse('2026-10-25'),
        ]);

        $active->refresh();
        $this->assertTrue($active->shouldBlockDates(), 'Active reservation should block');
    }

    /**
     * Scenario G: Overlapping logic with completed and active reservations
     */
    public function test_overlap_detection_with_mixed_reservations()
    {
        // Completed reservation: Oct 10-15
        $completed = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'confirmee',
            'date_debut' => Carbon::parse('2026-10-10'),
            'date_fin' => Carbon::parse('2026-10-15'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $completed->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'terminee',
            'planned_delivery_date' => Carbon::parse('2026-10-10'),
            'planned_return_date' => Carbon::parse('2026-10-15'),
        ]);

        // Active reservation: Oct 20-25
        $active = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_cours',
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-25'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $active->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'remise_au_client',
            'planned_delivery_date' => Carbon::parse('2026-10-20'),
            'planned_return_date' => Carbon::parse('2026-10-25'),
        ]);

        // Test various date ranges
        
        // Within completed range - should NOT block
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2026-10-12', '2026-10-14'),
            'Should allow new reservation within completed delivery dates'
        );

        // Within active range - SHOULD block
        $this->assertTrue(
            $this->service->hasOverlap($this->equipment, '2026-10-22', '2026-10-24'),
            'Should block new reservation within active dates'
        );

        // Between completed and active - should NOT block
        $this->assertFalse(
            $this->service->hasOverlap($this->equipment, '2026-10-15', '2026-10-20'),
            'Should allow new reservation between completed and active'
        );

        // Overlapping both - should block (overlaps with active)
        $this->assertTrue(
            $this->service->hasOverlap($this->equipment, '2026-10-12', '2026-10-22'),
            'Should block if overlaps with active reservation'
        );
    }

    /**
     * Scenario H: Reservation editing should exclude itself
     */
    public function test_editing_reservation_excludes_itself()
    {
        // Create a confirmed reservation
        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_attente',
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-25'),
        ]);

        Delivery::factory()->create([
            'reservation_id' => $reservation->id,
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'prete',
            'planned_delivery_date' => Carbon::parse('2026-10-20'),
            'planned_return_date' => Carbon::parse('2026-10-25'),
        ]);

        // When editing, exclude the reservation itself
        $hasOverlap = $this->service->hasOverlap(
            $this->equipment,
            '2026-10-20',
            '2026-10-25',
            $reservation
        );

        // Should NOT have overlap when checking against itself
        $this->assertFalse($hasOverlap, 'Editing should not conflict with itself');
    }
}

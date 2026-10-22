<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ReservationDateOffsetTest
 *
 * Tests to verify that the one-day date offset issue is fixed.
 * Validates:
 * 1. Selected calendar dates are preserved exactly (no offset)
 * 2. Today's date is allowed as a valid start date
 * 3. Dates before today are rejected
 * 4. Valid future reservations are accepted
 * 5. End dates follow the correct rules
 * 6. Overlapping reservations are rejected
 * 7. Date-only values are not shifted by one day
 * 8. Invalid submissions do not create reservation records
 */
class ReservationDateOffsetTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $provider;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create category first
        $category = \App\Models\Category::create([
            'name' => 'Solar Equipment',
            'description' => 'Solar and renewable energy equipment'
        ]);

        // Create provider and equipment
        $this->provider = User::factory()->create(['role' => 'provider']);
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->provider->id,
            'category_id' => $category->id,
            'price_per_day' => 100.00,
            'availability' => true,
            'status' => 'active'
        ]);

        // Create client
        $this->client = User::factory()->create(['role' => 'client']);
    }

    /**
     * Test 1: Selecting a date preserves the exact YYYY-MM-DD value
     * When selecting October 17, 2026, the submitted value should be 2026-10-17
     * Not 2026-10-16
     */
    public function test_selected_date_preserves_exact_yyyy_mm_dd_value(): void
    {
        $this->actingAs($this->client);

        $selectedDate = '2026-11-17';
        $endDate = '2026-11-18';

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $selectedDate,
            'date_fin' => $endDate,
        ]);

        // Verify the exact date is stored (not shifted by one day)
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => $selectedDate,
            'date_fin' => $endDate,
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Test 2: A reservation starting today is accepted when available
     * If today is October 16, 2026, submitting 2026-10-16 should be accepted
     * (provided the business rules allow same-day reservations)
     */
    public function test_reservation_starting_today_is_accepted_when_available(): void
    {
        $this->actingAs($this->client);

        $today = now()->format('Y-m-d');
        $endDate = now()->addDay()->format('Y-m-d');

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $today,
            'date_fin' => $endDate,
        ]);

        // Should succeed (no 422 validation error)
        $this->assertNotEquals(422, $response->status(), 
            'Reservation starting today should be accepted when available');

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => $today,
        ]);
    }

    /**
     * Test 3: A date before today is rejected
     * Submitting a date strictly in the past should be rejected
     */
    public function test_date_before_today_is_rejected(): void
    {
        $this->actingAs($this->client);

        $yesterday = now()->subDay()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $yesterday,
            'date_fin' => $today,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_debut']);

        $this->assertDatabaseMissing('reservations', [
            'date_debut' => $yesterday,
        ]);
    }

    /**
     * Test 4: A valid future reservation is accepted
     * Submitting dates well in the future with valid duration should work
     */
    public function test_valid_future_reservation_is_accepted(): void
    {
        $this->actingAs($this->client);

        $startDate = now()->addDays(10)->format('Y-m-d');
        $endDate = now()->addDays(17)->format('Y-m-d'); // 7 days later

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
        ]);

        $this->assertNotEquals(422, $response->status(), 
            'Valid future reservation should be accepted');

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Test 5: End date must be after start date
     * Submitting an end date before or equal to start date should be rejected
     */
    public function test_end_date_must_be_after_start_date(): void
    {
        $this->actingAs($this->client);

        $startDate = now()->addDays(5)->format('Y-m-d');
        $sameDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $sameDate,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_fin']);
    }

    /**
     * Test 6: An overlapping reservation is rejected
     * Creating a second reservation that overlaps with an existing one should fail
     */
    public function test_overlapping_reservation_is_rejected(): void
    {
        $this->actingAs($this->client);

        // Create first reservation
        $firstStart = now()->addDays(10)->format('Y-m-d');
        $firstEnd = now()->addDays(17)->format('Y-m-d');

        $response1 = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $firstStart,
            'date_fin' => $firstEnd,
        ]);

        $this->assertDatabaseHas('reservations', [
            'date_debut' => $firstStart,
            'date_fin' => $firstEnd,
        ]);

        // Try to create an overlapping reservation
        $overlapStart = now()->addDays(15)->format('Y-m-d');
        $overlapEnd = now()->addDays(22)->format('Y-m-d');

        $response2 = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $overlapStart,
            'date_fin' => $overlapEnd,
        ]);

        $response2->assertStatus(422)
                  ->assertJsonValidationErrors(['date_debut']);

        // Verify only one reservation was created
        $this->assertEquals(1, Reservation::where('equipment_id', $this->equipment->id)->count());
    }

    /**
     * Test 7: Date-only value is not shifted by one day
     * Verify that a YYYY-MM-DD string stored in the database matches the submitted value
     * This tests the core issue: ensuring no UTC-to-local timezone conversion shifts the date
     */
    public function test_date_only_value_is_not_shifted_by_one_day(): void
    {
        $this->actingAs($this->client);

        $submittedStart = '2026-11-17';
        $submittedEnd = '2026-11-24';

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $submittedStart,
            'date_fin' => $submittedEnd,
        ]);

        $reservation = Reservation::where('equipment_id', $this->equipment->id)->first();
        
        // Cast to string to compare dates
        $storedStart = $reservation->date_debut->format('Y-m-d');
        $storedEnd = $reservation->date_fin->format('Y-m-d');

        $this->assertEquals($submittedStart, $storedStart, 
            'Stored start date must match submitted value (no one-day offset)');
        
        $this->assertEquals($submittedEnd, $storedEnd, 
            'Stored end date must match submitted value (no one-day offset)');
    }

    /**
     * Test 8: Invalid submissions do not create reservation records
     * Verifying that failed validation prevents database writes
     */
    public function test_invalid_submission_does_not_create_reservation(): void
    {
        $this->actingAs($this->client);

        $initialCount = Reservation::count();

        // Submit invalid data (no date_debut)
        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '',
            'date_fin' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertStatus(422);

        // Count should not increase
        $this->assertEquals($initialCount, Reservation::count(), 
            'Failed validation should not create reservation records');
    }

    /**
     * Test 9: Supported rental durations are enforced
     * Only 1, 3, 7, 14 day rentals are allowed
     */
    public function test_unsupported_rental_duration_is_rejected(): void
    {
        $this->actingAs($this->client);

        $startDate = now()->addDays(5)->format('Y-m-d');
        $endDate = now()->addDays(6)->format('Y-m-d'); // 1 day - supported

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
        ]);

        // 1 day is supported, so should pass
        $this->assertNotEquals(422, $response->status());

        // Try 2 days - not supported
        $startDate2 = now()->addDays(20)->format('Y-m-d');
        $endDate2 = now()->addDays(25)->format('Y-m-d'); // 5 days, outside the one-day tolerance

        $response2 = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate2,
            'date_fin' => $endDate2,
        ]);

        $response2->assertStatus(422)
                  ->assertJsonValidationErrors(['date_fin']);
    }

    /**
     * Test 10: User cannot reserve their own equipment
     * A provider should not be able to reserve their own equipment
     */
    public function test_user_cannot_reserve_own_equipment(): void
    {
        $this->actingAs($this->provider);

        $startDate = now()->addDays(5)->format('Y-m-d');
        $endDate = now()->addDays(12)->format('Y-m-d');

        $response = $this->postJson(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['equipment_id']);

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $this->provider->id,
            'equipment_id' => $this->equipment->id,
        ]);
    }
}

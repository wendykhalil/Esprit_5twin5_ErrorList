<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\CompensationCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ConsolidatedReservationFormTest
 *
 * Tests for the new consolidated reservation form integrated into the equipment detail page.
 * Validates the single-page reservation flow that replaces the previous two-page process.
 */
class ConsolidatedReservationFormTest extends TestCase
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
            'price_per_day' => 100,
            'availability' => true,
            'status' => 'active'
        ]);

        // Create client
        $this->client = User::factory()->create(['role' => 'client']);
    }

    /**
     * Test: Valid reservation submission from consolidated form
     * Should create a reservation with correct dates and price
     */
    public function test_valid_reservation_submission_from_consolidated_form(): void
    {
        $this->actingAs($this->client);

        $startDate = now()->addDay()->format('Y-m-d');
        $endDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
            'compensation_code' => null,
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'redirect']);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
            'statut' => 'en_attente',
            'prix_total' => 100, // 1 day × 100 per day (diffInDays counts as 1 day)
        ]);
    }

    /**
     * Test: Missing start date validation
     * Should reject submission without date_debut
     */
    public function test_missing_start_date_validation(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '',
            'date_fin' => now()->addDays(2)->format('Y-m-d'),
            'compensation_code' => null,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_debut']);
    }

    /**
     * Test: Past start date validation
     * Should reject dates in the past
     */
    public function test_past_start_date_validation(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->subDay()->format('Y-m-d'),
            'date_fin' => now()->addDay()->format('Y-m-d'),
            'compensation_code' => null,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_debut']);
    }

    /**
     * Test: Today as start date is valid
     * Should allow today as start date
     */
    public function test_today_as_start_date_is_valid(): void
    {
        $this->actingAs($this->client);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
            'compensation_code' => null,
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reservations', [
            'date_debut' => $startDate,
        ]);
    }

    /**
     * Test: Invalid duration validation
     * Should only accept 1, 3, 7, or 14 day durations
     */
    public function test_invalid_duration_validation(): void
    {
        $this->actingAs($this->client);

        // Test with 2 days (not supported)
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(3)->format('Y-m-d'), // 2 days
            'compensation_code' => null,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_fin']);
    }

    /**
     * Test: Supported duration validation
     * Should accept 1, 3, 7, and 14 day durations
     */
    public function test_supported_durations_validation(): void
    {
        $this->actingAs($this->client);

        foreach ([1, 3, 7, 14] as $days) {
            $startDate = now()->addDays(10 + ($days * 2))->format('Y-m-d');
            $endDate = now()->addDays(10 + ($days * 2) + $days)->format('Y-m-d');

            $response = $this->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => $startDate,
                'date_fin' => $endDate,
                'compensation_code' => null,
            ], [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]);

            $response->assertStatus(200, "Failed for duration: {$days} days");
        }
    }

    /**
     * Test: Price calculation not trusted from client
     * Should ignore any total sent by frontend and recalculate on server
     */
    public function test_price_calculated_server_side_not_trusted(): void
    {
        $this->actingAs($this->client);

        $startDate = now()->addDay()->format('Y-m-d');
        $endDate = now()->addDays(4)->format('Y-m-d'); // 3 days

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
            'compensation_code' => null,
            // Client tries to send manipulated price (should be ignored)
            'prix_total' => '999.99',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);

        // Verify actual price is 3 × 100 = 300, not 999.99
        $reservation = Reservation::where('user_id', $this->client->id)->first();
        $this->assertEquals(300, $reservation->prix_total);
    }

    /**
     * Test: Overlapping reservation detection
     * Should prevent booking same equipment during reserved period
     */
    public function test_overlapping_reservation_detection(): void
    {
        $this->actingAs($this->client);

        // Create existing reservation
        $existing = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(5),
            'date_fin' => now()->addDays(8),
            'statut' => 'confirmee',
            'prix_total' => 300,
        ]);

        // Try to book overlapping period
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(6)->format('Y-m-d'),
            'date_fin' => now()->addDays(9)->format('Y-m-d'),
            'compensation_code' => null,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_debut']);

        // Verify only one reservation exists
        $this->assertEquals(1, Reservation::where('equipment_id', $this->equipment->id)->count());
    }

    /**
     * Test: Non-overlapping reservation after existing is allowed
     * Should allow booking after an existing reservation ends
     */
    public function test_non_overlapping_reservation_allowed(): void
    {
        $this->actingAs($this->client);

        // Create existing reservation
        Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(5),
            'date_fin' => now()->addDays(8),
            'statut' => 'confirmee',
            'prix_total' => 300,
        ]);

        // Book after existing ends
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(8)->format('Y-m-d'),
            'date_fin' => now()->addDays(11)->format('Y-m-d'),
            'compensation_code' => null,
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(2, Reservation::where('equipment_id', $this->equipment->id)->count());
    }

    /**
     * Test: Invalid compensation code rejection
     * Should reject non-existent codes
     */
    public function test_invalid_compensation_code_rejection(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => 'INVALID_CODE_12345',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['compensation_code']);
    }

    /**
     * Test: Expired compensation code rejection
     * Should reject expired codes
     */
    public function test_expired_compensation_code_rejection(): void
    {
        $this->actingAs($this->client);

        // Create expired compensation code
        $code = CompensationCode::create([
            'user_id' => $this->client->id,
            'code' => 'EXPIRED_CODE',
            'type' => 'fixed',
            'value' => 10,
            'is_active' => true,
            'valid_from' => now()->subDays(10),
            'valid_until' => now()->subDay(),
            'max_uses' => 5,
            'uses_count' => 0,
        ]);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => 'EXPIRED_CODE',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['compensation_code']);
    }

    /**
     * Test: Inactive compensation code rejection
     * Should reject disabled codes
     */
    public function test_inactive_compensation_code_rejection(): void
    {
        $this->actingAs($this->client);

        // Create inactive code
        $code = CompensationCode::create([
            'user_id' => $this->client->id,
            'code' => 'INACTIVE_CODE',
            'type' => 'fixed',
            'value' => 10,
            'is_active' => false,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(10),
            'max_uses' => 5,
            'uses_count' => 0,
        ]);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => 'INACTIVE_CODE',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['compensation_code']);
    }

    /**
     * Test: Exhausted compensation code rejection
     * Should reject codes with no remaining uses
     */
    public function test_exhausted_compensation_code_rejection(): void
    {
        $this->actingAs($this->client);

        // Create exhausted code
        $code = CompensationCode::create([
            'user_id' => $this->client->id,
            'code' => 'EXHAUSTED_CODE',
            'type' => 'fixed',
            'value' => 10,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(10),
            'max_uses' => 1,
            'uses_count' => 1,
        ]);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => 'EXHAUSTED_CODE',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['compensation_code']);
    }

    /**
     * Test: Valid compensation code acceptance
     * Should accept and apply valid codes
     */
    public function test_valid_compensation_code_acceptance(): void
    {
        $this->actingAs($this->client);

        // Create valid code
        $code = CompensationCode::create([
            'user_id' => $this->client->id,
            'code' => 'VALID_CODE',
            'type' => 'fixed',
            'value' => 50,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(10),
            'max_uses' => 5,
            'uses_count' => 0,
        ]);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => 'VALID_CODE',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);

        $reservation = Reservation::where('user_id', $this->client->id)->first();
        $this->assertNotNull($reservation->compensation_code_id);
        $this->assertEquals(300 - 50, $reservation->prix_total); // 3 days × 100 - 50 discount
    }

    /**
     * Test: Self-reservation prevention
     * Should prevent provider from reserving their own equipment
     */
    public function test_self_reservation_prevention(): void
    {
        $this->actingAs($this->provider);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => null,
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        // Should fail validation (either 422 JSON or 302 redirect)
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(422),
                $this->equalTo(302)
            )
        );
    }

    /**
     * Test: End date after start date validation
     * Should reject end date that is not after start date
     */
    public function test_end_date_after_start_date_validation(): void
    {
        $this->actingAs($this->client);

        $date = now()->addDay()->format('Y-m-d');

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $date,
            'date_fin' => $date, // Same day
            'compensation_code' => null,
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        // Should fail validation (either 422 JSON or 302 redirect)
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(422),
                $this->equalTo(302)
            )
        );
    }

    /**
     * Test: Unauthenticated user cannot submit
     * Should redirect to login
     */
    public function test_unauthenticated_user_cannot_submit(): void
    {
        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDay()->format('Y-m-d'),
            'date_fin' => now()->addDays(4)->format('Y-m-d'),
            'compensation_code' => null,
        ]);

        $response->assertRedirect(route('login'));
    }
}

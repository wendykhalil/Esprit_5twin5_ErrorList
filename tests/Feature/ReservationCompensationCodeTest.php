<?php

namespace Tests\Feature;

use App\Models\CompensationCode;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCompensationCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $provider;
    protected Equipment $equipment;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create category first
        $this->category = Category::create(['name' => 'Équipement Solaire']);

        // Create users
        $this->client = User::factory()->create(['role' => 'client']);
        $this->provider = User::factory()->create(['role' => 'provider']);

        // Create equipment owned by provider with required category_id
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->provider->id,
            'price_per_day' => 100, // 100 TND per day
            'category_id' => $this->category->id,
        ]);
    }

    // =====================================================================
    // TEST 1: Percentage-based compensation code discount
    // =====================================================================
    public function test_reservation_with_percentage_compensation_code_applies_discount(): void
    {
        // Create 20% discount code
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertRedirect(route('reservations.show', 1));

        $reservation = Reservation::first();

        // Base price: 1 day × 100 TND = 100 TND
        // Discount: 20% of 100 = 20 TND
        // Final: 100 - 20 = 80 TND
        $this->assertEquals(100.00, $reservation->base_price);
        $this->assertEquals(20.00, $reservation->discount_amount);
        $this->assertEquals(80.00, $reservation->prix_total);
        $this->assertEquals($code->id, $reservation->compensation_code_id);
    }

    // =====================================================================
    // TEST 2: Fixed-amount compensation code discount
    // =====================================================================
    public function test_reservation_with_fixed_compensation_code_applies_discount(): void
    {
        // Create 30 TND fixed discount code
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'fixed',
            'value' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDays(2)->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertRedirect();

        $reservation = Reservation::first();

        // Base price: 2 days × 100 TND = 200 TND
        // Discount: 30 TND fixed
        // Final: 200 - 30 = 170 TND
        $this->assertEquals(200.00, $reservation->base_price);
        $this->assertEquals(30.00, $reservation->discount_amount);
        $this->assertEquals(170.00, $reservation->prix_total);
    }

    // =====================================================================
    // TEST 3: Code belonging to current client
    // =====================================================================
    public function test_code_must_belong_to_client_applying_it(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);

        // Create code for other client
        $code = CompensationCode::factory()->create([
            'user_id' => $otherClient->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertSessionHasErrors('compensation_code')
            ->assertSessionHasErrorsIn('default', ['compensation_code' => 'Ce code de compensation ne vous appartient pas.']);

        // Reservation should not be created
        $this->assertCount(0, Reservation::all());
    }

    // =====================================================================
    // TEST 4: Expired code is rejected
    // =====================================================================
    public function test_expired_compensation_code_is_rejected(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'valid_until' => now()->subDay(), // Expired yesterday
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertSessionHasErrors('compensation_code')
            ->assertSessionHasErrorsIn('default', ['compensation_code' => 'Ce code de compensation a expiré.']);
    }

    // =====================================================================
    // TEST 5: Inactive code is rejected
    // =====================================================================
    public function test_inactive_compensation_code_is_rejected(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => false, // Disabled
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertSessionHasErrors('compensation_code')
            ->assertSessionHasErrorsIn('default', ['compensation_code' => 'Ce code de compensation a été désactivé.']);
    }

    // =====================================================================
    // TEST 6: Code not yet valid (valid_from in future)
    // =====================================================================
    public function test_not_yet_valid_compensation_code_is_rejected(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'valid_from' => now()->addDay()->format('Y-m-d H:i:s'), // Valid tomorrow
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertSessionHasErrors('compensation_code')
            ->assertSessionHasErrorsIn('default', ['compensation_code' => 'Ce code de compensation n\'est pas encore valide.']);
    }

    // =====================================================================
    // TEST 7: Code with exhausted uses is rejected
    // =====================================================================
    public function test_exhausted_compensation_code_is_rejected(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'max_uses' => 1,
            'uses_count' => 1, // Already used
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertSessionHasErrors('compensation_code')
            ->assertSessionHasErrorsIn('default', ['compensation_code' => 'Ce code de compensation a déjà été utilisé.']);
    }

    // =====================================================================
    // TEST 8: Nonexistent code is rejected
    // =====================================================================
    public function test_nonexistent_compensation_code_is_rejected(): void
    {
        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => 'CC-NONEXISTENT',
            ])
            ->assertSessionHasErrors('compensation_code')
            ->assertSessionHasErrorsIn('default', ['compensation_code' => 'Ce code de compensation n\'existe pas.']);
    }

    // =====================================================================
    // TEST 9: Discount amount never exceeds base price
    // =====================================================================
    public function test_discount_amount_never_exceeds_base_price(): void
    {
        // Create code with 150% discount (should be capped at 100%)
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 150, // 150%
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertRedirect();

        $reservation = Reservation::first();

        // Base price: 100 TND
        // Discount should be capped at 100 TND (not 150)
        // Final: max(0, 100 - 100) = 0 TND (but never negative)
        $this->assertEquals(100.00, $reservation->base_price);
        $this->assertEquals(100.00, $reservation->discount_amount); // Capped
        $this->assertEquals(0.00, $reservation->prix_total); // Never negative
    }

    // =====================================================================
    // TEST 10: Code usage counter is incremented atomically
    // =====================================================================
    public function test_code_usage_counter_incremented_on_reservation_creation(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'max_uses' => 5,
            'uses_count' => 0,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ]);

        $code->refresh();

        // Usage count should increment from 0 to 1
        $this->assertEquals(1, $code->uses_count);
        $this->assertEquals(now()->toDateString(), $code->last_used_at->format('Y-m-d'));
    }

    // =====================================================================
    // TEST 11: Failed reservation does not consume code
    // =====================================================================
    public function test_failed_reservation_does_not_consume_code(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'max_uses' => 1,
            'uses_count' => 0,
        ]);

        // Try to create reservation with invalid equipment_id
        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => 99999, // Nonexistent
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertSessionHasErrors('equipment_id');

        // Code should not be consumed
        $code->refresh();
        $this->assertEquals(0, $code->uses_count);
    }

    // =====================================================================
    // TEST 12: Final price is never negative
    // =====================================================================
    public function test_final_price_never_becomes_negative(): void
    {
        // Create code with fixed discount exceeding base price
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'fixed',
            'value' => 500, // 500 TND
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDay()->format('Y-m-d'), // 1 day × 100 = 100 TND base
                'compensation_code' => $code->code,
            ]);

        $reservation = Reservation::first();

        // Final price must be >= 0
        $this->assertGreaterThanOrEqual(0, $reservation->prix_total);
        $this->assertEquals(0.00, $reservation->prix_total);
    }

    // =====================================================================
    // TEST 13: Compensation code can be applied during reservation edit
    // =====================================================================
    public function test_compensation_code_can_be_applied_during_edit(): void
    {
        // Create reservation without code - specify dates to ensure 1 day = 100 TND
        $debut = now()->addDay();
        $fin = $debut->clone()->addDay();

        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => $debut,
            'date_fin' => $fin,
            'statut' => 'en_attente',
            'prix_total' => 100,
        ]);

        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 50, // 50% discount
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->put(route('reservations.update', $reservation), [
                'equipment_id' => $reservation->equipment_id,
                'date_debut' => $reservation->date_debut->format('Y-m-d'),
                'date_fin' => $reservation->date_fin->format('Y-m-d'),
                'compensation_code' => $code->code,
            ])
            ->assertRedirect();

        $reservation->refresh();

        // Discount should be applied
        $this->assertEquals($code->id, $reservation->compensation_code_id);
        $this->assertEquals(100, $reservation->base_price); // Original
        $this->assertEquals(50, $reservation->discount_amount); // 50% of 100
        $this->assertEquals(50, $reservation->prix_total); // Final
    }

    // =====================================================================
    // TEST 14: Compensation code can be removed during edit
    // =====================================================================
    public function test_compensation_code_can_be_removed_during_edit(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 50,
            'is_active' => true,
        ]);

        // Create reservation with code applied - specify dates to ensure 1 day = 100 TND
        $debut = now()->addDay();
        $fin = $debut->clone()->addDay();

        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => $debut,
            'date_fin' => $fin,
            'statut' => 'en_attente',
            'compensation_code_id' => $code->id,
            'base_price' => 100,
            'discount_amount' => 50,
            'prix_total' => 50,
        ]);

        $this->actingAs($this->client)
            ->put(route('reservations.update', $reservation), [
                'equipment_id' => $reservation->equipment_id,
                'date_debut' => $reservation->date_debut->format('Y-m-d'),
                'date_fin' => $reservation->date_fin->format('Y-m-d'),
                'compensation_code' => '', // Empty = remove
            ])
            ->assertRedirect();

        $reservation->refresh();

        // Code should be removed
        $this->assertNull($reservation->compensation_code_id);
        $this->assertEquals(100, $reservation->prix_total); // Back to base price
    }

    // =====================================================================
    // TEST 15: Non-discounted reservations work normally
    // =====================================================================
    public function test_reservation_without_code_works_normally(): void
    {
        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDays(3)->format('Y-m-d'),
                'compensation_code' => '', // No code
            ])
            ->assertRedirect();

        $reservation = Reservation::first();

        // No discount applied
        $this->assertNull($reservation->compensation_code_id);
        $this->assertNull($reservation->base_price);
        $this->assertEquals(0, $reservation->discount_amount);
        $this->assertEquals(300, $reservation->prix_total); // 3 days × 100
    }

    // =====================================================================
    // TEST 16: Verify stored audit trail (immutable pricing)
    // =====================================================================
    public function test_audit_trail_stored_immutably(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 25,
            'is_active' => true,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->format('Y-m-d'),
                'date_fin' => now()->addDays(2)->format('Y-m-d'), // 2 days × 100 = 200
                'compensation_code' => $code->code,
            ]);

        $reservation = Reservation::first();

        // Verify immutable audit trail
        $this->assertNotNull($reservation->base_price);
        $this->assertNotNull($reservation->discount_amount);
        $this->assertEquals(200, $reservation->base_price); // Recorded original price
        $this->assertEquals(50, $reservation->discount_amount); // Recorded discount (25% of 200)

        // Even if we somehow change the code, the reservation data remains unchanged
        $code->update(['value' => 100]); // Change code to 100%

        $reservation->refresh();
        $this->assertEquals(200, $reservation->base_price);
        $this->assertEquals(50, $reservation->discount_amount); // Unchanged!
    }

    // =====================================================================
    // TEST 17: Reservation price breakdown visible in show view
    // =====================================================================
    public function test_discount_breakdown_visible_in_show_view(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'compensation_code_id' => $code->id,
            'base_price' => 100,
            'discount_amount' => 20,
            'prix_total' => 80,
        ]);

        $this->actingAs($this->client)
            ->get(route('reservations.show', $reservation))
            ->assertSee('Prix initial') // Should show breakdown
            ->assertSee('Réduction appliquée')
            ->assertSee('Prix final')
            ->assertSee($code->code);
    }

    // =====================================================================
    // TEST 18: Non-owner cannot access reservation discount details
    // =====================================================================
    public function test_only_owner_can_see_reservation_with_discount(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'compensation_code_id' => $code->id,
        ]);

        $otherClient = User::factory()->create(['role' => 'client']);

        $this->actingAs($otherClient)
            ->get(route('reservations.show', $reservation))
            ->assertForbidden();
    }
}

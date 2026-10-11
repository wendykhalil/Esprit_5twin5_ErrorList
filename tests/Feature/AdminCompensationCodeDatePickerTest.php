<?php

namespace Tests\Feature;

use App\Models\CompensationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCompensationCodeDatePickerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = User::factory()->create(['role' => 'client']);
    }

    /**
     * Test creating a compensation code with datetime-local format (Y-m-d\TH:i)
     * The form submits dates like "2026-10-11T18:14" which must be normalized to "2026-10-11 18:14"
     */
    public function test_create_compensation_code_with_datetime_local_format(): void
    {
        $validFrom = '2026-10-11T18:14';  // datetime-local format
        $validUntil = '2026-11-11T18:14'; // datetime-local format

        $response = $this->actingAs($this->admin)
            ->post(route('admin.compensation-codes.store'), [
                'type' => 'percentage',
                'value' => 20,
                'user_id' => $this->client->id,
                'max_uses' => 3,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'is_active' => 1,
                'description' => 'Test code with datetime picker',
            ]);

        $response->assertRedirect();

        // Verify the code was created successfully
        $code = CompensationCode::first();
        $this->assertNotNull($code);
        $this->assertEquals('percentage', $code->type);
        $this->assertEquals(20, $code->value);
        $this->assertEquals($this->client->id, $code->user_id);
        $this->assertEquals(3, $code->max_uses);
        $this->assertTrue($code->is_active);

        // Verify dates are stored correctly (without the T separator)
        $this->assertEquals('2026-10-11 18:14', $code->valid_from->format('Y-m-d H:i'));
        $this->assertEquals('2026-11-11 18:14', $code->valid_until->format('Y-m-d H:i'));
    }

    /**
     * Test that validation fails when valid_until is before valid_from,
     * even when using datetime-local format
     */
    public function test_validation_fails_when_until_before_from_datetime_local(): void
    {
        $validFrom = '2026-11-11T18:14';  // Later date
        $validUntil = '2026-10-11T18:14'; // Earlier date

        $response = $this->actingAs($this->admin)
            ->post(route('admin.compensation-codes.store'), [
                'type' => 'fixed',
                'value' => 50,
                'user_id' => $this->client->id,
                'max_uses' => 1,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'is_active' => 1,
            ]);

        // Should have validation errors
        $response->assertSessionHasErrors(['valid_until']);

        // Code should not be created
        $this->assertEquals(0, CompensationCode::count());
    }

    /**
     * Test updating a compensation code with datetime-local format
     */
    public function test_update_compensation_code_with_datetime_local_format(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'type' => 'percentage',
            'value' => 10,
            'max_uses' => 1,
            'is_active' => true,
        ]);

        $newValidFrom = '2026-12-01T09:00';   // datetime-local format
        $newValidUntil = '2026-12-31T17:00';  // datetime-local format

        $response = $this->actingAs($this->admin)
            ->put(route('admin.compensation-codes.update', $code->id), [
                'type' => 'fixed',
                'value' => 100,
                'user_id' => $this->client->id,
                'max_uses' => 5,
                'valid_from' => $newValidFrom,
                'valid_until' => $newValidUntil,
                'is_active' => true,
                'description' => 'Updated code',
            ]);

        $response->assertRedirect();

        // Refresh and verify updates
        $code->refresh();
        $this->assertEquals('fixed', $code->type);
        $this->assertEquals(100, $code->value);
        $this->assertEquals(5, $code->max_uses);
        $this->assertEquals('2026-12-01 09:00', $code->valid_from->format('Y-m-d H:i'));
        $this->assertEquals('2026-12-31 17:00', $code->valid_until->format('Y-m-d H:i'));
    }

    /**
     * Test that codes cannot be updated if they've been used
     */
    public function test_cannot_update_compensation_code_if_used(): void
    {
        $code = CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'uses_count' => 1, // Already used
            'value' => 50,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.compensation-codes.update', $code->id), [
                'type' => 'fixed',
                'value' => 999,
                'valid_from' => '2026-12-01T09:00',
                'valid_until' => '2026-12-31T17:00',
            ]);

        // The controller checks uses_count and redirects back with error
        // If policy denies, we get 403 forbidden
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 403,
            "Expected redirect (302) or forbidden (403), got {$response->status()}"
        );

        $code->refresh();
        $this->assertEquals(50, $code->value); // Value unchanged
    }

    /**
     * Test that validation still requires valid_until > valid_from
     * with datetime-local format to ensure business logic is preserved
     */
    public function test_validation_rules_preserved_with_datetime_local_format(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.compensation-codes.store'), [
                'type' => 'percentage',
                'value' => 150, // > 100% should fail
                'user_id' => $this->client->id,
                'max_uses' => 1,
                'valid_from' => '2026-10-11T10:00',
                'valid_until' => '2026-10-11T10:00', // Same time should fail
                'is_active' => 1,
            ]);

        // Should have validation errors for both percentage and date
        $response->assertSessionHasErrors(['value', 'valid_until']);
    }

    /**
     * Test creating multiple codes on the same day with different times
     */
    public function test_create_multiple_codes_same_day_different_times(): void
    {
        // Create 3 codes all on 2026-10-15 with different times
        for ($hour = 8; $hour <= 16; $hour += 4) {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.compensation-codes.store'), [
                    'type' => 'fixed',
                    'value' => 50 + ($hour * 10),
                    'user_id' => $this->client->id,
                    'max_uses' => 1,
                    'valid_from' => "2026-10-15T" . str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00",
                    'valid_until' => "2026-10-15T" . str_pad($hour + 1, 2, '0', STR_PAD_LEFT) . ":00",
                    'is_active' => 1,
                ]);

            $response->assertRedirect();
        }

        // Verify all 3 codes were created
        $this->assertEquals(3, CompensationCode::count());

        // Verify times are properly stored
        $codes = CompensationCode::orderBy('valid_from')->get();
        $this->assertEquals('2026-10-15 08:00', $codes[0]->valid_from->format('Y-m-d H:i'));
        $this->assertEquals('2026-10-15 12:00', $codes[1]->valid_from->format('Y-m-d H:i'));
        $this->assertEquals('2026-10-15 16:00', $codes[2]->valid_from->format('Y-m-d H:i'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\CompensationCode;
use Tests\TestCase;

class CompensationCodeValidationTest extends TestCase
{
    /**
     * Test Case 1: Valid percentage discount (50%)
     * Expected: Discount calculation correct
     */
    public function test_percentage_discount_calculation()
    {
        $code = new CompensationCode([
            'type' => 'percentage',
            'value' => 50,
        ]);

        // 100 TND * 50% = 50 TND
        $this->assertEquals(50, $code->calculateDiscount(100));

        // 200 TND * 50% = 100 TND
        $this->assertEquals(100, $code->calculateDiscount(200));

        // 75 TND * 50% = 37.5 TND
        $this->assertEquals(37.5, $code->calculateDiscount(75));
    }

    /**
     * Test Case 2: Valid fixed discount
     * Expected: Fixed discount applied, capped at base price
     */
    public function test_fixed_discount_calculation()
    {
        $code = new CompensationCode([
            'type' => 'fixed',
            'value' => 50,
        ]);

        // 100 TND base, 50 TND fixed = 50 TND discount
        $this->assertEquals(50, $code->calculateDiscount(100));

        // 40 TND base, 50 TND fixed = 40 TND (capped at base)
        $this->assertEquals(40, $code->calculateDiscount(40));

        // 200 TND base, 50 TND fixed = 50 TND
        $this->assertEquals(50, $code->calculateDiscount(200));
    }

    /**
     * Test Case 3: Discount never exceeds base price
     * Expected: Discount capped at base price
     */
    public function test_discount_cap_at_base_price()
    {
        // Percentage that would exceed base
        $code = new CompensationCode([
            'type' => 'percentage',
            'value' => 150,  // 150% would be 150 TND on 100 TND base
        ]);

        $discount = $code->calculateDiscount(100);
        $this->assertEquals(100, $discount);  // Capped at base price

        // Fixed that exceeds base
        $code2 = new CompensationCode([
            'type' => 'fixed',
            'value' => 200,  // 200 TND fixed on 100 TND base
        ]);

        $discount2 = $code2->calculateDiscount(100);
        $this->assertEquals(100, $discount2);  // Capped at base price
    }

    /**
     * Test Case 4: isValid() checks all conditions
     * Expected: Only valid if active, not expired, started, and has remaining uses
     */
    public function test_code_validity_checks()
    {
        // Active, in valid range, has uses
        $code = new CompensationCode([
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'max_uses' => 1,
            'uses_count' => 0,
        ]);
        $this->assertTrue($code->isValid());

        // Inactive
        $code->is_active = false;
        $this->assertFalse($code->isValid());

        // Expired
        $code->is_active = true;
        $code->valid_until = now()->subDay();
        $this->assertFalse($code->isValid());

        // Not started yet
        $code->valid_until = now()->addDays(30);
        $code->valid_from = now()->addDays(5);
        $this->assertFalse($code->isValid());

        // No remaining uses
        $code->valid_from = now()->subDay();
        $code->max_uses = 1;
        $code->uses_count = 1;
        $this->assertFalse($code->isValid());
    }

    /**
     * Test Case 5: hasUsesRemaining() checks use count
     * Expected: Returns true while uses_count < max_uses
     */
    public function test_has_uses_remaining()
    {
        $code = new CompensationCode([
            'max_uses' => 3,
            'uses_count' => 0,
        ]);

        $this->assertTrue($code->hasUsesRemaining());

        $code->uses_count = 1;
        $this->assertTrue($code->hasUsesRemaining());

        $code->uses_count = 2;
        $this->assertTrue($code->hasUsesRemaining());

        $code->uses_count = 3;
        $this->assertFalse($code->hasUsesRemaining());
    }

    /**
     * Test Case 6: hasStarted() checks validity start
     * Expected: Returns true if now >= valid_from
     */
    public function test_has_started()
    {
        // Already started
        $code = new CompensationCode([
            'valid_from' => now()->subDay(),
        ]);
        $this->assertTrue($code->hasStarted());

        // Just now
        $code->valid_from = now();
        $this->assertTrue($code->hasStarted());

        // Future
        $code->valid_from = now()->addDay();
        $this->assertFalse($code->hasStarted());
    }

    /**
     * Test Case 7: isExpired() checks expiry date
     * Expected: Returns true if now > valid_until
     */
    public function test_is_expired()
    {
        // Not expired
        $code = new CompensationCode([
            'valid_until' => now()->addDay(),
        ]);
        $this->assertFalse($code->isExpired());

        // Just expired
        $code->valid_until = now()->subSecond();
        $this->assertTrue($code->isExpired());

        // Long expired
        $code->valid_until = now()->subDays(10);
        $this->assertTrue($code->isExpired());
    }

    /**
     * Test Case 8: getStatusAttribute() returns readable status
     * Expected: Correct status based on all conditions
     */
    public function test_status_attribute()
    {
        // Available code
        $code = new CompensationCode([
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'max_uses' => 1,
            'uses_count' => 0,
        ]);
        $this->assertEquals('Disponible', $code->status);

        // Disabled
        $code->is_active = false;
        $this->assertEquals('Désactivé', $code->status);

        // Expired
        $code->is_active = true;
        $code->valid_until = now()->subDay();
        $this->assertEquals('Expiré', $code->status);

        // Not yet valid
        $code->valid_until = now()->addDays(30);
        $code->valid_from = now()->addDays(5);
        $this->assertEquals('Pas encore valide', $code->status);

        // Exhausted
        $code->valid_from = now()->subDay();
        $code->max_uses = 1;
        $code->uses_count = 1;
        $this->assertEquals('Utilisé', $code->status);
    }

    /**
     * Test Case 9: generateCode() creates unique codes
     * Expected: Code format CC-XXXXXXXXXXXX with 12 random chars
     */
    public function test_generate_code_format()
    {
        $code = CompensationCode::generateCode();

        // Check format
        $this->assertStringStartsWith('CC-', $code);
        $this->assertEquals(15, strlen($code));  // CC- (3) + 12 chars

        // Check alphanumeric
        preg_match('/^CC-[0-9A-Z]{12}$/', $code, $matches);
        $this->assertNotEmpty($matches);
    }

    /**
     * Test Case 10: Zero discount calculation
     * Expected: 0% discount returns 0 TND discount
     */
    public function test_zero_discount()
    {
        $code = new CompensationCode([
            'type' => 'percentage',
            'value' => 0,
        ]);

        $this->assertEquals(0, $code->calculateDiscount(100));
        $this->assertEquals(0, $code->calculateDiscount(1000));
    }

    /**
     * Test Case 11: Rounding precision for percentage
     * Expected: Discount properly rounded to 2 decimals
     */
    public function test_percentage_rounding()
    {
        $code = new CompensationCode([
            'type' => 'percentage',
            'value' => 33.33,
        ]);

        // 100 TND * 33.33% = 33.33 TND
        $discount = $code->calculateDiscount(100);
        $this->assertEquals(33.33, $discount);

        // 150.50 TND * 33.33% = 50.18 TND (rounded)
        $discount2 = $code->calculateDiscount(150.50);
        $this->assertEquals(50.18, $discount2);
    }
}


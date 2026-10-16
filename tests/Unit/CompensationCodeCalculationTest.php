<?php

namespace Tests\Unit;

use App\Models\CompensationCode;
use Tests\TestCase;

class CompensationCodeCalculationTest extends TestCase
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
     * Test Case 4: hasUsesRemaining() checks use count
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
     * Test Case 5: Zero discount calculation
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
     * Test Case 6: Rounding precision for percentage
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

        // 150.50 TND * 33.33% = 50.16 TND (rounded)
        $discount2 = $code->calculateDiscount(150.50);
        $this->assertEquals(50.16, $discount2);
    }

    /**
     * Test Case 7: 50% discount on 100 TND base (realistic scenario)
     * Expected: Price before=100, discount=50, final price=50
     */
    public function test_realistic_scenario_50_percent()
    {
        $code = new CompensationCode([
            'type' => 'percentage',
            'value' => 50,
        ]);

        $basePrice = 100;  // 100 TND (1 day × 100 TND/day)
        $discount = $code->calculateDiscount($basePrice);
        $finalPrice = $basePrice - $discount;

        $this->assertEquals(100, $basePrice);
        $this->assertEquals(50, $discount);
        $this->assertEquals(50, $finalPrice);
    }

    /**
     * Test Case 8: 50 TND fixed discount on 100 TND base (realistic scenario)
     * Expected: Price before=100, discount=50, final price=50
     */
    public function test_realistic_scenario_50_fixed()
    {
        $code = new CompensationCode([
            'type' => 'fixed',
            'value' => 50,
        ]);

        $basePrice = 100;  // 100 TND
        $discount = $code->calculateDiscount($basePrice);
        $finalPrice = $basePrice - $discount;

        $this->assertEquals(100, $basePrice);
        $this->assertEquals(50, $discount);
        $this->assertEquals(50, $finalPrice);
    }

    /**
     * Test Case 9: Multiple durations with 50% discount
     * Expected: Discount scales with price
     */
    public function test_multiple_durations_with_discount()
    {
        $code = new CompensationCode([
            'type' => 'percentage',
            'value' => 50,
        ]);

        // 1 day: 100 TND → 50 TND discount
        $this->assertEquals(50, $code->calculateDiscount(100));

        // 5 days: 500 TND → 250 TND discount
        $this->assertEquals(250, $code->calculateDiscount(500));

        // 10 days: 1000 TND → 500 TND discount
        $this->assertEquals(500, $code->calculateDiscount(1000));
    }
}

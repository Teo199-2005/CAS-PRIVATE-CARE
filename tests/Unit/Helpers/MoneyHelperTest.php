<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Helpers\MoneyHelper;

/**
 * Test cases for MoneyHelper utility class
 */
class MoneyHelperTest extends TestCase
{
    /**
     * Test currency formatting
     */
    public function test_format_creates_currency_string(): void
    {
        $this->assertEquals('$100.00', MoneyHelper::format(100));
        $this->assertEquals('$1,234.56', MoneyHelper::format(1234.56));
        $this->assertEquals('-$50.00', MoneyHelper::format(-50));
    }

    /**
     * Test formatting without symbol
     */
    public function test_format_without_symbol(): void
    {
        $this->assertEquals('100.00', MoneyHelper::format(100, 'USD', false));
    }

    /**
     * Test cents to dollars formatting
     */
    public function test_format_cents(): void
    {
        $this->assertEquals('$10.00', MoneyHelper::formatCents(1000));
        $this->assertEquals('$99.99', MoneyHelper::formatCents(9999));
    }

    /**
     * Test dollars to cents conversion
     */
    public function test_to_cents(): void
    {
        $this->assertEquals(1000, MoneyHelper::toCents(10));
        $this->assertEquals(9999, MoneyHelper::toCents(99.99));
        $this->assertEquals(1050, MoneyHelper::toCents(10.50));
    }

    /**
     * Test cents to dollars conversion
     */
    public function test_to_dollars(): void
    {
        $this->assertEquals(10.00, MoneyHelper::toDollars(1000));
        $this->assertEquals(99.99, MoneyHelper::toDollars(9999));
    }

    /**
     * Test compact formatting
     */
    public function test_format_compact(): void
    {
        $this->assertEquals('$500.00', MoneyHelper::formatCompact(500));
        $this->assertEquals('$1.5K', MoneyHelper::formatCompact(1500));
        $this->assertEquals('$2.5M', MoneyHelper::formatCompact(2500000));
        $this->assertEquals('$1.0B', MoneyHelper::formatCompact(1000000000));
    }

    /**
     * Test percentage calculation
     */
    public function test_percentage(): void
    {
        $this->assertEquals(10.00, MoneyHelper::percentage(100, 10));
        $this->assertEquals(25.00, MoneyHelper::percentage(100, 25));
    }

    /**
     * Test adding percentage
     */
    public function test_add_percentage(): void
    {
        $this->assertEquals(110.00, MoneyHelper::addPercentage(100, 10));
        $this->assertEquals(120.00, MoneyHelper::addPercentage(100, 20));
    }

    /**
     * Test subtracting percentage
     */
    public function test_subtract_percentage(): void
    {
        $this->assertEquals(90.00, MoneyHelper::subtractPercentage(100, 10));
        $this->assertEquals(80.00, MoneyHelper::subtractPercentage(100, 20));
    }

    /**
     * Test percentage of calculation
     */
    public function test_percentage_of(): void
    {
        $this->assertEquals(50.0, MoneyHelper::percentageOf(50, 100));
        $this->assertEquals(25.0, MoneyHelper::percentageOf(25, 100));
        $this->assertEquals(0, MoneyHelper::percentageOf(50, 0)); // Division by zero protection
    }

    /**
     * Test hourly rate calculation
     */
    public function test_hourly_rate(): void
    {
        $this->assertEquals(25.00, MoneyHelper::hourlyRate(100, 4));
        $this->assertEquals(0, MoneyHelper::hourlyRate(100, 0)); // Division by zero protection
    }

    /**
     * Test total calculation
     */
    public function test_calculate_total(): void
    {
        $this->assertEquals(100.00, MoneyHelper::calculateTotal(25, 4));
        $this->assertEquals(187.50, MoneyHelper::calculateTotal(25, 7.5));
    }

    /**
     * Test rounding to cents
     */
    public function test_round_to_cents(): void
    {
        $this->assertEquals(10.55, MoneyHelper::roundToCents(10.554));
        $this->assertEquals(10.56, MoneyHelper::roundToCents(10.555));
    }

    /**
     * Test parsing currency string
     */
    public function test_parse(): void
    {
        $this->assertEquals(100.50, MoneyHelper::parse('$100.50'));
        $this->assertEquals(1234.56, MoneyHelper::parse('$1,234.56'));
    }

    /**
     * Test positive amount validation
     */
    public function test_is_positive(): void
    {
        $this->assertTrue(MoneyHelper::isPositive(100));
        $this->assertFalse(MoneyHelper::isPositive(0));
        $this->assertFalse(MoneyHelper::isPositive(-50));
    }

    /**
     * Test non-negative amount validation
     */
    public function test_is_non_negative(): void
    {
        $this->assertTrue(MoneyHelper::isNonNegative(100));
        $this->assertTrue(MoneyHelper::isNonNegative(0));
        $this->assertFalse(MoneyHelper::isNonNegative(-50));
    }

    /**
     * Test sum of amounts
     */
    public function test_sum(): void
    {
        $this->assertEquals(150.00, MoneyHelper::sum([50, 75, 25]));
        $this->assertEquals(0, MoneyHelper::sum([]));
    }

    /**
     * Test average of amounts
     */
    public function test_average(): void
    {
        $this->assertEquals(50.00, MoneyHelper::average([25, 50, 75]));
        $this->assertEquals(0, MoneyHelper::average([]));
    }
}

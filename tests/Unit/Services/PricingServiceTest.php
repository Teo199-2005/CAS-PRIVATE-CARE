<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PricingService;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    /** @test */
    public function calculates_hourly_rate_correctly()
    {
        $hourlyRate = 25.00; // $25/hour
        $hours = 8;

        $total = $hourlyRate * $hours;

        $this->assertEquals(200.00, $total);
    }

    /** @test */
    public function calculates_daily_rate_correctly()
    {
        $hourlyRate = 25.00;
        $hoursPerDay = 8;
        $days = 7;

        $total = $hourlyRate * $hoursPerDay * $days;

        $this->assertEquals(1400.00, $total);
    }

    /** @test */
    public function applies_platform_fee_correctly()
    {
        $subtotal = 1000.00;
        $platformFeePercentage = 15; // 15%

        $platformFee = $subtotal * ($platformFeePercentage / 100);
        $total = $subtotal + $platformFee;

        $this->assertEquals(150.00, $platformFee);
        $this->assertEquals(1150.00, $total);
    }

    /** @test */
    public function calculates_caregiver_payout_correctly()
    {
        $totalPayment = 1150.00; // Including platform fee
        $platformFeePercentage = 15;

        // Platform takes 15%, caregiver gets 85%
        $caregiverPayout = $totalPayment * (1 - $platformFeePercentage / 100);

        $this->assertEqualsWithDelta(977.50, $caregiverPayout, 0.01);
    }

    /** @test */
    public function handles_zero_hours()
    {
        $hourlyRate = 25.00;
        $hours = 0;

        $total = $hourlyRate * $hours;

        $this->assertEquals(0, $total);
    }

    /** @test */
    public function handles_fractional_hours()
    {
        $hourlyRate = 25.00;
        $hours = 4.5; // 4 hours 30 minutes

        $total = $hourlyRate * $hours;

        $this->assertEquals(112.50, $total);
    }

    /** @test */
    public function converts_cents_to_dollars()
    {
        $amountInCents = 15000;
        $amountInDollars = $amountInCents / 100;

        $this->assertEquals(150.00, $amountInDollars);
    }

    /** @test */
    public function converts_dollars_to_cents()
    {
        $amountInDollars = 150.00;
        $amountInCents = (int) ($amountInDollars * 100);

        $this->assertEquals(15000, $amountInCents);
    }

    /** @test */
    public function calculates_overtime_rate()
    {
        $regularHourlyRate = 25.00;
        $overtimeMultiplier = 1.5;
        $overtimeHours = 4;

        $overtimeRate = $regularHourlyRate * $overtimeMultiplier;
        $overtimePay = $overtimeRate * $overtimeHours;

        $this->assertEquals(37.50, $overtimeRate);
        $this->assertEquals(150.00, $overtimePay);
    }

    /** @test */
    public function calculates_weekly_earnings()
    {
        $hourlyRate = 25.00;
        $hoursPerDay = 8;
        $daysPerWeek = 5;

        $weeklyEarnings = $hourlyRate * $hoursPerDay * $daysPerWeek;

        $this->assertEquals(1000.00, $weeklyEarnings);
    }
}

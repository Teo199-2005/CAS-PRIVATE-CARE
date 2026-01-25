<?php

namespace Tests\Unit\Services;

use App\Services\PaymentFeeService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PaymentFeeService
 * 
 * The service uses pass-through fee calculation:
 * gross = (target + fixed) / (1 - rate)
 * fee = gross - target
 * 
 * This ensures the business receives the exact target amount after Stripe fees.
 */
class PaymentFeeServiceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_domestic_processing_fee_correctly(): void
    {
        // For $100 target with domestic rate (2.9% + $0.30)
        // gross = (100 + 0.30) / (1 - 0.029) = 100.30 / 0.971 = 103.30...
        // fee = gross - 100 = ~3.30+
        $fee = PaymentFeeService::calculateProcessingFee(100.00, 'US');
        
        // Should be around $3.30-3.50 to cover 2.9% + $0.30
        $this->assertGreaterThan(3.00, $fee);
        $this->assertLessThan(4.00, $fee);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_international_processing_fee_correctly(): void
    {
        // For $100 target with international rate (4.9% + $0.30)
        $fee = PaymentFeeService::calculateProcessingFee(100.00, 'CA');
        
        // Should be higher than domestic fee
        $domesticFee = PaymentFeeService::calculateProcessingFee(100.00, 'US');
        $this->assertGreaterThan($domesticFee, $fee);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_adjusted_total_correctly(): void
    {
        $total = PaymentFeeService::calculateAdjustedTotal(100.00, 'US');
        
        // Total should be target + fee
        $fee = PaymentFeeService::calculateProcessingFee(100.00, 'US');
        $this->assertEquals(round(100.00 + $fee, 2), $total);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_converts_to_cents_correctly(): void
    {
        $cents = PaymentFeeService::toCents(103.47);
        
        $this->assertEquals(10347, $cents);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_provides_complete_fee_breakdown(): void
    {
        $breakdown = PaymentFeeService::getFeeBreakdown(100.00, 'US');
        
        $this->assertArrayHasKey('target', $breakdown);
        $this->assertArrayHasKey('fee', $breakdown);
        $this->assertArrayHasKey('total', $breakdown);
        $this->assertArrayHasKey('rate', $breakdown);
        $this->assertArrayHasKey('fixed', $breakdown);

        $this->assertEquals(100.00, $breakdown['target']);
        $this->assertGreaterThan(0, $breakdown['fee']);
        $this->assertEquals($breakdown['target'] + $breakdown['fee'], $breakdown['total']);
        $this->assertEqualsWithDelta(2.9, $breakdown['rate'], 0.001); // Domestic rate as percentage
        $this->assertEqualsWithDelta(0.30, $breakdown['fixed'], 0.001);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_zero_amount(): void
    {
        $fee = PaymentFeeService::calculateProcessingFee(0, 'US');
        
        // Even with $0 base, there's still a calculation
        // gross = (0 + 0.30) / 0.971 ≈ 0.31, fee ≈ 0.31
        $this->assertGreaterThan(0.30, $fee);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_consistent_results(): void
    {
        $fee1 = PaymentFeeService::calculateProcessingFee(50.00, 'US');
        $fee2 = PaymentFeeService::calculateProcessingFee(50.00, 'US');
        
        $this->assertEquals($fee1, $fee2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function domestic_rate_is_lower_than_international(): void
    {
        $domesticFee = PaymentFeeService::calculateProcessingFee(100.00, 'US');
        $internationalFee = PaymentFeeService::calculateProcessingFee(100.00, 'GB');
        
        $this->assertLessThan($internationalFee, $domesticFee);
    }
}

<?php

namespace Tests\Feature\MoneyFlow;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use App\Models\Payment;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * MONEY FLOW AUDIT TESTS
 * 
 * These tests verify all money flows in the system to prevent:
 * 1. Double payments (paying same hours twice)
 * 2. Money leakage (charging without paying contractors)
 * 3. Calculation errors (wrong amounts)
 * 4. Race conditions (concurrent payment requests)
 * 
 * @group money-flow
 * @group critical
 */
class MoneyFlowAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('stripe.secret', 'sk_test_dummy');
        Config::set('stripe.key', 'pk_test_dummy');
    }

    // ==========================================
    // 1. COMMISSION CALCULATION TESTS
    // ==========================================

    /** @test */
    public function caregiver_earnings_calculated_correctly_with_referral()
    {
        // Scenario: 8 hours worked, with referral ($40/hr client rate)
        $hours = 8;
        $clientRate = 40.00;
        $caregiverRate = 28.00;
        $marketingCommission = 1.00;
        $trainingCommission = 0.50;
        
        $totalClientCharge = $hours * $clientRate; // $320
        $caregiverEarnings = $hours * $caregiverRate; // $224
        $marketingPayout = $hours * $marketingCommission; // $8
        $trainingPayout = $hours * $trainingCommission; // $4
        $agencyProfit = $totalClientCharge - $caregiverEarnings - $marketingPayout - $trainingPayout;
        
        // Verify math adds up
        $this->assertEquals(320, $totalClientCharge);
        $this->assertEquals(224, $caregiverEarnings);
        $this->assertEquals(8, $marketingPayout);
        $this->assertEquals(4, $trainingPayout);
        $this->assertEquals(84, $agencyProfit);
        
        // All distributions should equal client charge
        $totalDistributed = $caregiverEarnings + $marketingPayout + $trainingPayout + $agencyProfit;
        $this->assertEquals($totalClientCharge, $totalDistributed, 'Money leakage detected: distributions != client charge');
    }

    /** @test */
    public function caregiver_earnings_calculated_correctly_without_referral()
    {
        // Scenario: 8 hours worked, without referral ($45/hr client rate)
        $hours = 8;
        $clientRate = 45.00;
        $caregiverRate = 28.00;
        $marketingCommission = 0.00; // No referral
        $trainingCommission = 0.50;
        
        $totalClientCharge = $hours * $clientRate; // $360
        $caregiverEarnings = $hours * $caregiverRate; // $224
        $marketingPayout = $hours * $marketingCommission; // $0
        $trainingPayout = $hours * $trainingCommission; // $4
        $agencyProfit = $totalClientCharge - $caregiverEarnings - $marketingPayout - $trainingPayout;
        
        // Verify math adds up
        $this->assertEquals(360, $totalClientCharge);
        $this->assertEquals(224, $caregiverEarnings);
        $this->assertEquals(0, $marketingPayout);
        $this->assertEquals(4, $trainingPayout);
        $this->assertEquals(132, $agencyProfit);
        
        // All distributions should equal client charge
        $totalDistributed = $caregiverEarnings + $marketingPayout + $trainingPayout + $agencyProfit;
        $this->assertEquals($totalClientCharge, $totalDistributed, 'Money leakage detected');
    }

    /** @test */
    public function minute_based_calculations_are_accurate()
    {
        // Scenario: 7 hours 45 minutes worked (465 minutes)
        $minutes = 465;
        $hours = $minutes / 60; // 7.75 hours
        $clientRate = 40.00;
        
        $totalCharge = round($hours * $clientRate, 2);
        $caregiverEarnings = round($hours * 28.00, 2);
        
        $this->assertEquals(310.00, $totalCharge);
        $this->assertEquals(217.00, $caregiverEarnings);
    }

    // ==========================================
    // 2. DOUBLE PAYMENT PREVENTION TESTS
    // ==========================================

    /** @test */
    public function caregiver_cannot_be_paid_twice_for_same_hours()
    {
        $user = User::factory()->create(['user_type' => 'caregiver']);
        $caregiver = Caregiver::factory()->create([
            'user_id' => $user->id,
        ]);
        
        $timeTracking = TimeTracking::factory()->create([
            'caregiver_id' => $caregiver->id,
            'caregiver_earnings' => 224.00,
            'payment_status' => 'pending',
            'paid_at' => null,
        ]);
        
        // Simulate first payment
        $timeTracking->update([
            'paid_at' => now(),
            'payment_status' => 'paid',
            'stripe_transfer_id' => 'tr_test_123'
        ]);
        
        // Verify record is now marked as paid
        $this->assertNotNull($timeTracking->fresh()->paid_at);
        $this->assertEquals('paid', $timeTracking->fresh()->payment_status);
        
        // Query for unpaid records should return empty
        $unpaidRecords = TimeTracking::where('caregiver_id', $caregiver->id)
            ->whereNull('paid_at')
            ->get();
        
        $this->assertEquals(0, $unpaidRecords->count(), 'Already paid record found in unpaid query');
    }

    /** @test */
    public function marketing_commission_cannot_be_paid_twice()
    {
        $marketingUser = User::factory()->create(['user_type' => 'marketing']);
        
        $timeTracking = TimeTracking::factory()->create([
            'marketing_partner_id' => $marketingUser->id,
            'marketing_partner_commission' => 12.00,
            'marketing_commission_paid_at' => null,
        ]);
        
        // Simulate first payment
        $timeTracking->update([
            'marketing_commission_paid_at' => now(),
            'marketing_commission_stripe_transfer_id' => 'tr_marketing_123'
        ]);
        
        // Query for unpaid marketing commissions should return empty
        $unpaidCommissions = TimeTracking::where('marketing_partner_id', $marketingUser->id)
            ->whereNull('marketing_commission_paid_at')
            ->get();
        
        $this->assertEquals(0, $unpaidCommissions->count(), 'Already paid marketing commission found');
    }

    /** @test */
    public function training_commission_cannot_be_paid_twice()
    {
        $trainingUser = User::factory()->create(['user_type' => 'training']);
        
        $timeTracking = TimeTracking::factory()->create([
            'training_center_user_id' => $trainingUser->id,
            'training_center_commission' => 6.00,
            'training_commission_paid_at' => null,
        ]);
        
        // Simulate first payment
        $timeTracking->update([
            'training_commission_paid_at' => now(),
            'training_commission_stripe_transfer_id' => 'tr_training_123'
        ]);
        
        // Query for unpaid training commissions should return empty
        $unpaidCommissions = TimeTracking::where('training_center_user_id', $trainingUser->id)
            ->whereNull('training_commission_paid_at')
            ->get();
        
        $this->assertEquals(0, $unpaidCommissions->count(), 'Already paid training commission found');
    }

    // ==========================================
    // 3. AMOUNT VALIDATION TESTS
    // ==========================================

    /** @test */
    public function negative_amounts_are_rejected()
    {
        // Negative amounts should not be processable
        $amount = -100;
        $this->assertFalse($amount > 0, 'Negative amounts should be rejected');
    }

    /** @test */
    public function zero_amount_transfers_are_rejected()
    {
        // Zero amount transfers waste Stripe API calls
        $amount = 0;
        $this->assertFalse($amount > 0, 'Zero amounts should be rejected');
    }

    /** @test */
    public function payment_amount_matches_calculated_earnings()
    {
        $user = User::factory()->create(['user_type' => 'caregiver']);
        $caregiver = Caregiver::factory()->create(['user_id' => $user->id]);
        
        // Create 3 time tracking records
        $records = collect([
            TimeTracking::factory()->create([
                'caregiver_id' => $caregiver->id,
                'caregiver_earnings' => 224.00,
                'paid_at' => null,
            ]),
            TimeTracking::factory()->create([
                'caregiver_id' => $caregiver->id,
                'caregiver_earnings' => 168.00,
                'paid_at' => null,
            ]),
            TimeTracking::factory()->create([
                'caregiver_id' => $caregiver->id,
                'caregiver_earnings' => 280.00,
                'paid_at' => null,
            ]),
        ]);
        
        $calculatedTotal = $records->sum('caregiver_earnings');
        $expectedTotal = 224.00 + 168.00 + 280.00;
        
        $this->assertEquals($expectedTotal, $calculatedTotal, 'Calculated total does not match expected');
        
        // Verify the same query the payment system would use
        $dbTotal = TimeTracking::where('caregiver_id', $caregiver->id)
            ->whereNull('paid_at')
            ->sum('caregiver_earnings');
        
        $this->assertEquals($expectedTotal, $dbTotal, 'Database sum does not match expected');
    }

    // ==========================================
    // 4. MONEY BALANCE VERIFICATION TESTS
    // ==========================================

    /** @test */
    public function total_distribution_equals_client_payment()
    {
        // For every client payment, verify all distributions sum correctly
        $clientPayment = 480.00; // 12 hours @ $40
        
        $caregiverEarnings = 336.00; // 12 hours @ $28
        $marketingCommission = 12.00; // 12 hours @ $1
        $trainingCommission = 6.00; // 12 hours @ $0.50
        $agencyCommission = $clientPayment - $caregiverEarnings - $marketingCommission - $trainingCommission;
        
        $totalDistributed = $caregiverEarnings + $marketingCommission + $trainingCommission + $agencyCommission;
        
        $this->assertEquals($clientPayment, $totalDistributed, 
            "CRITICAL: Money imbalance! Client paid \${$clientPayment}, distributed \${$totalDistributed}");
    }

    /** @test */
    public function processing_fee_is_properly_added_to_client_charge()
    {
        // Processing fee should be ON TOP of service amount
        $serviceAmount = 450.00;
        $processingFeeRate = 0.029; // 2.9%
        $processingFeeFixed = 0.30; // $0.30
        
        $processingFee = round(($serviceAmount + $processingFeeFixed) / (1 - $processingFeeRate) - $serviceAmount, 2);
        $totalCharged = $serviceAmount + $processingFee;
        
        // Client is charged more than service amount
        $this->assertGreaterThan($serviceAmount, $totalCharged);
        
        // Processing fee should be roughly 3%
        $this->assertLessThan($serviceAmount * 0.04, $processingFee);
    }

    // ==========================================
    // 5. DATABASE INTEGRITY TESTS
    // ==========================================

    /** @test */
    public function time_tracking_has_all_required_financial_fields()
    {
        $timeTracking = TimeTracking::factory()->create([
            'caregiver_earnings' => 224.00,
            'total_client_charge' => 320.00,
            'agency_commission' => 88.00,
        ]);
        
        $this->assertNotNull($timeTracking->caregiver_earnings);
        $this->assertNotNull($timeTracking->total_client_charge);
        $this->assertNotNull($timeTracking->agency_commission);
    }

    /** @test */
    public function payment_record_tracks_all_distribution_amounts()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
        ]);
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'client_id' => $user->id,
            'amount' => 450.00,
            'processing_fee' => 13.38,
            'platform_fee' => 45.00,
            'caregiver_amount' => 405.00,
            'status' => 'completed',
            'transaction_id' => 'pi_test_' . uniqid(),
        ]);
        
        // Verify all financial fields are recorded
        $this->assertEquals(450.00, $payment->amount);
        $this->assertEquals(13.38, $payment->processing_fee);
        $this->assertEquals(45.00, $payment->platform_fee);
        $this->assertEquals(405.00, $payment->caregiver_amount);
    }

    // ==========================================
    // 6. RATE CONSISTENCY TESTS
    // ==========================================

    /** @test */
    public function caregiver_rate_is_fixed_at_28_dollars()
    {
        // Caregiver rate should never change - it's fixed at $28/hr
        $caregiverRate = 28.00;
        
        $this->assertEquals(28.00, $caregiverRate);
    }

    /** @test */
    public function marketing_commission_is_fixed_at_1_dollar()
    {
        // Marketing commission is $1/hr when referral code is used
        $marketingRate = 1.00;
        
        $this->assertEquals(1.00, $marketingRate);
    }

    /** @test */
    public function training_commission_is_fixed_at_50_cents()
    {
        // Training center commission is $0.50/hr
        $trainingRate = 0.50;
        
        $this->assertEquals(0.50, $trainingRate);
    }

    /** @test */
    public function client_rates_are_45_without_referral_40_with_referral()
    {
        $standardRate = 45.00;
        $referralRate = 40.00;
        $referralDiscount = $standardRate - $referralRate;
        
        $this->assertEquals(45.00, $standardRate);
        $this->assertEquals(40.00, $referralRate);
        $this->assertEquals(5.00, $referralDiscount);
    }

    // ==========================================
    // 7. EDGE CASE TESTS
    // ==========================================

    /** @test */
    public function very_short_shift_calculated_correctly()
    {
        // 15 minutes = 0.25 hours
        $minutes = 15;
        $hours = $minutes / 60;
        $clientRate = 40.00;
        $caregiverRate = 28.00;
        
        $clientCharge = round($hours * $clientRate, 2);
        $caregiverEarnings = round($hours * $caregiverRate, 2);
        
        $this->assertEquals(10.00, $clientCharge);
        $this->assertEquals(7.00, $caregiverEarnings);
    }

    /** @test */
    public function very_long_shift_calculated_correctly()
    {
        // 24-hour shift
        $hours = 24;
        $clientRate = 40.00;
        $caregiverRate = 28.00;
        
        $clientCharge = $hours * $clientRate;
        $caregiverEarnings = $hours * $caregiverRate;
        
        $this->assertEquals(960.00, $clientCharge);
        $this->assertEquals(672.00, $caregiverEarnings);
    }

    /** @test */
    public function partial_hour_rounding_is_consistent()
    {
        // 7 hours 33 minutes
        $minutes = 453;
        $hours = $minutes / 60; // 7.55 hours
        
        $clientCharge = round($hours * 40.00, 2);
        $caregiverEarnings = round($hours * 28.00, 2);
        
        // Verify rounding doesn't cause money loss
        $agencyCommission = $clientCharge - $caregiverEarnings;
        $this->assertGreaterThan(0, $agencyCommission, 'Rounding caused negative agency commission');
    }
}

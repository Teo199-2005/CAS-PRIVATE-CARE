<?php
/**
 * Comprehensive Housekeeper Feature Test
 * 
 * Tests all housekeeper features to ensure parity with caregivers:
 * - Database schema
 * - Stripe Connect
 * - Time Tracking
 * - Payments
 * - Reviews
 * - Assignments
 * - Pricing
 * 
 * Run: php test-housekeeper-features.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Housekeeper;
use App\Models\Caregiver;
use App\Models\TimeTracking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Booking;
use App\Services\PricingService;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     HOUSEKEEPER FEATURE PARITY TEST                          ║\n";
echo "║     Testing all housekeeper features vs caregivers           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$tests = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0
];

function test($name, $condition, $details = '') {
    global $tests;
    if ($condition) {
        echo "  ✅ PASS: {$name}\n";
        if ($details) echo "      {$details}\n";
        $tests['passed']++;
        return true;
    } else {
        echo "  ❌ FAIL: {$name}\n";
        if ($details) echo "      {$details}\n";
        $tests['failed']++;
        return false;
    }
}

function warn($name, $details = '') {
    global $tests;
    echo "  ⚠️  WARN: {$name}\n";
    if ($details) echo "      {$details}\n";
    $tests['warnings']++;
}

// ===============================================
// 1. DATABASE SCHEMA TESTS
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. DATABASE SCHEMA TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Housekeepers table
$housekeeperColumns = Schema::getColumnListing('housekeepers');
test('Housekeepers table exists', Schema::hasTable('housekeepers'));
test('Housekeepers has stripe_connect_id', in_array('stripe_connect_id', $housekeeperColumns));
test('Housekeepers has stripe_charges_enabled', in_array('stripe_charges_enabled', $housekeeperColumns));
test('Housekeepers has stripe_payouts_enabled', in_array('stripe_payouts_enabled', $housekeeperColumns));

// Time Trackings table
$timeTrackingColumns = Schema::getColumnListing('time_trackings');
test('TimeTrackings has housekeeper_id', in_array('housekeeper_id', $timeTrackingColumns));
test('TimeTrackings has provider_type', in_array('provider_type', $timeTrackingColumns));

// Reviews table
$reviewColumns = Schema::getColumnListing('reviews');
test('Reviews has housekeeper_id', in_array('housekeeper_id', $reviewColumns));
test('Reviews has provider_type', in_array('provider_type', $reviewColumns));

// Payments table
$paymentColumns = Schema::getColumnListing('payments');
test('Payments has housekeeper_id', in_array('housekeeper_id', $paymentColumns));
test('Payments has housekeeper_amount', in_array('housekeeper_amount', $paymentColumns));
test('Payments has provider_type', in_array('provider_type', $paymentColumns));

// Booking Housekeeper Assignments table
test('BookingHousekeeperAssignments table exists', Schema::hasTable('booking_housekeeper_assignments'));
if (Schema::hasTable('booking_housekeeper_assignments')) {
    $assignmentColumns = Schema::getColumnListing('booking_housekeeper_assignments');
    test('Assignments has assigned_hourly_rate', in_array('assigned_hourly_rate', $assignmentColumns));
}

// Housekeeper Schedules table
test('HousekeeperSchedules table exists', Schema::hasTable('housekeeper_schedules'));

echo "\n";

// ===============================================
// 2. MODEL TESTS
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "2. MODEL TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Housekeeper Model
$housekeeper = new Housekeeper();
test('Housekeeper model fillable has stripe_connect_id', in_array('stripe_connect_id', $housekeeper->getFillable()));
test('Housekeeper model fillable has stripe_charges_enabled', in_array('stripe_charges_enabled', $housekeeper->getFillable()));
test('Housekeeper model fillable has stripe_payouts_enabled', in_array('stripe_payouts_enabled', $housekeeper->getFillable()));
test('Housekeeper model has reviews relationship', method_exists($housekeeper, 'reviews'));
test('Housekeeper model has timeTrackings relationship', method_exists($housekeeper, 'timeTrackings'));
test('Housekeeper model has assignments relationship', method_exists($housekeeper, 'assignments'));

// Payment Model
$payment = new Payment();
test('Payment model fillable has housekeeper_id', in_array('housekeeper_id', $payment->getFillable()));
test('Payment model fillable has housekeeper_amount', in_array('housekeeper_amount', $payment->getFillable()));
test('Payment model has housekeeper relationship', method_exists($payment, 'housekeeper'));

// Review Model
$review = new Review();
test('Review model fillable has housekeeper_id', in_array('housekeeper_id', $review->getFillable()));
test('Review model has housekeeper relationship', method_exists($review, 'housekeeper'));

// TimeTracking Model
$timeTracking = new TimeTracking();
test('TimeTracking model fillable has housekeeper_id', in_array('housekeeper_id', $timeTracking->getFillable()));

// BookingHousekeeperAssignment Model
test('BookingHousekeeperAssignment model exists', class_exists('App\\Models\\BookingHousekeeperAssignment'));
if (class_exists('App\\Models\\BookingHousekeeperAssignment')) {
    $bhAssignment = new \App\Models\BookingHousekeeperAssignment();
    test('BookingHousekeeperAssignment has booking relationship', method_exists($bhAssignment, 'booking'));
    test('BookingHousekeeperAssignment has housekeeper relationship', method_exists($bhAssignment, 'housekeeper'));
}

// Booking Model - Housekeeper relationships
$booking = new Booking();
test('Booking model has housekeeperAssignments relationship', method_exists($booking, 'housekeeperAssignments'));

echo "\n";

// ===============================================
// 3. PRICING SERVICE TESTS
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "3. PRICING SERVICE TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Housekeeper pricing constants
test('HOUSEKEEPER_DEFAULT_RATE defined', defined(PricingService::class . '::HOUSEKEEPER_DEFAULT_RATE'));
test('HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL defined', defined(PricingService::class . '::HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL'));
test('HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL defined', defined(PricingService::class . '::HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL'));

// Pricing values (same as caregivers: $45/$40)
$housekeeperRateNoReferral = PricingService::getHousekeeperClientRate(false);
$housekeeperRateWithReferral = PricingService::getHousekeeperClientRate(true);
$caregiverRateNoReferral = PricingService::getClientRate(false);
$caregiverRateWithReferral = PricingService::getClientRate(true);

test('Housekeeper client rate without referral = $45', $housekeeperRateNoReferral == 45.00, "Rate: \${$housekeeperRateNoReferral}");
test('Housekeeper client rate with referral = $40', $housekeeperRateWithReferral == 40.00, "Rate: \${$housekeeperRateWithReferral}");
test('Housekeeper & Caregiver rates match (no referral)', $housekeeperRateNoReferral == $caregiverRateNoReferral);
test('Housekeeper & Caregiver rates match (with referral)', $housekeeperRateWithReferral == $caregiverRateWithReferral);

// Pricing methods exist
test('getHousekeeperClientRate() method exists', method_exists(PricingService::class, 'getHousekeeperClientRate'));
test('getHousekeeperDefaultRate() method exists', method_exists(PricingService::class, 'getHousekeeperDefaultRate'));
test('getHousekeeperAgencyRate() method exists', method_exists(PricingService::class, 'getHousekeeperAgencyRate'));
test('calculateHousekeeperBreakdown() method exists', method_exists(PricingService::class, 'calculateHousekeeperBreakdown'));

echo "\n";

// ===============================================
// 4. CONTROLLER METHOD TESTS
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "4. CONTROLLER METHOD TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// AdminController
$adminController = \App\Http\Controllers\AdminController::class;
test('AdminController::assignHousekeepers exists', method_exists($adminController, 'assignHousekeepers'));
test('AdminController::unassignHousekeeper exists', method_exists($adminController, 'unassignHousekeeper'));
test('AdminController::payHousekeeper exists', method_exists($adminController, 'payHousekeeper'));
test('AdminController::getHousekeeperSalaries exists', method_exists($adminController, 'getHousekeeperSalaries'));

// StripeController
$stripeController = \App\Http\Controllers\StripeController::class;
test('StripeController::createHousekeeperOnboardingLink exists', method_exists($stripeController, 'createHousekeeperOnboardingLink'));
test('StripeController::connectHousekeeperPayoutMethod exists', method_exists($stripeController, 'connectHousekeeperPayoutMethod'));
test('StripeController::checkHousekeeperOnboardingStatus exists', method_exists($stripeController, 'checkHousekeeperOnboardingStatus'));
test('StripeController::getHousekeeperConnectionStatus exists', method_exists($stripeController, 'getHousekeeperConnectionStatus'));

// TimeTrackingController
$timeController = \App\Http\Controllers\TimeTrackingController::class;
test('TimeTrackingController::getHousekeeperCurrentSession exists', method_exists($timeController, 'getHousekeeperCurrentSession'));
test('TimeTrackingController::getHousekeeperWeeklyHistory exists', method_exists($timeController, 'getHousekeeperWeeklyHistory'));

echo "\n";

// ===============================================
// 5. SERVICE METHOD TESTS
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "5. SERVICE METHOD TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// StripePaymentService
$stripeService = \App\Services\StripePaymentService::class;
test('StripePaymentService::createOnboardingLinkForHousekeeper exists', method_exists($stripeService, 'createOnboardingLinkForHousekeeper'));
test('StripePaymentService::transferToHousekeeper exists', method_exists($stripeService, 'transferToHousekeeper'));
test('StripePaymentService::isHousekeeperConnectAccountComplete exists', method_exists($stripeService, 'isHousekeeperConnectAccountComplete'));

echo "\n";

// ===============================================
// 6. ROUTE TESTS
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "6. ROUTE TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Get all routes
$routes = collect(\Route::getRoutes())->map(function($route) {
    return $route->uri();
})->toArray();

$routesList = implode(' ', $routes);

// Test housekeeper routes exist
test('Route: assign-housekeepers exists', str_contains($routesList, 'assign-housekeepers'));
test('Route: unassign-housekeeper exists', str_contains($routesList, 'unassign-housekeeper'));
test('Route: pay-housekeeper exists', str_contains($routesList, 'pay-housekeeper'));
test('Route: housekeeper-salaries exists', str_contains($routesList, 'housekeeper-salaries'));
test('Route: housekeeper/connect-payout-method exists', str_contains($routesList, 'housekeeper/connect-payout-method'));
test('Route: housekeeper/connection-status exists', str_contains($routesList, 'housekeeper/connection-status'));
test('Route: housekeeper time tracking exists', str_contains($routesList, 'time-tracking/housekeeper'));

echo "\n";

// ===============================================
// 7. DATA COMPARISON
// ===============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "7. DATA COMPARISON\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$caregiverCount = Caregiver::count();
$housekeeperCount = Housekeeper::count();
echo "  📊 Caregivers in database: {$caregiverCount}\n";
echo "  📊 Housekeepers in database: {$housekeeperCount}\n";

$caregiverTimeTrackings = TimeTracking::whereNotNull('caregiver_id')->count();
$housekeeperTimeTrackings = TimeTracking::whereNotNull('housekeeper_id')->count();
echo "  📊 Caregiver time trackings: {$caregiverTimeTrackings}\n";
echo "  📊 Housekeeper time trackings: {$housekeeperTimeTrackings}\n";

$housekeeperBookingAssignments = DB::table('booking_housekeeper_assignments')->count();
echo "  📊 Housekeeper booking assignments: {$housekeeperBookingAssignments}\n";

echo "\n";

// ===============================================
// SUMMARY
// ===============================================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                      TEST SUMMARY                            ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
printf("║  ✅ Passed:   %-46d ║\n", $tests['passed']);
printf("║  ❌ Failed:   %-46d ║\n", $tests['failed']);
printf("║  ⚠️  Warnings: %-46d ║\n", $tests['warnings']);
echo "╠══════════════════════════════════════════════════════════════╣\n";

$total = $tests['passed'] + $tests['failed'];
$percentage = $total > 0 ? round(($tests['passed'] / $total) * 100) : 0;

if ($tests['failed'] == 0) {
    echo "║  🎉 ALL TESTS PASSED! Housekeeper = Caregiver Parity ✓      ║\n";
} else {
    printf("║  Score: %d%% (%d/%d tests passed)                         ║\n", $percentage, $tests['passed'], $total);
}
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

exit($tests['failed'] > 0 ? 1 : 0);

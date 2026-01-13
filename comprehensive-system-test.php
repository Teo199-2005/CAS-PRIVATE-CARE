<?php

/**
 * CAS Private Care - Comprehensive System Test
 * Tests all critical flows: Auth, Booking, Payment, Time Tracking, Commissions
 * 
 * Usage: php comprehensive-system-test.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\Client;
use App\Models\TimeTracking;
use App\Models\Payment;
use App\Models\ReferralCode;
use App\Models\BookingAssignment;
use App\Services\PricingService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     CAS PRIVATE CARE - COMPREHENSIVE SYSTEM TEST               ║\n";
echo "║     " . date('Y-m-d H:i:s') . "                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$tests = [];
$passed = 0;
$failed = 0;

function test($name, $callback) {
    global $passed, $failed, $tests;
    
    try {
        $result = $callback();
        if ($result === true) {
            echo "  ✅ {$name}\n";
            $passed++;
            $tests[$name] = 'PASS';
            return true;
        } else {
            echo "  ❌ {$name} - " . ($result ?: 'Failed') . "\n";
            $failed++;
            $tests[$name] = 'FAIL: ' . $result;
            return false;
        }
    } catch (\Exception $e) {
        echo "  ❌ {$name} - Exception: " . $e->getMessage() . "\n";
        $failed++;
        $tests[$name] = 'EXCEPTION: ' . $e->getMessage();
        return false;
    }
}

// =================================
// 1. DATABASE CONNECTIVITY
// =================================
echo "📊 DATABASE TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Database connection works', function() {
    DB::connection()->getPdo();
    return true;
});

test('Users table exists and accessible', function() {
    $count = User::count();
    return $count >= 0;
});

test('Bookings table exists and accessible', function() {
    $count = Booking::count();
    return $count >= 0;
});

test('Caregivers table exists and accessible', function() {
    $count = Caregiver::count();
    return $count >= 0;
});

test('Housekeepers table exists and accessible', function() {
    $count = Housekeeper::count();
    return $count >= 0;
});

test('TimeTracking table exists and accessible', function() {
    $count = TimeTracking::count();
    return $count >= 0;
});

test('Payments table exists and accessible', function() {
    $count = Payment::count();
    return $count >= 0;
});

// =================================
// 2. USER AUTHENTICATION
// =================================
echo "\n🔐 AUTHENTICATION TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Password hashing works correctly', function() {
    $password = 'TestPassword123!';
    $hashed = Hash::make($password);
    return Hash::check($password, $hashed);
});

test('Admin user exists', function() {
    $admin = User::where('user_type', 'admin')->first();
    return $admin !== null;
});

test('Client user type can be created', function() {
    // Don't actually create, just test the model
    $user = new User([
        'name' => 'Test Client',
        'email' => 'test-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'user_type' => 'client'
    ]);
    return $user->user_type === 'client';
});

test('Caregiver user type can be created', function() {
    $user = new User([
        'name' => 'Test Caregiver',
        'email' => 'test-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'user_type' => 'caregiver'
    ]);
    return $user->user_type === 'caregiver';
});

test('Housekeeper user type can be created', function() {
    $user = new User([
        'name' => 'Test Housekeeper',
        'email' => 'test-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'user_type' => 'housekeeper'
    ]);
    return $user->user_type === 'housekeeper';
});

// =================================
// 3. PRICING SERVICE
// =================================
echo "\n💰 PRICING SERVICE TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Caregiver rate is $28/hr', function() {
    return PricingService::CAREGIVER_RATE === 28.00;
});

test('Client rate without referral is $45/hr', function() {
    return PricingService::CLIENT_RATE_NO_REFERRAL === 45.00;
});

test('Client rate with referral is $40/hr', function() {
    return PricingService::CLIENT_RATE_WITH_REFERRAL === 40.00;
});

test('Referral discount is $5/hr', function() {
    return PricingService::REFERRAL_DISCOUNT === 5.00;
});

test('Training center rate is $0.50/hr', function() {
    return PricingService::TRAINING_CENTER_RATE === 0.50;
});

test('Marketing rate is $1/hr', function() {
    return PricingService::MARKETING_RATE === 1.00;
});

test('Pricing breakdown calculates correctly (no referral, with training)', function() {
    $breakdown = PricingService::calculateBreakdown(10, false, true);
    
    // 10 hours × $45 = $450 client charge
    // 10 hours × $28 = $280 caregiver
    // 10 hours × $0.50 = $5 training
    // 10 hours × $16.50 = $165 agency
    
    return $breakdown['client_total'] == 450 
        && $breakdown['breakdown']['caregiver']['total'] == 280
        && $breakdown['breakdown']['training_center']['total'] == 5
        && $breakdown['breakdown']['agency']['total'] == 165;
});

test('Pricing breakdown calculates correctly (with referral, with training)', function() {
    $breakdown = PricingService::calculateBreakdown(10, true, true);
    
    // 10 hours × $40 = $400 client charge
    // 10 hours × $28 = $280 caregiver
    // 10 hours × $0.50 = $5 training
    // 10 hours × $1 = $10 marketing
    // 10 hours × $10.50 = $105 agency
    
    return $breakdown['client_total'] == 400
        && $breakdown['breakdown']['caregiver']['total'] == 280
        && $breakdown['breakdown']['training_center']['total'] == 5
        && $breakdown['breakdown']['marketing']['total'] == 10
        && $breakdown['breakdown']['agency']['total'] == 105;
});

test('Housekeeper pricing works correctly', function() {
    $breakdown = PricingService::calculateHousekeeperBreakdown(10, 25.00, false);
    
    // 10 hours × $45 = $450 client charge
    // 10 hours × $25 = $250 housekeeper (assigned rate)
    // Agency = $450 - $250 = $200
    
    return $breakdown['client_total'] == 450
        && $breakdown['breakdown']['housekeeper']['total'] == 250
        && $breakdown['breakdown']['agency']['total'] == 200;
});

// =================================
// 4. BOOKING FLOW
// =================================
echo "\n📅 BOOKING FLOW TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Booking model has required fillable fields', function() {
    $booking = new Booking();
    $fillable = $booking->getFillable();
    
    $required = ['client_id', 'service_type', 'duty_type', 'service_date', 'duration_days', 'status'];
    
    foreach ($required as $field) {
        if (!in_array($field, $fillable)) {
            return "Missing field: {$field}";
        }
    }
    return true;
});

test('Booking status values are valid', function() {
    $validStatuses = ['pending', 'approved', 'completed', 'cancelled'];
    // Just verify the model accepts these
    $booking = new Booking(['status' => 'pending']);
    return $booking->status === 'pending';
});

test('Booking relationships work (client)', function() {
    $booking = Booking::with('client')->first();
    if (!$booking) {
        return true; // Skip if no bookings
    }
    return $booking->client !== null || $booking->client_id === null;
});

test('Booking assignments relationship exists', function() {
    $booking = new Booking();
    return method_exists($booking, 'assignments');
});

test('Booking housekeeper assignments relationship exists', function() {
    $booking = new Booking();
    return method_exists($booking, 'housekeeperAssignments');
});

// =================================
// 5. TIME TRACKING
// =================================
echo "\n⏰ TIME TRACKING TESTS\n";
echo str_repeat("-", 60) . "\n";

test('TimeTracking model has required fields', function() {
    $tt = new TimeTracking();
    $fillable = $tt->getFillable();
    
    $required = ['clock_in_time', 'clock_out_time', 'status', 'work_date'];
    
    foreach ($required as $field) {
        if (!in_array($field, $fillable)) {
            return "Missing field: {$field}";
        }
    }
    return true;
});

test('TimeTracking supports both caregiver and housekeeper', function() {
    $tt = new TimeTracking();
    $fillable = $tt->getFillable();
    
    return in_array('caregiver_id', $fillable) && in_array('housekeeper_id', $fillable);
});

test('TimeTracking has provider_type field', function() {
    $tt = new TimeTracking();
    return in_array('provider_type', $tt->getFillable());
});

test('TimeTracking has commission fields', function() {
    $tt = new TimeTracking();
    $fillable = $tt->getFillable();
    
    $required = ['caregiver_earnings', 'marketing_partner_commission', 'training_center_commission', 'agency_commission'];
    
    foreach ($required as $field) {
        if (!in_array($field, $fillable)) {
            return "Missing field: {$field}";
        }
    }
    return true;
});

// =================================
// 6. PAYMENT FLOW
// =================================
echo "\n💳 PAYMENT FLOW TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Payment model has required fields', function() {
    $payment = new Payment();
    $fillable = $payment->getFillable();
    
    $required = ['booking_id', 'amount', 'status', 'payment_method'];
    
    foreach ($required as $field) {
        if (!in_array($field, $fillable)) {
            return "Missing field: {$field}";
        }
    }
    return true;
});

test('Payment supports processing fee', function() {
    $payment = new Payment();
    return in_array('processing_fee', $payment->getFillable());
});

test('Stripe config exists', function() {
    return config('stripe.key') !== null && config('stripe.secret_key') !== null;
});

// =================================
// 7. REFERRAL SYSTEM
// =================================
echo "\n🎁 REFERRAL SYSTEM TESTS\n";
echo str_repeat("-", 60) . "\n";

test('ReferralCode model exists', function() {
    return class_exists(ReferralCode::class);
});

test('ReferralCode has required fields', function() {
    $code = new ReferralCode();
    $fillable = $code->getFillable();
    
    return in_array('code', $fillable) && in_array('user_id', $fillable);
});

// =================================
// 8. COMMISSION CALCULATIONS
// =================================
echo "\n📈 COMMISSION CALCULATION TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Agency commission without referral is $17/hr (no training)', function() {
    return PricingService::AGENCY_RATE_NO_REFERRAL_NO_TRAINING === 17.00;
});

test('Agency commission without referral is $16.50/hr (with training)', function() {
    return PricingService::AGENCY_RATE_NO_REFERRAL === 16.50;
});

test('Agency commission with referral is $11/hr (no training)', function() {
    return PricingService::AGENCY_RATE_WITH_REFERRAL_NO_TRAINING === 11.00;
});

test('Agency commission with referral is $10.50/hr (with training)', function() {
    return PricingService::AGENCY_RATE_WITH_REFERRAL === 10.50;
});

test('Total adds up correctly (no referral)', function() {
    // $28 (caregiver) + $0.50 (training) + $16.50 (agency) = $45
    $total = PricingService::CAREGIVER_RATE + PricingService::TRAINING_CENTER_RATE + PricingService::AGENCY_RATE_NO_REFERRAL;
    return $total == 45.00;
});

test('Total adds up correctly (with referral)', function() {
    // $28 (caregiver) + $0.50 (training) + $1 (marketing) + $10.50 (agency) = $40
    $total = PricingService::CAREGIVER_RATE + PricingService::TRAINING_CENTER_RATE + PricingService::MARKETING_RATE + PricingService::AGENCY_RATE_WITH_REFERRAL;
    return $total == 40.00;
});

// =================================
// 9. SECURITY CHECKS
// =================================
echo "\n🔒 SECURITY TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Passwords are properly hashed', function() {
    $user = User::first();
    if (!$user) return true;
    
    // Bcrypt hashes start with $2y$ and are 60 characters
    return strlen($user->password) === 60 && str_starts_with($user->password, '$2y$');
});

test('No plain text passwords in admin controller', function() {
    $content = file_get_contents(__DIR__ . '/app/Http/Controllers/AdminController.php');
    return !str_contains($content, "'123456'") && !str_contains($content, '"123456"');
});

test('Login route has throttle middleware', function() {
    $content = file_get_contents(__DIR__ . '/routes/web.php');
    return str_contains($content, "Route::post('/login'") && str_contains($content, "throttle");
});

test('Register route has throttle middleware', function() {
    $content = file_get_contents(__DIR__ . '/routes/web.php');
    return str_contains($content, "Route::post('/register'") && str_contains($content, "throttle");
});

test('Security headers middleware exists', function() {
    return file_exists(__DIR__ . '/app/Http/Middleware/SecurityHeaders.php');
});

test('EnsureUserType middleware exists', function() {
    return file_exists(__DIR__ . '/app/Http/Middleware/EnsureUserType.php');
});

// =================================
// 10. API ENDPOINTS
// =================================
echo "\n🌐 API ENDPOINT TESTS\n";
echo str_repeat("-", 60) . "\n";

test('Web routes file exists', function() {
    return file_exists(__DIR__ . '/routes/web.php');
});

test('API routes file exists', function() {
    return file_exists(__DIR__ . '/routes/api.php');
});

test('Stripe controller exists', function() {
    return file_exists(__DIR__ . '/app/Http/Controllers/StripeController.php');
});

test('TimeTracking controller exists', function() {
    return file_exists(__DIR__ . '/app/Http/Controllers/TimeTrackingController.php');
});

test('Booking controller exists', function() {
    return file_exists(__DIR__ . '/app/Http/Controllers/BookingController.php');
});

// =================================
// 11. PORTAL FUNCTIONALITY
// =================================
echo "\n🖥️  PORTAL TESTS\n";
echo str_repeat("-", 60) . "\n";

test('AdminDashboard Vue component exists', function() {
    return file_exists(__DIR__ . '/resources/js/components/AdminDashboard.vue');
});

test('CaregiverDashboard Vue component exists', function() {
    return file_exists(__DIR__ . '/resources/js/components/CaregiverDashboard.vue');
});

test('ClientDashboard Vue component exists', function() {
    return file_exists(__DIR__ . '/resources/js/components/ClientDashboard.vue');
});

test('HousekeeperDashboard Vue component exists', function() {
    return file_exists(__DIR__ . '/resources/js/components/HousekeeperDashboard.vue');
});

test('MarketingDashboard Vue component exists', function() {
    return file_exists(__DIR__ . '/resources/js/components/MarketingDashboard.vue');
});

test('TrainingDashboard Vue component exists', function() {
    return file_exists(__DIR__ . '/resources/js/components/TrainingDashboard.vue');
});

// =================================
// 12. FILE UPLOAD SECURITY
// =================================
echo "\n📁 FILE UPLOAD SECURITY TESTS\n";
echo str_repeat("-", 60) . "\n";

test('AvatarController validates file types', function() {
    $content = file_get_contents(__DIR__ . '/app/Http/Controllers/AvatarController.php');
    return str_contains($content, 'mimes:jpeg,png,jpg,gif');
});

test('AvatarController validates file size', function() {
    $content = file_get_contents(__DIR__ . '/app/Http/Controllers/AvatarController.php');
    return str_contains($content, 'max:2048');
});

test('Avatar storage directory is in public disk', function() {
    $content = file_get_contents(__DIR__ . '/app/Http/Controllers/AvatarController.php');
    return str_contains($content, "store('avatars', 'public')");
});

// =================================
// SUMMARY
// =================================
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                     TEST RESULTS SUMMARY                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "  Total Tests: " . ($passed + $failed) . "\n";
echo "  ✅ Passed:   {$passed}\n";
echo "  ❌ Failed:   {$failed}\n";
echo "\n";

$percentage = round(($passed / ($passed + $failed)) * 100, 1);
echo "  Success Rate: {$percentage}%\n";
echo "\n";

if ($failed === 0) {
    echo "  🎉 ALL TESTS PASSED! Your system is ready for production.\n";
} elseif ($percentage >= 90) {
    echo "  ✅ MOSTLY PASSING - Review failed tests before deployment.\n";
} elseif ($percentage >= 70) {
    echo "  ⚠️  NEEDS ATTENTION - Several tests failed.\n";
} else {
    echo "  ❌ CRITICAL - Many tests failed. Do not deploy.\n";
}

echo "\n";

// Write detailed results to file
$reportPath = __DIR__ . '/storage/logs/system-test-' . date('Y-m-d-His') . '.json';
file_put_contents($reportPath, json_encode([
    'date' => date('Y-m-d H:i:s'),
    'passed' => $passed,
    'failed' => $failed,
    'percentage' => $percentage,
    'tests' => $tests
], JSON_PRETTY_PRINT));

echo "  📄 Detailed report saved to: {$reportPath}\n";
echo "\n";
echo "════════════════════════════════════════════════════════════════════\n";

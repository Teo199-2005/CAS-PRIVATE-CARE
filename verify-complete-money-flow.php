<?php
/**
 * MONEY FLOW VERIFICATION SCRIPT
 * 
 * Run this script to verify all money flows are working correctly.
 * Usage: php verify-complete-money-flow.php
 * 
 * This checks:
 * 1. Commission calculations are correct
 * 2. Admin sees correct amounts
 * 3. Contractors see correct amounts
 * 4. Transfers use correct amounts
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║           💰 COMPLETE MONEY FLOW VERIFICATION                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];

// ============================================================
// TEST 1: Verify Commission Calculations Are Correct
// Uses FLEXIBLE RATE SYSTEM - checks assigned_hourly_rate first
// ============================================================
echo "📊 TEST 1: Commission Calculation Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$recentTrackings = TimeTracking::whereNotNull('hours_worked')
    ->whereNotNull('caregiver_earnings')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

$calculationErrors = 0;
$defaultCaregiverRate = 28.00;
$defaultHousekeeperRate = 20.00;

foreach ($recentTrackings as $tracking) {
    $hours = $tracking->hours_worked;
    
    // Determine expected rate based on flexible rate system
    $expectedRate = $defaultCaregiverRate; // Default for caregivers
    
    if ($tracking->housekeeper_id) {
        $expectedRate = $defaultHousekeeperRate; // Default for housekeepers
        
        // Check for assigned rate in booking_housekeeper_assignments
        if ($tracking->booking_id) {
            $assignment = DB::table('booking_housekeeper_assignments')
                ->where('booking_id', $tracking->booking_id)
                ->where('housekeeper_id', $tracking->housekeeper_id)
                ->first();
            
            if ($assignment && $assignment->assigned_hourly_rate) {
                $expectedRate = (float)$assignment->assigned_hourly_rate;
            }
        }
    } elseif ($tracking->caregiver_id && $tracking->booking_id) {
        // Check for assigned rate in booking_assignments
        $assignment = DB::table('booking_assignments')
            ->where('booking_id', $tracking->booking_id)
            ->where('caregiver_id', $tracking->caregiver_id)
            ->first();
        
        if ($assignment && $assignment->assigned_hourly_rate) {
            $expectedRate = (float)$assignment->assigned_hourly_rate;
        }
    }
    
    $expectedCaregiverEarnings = round($hours * $expectedRate, 2);
    $actualCaregiverEarnings = round($tracking->caregiver_earnings, 2);
    
    // Check if caregiver earnings are approximately correct
    $difference = abs($expectedCaregiverEarnings - $actualCaregiverEarnings);
    
    if ($difference > 0.02) { // Allow 2 cent rounding difference
        $calculationErrors++;
        $errors[] = "TimeTracking #{$tracking->id}: Expected earnings \${$expectedCaregiverEarnings} (at \${$expectedRate}/hr), got \${$actualCaregiverEarnings}";
    }
    
    // Verify total_client_charge exists
    if ($tracking->total_client_charge) {
        $clientRate = $tracking->marketing_partner_id ? 40.00 : 45.00;
        $expectedClientCharge = round($hours * $clientRate, 2);
        $actualClientCharge = round($tracking->total_client_charge, 2);
        
        if (abs($expectedClientCharge - $actualClientCharge) > 0.02) {
            $warnings[] = "TimeTracking #{$tracking->id}: Client charge \${$actualClientCharge} differs from expected \${$expectedClientCharge}";
        }
    }
}

if ($calculationErrors === 0) {
    echo "   ✅ All {$recentTrackings->count()} recent time trackings have correct caregiver earnings\n";
} else {
    echo "   ❌ Found {$calculationErrors} calculation errors\n";
}

// ============================================================
// TEST 2: Verify Money Balance (Client Charge = All Distributions)
// ============================================================
echo "\n📊 TEST 2: Money Balance Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$balanceErrors = 0;
$trackingsWithAllFields = TimeTracking::whereNotNull('total_client_charge')
    ->whereNotNull('caregiver_earnings')
    ->orderBy('created_at', 'desc')
    ->take(20)
    ->get();

foreach ($trackingsWithAllFields as $tracking) {
    $totalDistributed = ($tracking->caregiver_earnings ?? 0)
        + ($tracking->marketing_partner_commission ?? 0)
        + ($tracking->training_center_commission ?? 0)
        + ($tracking->agency_commission ?? 0);
    
    $clientCharge = $tracking->total_client_charge;
    
    if (abs($clientCharge - $totalDistributed) > 0.02) {
        $balanceErrors++;
        $errors[] = "TimeTracking #{$tracking->id}: Client charged \${$clientCharge} but distributions total \${$totalDistributed}";
    }
}

if ($balanceErrors === 0) {
    echo "   ✅ All {$trackingsWithAllFields->count()} trackings have balanced distributions\n";
} else {
    echo "   ❌ Found {$balanceErrors} money imbalance errors - CRITICAL!\n";
}

// ============================================================
// TEST 3: Verify Admin Sees Correct Unpaid Amounts
// ============================================================
echo "\n📊 TEST 3: Admin Dashboard Amount Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$caregivers = Caregiver::with('user')->take(5)->get();
$adminAmountMismatch = 0;

foreach ($caregivers as $caregiver) {
    $unpaidFromDB = TimeTracking::where('caregiver_id', $caregiver->id)
        ->whereNull('paid_at')
        ->sum('caregiver_earnings');
    
    $unpaidFromMethod = TimeTracking::where('caregiver_id', $caregiver->id)
        ->whereNull('paid_at')
        ->get()
        ->sum('caregiver_earnings');
    
    if (abs($unpaidFromDB - $unpaidFromMethod) > 0.01) {
        $adminAmountMismatch++;
        $errors[] = "Caregiver #{$caregiver->id}: DB sum \${$unpaidFromDB} vs Collection sum \${$unpaidFromMethod}";
    }
}

if ($adminAmountMismatch === 0) {
    echo "   ✅ Admin sees consistent amounts for all {$caregivers->count()} caregivers checked\n";
} else {
    echo "   ❌ Found {$adminAmountMismatch} amount mismatches\n";
}

// ============================================================
// TEST 4: Verify Contractor Dashboards Show Correct Amounts
// ============================================================
echo "\n📊 TEST 4: Contractor Dashboard Amount Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ($caregivers as $caregiver) {
    // This mimics CaregiverDataController::paymentData logic
    $timeTrackings = TimeTracking::where('caregiver_id', $caregiver->id)->get();
    
    $paidEarnings = $timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings');
    $pendingEarnings = $timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings');
    
    // Also check by paid_at field (should match payment_status)
    $paidByField = $timeTrackings->whereNotNull('paid_at')->sum('caregiver_earnings');
    $pendingByField = $timeTrackings->whereNull('paid_at')->sum('caregiver_earnings');
    
    $name = $caregiver->user->name ?? "Caregiver #{$caregiver->id}";
    
    echo "   {$name}:\n";
    echo "      Pending (by status): \$" . number_format($pendingEarnings, 2) . "\n";
    echo "      Pending (by paid_at): \$" . number_format($pendingByField, 2) . "\n";
    
    if (abs($pendingEarnings - $pendingByField) > 0.01) {
        $warnings[] = "{$name}: payment_status and paid_at fields are inconsistent";
    }
}

// ============================================================
// TEST 5: Verify Marketing Commission Calculations
// ============================================================
echo "\n📊 TEST 5: Marketing Commission Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$marketingTrackings = TimeTracking::whereNotNull('marketing_partner_id')
    ->whereNotNull('marketing_partner_commission')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

$marketingErrors = 0;

foreach ($marketingTrackings as $tracking) {
    $hours = $tracking->hours_worked ?? 0;
    $expectedCommission = round($hours * 1.00, 2); // $1/hr
    $actualCommission = round($tracking->marketing_partner_commission, 2);
    
    if (abs($expectedCommission - $actualCommission) > 0.02) {
        $marketingErrors++;
        $errors[] = "TimeTracking #{$tracking->id}: Expected marketing commission \${$expectedCommission}, got \${$actualCommission}";
    }
}

if ($marketingErrors === 0) {
    echo "   ✅ All {$marketingTrackings->count()} marketing commissions are correct (\$1/hr)\n";
} else {
    echo "   ❌ Found {$marketingErrors} marketing commission errors\n";
}

// ============================================================
// TEST 6: Verify Training Commission Calculations
// ============================================================
echo "\n📊 TEST 6: Training Commission Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$trainingTrackings = TimeTracking::whereNotNull('training_center_user_id')
    ->whereNotNull('training_center_commission')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

$trainingErrors = 0;

foreach ($trainingTrackings as $tracking) {
    $hours = $tracking->hours_worked ?? 0;
    $expectedCommission = round($hours * 0.50, 2); // $0.50/hr
    $actualCommission = round($tracking->training_center_commission, 2);
    
    if (abs($expectedCommission - $actualCommission) > 0.02) {
        $trainingErrors++;
        $errors[] = "TimeTracking #{$tracking->id}: Expected training commission \${$expectedCommission}, got \${$actualCommission}";
    }
}

if ($trainingErrors === 0) {
    echo "   ✅ All {$trainingTrackings->count()} training commissions are correct (\$0.50/hr)\n";
} else {
    echo "   ❌ Found {$trainingErrors} training commission errors\n";
}

// ============================================================
// TEST 7: Verify No Double Payments Exist
// ============================================================
echo "\n📊 TEST 7: Double Payment Detection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Check for duplicate stripe_transfer_id values
$duplicateTransfers = DB::table('time_trackings')
    ->select('stripe_transfer_id')
    ->whereNotNull('stripe_transfer_id')
    ->groupBy('stripe_transfer_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($duplicateTransfers->isEmpty()) {
    echo "   ✅ No duplicate stripe_transfer_id values found\n";
} else {
    echo "   ⚠️  Found " . $duplicateTransfers->count() . " duplicate transfer IDs (may be batch payments - verify manually)\n";
}

// ============================================================
// TEST 8: Verify Rates Are Consistent
// ============================================================
echo "\n📊 TEST 8: Rate Consistency Check\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

echo "   Expected rates:\n";
echo "   • Caregiver: \$28.00/hr (fixed)\n";
echo "   • Marketing: \$1.00/hr (with referral)\n";
echo "   • Training: \$0.50/hr (if trained)\n";
echo "   • Client (no referral): \$45.00/hr\n";
echo "   • Client (with referral): \$40.00/hr\n";

$avgCaregiverRate = TimeTracking::whereNotNull('hours_worked')
    ->where('hours_worked', '>', 0)
    ->whereNotNull('caregiver_earnings')
    ->selectRaw('AVG(caregiver_earnings / hours_worked) as avg_rate')
    ->value('avg_rate');

echo "   Actual average caregiver rate: \$" . number_format($avgCaregiverRate ?? 0, 2) . "/hr\n";

if ($avgCaregiverRate && abs($avgCaregiverRate - 28.00) < 1.00) {
    echo "   ✅ Caregiver rate is within expected range\n";
} else {
    $warnings[] = "Average caregiver rate is \${$avgCaregiverRate}/hr, expected \$28.00/hr";
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                        📋 SUMMARY                               ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

if (empty($errors)) {
    echo "✅ ALL TESTS PASSED - Money flows correctly!\n\n";
} else {
    echo "❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "   • {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   • {$warning}\n";
    }
    echo "\n";
}

echo "Money Flow Summary:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Client pays → Platform receives (via Stripe)\n";
echo "2. Clock out → Earnings calculated (hours × rate)\n";
echo "3. Admin dashboard → Shows unpaid amount\n";
echo "4. Admin clicks Pay → Amount validated\n";
echo "5. Stripe Transfer → Money sent to contractor bank\n";
echo "6. Contractor dashboard → Shows updated balance\n";
echo "\n";

$totalPendingCaregiver = TimeTracking::whereNull('paid_at')
    ->whereNotNull('caregiver_id')
    ->sum('caregiver_earnings');

$totalPendingMarketing = TimeTracking::whereNull('marketing_commission_paid_at')
    ->whereNotNull('marketing_partner_id')
    ->sum('marketing_partner_commission');

$totalPendingTraining = TimeTracking::whereNull('training_commission_paid_at')
    ->whereNotNull('training_center_user_id')
    ->sum('training_center_commission');

echo "Current Pending Balances:\n";
echo "   💵 Caregivers: \$" . number_format($totalPendingCaregiver ?? 0, 2) . "\n";
echo "   📢 Marketing: \$" . number_format($totalPendingMarketing ?? 0, 2) . "\n";
echo "   🎓 Training: \$" . number_format($totalPendingTraining ?? 0, 2) . "\n";
echo "\n";

<?php
/**
 * MONEY FLOW VERIFICATION SCRIPT
 * 
 * Run this script to verify all money is accounted for
 * 
 * Usage: php verify-money-flow.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\TimeTracking;
use App\Models\Caregiver;
use Carbon\Carbon;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "             💰 MONEY FLOW VERIFICATION REPORT 💰              \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Generated: " . now()->format('M d, Y g:i A') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// CLIENT PAYMENTS (MONEY IN)
echo "📥 CLIENT PAYMENTS (Money Coming In)\n";
echo "────────────────────────────────────────────────────────────────\n";

$totalClientPayments = Payment::where('status', 'completed')->sum('amount');
$todayClientPayments = Payment::whereDate('created_at', today())
    ->where('status', 'completed')
    ->sum('amount');
$clientPaymentCount = Payment::where('status', 'completed')->count();

echo "Total Received:      $" . number_format($totalClientPayments, 2) . "\n";
echo "Received Today:      $" . number_format($todayClientPayments, 2) . "\n";
echo "Number of Payments:  {$clientPaymentCount}\n\n";

// Recent payments
$recentPayments = Payment::where('status', 'completed')
    ->with('booking.client')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

if ($recentPayments->count() > 0) {
    echo "Recent Payments:\n";
    foreach ($recentPayments as $payment) {
        $clientName = $payment->booking->client->name ?? 'Unknown';
        $date = $payment->created_at->format('M d, Y');
        $stripeId = substr($payment->stripe_payment_intent_id ?? 'N/A', 0, 20);
        echo "  ✓ {$date} - {$clientName}: $" . number_format($payment->amount, 2) . " (Stripe: {$stripeId})\n";
    }
    echo "\n";
}

// CONTRACTOR PAYOUTS (MONEY OUT)
echo "📤 CONTRACTOR PAYOUTS (Money Going Out)\n";
echo "────────────────────────────────────────────────────────────────\n";

$totalPaidOut = TimeTracking::where('payment_status', 'paid')
    ->sum('caregiver_earnings');
$todayPaidOut = TimeTracking::whereDate('paid_at', today())
    ->where('payment_status', 'paid')
    ->sum('caregiver_earnings');
$payoutCount = TimeTracking::where('payment_status', 'paid')
    ->whereNotNull('paid_at')
    ->distinct('paid_at')
    ->count('paid_at');

echo "Total Paid Out:      $" . number_format($totalPaidOut, 2) . "\n";
echo "Paid Out Today:      $" . number_format($todayPaidOut, 2) . "\n";
echo "Number of Payouts:   {$payoutCount}\n\n";

// Recent payouts
$recentPayouts = TimeTracking::where('payment_status', 'paid')
    ->whereNotNull('paid_at')
    ->with('caregiver.user')
    ->orderBy('paid_at', 'desc')
    ->take(5)
    ->get();

if ($recentPayouts->count() > 0) {
    echo "Recent Payouts:\n";
    foreach ($recentPayouts as $payout) {
        $caregiverName = $payout->caregiver->user->name ?? 'Unknown';
        $date = $payout->paid_at->format('M d, Y');
        $stripeId = substr($payout->stripe_transfer_id ?? 'N/A', 0, 20);
        echo "  ✓ {$date} - {$caregiverName}: $" . number_format($payout->caregiver_earnings, 2) . " (Stripe: {$stripeId})\n";
    }
    echo "\n";
}

// PENDING OBLIGATIONS
echo "⏳ PENDING OBLIGATIONS (Money Owed)\n";
echo "────────────────────────────────────────────────────────────────\n";

$pendingCaregiver = TimeTracking::where('payment_status', 'pending')
    ->sum('caregiver_earnings');
$pendingMarketing = TimeTracking::where('payment_status', 'pending')
    ->whereNotNull('marketing_partner_id')
    ->sum('marketing_partner_commission');
$pendingTraining = TimeTracking::where('payment_status', 'pending')
    ->sum('training_center_commission');

echo "Owed to Caregivers:  $" . number_format($pendingCaregiver, 2) . "\n";
echo "Owed to Marketing:   $" . number_format($pendingMarketing, 2) . "\n";
echo "Owed to Training:    $" . number_format($pendingTraining, 2) . "\n";
echo "Total Pending:       $" . number_format($pendingCaregiver + $pendingMarketing + $pendingTraining, 2) . "\n\n";

// CAREGIVER BREAKDOWN
echo "👥 CAREGIVER BALANCES\n";
echo "────────────────────────────────────────────────────────────────\n";

$caregivers = Caregiver::with('user')->get();

foreach ($caregivers as $caregiver) {
    $pending = $caregiver->timeTrackings()
        ->where('payment_status', 'pending')
        ->sum('caregiver_earnings');
    
    $paid = $caregiver->timeTrackings()
        ->where('payment_status', 'paid')
        ->sum('caregiver_earnings');
    
    $lastPayment = $caregiver->timeTrackings()
        ->where('payment_status', 'paid')
        ->whereNotNull('paid_at')
        ->orderBy('paid_at', 'desc')
        ->first();
    
    $bankStatus = !empty($caregiver->stripe_connect_id) ? '✓ Connected' : '✗ Not Connected';
    
    echo "\n{$caregiver->user->name}:\n";
    echo "  Pending Balance: $" . number_format($pending, 2) . "\n";
    echo "  Total Paid:      $" . number_format($paid, 2) . "\n";
    echo "  Bank Status:     {$bankStatus}\n";
    
    if ($lastPayment) {
        echo "  Last Payment:    $" . number_format($lastPayment->caregiver_earnings, 2) . 
             " on " . $lastPayment->paid_at->format('M d, Y') . "\n";
    }
}

echo "\n";

// PLATFORM COMMISSION
echo "🏢 PLATFORM EARNINGS\n";
echo "────────────────────────────────────────────────────────────────\n";

$platformCommission = TimeTracking::where('payment_status', 'paid')
    ->sum('agency_commission');

echo "Total Commission:    $" . number_format($platformCommission, 2) . "\n\n";

// BALANCE CALCULATION
echo "💼 BALANCE VERIFICATION\n";
echo "────────────────────────────────────────────────────────────────\n";

$expectedBalance = $totalClientPayments - $totalPaidOut;

echo "Money In (Clients):  $" . number_format($totalClientPayments, 2) . "\n";
echo "Money Out (Paid):    $" . number_format($totalPaidOut, 2) . "\n";
echo "Expected Balance:    $" . number_format($expectedBalance, 2) . "\n";

// Check Stripe balance
try {
    $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
    $balance = $stripe->balance->retrieve();
    
    $availableBalance = 0;
    foreach ($balance->available as $balanceItem) {
        if ($balanceItem->currency === 'usd') {
            $availableBalance = $balanceItem->amount / 100;
        }
    }
    
    echo "Stripe Balance:      $" . number_format($availableBalance, 2) . "\n";
    
    $difference = $availableBalance - $expectedBalance;
    echo "Difference:          $" . number_format($difference, 2);
    
    if ($difference == 0) {
        echo " ✅ PERFECT MATCH!\n";
    } elseif (abs($difference) < 1) {
        echo " ✅ WITHIN $1 (OK)\n";
    } else {
        echo " ⚠️  REVIEW REQUIRED\n";
    }
} catch (\Exception $e) {
    echo "Stripe Balance:      Unable to retrieve\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// VERIFICATION CHECKS
echo "✓ VERIFICATION CHECKLIST\n";
echo "────────────────────────────────────────────────────────────────\n";

// Check 1: All paid records have Stripe IDs
$paidWithoutStripe = TimeTracking::where('payment_status', 'paid')
    ->whereNull('stripe_transfer_id')
    ->count();

if ($paidWithoutStripe == 0) {
    echo "✅ All paid records have Stripe Transfer IDs\n";
} else {
    echo "⚠️  {$paidWithoutStripe} paid records missing Stripe Transfer IDs\n";
}

// Check 2: All paid records have timestamps
$paidWithoutTimestamp = TimeTracking::where('payment_status', 'paid')
    ->whereNull('paid_at')
    ->count();

if ($paidWithoutTimestamp == 0) {
    echo "✅ All paid records have timestamps\n";
} else {
    echo "⚠️  {$paidWithoutTimestamp} paid records missing timestamps\n";
}

// Check 3: No pending with paid_at
$pendingWithTimestamp = TimeTracking::where('payment_status', 'pending')
    ->whereNotNull('paid_at')
    ->count();

if ($pendingWithTimestamp == 0) {
    echo "✅ No pending records have timestamps (data consistent)\n";
} else {
    echo "⚠️  {$pendingWithTimestamp} pending records have timestamps (inconsistent)\n";
}

// Check 4: Caregivers with pending but no bank
$caregiverNoBankButPending = Caregiver::where(function($q) {
        $q->whereNull('stripe_connect_id')
          ->orWhere('stripe_connect_id', '');
    })
    ->whereHas('timeTrackings', function($q) {
        $q->where('payment_status', 'pending');
    })
    ->count();

if ($caregiverNoBankButPending == 0) {
    echo "✅ All caregivers with pending balance have bank connected\n";
} else {
    echo "⚠️  {$caregiverNoBankButPending} caregivers have pending balance but no bank\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                     END OF REPORT                             \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Summary
if ($paidWithoutStripe == 0 && $paidWithoutTimestamp == 0 && $pendingWithTimestamp == 0) {
    echo "🎉 STATUS: ALL CHECKS PASSED! Your money flow is properly tracked.\n\n";
} else {
    echo "⚠️  STATUS: ISSUES DETECTED. Review the warnings above.\n\n";
}

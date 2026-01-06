<?php
/**
 * DATABASE DIAGNOSTIC - Check payments and bookings
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "             🔍 DATABASE DIAGNOSTIC REPORT 🔍              \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Check all payments
echo "📦 ALL PAYMENTS:\n";
echo "────────────────────────────────────────────────────────────────\n";
$payments = DB::table('payments')->orderBy('created_at', 'desc')->get();
if ($payments->count() == 0) {
    echo "No payments found!\n";
} else {
    foreach ($payments as $p) {
        echo "ID: {$p->id}\n";
        echo "  Booking ID: {$p->booking_id}\n";
        echo "  Amount: \${$p->amount}\n";
        echo "  Status: {$p->status}\n";
        echo "  Stripe Intent: " . ($p->stripe_payment_intent_id ?? 'N/A') . "\n";
        echo "  Created: {$p->created_at}\n\n";
    }
}

// Check all bookings
echo "\n📋 ALL BOOKINGS:\n";
echo "────────────────────────────────────────────────────────────────\n";
$bookings = DB::table('bookings')->orderBy('created_at', 'desc')->get();
if ($bookings->count() == 0) {
    echo "No bookings found!\n";
} else {
    foreach ($bookings as $b) {
        echo "ID: {$b->id}\n";
        echo "  Client ID: {$b->client_id}\n";
        echo "  Status: {$b->status}\n";
        echo "  Payment Status: " . ($b->payment_status ?? 'N/A') . "\n";
        echo "  Total Price: \$" . ($b->total_price ?? 'N/A') . "\n";
        echo "  Created: {$b->created_at}\n\n";
    }
}

// Check for mismatches
echo "\n🔍 ANALYSIS:\n";
echo "────────────────────────────────────────────────────────────────\n";

// Check bookings with completed payments but wrong status
$bookingsWithPayments = DB::table('bookings')
    ->whereIn('id', function($q) {
        $q->select('booking_id')
          ->from('payments')
          ->where('status', 'completed');
    })
    ->where(function($q) {
        $q->where('payment_status', '!=', 'paid')
          ->orWhereNull('payment_status');
    })
    ->get();

if ($bookingsWithPayments->count() > 0) {
    echo "⚠️  PROBLEM FOUND: Bookings with completed payments but NOT marked as paid:\n";
    foreach ($bookingsWithPayments as $b) {
        $payment = DB::table('payments')
            ->where('booking_id', $b->id)
            ->where('status', 'completed')
            ->first();
        
        echo "  - Booking #{$b->id}: payment_status='{$b->payment_status}' but has completed payment of \${$payment->amount}\n";
    }
    echo "\n";
} else {
    echo "✅ All bookings with completed payments are properly marked as paid.\n\n";
}

// Check for successful Stripe payments
echo "💳 STRIPE PAYMENTS:\n";
echo "────────────────────────────────────────────────────────────────\n";
$stripePayments = DB::table('payments')
    ->whereNotNull('stripe_payment_intent_id')
    ->orderBy('created_at', 'desc')
    ->get();

if ($stripePayments->count() == 0) {
    echo "⚠️  No Stripe payments found in database!\n";
    echo "   This means the payment webhook might not be working.\n";
} else {
    foreach ($stripePayments as $sp) {
        echo "  - Intent: {$sp->stripe_payment_intent_id}\n";
        echo "    Amount: \${$sp->amount} | Status: {$sp->status} | Booking: #{$sp->booking_id}\n\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "                     END OF REPORT                             \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

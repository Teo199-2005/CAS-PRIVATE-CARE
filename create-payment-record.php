<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        💳 CREATE PAYMENT RECORD FOR BOOKING #2 💳\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Check if payment already exists
$existing = DB::table('payments')->where('booking_id', 2)->first();
if ($existing) {
    echo "✅ Payment record already exists!\n";
    print_r($existing);
    exit;
}

// Get booking details
$booking = DB::table('bookings')->find(2);
if (!$booking) {
    echo "❌ Booking #2 not found!\n";
    exit;
}

// Calculate amounts
// 30 days x 24 hours x $45/hr = $32,400
$totalAmount = 30 * 24 * 45;  // $32,400
$platformFee = $totalAmount * 0.10;  // $3,240
$caregiverAmount = $totalAmount * 0.90;  // $29,160

// Try to get caregiver from time_trackings
$caregiverId = null;
try {
    $tracking = DB::table('time_trackings')
        ->where('booking_id', 2)
        ->first();
    $caregiverId = $tracking ? $tracking->caregiver_id : null;
} catch (\Exception $e) {
    $caregiverId = null;
}

echo "📊 PAYMENT DETAILS:\n";
echo "  Booking ID: 2\n";
echo "  Client ID: {$booking->client_id}\n";
echo "  Caregiver ID: " . ($caregiverId ?? 'Not assigned') . "\n";
echo "  Total Amount: \$" . number_format($totalAmount, 2) . "\n";
echo "  Platform Fee (10%): \$" . number_format($platformFee, 2) . "\n";
echo "  Caregiver Amount (90%): \$" . number_format($caregiverAmount, 2) . "\n\n";

// Create payment record
try {
    $paymentId = DB::table('payments')->insertGetId([
        'booking_id' => 2,
        'client_id' => $booking->client_id,
        'caregiver_id' => $caregiverId,
        'amount' => $totalAmount,
        'platform_fee' => $platformFee,
        'caregiver_amount' => $caregiverAmount,
        'payment_method' => 'credit_card',  // Valid enum value
        'status' => 'completed',
        'transaction_id' => 'stripe_' . time(),  // Placeholder
        'paid_at' => now(),
        'notes' => 'Payment recorded - Stripe payment completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Payment record created successfully!\n";
    echo "   Payment ID: $paymentId\n\n";
    
    // Verify
    $payment = DB::table('payments')->find($paymentId);
    echo "📋 CREATED PAYMENT:\n";
    echo "  ID: {$payment->id}\n";
    echo "  Booking ID: {$payment->booking_id}\n";
    echo "  Client ID: {$payment->client_id}\n";
    echo "  Amount: \$" . number_format($payment->amount, 2) . "\n";
    echo "  Platform Fee: \$" . number_format($payment->platform_fee, 2) . "\n";
    echo "  Caregiver Amount: \$" . number_format($payment->caregiver_amount, 2) . "\n";
    echo "  Status: {$payment->status}\n";
    echo "  Paid At: {$payment->paid_at}\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating payment: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";

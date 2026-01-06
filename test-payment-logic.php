<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        🧪 TESTING PAYMENT UPDATE LOGIC 🧪\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get the latest booking
$booking = DB::table('bookings')->orderBy('id', 'desc')->first();

if (!$booking) {
    echo "❌ No bookings found!\n";
    exit;
}

echo "📋 LATEST BOOKING:\n";
echo "  ID: {$booking->id}\n";
echo "  Client ID: {$booking->client_id}\n";
echo "  Duty Type: {$booking->duty_type}\n";
echo "  Duration: {$booking->duration_days} days\n";
echo "  Hourly Rate: \${$booking->hourly_rate}\n";
echo "  Payment Status: {$booking->payment_status}\n\n";

// Calculate hours from duty_type
$hours = 8; // Default
if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
    $hours = (int)$matches[1];
}
echo "  Parsed Hours/Day: {$hours}\n";

// Calculate amounts
$rate = $booking->hourly_rate ?: 45;
$amount = $hours * $booking->duration_days * $rate;
$platformFee = $amount * 0.10;
$caregiverAmount = $amount * 0.90;

echo "  Calculated Amount: \$" . number_format($amount, 2) . "\n";
echo "  Platform Fee (10%): \$" . number_format($platformFee, 2) . "\n";
echo "  Caregiver Amount (90%): \$" . number_format($caregiverAmount, 2) . "\n\n";

if ($booking->payment_status === 'paid') {
    echo "✅ Booking is already marked as paid!\n";
    
    // Check for payment record
    $payment = DB::table('payments')->where('booking_id', $booking->id)->first();
    if ($payment) {
        echo "✅ Payment record exists: \$" . number_format($payment->amount, 2) . "\n";
    } else {
        echo "❌ No payment record found - creating one...\n";
        
        DB::table('payments')->insert([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'caregiver_amount' => $caregiverAmount,
            'payment_method' => 'credit_card',
            'status' => 'completed',
            'transaction_id' => 'manual_fix_' . time(),
            'paid_at' => now(),
            'notes' => 'Payment record created by fix script',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✅ Payment record created!\n";
    }
} else {
    echo "⏳ Booking is NOT paid - updating...\n";
    
    // Update booking
    DB::table('bookings')->where('id', $booking->id)->update([
        'payment_status' => 'paid',
        'payment_date' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Booking updated to 'paid'\n";
    
    // Create payment record
    DB::table('payments')->insert([
        'booking_id' => $booking->id,
        'client_id' => $booking->client_id,
        'amount' => $amount,
        'platform_fee' => $platformFee,
        'caregiver_amount' => $caregiverAmount,
        'payment_method' => 'credit_card',
        'status' => 'completed',
        'transaction_id' => 'manual_fix_' . time(),
        'paid_at' => now(),
        'notes' => 'Payment record created by fix script',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Payment record created!\n";
}

echo "\n📊 FINAL STATE:\n";
$updatedBooking = DB::table('bookings')->find($booking->id);
$payment = DB::table('payments')->where('booking_id', $booking->id)->first();

echo "  Booking #{$updatedBooking->id} Payment Status: {$updatedBooking->payment_status}\n";
if ($payment) {
    echo "  Payment Record: \$" . number_format($payment->amount, 2) . " | {$payment->status}\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";

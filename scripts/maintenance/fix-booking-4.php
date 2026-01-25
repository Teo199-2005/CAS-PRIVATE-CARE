<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        💳 FIX BOOKING #4 PAYMENT STATUS 💳\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get booking details
$booking = DB::table('bookings')->find(4);
if (!$booking) {
    echo "❌ Booking #4 not found!\n";
    exit;
}

echo "📋 CURRENT STATUS:\n";
echo "  Booking ID: 4\n";
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

// Calculate amounts
$totalAmount = $hours * $booking->duration_days * $booking->hourly_rate;
$platformFee = $totalAmount * 0.10;  // 10%
$caregiverAmount = $totalAmount * 0.90;  // 90%

echo "💰 CALCULATED AMOUNTS:\n";
echo "  Hours per day: $hours\n";
echo "  Calculation: $hours hrs × {$booking->duration_days} days × \${$booking->hourly_rate}/hr\n";
echo "  Total Amount: \$" . number_format($totalAmount, 2) . "\n";
echo "  Platform Fee (10%): \$" . number_format($platformFee, 2) . "\n";
echo "  Caregiver Amount (90%): \$" . number_format($caregiverAmount, 2) . "\n\n";

// Check if payment already exists
$existingPayment = DB::table('payments')->where('booking_id', 4)->first();
if ($existingPayment) {
    echo "⚠️ Payment record already exists for Booking #4!\n";
    print_r($existingPayment);
    exit;
}

// Update booking
echo "🔄 Updating booking payment status...\n";
DB::table('bookings')->where('id', 4)->update([
    'payment_status' => 'paid',
    'payment_date' => now(),
    'updated_at' => now(),
]);
echo "✅ Booking updated to 'paid'\n\n";

// Create payment record
echo "🔄 Creating payment record...\n";
try {
    $paymentId = DB::table('payments')->insertGetId([
        'booking_id' => 4,
        'client_id' => $booking->client_id,
        'caregiver_id' => null,
        'amount' => $totalAmount,
        'platform_fee' => $platformFee,
        'caregiver_amount' => $caregiverAmount,
        'payment_method' => 'credit_card',
        'status' => 'completed',
        'transaction_id' => 'stripe_payment_' . time(),
        'paid_at' => now(),
        'notes' => 'Payment recorded - Stripe payment completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Payment record created!\n";
    echo "   Payment ID: $paymentId\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating payment: " . $e->getMessage() . "\n";
    exit;
}

// Verify
echo "📊 VERIFICATION:\n";
$updatedBooking = DB::table('bookings')->find(4);
$payment = DB::table('payments')->where('booking_id', 4)->first();

echo "  Booking #4 Payment Status: {$updatedBooking->payment_status}\n";
echo "  Payment Record Amount: \$" . number_format($payment->amount, 2) . "\n";
echo "  Payment Status: {$payment->status}\n";

echo "\n✅ FIX COMPLETE!\n";
echo "═══════════════════════════════════════════════════════════════\n";

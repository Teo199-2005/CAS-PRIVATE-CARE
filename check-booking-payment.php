<?php
// Quick script to manually mark booking as paid for testing
// Run: php check-booking-payment.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

$bookingId = $argv[1] ?? 12;

$booking = Booking::find($bookingId);

if (!$booking) {
    echo "❌ Booking #{$bookingId} not found\n";
    exit(1);
}

echo "📦 Booking #{$bookingId} Details:\n";
echo "   Client: {$booking->client->name}\n";
echo "   Service: {$booking->service_type}\n";
echo "   Amount: \${$booking->total_amount}\n";
echo "   Status: {$booking->status}\n";
echo "   Payment Status: " . ($booking->payment_status ?? 'null') . "\n";
echo "   Payment Intent: " . ($booking->payment_intent_id ?? 'null') . "\n";
echo "   Payment Date: " . ($booking->payment_date ?? 'null') . "\n";
echo "\n";

// Ask if user wants to mark as paid
if (!isset($argv[2]) || $argv[2] !== 'mark-paid') {
    echo "To mark this booking as paid, run:\n";
    echo "php check-booking-payment.php {$bookingId} mark-paid\n";
    exit(0);
}

// Mark as paid
$booking->update([
    'payment_status' => 'paid',
    'status' => 'approved', // Keep existing status or use 'approved'
    'payment_intent_id' => $booking->payment_intent_id ?? 'pi_test_' . time(),
    'payment_date' => now()
]);

echo "✅ Booking #{$bookingId} marked as PAID!\n";
echo "   Payment Status: paid\n";
echo "   Status: approved\n";
echo "   Payment Intent: {$booking->payment_intent_id}\n";
echo "   Payment Date: {$booking->payment_date}\n";
echo "\n";
echo "🎯 Now refresh the dashboard to see the 'View Receipt' button!\n";
echo "📄 Receipt URL: http://127.0.0.1:8000/receipts/payment/{$bookingId}\n";

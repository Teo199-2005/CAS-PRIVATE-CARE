<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$bookingId = $argv[1] ?? 12;

$booking = App\Models\Booking::find($bookingId);

if (!$booking) {
    echo "❌ Booking #{$bookingId} not found!\n";
    exit(1);
}

echo "📦 Current Status:\n";
echo "   Payment Status: {$booking->payment_status}\n";
echo "   Payment Intent: {$booking->stripe_payment_intent_id}\n";
echo "   Payment Date: {$booking->payment_date}\n\n";

$booking->update([
    'payment_status' => 'paid',
    'stripe_payment_intent_id' => 'pi_test_' . time(),
    'payment_date' => now()
]);

$booking->refresh();

echo "✅ Booking #{$bookingId} marked as PAID!\n";
echo "   Payment Status: {$booking->payment_status}\n";
echo "   Payment Intent: {$booking->stripe_payment_intent_id}\n";
echo "   Payment Date: {$booking->payment_date}\n";
echo "\n📄 Receipt URL: http://127.0.0.1:8000/receipts/payment/{$bookingId}\n";

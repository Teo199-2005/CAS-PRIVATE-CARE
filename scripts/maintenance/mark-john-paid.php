<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$booking = \App\Models\Booking::whereHas('client', function($q) {
    $q->where('name', 'like', '%John%');
})->first();

if ($booking) {
    // Update to completed and add fake stripe charge ID
    $booking->update([
        'status' => 'completed',
        'stripe_charge_id' => 'ch_test_' . uniqid(),
        'payment_status' => 'paid'
    ]);
    
    echo "✅ Updated John Doe's booking:" . PHP_EOL;
    echo "Status: " . $booking->status . PHP_EOL;
    echo "Payment Status: " . $booking->payment_status . PHP_EOL;
    echo "Stripe Charge ID: " . $booking->stripe_charge_id . PHP_EOL;
} else {
    echo "No booking found" . PHP_EOL;
}

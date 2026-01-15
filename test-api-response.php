<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Testing Client Stats API ===\n\n";

// Simulate authenticated request
$clientId = 4; // John Doe (client@demo.com)

// Get bookings directly (what the API does)
$bookings = App\Models\Booking::where('client_id', $clientId)
    ->with([
        'assignedCaregiver.user:id,name,email,phone,avatar',
        'assignments.caregiver.user:id,name,email,phone,avatar',
        'assignments.caregiver:id,user_id'
    ])
    ->latest()
    ->get();

echo "Total Bookings: " . $bookings->count() . "\n\n";

if ($bookings->count() > 0) {
    echo "First Booking (ID {$bookings[0]->id}):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $booking = $bookings[0];
    
    // Check all fields
    echo "id: " . ($booking->id ?? 'NULL') . "\n";
    echo "status: " . ($booking->status ?? 'NULL') . "\n";
    echo "payment_status: " . ($booking->payment_status ?? 'NULL') . "\n";
    echo "stripe_payment_intent_id: " . ($booking->stripe_payment_intent_id ?? 'NULL') . "\n";
    echo "payment_date: " . ($booking->payment_date ?? 'NULL') . "\n";
    echo "service_type: " . ($booking->service_type ?? 'NULL') . "\n";
    echo "service_date: " . ($booking->service_date ? $booking->service_date->toDateString() : 'NULL') . "\n";
    echo "hourly_rate: " . ($booking->hourly_rate ?? 'NULL') . "\n";
    echo "duration_days: " . ($booking->duration_days ?? 'NULL') . "\n";
    echo "duty_type: " . ($booking->duty_type ?? 'NULL') . "\n";
    
    // Convert to JSON (like the API does)
    echo "\n=== JSON Representation ===\n";
    $json = $booking->toArray();
    echo "payment_status in array: " . (isset($json['payment_status']) ? $json['payment_status'] : 'MISSING') . "\n";
    echo "stripe_payment_intent_id in array: " . (isset($json['stripe_payment_intent_id']) ? $json['stripe_payment_intent_id'] : 'MISSING') . "\n";
}

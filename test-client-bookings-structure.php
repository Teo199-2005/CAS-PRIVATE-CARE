<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Client Bookings Data Structure ===\n\n";

$clientId = 1; // John Doe

$bookings = App\Models\Booking::where('client_id', $clientId)
    ->with(['client', 'assignments.caregiver.user'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total Bookings Found: " . $bookings->count() . "\n\n";

foreach ($bookings as $booking) {
    echo "Booking ID: {$booking->id}\n";
    echo "Status: {$booking->status}\n";
    echo "Payment Status: " . ($booking->payment_status ?? 'NULL') . "\n";
    echo "Service Type: {$booking->service_type}\n";
    echo "Service Date: " . ($booking->service_date ? $booking->service_date->toDateString() : 'NULL') . "\n";
    echo "Hourly Rate: \${$booking->hourly_rate}\n";
    echo "Duration: {$booking->duration_days} days\n";
    echo "Total Budget: \${$booking->total_budget}\n";
    echo "Payment Intent: " . ($booking->stripe_payment_intent_id ?? 'NULL') . "\n";
    echo "Payment Date: " . ($booking->payment_date ?? 'NULL') . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
}

// Check what the client API returns
echo "=== Client API Response ===\n\n";
$response = (new \App\Http\Controllers\DashboardController())->getClientStats();
$data = json_decode($response->getContent(), true);

echo "My Bookings Count: " . count($data['my_bookings'] ?? []) . "\n";
if (isset($data['my_bookings']) && count($data['my_bookings']) > 0) {
    echo "\nFirst Booking:\n";
    $first = $data['my_bookings'][0];
    echo "ID: " . ($first['id'] ?? 'N/A') . "\n";
    echo "Status: " . ($first['status'] ?? 'N/A') . "\n";
    echo "Payment Status: " . ($first['payment_status'] ?? 'MISSING') . "\n";
    echo "Payment Intent: " . ($first['stripe_payment_intent_id'] ?? 'MISSING') . "\n";
}

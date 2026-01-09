<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

// Check booking #11
$booking = Booking::find(11);

if ($booking) {
    echo "Booking #11 Details:\n";
    echo "==================\n";
    echo "ID: " . $booking->id . "\n";
    echo "Client ID: " . $booking->client_id . "\n";
    echo "Status: " . $booking->status . "\n";
    echo "Payment Status: " . ($booking->payment_status ?? 'NULL') . "\n";
    echo "Recurring Service: " . ($booking->recurring_service ? 'true' : 'false') . "\n";
    echo "Auto Pay Enabled: " . ($booking->auto_pay_enabled ? 'true' : 'false') . "\n";
    echo "Recurring Status: " . ($booking->recurring_status ?? 'NULL') . "\n";
    echo "\n";
    
    echo "Should appear in recurring list?: \n";
    $statuses = ['completed', 'approved', 'confirmed', 'in_progress'];
    echo "- Status check: " . (in_array($booking->status, $statuses) ? "✓ YES" : "✗ NO (status: {$booking->status})") . "\n";
    echo "- Payment status check: " . ($booking->payment_status === 'paid' ? "✓ YES" : "✗ NO (payment_status: {$booking->payment_status})") . "\n";
    echo "- Payment status not null: " . ($booking->payment_status !== null ? "✓ YES" : "✗ NO") . "\n";
    
    $shouldAppear = in_array($booking->status, $statuses) && 
                    $booking->payment_status !== null && 
                    $booking->payment_status === 'paid';
    
    echo "\nFinal result: " . ($shouldAppear ? "✓ SHOULD APPEAR" : "✗ WILL NOT APPEAR") . "\n";
} else {
    echo "Booking #11 not found!\n";
}

// Also check what the controller would return
echo "\n\n";
echo "Testing RecurringBookingController query:\n";
echo "=========================================\n";

$bookings = Booking::where('client_id', $booking->client_id ?? 0)
    ->whereIn('status', ['completed', 'approved', 'confirmed', 'in_progress'])
    ->whereNotNull('payment_status')
    ->where('payment_status', 'paid')
    ->orderBy('created_at', 'desc')
    ->get();

echo "Found " . $bookings->count() . " bookings\n";
foreach ($bookings as $b) {
    echo "- Booking #{$b->id}: status={$b->status}, payment_status={$b->payment_status}\n";
}

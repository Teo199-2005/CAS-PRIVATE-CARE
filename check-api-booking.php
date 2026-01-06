<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking API Booking Data Format ===\n\n";

$booking = Booking::with(['client', 'assignments.caregiver.user'])->find(10);

if ($booking) {
    echo "Raw start_time from database: " . var_export($booking->start_time, true) . "\n";
    echo "Type: " . gettype($booking->start_time) . "\n\n";
    
    // Simulate what the API returns
    $apiData = $booking->toArray();
    echo "start_time in API response: " . var_export($apiData['start_time'], true) . "\n";
    echo "starting_time in API response: " . var_export($apiData['starting_time'] ?? null, true) . "\n\n";
    
    // Check if it's a datetime object
    if ($booking->start_time instanceof \DateTime || $booking->start_time instanceof \Carbon\Carbon) {
        echo "It's a Carbon/DateTime object\n";
        echo "Format as string: " . $booking->start_time->toDateTimeString() . "\n";
        echo "Format time only: " . $booking->start_time->format('H:i:s') . "\n";
    }
} else {
    echo "Booking not found!\n";
}

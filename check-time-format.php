<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking Time Format ===\n\n";

$booking = Booking::find(10);

if ($booking) {
    echo "Raw start_time value: '" . $booking->start_time . "'\n";
    echo "Type: " . gettype($booking->start_time) . "\n";
    echo "Length: " . strlen($booking->start_time) . "\n";
    
    // Check if it's a datetime object
    if (is_object($booking->start_time)) {
        echo "It's a DateTime object\n";
        echo "Formatted: " . $booking->start_time->format('H:i:s') . "\n";
        echo "As string: " . (string)$booking->start_time . "\n";
    }
    
    // Check the JSON serialization
    echo "\nJSON serialization:\n";
    echo json_encode(['start_time' => $booking->start_time], JSON_PRETTY_PRINT) . "\n";
    
    // Check what the API would return
    echo "\nWhat API returns:\n";
    $data = $booking->toArray();
    echo "start_time in array: '" . ($data['start_time'] ?? 'NULL') . "'\n";
} else {
    echo "Booking not found!\n";
}

<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking Booking Time Fields ===\n\n";

$booking = Booking::find(10);

if ($booking) {
    echo "Booking ID: " . $booking->id . "\n";
    echo "start_time: " . ($booking->start_time ?? 'NULL') . "\n";
    echo "starting_time: " . ($booking->starting_time ?? 'NULL') . "\n";
    echo "service_date: " . $booking->service_date . "\n";
    echo "duty_type: " . $booking->duty_type . "\n";
    
    // Show what the formatted output should be
    $startTime = $booking->start_time ?? $booking->starting_time;
    if ($startTime) {
        list($hours, $minutes) = explode(':', $startTime);
        $hour = (int)$hours;
        $ampm = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour % 12 ?: 12;
        $formatted = "$displayHour:$minutes $ampm";
        echo "\nFormatted start time should be: $formatted\n";
        
        // Calculate end time
        preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches);
        $hoursPerDay = $matches ? (int)$matches[1] : 8;
        $endHour = ($hour + $hoursPerDay) % 24;
        $endAmpm = $endHour >= 12 ? 'PM' : 'AM';
        $endDisplayHour = $endHour === 0 ? 12 : ($endHour > 12 ? $endHour - 12 : $endHour);
        $endFormatted = "$endDisplayHour:$minutes $endAmpm";
        echo "Formatted end time should be: $endFormatted\n";
        echo "Full range: $formatted - $endFormatted\n";
    }
} else {
    echo "Booking not found!\n";
}

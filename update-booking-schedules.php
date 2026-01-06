<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Updating Booking #12 with Day Schedules ===\n\n";

$booking = Booking::find(12);

if ($booking) {
    // Set the day schedules based on what the client selected
    $daySchedules = [
        'monday' => '11:00 AM - 11:00 PM',
        'tuesday' => '12:00 AM - 12:00 PM',
        'wednesday' => '12:00 AM - 12:00 PM'
    ];
    
    $booking->day_schedules = $daySchedules;
    $booking->save();
    
    echo "✅ Updated booking #12 with day schedules:\n";
    echo json_encode($daySchedules, JSON_PRETTY_PRINT) . "\n\n";
    
    // Verify
    $booking->refresh();
    echo "Verification - day_schedules from database:\n";
    echo json_encode($booking->day_schedules, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ Booking #12 not found!\n";
}

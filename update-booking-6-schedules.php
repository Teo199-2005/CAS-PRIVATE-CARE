<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        🗓️ UPDATE BOOKING #6 DAY SCHEDULES 🗓️\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get booking #6
$booking = DB::table('bookings')->find(6);

if (!$booking) {
    echo "❌ Booking #6 not found!\n";
    exit;
}

echo "📋 CURRENT BOOKING #6:\n";
echo "  Client ID: {$booking->client_id}\n";
echo "  Duty Type: {$booking->duty_type}\n";
echo "  Duration: {$booking->duration_days} days\n";
echo "  Current day_schedules: " . ($booking->day_schedules ?: 'NULL') . "\n\n";

// Set the day schedules based on what you selected:
// Tuesday: 11:00 AM - 11:00 PM
// Wednesday: 09:02 AM - 09:02 PM
// Thursday: 11:00 AM - 11:00 PM
// Friday: 01:00 AM - 01:00 PM

$daySchedules = [
    'tuesday' => '11:00 AM - 11:00 PM',
    'wednesday' => '9:02 AM - 9:02 PM',
    'thursday' => '11:00 AM - 11:00 PM',
    'friday' => '1:00 AM - 1:00 PM'
];

echo "📅 SETTING DAY SCHEDULES:\n";
foreach ($daySchedules as $day => $time) {
    echo "  " . ucfirst($day) . ": {$time}\n";
}
echo "\n";

// Update the booking
DB::table('bookings')->where('id', 6)->update([
    'day_schedules' => json_encode($daySchedules),
    'updated_at' => now()
]);

echo "✅ Booking #6 updated successfully!\n\n";

// Verify
$updated = DB::table('bookings')->find(6);
echo "📊 VERIFICATION:\n";
echo "  day_schedules: {$updated->day_schedules}\n";

$parsed = json_decode($updated->day_schedules, true);
if ($parsed) {
    echo "\n  Parsed Days:\n";
    foreach ($parsed as $day => $time) {
        echo "    " . ucfirst($day) . ": {$time}\n";
    }
}

echo "\n✅ COMPLETE! Refresh the Admin Dashboard to see only the 4 selected days.\n";
echo "═══════════════════════════════════════════════════════════════\n";

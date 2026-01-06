<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        📅 CHECKING DAY_SCHEDULES DATA 📅\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get the latest booking
$booking = DB::table('bookings')->orderBy('id', 'desc')->first();

if (!$booking) {
    echo "❌ No bookings found!\n";
    exit;
}

echo "📋 BOOKING #{$booking->id}:\n";
echo "  Client ID: {$booking->client_id}\n";
echo "  Service Type: {$booking->service_type}\n";
echo "  Duty Type: {$booking->duty_type}\n";
echo "  Duration: {$booking->duration_days} days\n";
echo "  Service Date: {$booking->service_date}\n";
echo "  Start Time: {$booking->start_time}\n";
echo "  Status: {$booking->status}\n";
echo "  Payment Status: {$booking->payment_status}\n\n";

echo "📅 DAY_SCHEDULES FIELD:\n";
echo "  Raw Value: " . ($booking->day_schedules ?: 'NULL') . "\n";
echo "  Type: " . gettype($booking->day_schedules) . "\n\n";

if ($booking->day_schedules) {
    echo "📊 PARSED DAY_SCHEDULES:\n";
    $daySchedules = json_decode($booking->day_schedules, true);
    
    if ($daySchedules) {
        echo "  Type after decode: " . gettype($daySchedules) . "\n";
        echo "  Keys: " . implode(', ', array_keys($daySchedules)) . "\n\n";
        
        foreach ($daySchedules as $day => $timeRange) {
            echo "  {$day}: {$timeRange}\n";
        }
    } else {
        echo "  ❌ Failed to parse JSON\n";
    }
} else {
    echo "  ℹ️ No day_schedules set - will show all days\n";
}

echo "\n📝 RECURRING_SCHEDULE FIELD:\n";
echo "  Value: " . ($booking->recurring_schedule ?: 'NULL') . "\n\n";

echo "═══════════════════════════════════════════════════════════════\n";

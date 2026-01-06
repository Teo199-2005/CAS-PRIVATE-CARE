<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\Booking;
use App\Models\BookingAssignment;
use Carbon\Carbon;

echo "=== CREATE TEST TIME TRACKING FOR AUTO CLOCK-OUT ===\n\n";

// Get booking #12 with Monday schedule
$booking = Booking::find(12);
if (!$booking) {
    echo "Error: Booking #12 not found\n";
    exit(1);
}

echo "Booking #12 Details:\n";
echo "  Client ID: {$booking->client_id}\n";
echo "  Hourly Rate: \${$booking->hourly_rate}\n";
echo "  Day Schedules: " . json_encode($booking->day_schedules) . "\n\n";

// Get the assigned caregiver
$assignment = $booking->assignments()->where('status', 'assigned')->where('is_active', true)->first();
if (!$assignment) {
    echo "Error: No assigned caregiver found for booking #12\n";
    exit(1);
}

echo "Assigned Caregiver ID: {$assignment->caregiver_id}\n\n";

// Ask user which day to create tracking for
echo "Available days with schedules:\n";
foreach ($booking->day_schedules as $day => $schedule) {
    echo "  - {$day}: {$schedule}\n";
}
echo "\n";

// Default to Monday for demonstration
$testDay = 'monday'; // You can change this to tuesday or wednesday
$schedule = $booking->day_schedules[$testDay];

// Parse start time from schedule
if (preg_match('/(\d+:\d+\s*[AP]M)\s*-\s*(\d+:\d+\s*[AP]M)/', $schedule, $matches)) {
    $startTimeStr = $matches[1];
    $endTimeStr = $matches[2];
    
    echo "Creating test time tracking for {$testDay}:\n";
    echo "  Scheduled Start: {$startTimeStr}\n";
    echo "  Scheduled End: {$endTimeStr}\n\n";
    
    // Find the next occurrence of this day
    $now = Carbon::now();
    $targetDate = Carbon::parse("next {$testDay}");
    if ($targetDate->isPast()) {
        $targetDate->addWeek();
    }
    
    echo "Target Date: {$targetDate->toDateString()} ({$testDay})\n\n";
    
    // Create clock in time at scheduled start
    $startTime = Carbon::createFromFormat('g:i A', $startTimeStr);
    $clockInTime = Carbon::parse($targetDate->toDateString() . ' ' . $startTime->format('H:i:s'));
    
    echo "Creating time tracking entry:\n";
    echo "  Caregiver ID: {$assignment->caregiver_id}\n";
    echo "  Booking ID: {$booking->id}\n";
    echo "  Client ID: {$booking->client_id}\n";
    echo "  Work Date: {$targetDate->toDateString()}\n";
    echo "  Clock In Time: {$clockInTime->format('Y-m-d H:i:s')}\n";
    echo "  Clock Out Time: NOT SET (waiting for auto clock-out)\n\n";
    
    // Check if already exists
    $existing = TimeTracking::where('caregiver_id', $assignment->caregiver_id)
        ->where('booking_id', $booking->id)
        ->whereDate('work_date', $targetDate->toDateString())
        ->first();
        
    if ($existing) {
        echo "⚠️ Time tracking entry already exists for this day!\n";
        echo "  ID: {$existing->id}\n";
        echo "  Clock In: " . ($existing->clock_in_time ? $existing->clock_in_time->format('Y-m-d H:i:s') : 'N/A') . "\n";
        echo "  Clock Out: " . ($existing->clock_out_time ? $existing->clock_out_time->format('Y-m-d H:i:s') : 'NOT SET') . "\n\n";
        
        echo "Do you want to delete and recreate? Type 'yes' to confirm: ";
        $confirm = trim(fgets(STDIN));
        
        if (strtolower($confirm) !== 'yes') {
            echo "Aborted.\n";
            exit(0);
        }
        
        $existing->delete();
        echo "Deleted existing entry.\n\n";
    }
    
    // Create new time tracking entry
    $timeTracking = TimeTracking::create([
        'caregiver_id' => $assignment->caregiver_id,
        'client_id' => $booking->client_id,
        'booking_id' => $booking->id,
        'work_date' => $targetDate->toDateString(),
        'clock_in_time' => $clockInTime,
        'clock_out_time' => null, // Will be set by auto clock-out
        'hours_worked' => null,
        'status' => 'active',
        'caregiver_earnings' => null,
        'total_client_charge' => null,
    ]);
    
    echo "✅ Time tracking entry created successfully!\n";
    echo "  ID: {$timeTracking->id}\n\n";
    
    // Calculate when auto clock-out should happen
    $endTime = Carbon::createFromFormat('g:i A', $endTimeStr);
    $clockOutTime = Carbon::parse($targetDate->toDateString() . ' ' . $endTime->format('H:i:s'));
    
    echo "Auto Clock-Out Information:\n";
    echo "  Will trigger at: {$clockOutTime->format('Y-m-d H:i:s')} ({$endTimeStr})\n";
    echo "  Expected hours worked: " . $clockInTime->diffInHours($clockOutTime) . " hours\n";
    echo "  Expected earnings: \$" . ($clockInTime->diffInHours($clockOutTime) * $booking->hourly_rate) . "\n\n";
    
    echo "To manually trigger auto clock-out (if time has passed):\n";
    echo "  php artisan app:auto-clock-out\n\n";
    
    echo "To check if auto clock-out worked:\n";
    echo "  php test-auto-clockout.php\n\n";
    
} else {
    echo "Error: Could not parse schedule format\n";
    exit(1);
}

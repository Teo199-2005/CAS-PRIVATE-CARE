<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\Booking;
use App\Models\BookingAssignment;
use Carbon\Carbon;

echo "=== AUTO CLOCK-OUT TEST SCRIPT ===\n\n";

// 1. Check current time and day
$now = Carbon::now();
$today = strtolower($now->format('l'));
echo "Current Time: {$now->toDateTimeString()}\n";
echo "Current Day: {$today}\n";
echo "Current Time (24h): {$now->format('H:i')}\n\n";

// 2. Find all bookings with day_schedules
echo "=== BOOKINGS WITH DAY SCHEDULES ===\n";
$bookings = Booking::whereNotNull('day_schedules')
    ->where('status', 'approved')
    ->with(['assignments' => function ($query) {
        $query->where('status', 'assigned')->where('is_active', true);
    }])
    ->get();

echo "Found {$bookings->count()} approved bookings with day schedules\n\n";

foreach ($bookings as $booking) {
    echo "Booking #{$booking->id}:\n";
    echo "  Status: {$booking->status}\n";
    echo "  Hourly Rate: \${$booking->hourly_rate}\n";
    echo "  Day Schedules: " . json_encode($booking->day_schedules) . "\n";
    
    // Check if has schedule for today
    if (isset($booking->day_schedules[$today])) {
        echo "  ✓ HAS SCHEDULE FOR TODAY ({$today}): {$booking->day_schedules[$today]}\n";
        
        // Parse end time
        $daySchedule = $booking->day_schedules[$today];
        if (preg_match('/(\d+:\d+\s*[AP]M)\s*-\s*(\d+:\d+\s*[AP]M)/', $daySchedule, $matches)) {
            $startTimeStr = $matches[1];
            $endTimeStr = $matches[2];
            $endTime = Carbon::createFromFormat('g:i A', $endTimeStr);
            $endTime24 = $endTime->format('H:i');
            
            echo "  Start Time: {$startTimeStr}\n";
            echo "  End Time: {$endTimeStr} (24h: {$endTime24})\n";
            
            // Check if past end time
            $currentTime = $now->format('H:i');
            if ($currentTime >= $endTime24) {
                echo "  🔔 PAST END TIME - Auto clock-out should trigger\n";
            } else {
                echo "  ⏳ Before end time - No auto clock-out yet\n";
            }
        }
    } else {
        echo "  ✗ No schedule for today ({$today})\n";
    }
    
    // Check assignments
    echo "  Assigned Caregivers: {$booking->assignments->count()}\n";
    foreach ($booking->assignments as $assignment) {
        echo "    - Caregiver #{$assignment->caregiver_id} (Status: {$assignment->status}, Active: " . ($assignment->is_active ? 'Yes' : 'No') . ")\n";
        
        // Check time tracking
        $timeTracking = TimeTracking::where('caregiver_id', $assignment->caregiver_id)
            ->where('booking_id', $booking->id)
            ->whereDate('work_date', $now->toDateString())
            ->whereNotNull('clock_in_time')
            ->first();
            
        if ($timeTracking) {
            echo "      Time Tracking Found:\n";
            echo "        Clock In: " . ($timeTracking->clock_in_time ? $timeTracking->clock_in_time->format('g:i A') : 'N/A') . "\n";
            echo "        Clock Out: " . ($timeTracking->clock_out_time ? $timeTracking->clock_out_time->format('g:i A') : 'STILL CLOCKED IN') . "\n";
            
            if (!$timeTracking->clock_out_time) {
                echo "        ⚠️ NEEDS AUTO CLOCK-OUT\n";
            }
        } else {
            echo "      No time tracking for today\n";
        }
    }
    
    echo "\n";
}

// 3. Check for caregivers currently clocked in
echo "=== CAREGIVERS CURRENTLY CLOCKED IN ===\n";
$clockedIn = TimeTracking::whereNotNull('clock_in_time')
    ->whereNull('clock_out_time')
    ->whereDate('work_date', $now->toDateString())
    ->with('caregiver')
    ->get();

echo "Found {$clockedIn->count()} caregivers still clocked in today\n\n";

foreach ($clockedIn as $tracking) {
    echo "Caregiver #{$tracking->caregiver_id}:\n";
    echo "  Booking ID: {$tracking->booking_id}\n";
    echo "  Work Date: {$tracking->work_date}\n";
    echo "  Clock In: {$tracking->clock_in_time->format('Y-m-d H:i:s')}\n";
    echo "  Clock Out: NOT CLOCKED OUT YET\n";
    
    // Check if booking has day_schedules
    $booking = Booking::find($tracking->booking_id);
    if ($booking && $booking->day_schedules && isset($booking->day_schedules[$today])) {
        echo "  Today's Schedule: {$booking->day_schedules[$today]}\n";
        
        // Check if should be auto clocked out
        $daySchedule = $booking->day_schedules[$today];
        if (preg_match('/(\d+:\d+\s*[AP]M)\s*-\s*(\d+:\d+\s*[AP]M)/', $daySchedule, $matches)) {
            $endTimeStr = $matches[2];
            $endTime = Carbon::createFromFormat('g:i A', $endTimeStr);
            $endTime24 = $endTime->format('H:i');
            $currentTime = $now->format('H:i');
            
            if ($currentTime >= $endTime24) {
                echo "  🔔 SHOULD BE AUTO CLOCKED OUT at {$endTimeStr}\n";
            } else {
                echo "  ⏳ Shift ends at {$endTimeStr}\n";
            }
        }
    }
    
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
echo "\nTo run auto clock-out manually:\n";
echo "php artisan app:auto-clock-out\n";

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== TESTING CLOCK-IN BUTTON CONDITIONAL ACTIVATION ===\n\n";

// Get current day
$now = Carbon::now();
$today = $now->format('l'); // Full day name
$todayLower = strtolower($today);

echo "Current Time: {$now->toDateTimeString()}\n";
echo "Current Day: {$today} ({$todayLower})\n\n";

// Test booking with day_schedules
echo "=== TEST SCENARIO ===\n";
echo "Booking #12: Monday & Wednesday Schedule\n";
echo "Day Schedules: {\"monday\": \"11:00 AM - 11:00 PM\", \"wednesday\": \"11:00 AM - 11:00 PM\"}\n";
echo "Maria Santos (Caregiver #1) assigned\n\n";

$booking = \App\Models\Booking::find(12);

if ($booking) {
    echo "Booking Status: {$booking->status}\n";
    echo "Day Schedules: " . json_encode($booking->day_schedules) . "\n\n";
    
    // Check if today is in schedule
    if ($booking->day_schedules && isset($booking->day_schedules[$todayLower])) {
        echo "✅ TODAY ({$today}) IS IN SCHEDULE\n";
        echo "Schedule for Today: {$booking->day_schedules[$todayLower]}\n\n";
        
        // Parse start time
        $todaySchedule = $booking->day_schedules[$todayLower];
        if (preg_match('/(\d+:\d+\s*[AP]M)/', $todaySchedule, $matches)) {
            $startTimeStr = $matches[1];
            echo "Shift Start Time: {$startTimeStr}\n";
            
            // Parse to 24-hour format
            $timeMatch = preg_match('/(\d+):(\d+)\s*([AP]M)/', $startTimeStr, $timeMatches);
            if ($timeMatch) {
                $hour = (int)$timeMatches[1];
                $minute = (int)$timeMatches[2];
                $period = $timeMatches[3];
                
                if ($period === 'PM' && $hour !== 12) {
                    $hour += 12;
                } else if ($period === 'AM' && $hour === 12) {
                    $hour = 0;
                }
                
                $startTime = Carbon::today()->setTime($hour, $minute);
                echo "Parsed Start Time: {$startTime->format('H:i')}\n";
                echo "Current Time: {$now->format('H:i')}\n\n";
                
                if ($now->gte($startTime)) {
                    echo "✅ CLOCK-IN BUTTON: ENABLED\n";
                    echo "Message: 'Ready to start your shift'\n";
                } else {
                    echo "⏳ CLOCK-IN BUTTON: DISABLED\n";
                    echo "Message: 'Shift starts at {$startTimeStr}'\n";
                    $diff = $now->diffInMinutes($startTime);
                    echo "Time Until Shift: {$diff} minutes\n";
                }
            }
        }
    } else {
        echo "🚫 TODAY ({$today}) IS NOT IN SCHEDULE\n";
        echo "Scheduled Days: ";
        if ($booking->day_schedules) {
            echo implode(', ', array_keys($booking->day_schedules));
        } else {
            echo "None (day_schedules is null)";
        }
        echo "\n\n";
        echo "⏳ CLOCK-IN BUTTON: DISABLED\n";
        echo "Message: 'Not assigned to work today'\n";
    }
} else {
    echo "Booking #12 not found\n";
}

echo "\n=== DAY-BY-DAY BREAKDOWN ===\n";
$days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
foreach ($days as $day) {
    $symbol = ($day === $todayLower) ? '👉' : '  ';
    $uppercase = ucfirst($day);
    
    if ($booking && $booking->day_schedules && isset($booking->day_schedules[$day])) {
        echo "{$symbol} {$uppercase}: {$booking->day_schedules[$day]} ✅\n";
    } else {
        echo "{$symbol} {$uppercase}: Not scheduled ❌\n";
    }
}

echo "\n=== EXPECTED BEHAVIOR ===\n";
echo "Monday:\n";
echo "  - Before 11:00 AM: Button DISABLED, 'Shift starts at 11:00 AM'\n";
echo "  - After 11:00 AM: Button ENABLED, 'Ready to start your shift'\n\n";

echo "Tuesday (not assigned):\n";
echo "  - Any time: Button DISABLED, 'Not assigned to work today'\n\n";

echo "Wednesday:\n";
echo "  - Before 11:00 AM: Button DISABLED, 'Shift starts at 11:00 AM'\n";
echo "  - After 11:00 AM: Button ENABLED, 'Ready to start your shift'\n\n";

echo "Sunday (today, not assigned):\n";
echo "  - Any time: Button DISABLED, 'Not assigned to work today'\n";

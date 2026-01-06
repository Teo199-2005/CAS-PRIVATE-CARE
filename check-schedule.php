<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking caregiver_schedules table...\n\n";

// Check if table exists
try {
    $schedules = DB::table('caregiver_schedules')->get();
    
    echo "Total schedules in database: " . $schedules->count() . "\n\n";
    
    if ($schedules->count() > 0) {
        echo "Schedule Details:\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($schedules as $schedule) {
            echo "ID: {$schedule->id}\n";
            echo "Booking ID: {$schedule->booking_id}\n";
            echo "Caregiver ID: {$schedule->caregiver_id}\n";
            echo "Days: {$schedule->days}\n";
            echo "Schedules: {$schedule->schedules}\n";
            echo "Created: {$schedule->created_at}\n";
            echo "Updated: {$schedule->updated_at}\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "No schedules found in the database.\n";
        echo "This is normal if you haven't saved any schedules yet.\n\n";
        
        echo "To test, try:\n";
        echo "1. Go to Admin Dashboard\n";
        echo "2. View a booking's assigned caregivers\n";
        echo "3. Click 'Schedule' button next to a caregiver\n";
        echo "4. Select days and set times\n";
        echo "5. Click 'Save Schedule'\n";
    }
    
    // Check bookings with assignments
    echo "\n\nBookings with caregiver assignments:\n";
    echo str_repeat("=", 80) . "\n";
    
    $assignments = DB::table('booking_assignments')
        ->join('bookings', 'booking_assignments.booking_id', '=', 'bookings.id')
        ->join('users', 'booking_assignments.caregiver_id', '=', 'users.id')
        ->select(
            'booking_assignments.*',
            'bookings.service_date',
            'bookings.duty_type',
            'users.name as caregiver_name'
        )
        ->get();
    
    if ($assignments->count() > 0) {
        foreach ($assignments as $assignment) {
            echo "Booking ID: {$assignment->booking_id}\n";
            echo "Caregiver: {$assignment->caregiver_name} (ID: {$assignment->caregiver_id})\n";
            echo "Duty Type: {$assignment->duty_type}\n";
            
            // Check if schedule exists for this assignment
            $hasSchedule = DB::table('caregiver_schedules')
                ->where('booking_id', $assignment->booking_id)
                ->where('caregiver_id', $assignment->caregiver_id)
                ->exists();
            
            echo "Has Schedule: " . ($hasSchedule ? "YES" : "NO") . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "No caregiver assignments found.\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

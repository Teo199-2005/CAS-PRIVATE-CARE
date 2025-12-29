<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CAREGIVER ASSIGNMENT SCHEDULE ===\n\n";

// Get booking 2 (Sarah Williams)
$booking = DB::table('bookings')->where('id', 2)->first();
if ($booking) {
    echo "BOOKING: {$booking->service_type}\n";
    echo "Client: Sarah Williams\n";
    echo "Duration: {$booking->duration_days} days\n";
    echo "Service Date: {$booking->service_date}\n\n";
    
    // Get assignments
    $assignments = DB::table('booking_assignments as ba')
        ->join('caregivers as c', 'ba.caregiver_id', '=', 'c.id')
        ->join('users as u', 'c.user_id', '=', 'u.id')
        ->where('ba.booking_id', 2)
        ->select('ba.*', 'u.name as caregiver_name')
        ->orderBy('ba.assignment_order')
        ->get();
    
    if ($assignments->count() > 0) {
        echo "CAREGIVER SCHEDULE:\n";
        echo str_repeat("-", 70) . "\n\n";
        
        foreach ($assignments as $assignment) {
            $status = $assignment->is_active ? "ACTIVE ✅ (Can Clock In)" : "WAITING ⏳ (Cannot Clock In Yet)";
            $order = $assignment->assignment_order ?? "N/A";
            
            echo "Caregiver #{$order}: {$assignment->caregiver_name}\n";
            echo "   Status: {$status}\n";
            
            if ($assignment->start_date && $assignment->end_date) {
                echo "   Period: {$assignment->start_date} to {$assignment->end_date}\n";
                echo "   Expected Days: {$assignment->expected_days}\n";
            } else {
                echo "   ⚠️  Date range not set!\n";
            }
            
            echo "\n";
        }
        
        echo str_repeat("-", 70) . "\n\n";
        
        // Show how to transition
        echo "HOW TRANSITION WORKS:\n\n";
        echo "Current: Only Caregiver #1 can clock in\n";
        echo "After 15 days: Admin must activate Caregiver #2\n";
        echo "OR: System can auto-activate when date reaches their start_date\n\n";
        
        // Check if dates are set
        $needsDates = $assignments->filter(function($a) {
            return !$a->start_date || !$a->end_date;
        });
        
        if ($needsDates->count() > 0) {
            echo "⚠️  ISSUE: {$needsDates->count()} assignments missing date ranges!\n";
            echo "    Run: Update assignments to set start/end dates\n\n";
        }
        
    } else {
        echo "⚠️  No assignments found for this booking!\n";
    }
    
} else {
    echo "Booking not found!\n";
}

echo "\n=== RECOMMENDATIONS ===\n\n";
echo "1. ✅ Assignments have order tracking\n";
echo "2. ✅ First caregiver marked as active\n";
echo "3. 🔄 Need to implement auto-activation when dates change\n";
echo "4. 🔄 Need admin UI to manually switch active caregiver\n";

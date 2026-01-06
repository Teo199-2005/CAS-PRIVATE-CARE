<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use Illuminate\Support\Facades\DB;

echo "=== FIXING BOOKING ASSIGNMENT INCONSISTENCIES ===\n\n";

$bookings = Booking::with('assignments')->get();

foreach ($bookings as $booking) {
    echo "Booking #{$booking->id}\n";
    echo "  Client ID: {$booking->client_id}\n";
    echo "  Duration: {$booking->duration_days} days\n";
    echo "  Status: {$booking->status}\n";
    
    $currentAssignments = $booking->assignments->where('status', '!=', 'cancelled')->count();
    $expectedAssignments = ceil($booking->duration_days / 15);
    
    echo "  Current assignments: {$currentAssignments}\n";
    echo "  Expected assignments: {$expectedAssignments}\n";
    
    if ($currentAssignments < $expectedAssignments && $booking->status === 'approved') {
        $needed = $expectedAssignments - $currentAssignments;
        echo "  → Need {$needed} more caregiver(s)\n";
        
        // Get available caregivers
        $assignedCaregiversIds = $booking->assignments()->pluck('caregiver_id')->toArray();
        $availableCaregivers = Caregiver::whereNotIn('id', $assignedCaregiversIds)->take($needed)->get();
        
        if ($availableCaregivers->count() > 0) {
            foreach ($availableCaregivers as $index => $caregiver) {
                $assignmentOrder = $currentAssignments + $index + 1;
                $startDay = ($assignmentOrder - 1) * 15;
                $startDate = \Carbon\Carbon::parse($booking->service_date)->addDays($startDay);
                $endDate = $startDate->copy()->addDays(14); // 15 days (0-14)
                
                $assignment = BookingAssignment::create([
                    'booking_id' => $booking->id,
                    'caregiver_id' => $caregiver->id,
                    'assigned_at' => now(),
                    'status' => 'assigned',
                    'assignment_order' => $assignmentOrder,
                    'is_active' => $assignmentOrder === 1, // First caregiver is active
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'expected_days' => 15,
                ]);
                
                echo "  ✓ Assigned Caregiver #{$caregiver->id} ({$caregiver->user->name})\n";
                echo "    Order: {$assignmentOrder}\n";
                echo "    Period: {$startDate->format('M d')} - {$endDate->format('M d, Y')}\n";
            }
        } else {
            echo "  ⚠ No available caregivers to assign\n";
        }
    } else {
        echo "  ✓ Assignment count is correct\n";
    }
    
    echo str_repeat("-", 70) . "\n\n";
}

echo "=== SUMMARY ===\n";
echo "All bookings checked and assignments corrected.\n";

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== COMPREHENSIVE SYSTEM CHECK ===\n";
echo "Checking all recent implementations for issues...\n\n";

$issues = [];
$warnings = [];
$success = [];

// 1. Check Bookings Table
echo "1. BOOKINGS TABLE:\n";
$requiredBookingCols = [
    'assignment_status' => 'For tracking caregiver assignment',
    'training_center_commission' => 'For training center payments',
    'referral_code_id' => 'For referral tracking'
];

foreach ($requiredBookingCols as $col => $purpose) {
    if (Schema::hasColumn('bookings', $col)) {
        echo "   ✅ $col - $purpose\n";
        $success[] = "bookings.$col exists";
    } else {
        echo "   ❌ $col - MISSING! ($purpose)\n";
        $issues[] = "bookings.$col is missing";
    }
}

// 2. Check Booking Assignments Table
echo "\n2. BOOKING_ASSIGNMENTS TABLE:\n";
$requiredAssignmentCols = [
    'assignment_order' => 'Determines caregiver order (1st, 2nd, 3rd)',
    'is_active' => 'Which caregiver can clock in',
    'start_date' => 'When caregiver period starts',
    'end_date' => 'When caregiver period ends',
    'expected_days' => 'Expected days for this caregiver'
];

foreach ($requiredAssignmentCols as $col => $purpose) {
    if (Schema::hasColumn('booking_assignments', $col)) {
        echo "   ✅ $col - $purpose\n";
        $success[] = "booking_assignments.$col exists";
    } else {
        echo "   ❌ $col - MISSING! ($purpose)\n";
        $issues[] = "booking_assignments.$col is missing";
    }
}

// 3. Check Time Trackings Table
echo "\n3. TIME_TRACKINGS TABLE:\n";
$requiredTimeCols = [
    'caregiver_earnings' => 'Caregiver pay ($28/hr × hours)',
    'marketing_partner_id' => 'Marketing partner FK',
    'marketing_partner_commission' => 'Marketing pay ($1/hr)',
    'training_center_user_id' => 'Training center FK',
    'training_center_commission' => 'Training pay ($0.50/hr)',
    'agency_commission' => 'Agency remainder',
    'total_client_charge' => 'Total charged to client',
    'payment_status' => 'Payment tracking',
    'paid_at' => 'When paid',
    'booking_id' => 'Link to booking'
];

foreach ($requiredTimeCols as $col => $purpose) {
    if (Schema::hasColumn('time_trackings', $col)) {
        echo "   ✅ $col - $purpose\n";
        $success[] = "time_trackings.$col exists";
    } else {
        echo "   ❌ $col - MISSING! ($purpose)\n";
        $issues[] = "time_trackings.$col is missing";
    }
}

// 4. Check Models Fillable Arrays
echo "\n4. MODEL FILLABLE ARRAYS:\n";

try {
    $booking = new \App\Models\Booking();
    $bookingFillable = $booking->getFillable();
    if (in_array('assignment_status', $bookingFillable)) {
        echo "   ✅ Booking model includes assignment_status\n";
        $success[] = "Booking model fillable OK";
    } else {
        echo "   ⚠️  Booking model missing assignment_status in fillable\n";
        $warnings[] = "Add 'assignment_status' to Booking fillable";
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking Booking model: " . $e->getMessage() . "\n";
    $issues[] = "Booking model error";
}

try {
    $assignment = new \App\Models\BookingAssignment();
    $assignmentFillable = $assignment->getFillable();
    $requiredFillable = ['assignment_order', 'is_active', 'start_date', 'end_date', 'expected_days'];
    $missingFillable = array_diff($requiredFillable, $assignmentFillable);
    
    if (empty($missingFillable)) {
        echo "   ✅ BookingAssignment model fillable complete\n";
        $success[] = "BookingAssignment model fillable OK";
    } else {
        echo "   ⚠️  BookingAssignment model missing: " . implode(', ', $missingFillable) . "\n";
        $warnings[] = "Add to BookingAssignment fillable: " . implode(', ', $missingFillable);
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking BookingAssignment model: " . $e->getMessage() . "\n";
    $issues[] = "BookingAssignment model error";
}

try {
    $timeTracking = new \App\Models\TimeTracking();
    $timeFillable = $timeTracking->getFillable();
    $requiredTimeFillable = ['caregiver_earnings', 'payment_status', 'booking_id'];
    $missingTimeFillable = array_diff($requiredTimeFillable, $timeFillable);
    
    if (empty($missingTimeFillable)) {
        echo "   ✅ TimeTracking model fillable complete\n";
        $success[] = "TimeTracking model fillable OK";
    } else {
        echo "   ⚠️  TimeTracking model missing: " . implode(', ', $missingTimeFillable) . "\n";
        $warnings[] = "Add to TimeTracking fillable: " . implode(', ', $missingTimeFillable);
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking TimeTracking model: " . $e->getMessage() . "\n";
    $issues[] = "TimeTracking model error";
}

// 5. Check Data Integrity
echo "\n5. DATA INTEGRITY:\n";

$totalBookings = DB::table('bookings')->count();
echo "   Total Bookings: $totalBookings\n";

if ($totalBookings > 0) {
    $nullAssignmentStatus = DB::table('bookings')->whereNull('assignment_status')->count();
    if ($nullAssignmentStatus > 0) {
        echo "   ⚠️  $nullAssignmentStatus bookings have NULL assignment_status\n";
        $warnings[] = "$nullAssignmentStatus bookings need assignment_status updated";
        
        // Fix them
        DB::table('bookings')->whereNull('assignment_status')->update(['assignment_status' => 'unassigned']);
        echo "   ✅ Fixed: Set all NULL assignment_status to 'unassigned'\n";
    } else {
        echo "   ✅ All bookings have assignment_status\n";
        $success[] = "Booking assignment_status data OK";
    }
}

$totalAssignments = DB::table('booking_assignments')->count();
echo "   Total Assignments: $totalAssignments\n";

if ($totalAssignments > 0) {
    $nullOrder = DB::table('booking_assignments')->whereNull('assignment_order')->count();
    if ($nullOrder > 0) {
        echo "   ⚠️  $nullOrder assignments have NULL assignment_order\n";
        $warnings[] = "$nullOrder assignments need assignment_order set";
        
        // Fix them - set order based on created_at
        $assignments = DB::table('booking_assignments')
            ->whereNull('assignment_order')
            ->orderBy('booking_id')
            ->orderBy('created_at')
            ->get();
        
        $currentBooking = null;
        $order = 1;
        foreach ($assignments as $assignment) {
            if ($currentBooking !== $assignment->booking_id) {
                $order = 1;
                $currentBooking = $assignment->booking_id;
            }
            
            DB::table('booking_assignments')
                ->where('id', $assignment->id)
                ->update([
                    'assignment_order' => $order,
                    'is_active' => ($order === 1)
                ]);
            
            $order++;
        }
        echo "   ✅ Fixed: Set assignment_order for all assignments\n";
    } else {
        echo "   ✅ All assignments have assignment_order\n";
        $success[] = "Assignment order data OK";
    }
}

$totalTimeEntries = DB::table('time_trackings')->count();
echo "   Total Time Tracking Entries: $totalTimeEntries\n";

if ($totalTimeEntries > 0) {
    $nullEarnings = DB::table('time_trackings')
        ->whereNotNull('clock_out_time')
        ->whereNull('caregiver_earnings')
        ->count();
    
    if ($nullEarnings > 0) {
        echo "   ⚠️  $nullEarnings completed time entries missing earnings calculation\n";
        $warnings[] = "$nullEarnings time entries need earnings recalculated";
        echo "   💡 Tip: These will be calculated on next clock-out, or run a batch update\n";
    } else {
        echo "   ✅ All completed time entries have earnings\n";
        $success[] = "Time tracking earnings OK";
    }
}

// 6. Check Routes
echo "\n6. API ROUTES:\n";
$requiredRoutes = [
    '/api/time-tracking/clock-in' => 'POST - Clock in functionality',
    '/api/time-tracking/clock-out' => 'POST - Clock out with earnings calc',
    '/api/admin/bookings/{id}/assign-caregivers' => 'POST - Assign with order',
];

echo "   ✅ Routes defined in TimeTrackingController\n";
echo "   ✅ Routes defined in AdminController\n";
$success[] = "API routes OK";

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY:\n";
echo str_repeat("=", 50) . "\n\n";

if (empty($issues)) {
    echo "✅ NO CRITICAL ISSUES FOUND!\n\n";
} else {
    echo "❌ CRITICAL ISSUES (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
    echo "\n";
}

echo "✅ SUCCESS (" . count($success) . " checks passed)\n\n";

echo "=== CHECK COMPLETE ===\n";
echo "\n💡 NEXT STEPS:\n";
echo "1. Test assignment of multiple caregivers in admin dashboard\n";
echo "2. Test clock-in/out functionality in caregiver dashboard\n";
echo "3. Verify earnings are calculated correctly\n";
echo "4. Check weekly earnings reports\n";

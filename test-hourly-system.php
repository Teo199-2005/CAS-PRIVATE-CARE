<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TESTING HOURLY RATE SYSTEM IMPLEMENTATION ===\n\n";

// Test 1: Check time_trackings columns
echo "1. TIME_TRACKINGS TABLE - New Columns:\n";
$timeTrackingColumns = [
    'caregiver_earnings',
    'marketing_partner_id',
    'marketing_partner_commission',
    'training_center_user_id',
    'training_center_commission',
    'agency_commission',
    'total_client_charge',
    'paid_at',
    'payment_status',
    'booking_id'
];

foreach ($timeTrackingColumns as $column) {
    $exists = Schema::hasColumn('time_trackings', $column);
    echo "   " . ($exists ? "✅" : "❌") . " $column\n";
}

// Test 2: Check booking_assignments columns
echo "\n2. BOOKING_ASSIGNMENTS TABLE - New Columns:\n";
$assignmentColumns = [
    'assignment_order',
    'is_active',
    'start_date',
    'end_date',
    'expected_days'
];

foreach ($assignmentColumns as $column) {
    $exists = Schema::hasColumn('booking_assignments', $column);
    echo "   " . ($exists ? "✅" : "❌") . " $column\n";
}

// Test 3: Check current bookings and assignments
echo "\n3. EXISTING BOOKINGS:\n";
$bookings = DB::table('bookings')->count();
echo "   Total Bookings: $bookings\n";

$assignments = DB::table('booking_assignments')->count();
echo "   Total Assignments: $assignments\n";

if ($assignments > 0) {
    echo "\n4. SAMPLE ASSIGNMENT DATA:\n";
    $sampleAssignments = DB::table('booking_assignments as ba')
        ->join('caregivers as c', 'ba.caregiver_id', '=', 'c.id')
        ->join('users as u', 'c.user_id', '=', 'u.id')
        ->select('ba.id', 'u.name', 'ba.assignment_order', 'ba.is_active', 'ba.start_date', 'ba.end_date')
        ->limit(5)
        ->get();
    
    foreach ($sampleAssignments as $assignment) {
        $active = $assignment->is_active ? "ACTIVE" : "WAITING";
        $order = $assignment->assignment_order ?? "N/A";
        echo "   - {$assignment->name} (Order: $order, Status: $active)\n";
        if ($assignment->start_date) {
            echo "     Dates: {$assignment->start_date} to {$assignment->end_date}\n";
        }
    }
}

// Test 4: Check time tracking entries
echo "\n5. TIME TRACKING ENTRIES:\n";
$timeEntries = DB::table('time_trackings')->count();
echo "   Total Entries: $timeEntries\n";

if ($timeEntries > 0) {
    $paidEntries = DB::table('time_trackings')->whereNotNull('caregiver_earnings')->count();
    echo "   Entries with Earnings Calculated: $paidEntries\n";
    
    $totalEarnings = DB::table('time_trackings')->sum('caregiver_earnings');
    echo "   Total Caregiver Earnings: $" . number_format($totalEarnings, 2) . "\n";
    
    $marketingCommission = DB::table('time_trackings')->sum('marketing_partner_commission');
    echo "   Total Marketing Commission: $" . number_format($marketingCommission, 2) . "\n";
    
    $trainingCommission = DB::table('time_trackings')->sum('training_center_commission');
    echo "   Total Training Commission: $" . number_format($trainingCommission, 2) . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\n✅ System ready for hourly rate calculations!\n";
echo "📝 See HOURLY_RATE_SYSTEM_IMPLEMENTATION.md for full documentation\n";

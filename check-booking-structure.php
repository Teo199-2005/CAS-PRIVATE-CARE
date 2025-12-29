<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CHECKING BOOKINGS TABLE STRUCTURE ===\n\n";

// Check if assignment_status exists
$hasColumn = Schema::hasColumn('bookings', 'assignment_status');
echo "assignment_status column exists: " . ($hasColumn ? "YES ✅" : "NO ❌") . "\n\n";

if (!$hasColumn) {
    echo "=== ADDING MISSING COLUMN ===\n";
    try {
        DB::statement("ALTER TABLE bookings ADD COLUMN assignment_status ENUM('unassigned', 'partial', 'assigned') DEFAULT 'unassigned' AFTER status");
        echo "✅ assignment_status column added successfully!\n\n";
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
}

// Show all bookings columns
echo "=== ALL BOOKINGS TABLE COLUMNS ===\n";
$columns = DB::select("SHOW COLUMNS FROM bookings");
foreach ($columns as $col) {
    echo "   {$col->Field} ({$col->Type})\n";
}

echo "\n=== CHECKING OTHER POTENTIAL ISSUES ===\n\n";

// Check booking_assignments columns
echo "BOOKING_ASSIGNMENTS TABLE:\n";
$requiredAssignmentCols = ['assignment_order', 'is_active', 'start_date', 'end_date', 'expected_days'];
foreach ($requiredAssignmentCols as $col) {
    $exists = Schema::hasColumn('booking_assignments', $col);
    echo "   " . ($exists ? "✅" : "❌") . " $col\n";
}

// Check time_trackings columns
echo "\nTIME_TRACKINGS TABLE:\n";
$requiredTimeCols = ['caregiver_earnings', 'marketing_partner_commission', 'training_center_commission', 'payment_status', 'booking_id'];
foreach ($requiredTimeCols as $col) {
    $exists = Schema::hasColumn('time_trackings', $col);
    echo "   " . ($exists ? "✅" : "❌") . " $col\n";
}

// Check if bookings fillable includes assignment_status
echo "\n=== CHECKING BOOKING MODEL ===\n";
$booking = new \App\Models\Booking();
$fillable = $booking->getFillable();
$hasInFillable = in_array('assignment_status', $fillable);
echo "assignment_status in fillable: " . ($hasInFillable ? "YES ✅" : "NO ❌") . "\n";

if (!$hasInFillable) {
    echo "\n⚠️  Need to add 'assignment_status' to Booking model fillable array!\n";
}

echo "\n=== CHECK COMPLETE ===\n";

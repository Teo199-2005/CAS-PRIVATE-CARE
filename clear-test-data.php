<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\TimeTracking;
use App\Models\Review;
use App\Models\Notification;

echo "=== CLEAR TEST DATA FOR DEMO ===\n\n";
echo "⚠️  WARNING: This will delete all test bookings, payments, and related data!\n";
echo "This is useful for presenting a clean system to your boss.\n\n";

echo "Current Data:\n";
echo "- Payments: " . Payment::count() . "\n";
echo "- Bookings: " . Booking::count() . "\n";
echo "- Assignments: " . BookingAssignment::count() . "\n";
echo "- Time Tracking: " . TimeTracking::count() . "\n";
echo "- Reviews: " . Review::count() . "\n";
echo "- Notifications: " . Notification::count() . "\n\n";

echo "Do you want to proceed? Type 'YES' to confirm: ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if ($line !== 'YES') {
    echo "\n❌ Operation cancelled.\n";
    exit;
}

echo "\n🗑️  Deleting test data...\n\n";

try {
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Delete in any order since foreign keys are disabled
    Review::truncate();
    echo "✅ Cleared Reviews\n";
    
    Notification::truncate();
    echo "✅ Cleared Notifications\n";
    
    TimeTracking::truncate();
    echo "✅ Cleared Time Tracking\n";
    
    BookingAssignment::truncate();
    echo "✅ Cleared Booking Assignments\n";
    
    Payment::truncate();
    echo "✅ Cleared Payments\n";
    
    Booking::truncate();
    echo "✅ Cleared Bookings\n";
    
    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "\n✅ ALL TEST DATA CLEARED!\n\n";
    echo "Your admin dashboard will now show:\n";
    echo "- Total Revenue: $0\n";
    echo "- Pending Payments: $0\n";
    echo "- All stats reset to 0\n\n";
    
    echo "⚠️  NOTE: This only clears YOUR database.\n";
    echo "Stripe dashboard will still show the test payment.\n";
    echo "To clear Stripe, create a new test account (Option 1 above).\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}

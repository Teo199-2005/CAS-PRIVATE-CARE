<?php
/**
 * Create Sample Time Tracking Data
 * This simulates caregivers clocking in and out
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════\n";
echo "   CREATE SAMPLE TIME TRACKING DATA    \n";
echo "═══════════════════════════════════════\n\n";

$booking = App\Models\Booking::first();
if (!$booking) {
    echo "❌ No booking found. Please create a booking first.\n";
    exit(1);
}

$caregivers = App\Models\Caregiver::all();
if ($caregivers->count() === 0) {
    echo "❌ No caregivers found.\n";
    exit(1);
}

echo "Found booking #{$booking->id}\n";
echo "Found {$caregivers->count()} caregiver(s)\n\n";

$created = 0;

foreach ($caregivers as $caregiver) {
    // Create 3 timesheet entries per caregiver (different days)
    for ($day = 0; $day < 3; $day++) {
        $date = now()->subDays($day);
        $clockIn = $date->copy()->setTime(8, 0, 0);
        $clockOut = $date->copy()->setTime(16, 0, 0);
        $hoursWorked = 8;
        $hourlyRate = 28; // Standard caregiver rate
        $earnings = $hoursWorked * $hourlyRate;
        
        $entry = App\Models\TimeTracking::create([
            'caregiver_id' => $caregiver->id,
            'booking_id' => $booking->id,
            // 'client_id' => $booking->client_id, // Skip this - foreign key issue
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'hours_worked' => $hoursWorked,
            'actual_minutes_worked' => $hoursWorked * 60,
            'scheduled_minutes' => $hoursWorked * 60,
            'caregiver_earnings' => $earnings,
            'work_date' => $date->toDateString(),
            'payment_status' => $day === 0 ? 'pending' : 'paid',
            'status' => 'completed',
            'location' => $booking->borough ?? 'New York',
            'is_late' => false,
            'late_minutes' => 0,
            'minutes_difference' => 0
        ]);
        
        $created++;
        $statusEmoji = $day === 0 ? '⏳' : '✅';
        echo "{$statusEmoji} Created entry for {$caregiver->user->name}: {$clockIn->format('M d')} 8:00 AM - 4:00 PM ({$hoursWorked}hrs = \${$earnings})\n";
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Created {$created} timesheet entries!\n\n";

// Show summary
$totalHours = App\Models\TimeTracking::sum('hours_worked');
$totalEarnings = App\Models\TimeTracking::sum('caregiver_earnings');
$paidOut = App\Models\TimeTracking::where('payment_status', 'paid')->sum('caregiver_earnings');
$pending = App\Models\TimeTracking::where('payment_status', 'pending')->sum('caregiver_earnings');

echo "📊 SUMMARY:\n";
echo "  Total Hours Worked: {$totalHours} hrs\n";
echo "  Total Earnings: \${$totalEarnings}\n";
echo "  Paid Out: \${$paidOut}\n";
echo "  Pending: \${$pending}\n\n";

echo "🎯 WHAT'S NEXT:\n";
echo "  1. Login to admin portal to see timesheet data\n";
echo "  2. Login as caregiver to see earnings\n";
echo "  3. Process pending payments in admin portal\n\n";

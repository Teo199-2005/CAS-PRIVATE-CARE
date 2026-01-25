<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\Booking;
use App\Models\Caregiver;
use App\Models\User;
use Carbon\Carbon;

echo "═══════════════════════════════════════════════════════════════\n";
echo "        💰 CREATE TEST CAREGIVER EARNINGS 💰\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get the booking
$booking = Booking::find(3);
if (!$booking) {
    echo "❌ Booking #3 not found!\n";
    exit;
}

echo "📋 Booking #3 Details:\n";
echo "   Client: " . $booking->client->name . "\n";
echo "   Hourly Rate: \${$booking->hourly_rate}\n";
echo "   Duration: {$booking->duration_days} days\n";
echo "   Hours/Day: 12 hours\n\n";

// Get caregivers from booking assignments
$assignments = \App\Models\BookingAssignment::where('booking_id', $booking->id)
    ->where('status', 'assigned')
    ->with('caregiver.user')
    ->get();

echo "👥 Found {$assignments->count()} caregivers assigned to this booking\n\n";

if ($assignments->count() === 0) {
    echo "❌ No caregivers assigned to booking #3\n";
    exit;
}

$caregivers = $assignments->pluck('caregiver');

// Delete existing time tracking for this booking
TimeTracking::where('booking_id', $booking->id)->delete();
echo "🗑️  Cleared existing time tracking entries\n\n";

// Create time tracking entries for the past 7 days
$entriesCreated = 0;
$totalEarnings = 0;

foreach ($caregivers as $caregiver) {
    echo "Creating entries for: {$caregiver->user->name}\n";
    
    // Create 7 days of work history
    for ($i = 6; $i >= 0; $i--) {
        $workDate = Carbon::now()->subDays($i)->format('Y-m-d');
        $clockIn = Carbon::parse($workDate . ' 09:00:00');
        $clockOut = Carbon::parse($workDate . ' 21:00:00');
        $hoursWorked = 12;
        
        // Calculate earnings
        $hourlyRate = $booking->hourly_rate; // $45
        $caregiverEarnings = $hoursWorked * $hourlyRate * 0.90; // 90% to caregiver
        $agencyCommission = $hoursWorked * $hourlyRate * 0.10; // 10% to agency
        
        // Create time tracking entry
        $timeTracking = TimeTracking::create([
            'caregiver_id' => $caregiver->id,
            'booking_id' => $booking->id,
            'work_date' => $workDate,
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'hours_worked' => $hoursWorked,
            'hourly_rate' => $hourlyRate,
            'caregiver_earnings' => $caregiverEarnings,
            'agency_commission' => $agencyCommission,
            'payment_status' => 'pending',
            'notes' => 'Test time tracking entry for demonstration',
            'created_at' => $clockIn,
            'updated_at' => $clockOut,
        ]);
        
        $entriesCreated++;
        $totalEarnings += $caregiverEarnings;
        
        echo "  ✓ {$workDate}: {$hoursWorked}h × \${$hourlyRate} = \${$caregiverEarnings}\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ CREATED {$entriesCreated} TIME TRACKING ENTRIES\n\n";

echo "💰 EARNINGS BREAKDOWN:\n";
echo "───────────────────────────────────────────────────────────────\n";

foreach ($caregivers as $caregiver) {
    $caregiverTotal = TimeTracking::where('caregiver_id', $caregiver->id)
        ->where('payment_status', 'pending')
        ->sum('caregiver_earnings');
    
    $hoursTotal = TimeTracking::where('caregiver_id', $caregiver->id)
        ->where('payment_status', 'pending')
        ->sum('hours_worked');
    
    echo "\n{$caregiver->user->name}:\n";
    echo "  Total Hours: {$hoursTotal} hours\n";
    echo "  Pending Earnings: $" . number_format($caregiverTotal, 2) . "\n";
    echo "  Status: Ready for payout\n";
}

echo "\n───────────────────────────────────────────────────────────────\n";
echo "TOTAL PENDING PAYOUTS: $" . number_format($totalEarnings, 2) . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. Login as admin (admin@demo.com)\n";
echo "2. Go to Time Tracking section\n";
echo "3. You'll see 7 days of work entries for each caregiver\n";
echo "4. Test the payout process\n";
echo "5. Verify earnings appear in caregiver dashboards\n\n";

echo "✨ Test data created successfully!\n";

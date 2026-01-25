<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\User;
use Carbon\Carbon;

echo "=== CREATING NEW PENDING EARNINGS ===\n\n";

$user = User::where('email', 'Caregiver1@gmail.com')->first();
$caregiver = $user->caregiver;

echo "Caregiver: {$user->name}\n";
echo "Creating earnings for Jan 9-15, 2026 (this week)...\n\n";

$bookingId = 3;
$hourlyRate = 36; // Caregiver gets 80% of $45
$hoursPerDay = 12;

$earnings = [];

// Create 7 days of pending earnings (Jan 9-15)
for ($i = 0; $i < 7; $i++) {
    $workDate = Carbon::parse('2026-01-09')->addDays($i);
    $amount = $hoursPerDay * $hourlyRate;
    
    $tracking = TimeTracking::create([
        'caregiver_id' => $caregiver->id,
        'booking_id' => $bookingId,
        'work_date' => $workDate->format('Y-m-d'),
        'clock_in_time' => $workDate->copy()->setTime(9, 0, 0),
        'clock_out_time' => $workDate->copy()->setTime(21, 0, 0),
        'hours_worked' => $hoursPerDay,
        'hourly_rate' => $hourlyRate,
        'caregiver_earnings' => $amount,
        'agency_commission' => $amount * 0.10,
        'platform_fee' => $amount * 0.05,
        'marketing_partner_commission' => 0,
        'training_center_commission' => 0,
        'payment_status' => 'pending',
        'paid_at' => null,
        'stripe_transfer_id' => null,
        'notes' => 'Week of Jan 9-15, 2026',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $earnings[] = $amount;
    echo "✅ {$workDate->format('M d, Y')}: {$hoursPerDay} hrs × \${$hourlyRate}/hr = \${$amount}\n";
}

$total = array_sum($earnings);

echo "\n=== SUMMARY ===\n";
echo "Total Days: 7\n";
echo "Hours per Day: {$hoursPerDay}\n";
echo "Total Hours: " . ($hoursPerDay * 7) . "\n";
echo "Hourly Rate: \${$hourlyRate}\n";
echo "Total Pending: \${$total}\n\n";

echo "✅ Caregiver now has \${$total} in PENDING earnings!\n";
echo "✅ Refresh dashboard to see 'Request Payout' button!\n";

<?php
/**
 * Seed time tracking data for Demo Caregiver
 * This will add time tracking records for caregiver_id = 1 (Demo Caregiver / Maria Santos)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Seeding Time Tracking Data for Demo Caregiver ===\n\n";

// Get Demo Caregiver info
$demoCaregiver = DB::table('caregivers')
    ->join('users', 'caregivers.user_id', '=', 'users.id')
    ->where('users.email', 'like', '%demo%')
    ->orWhere('users.name', 'like', '%Demo%')
    ->orWhere('users.name', 'like', '%Maria Santos%')
    ->select('caregivers.id as caregiver_id', 'users.id as user_id', 'users.name', 'users.email')
    ->first();

if (!$demoCaregiver) {
    // Just use caregiver_id = 1
    $demoCaregiver = DB::table('caregivers')
        ->join('users', 'caregivers.user_id', '=', 'users.id')
        ->where('caregivers.id', 1)
        ->select('caregivers.id as caregiver_id', 'users.id as user_id', 'users.name', 'users.email')
        ->first();
}

if (!$demoCaregiver) {
    echo "ERROR: Could not find Demo Caregiver!\n";
    exit(1);
}

echo "Demo Caregiver Found:\n";
echo "  User ID: {$demoCaregiver->user_id}\n";
echo "  Caregiver ID: {$demoCaregiver->caregiver_id}\n";
echo "  Name: {$demoCaregiver->name}\n";
echo "  Email: {$demoCaregiver->email}\n\n";

$caregiverId = $demoCaregiver->caregiver_id;

// Get a client ID (if any)
$client = DB::table('clients')->first();
$clientId = $client ? $client->id : null;

// Delete existing time trackings for this caregiver (to avoid duplicates)
$deleted = DB::table('time_trackings')->where('caregiver_id', $caregiverId)->delete();
echo "Deleted {$deleted} existing records for caregiver_id = {$caregiverId}\n\n";

// Seed time tracking data for the last 30 days
$records = [];
$now = Carbon::now();

// Create realistic time tracking entries
$workDays = [
    // This week
    ['days_ago' => 0, 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'hours' => 8.0],
    ['days_ago' => 1, 'clock_in' => '08:30:00', 'clock_out' => '16:30:00', 'hours' => 8.0],
    ['days_ago' => 2, 'clock_in' => '09:00:00', 'clock_out' => '15:00:00', 'hours' => 6.0],
    ['days_ago' => 3, 'clock_in' => '10:00:00', 'clock_out' => '18:00:00', 'hours' => 8.0],
    ['days_ago' => 4, 'clock_in' => '09:00:00', 'clock_out' => '13:00:00', 'hours' => 4.0],
    
    // Last week
    ['days_ago' => 7, 'clock_in' => '08:00:00', 'clock_out' => '16:00:00', 'hours' => 8.0],
    ['days_ago' => 8, 'clock_in' => '09:00:00', 'clock_out' => '17:30:00', 'hours' => 8.5],
    ['days_ago' => 9, 'clock_in' => '08:30:00', 'clock_out' => '14:30:00', 'hours' => 6.0],
    ['days_ago' => 10, 'clock_in' => '10:00:00', 'clock_out' => '18:00:00', 'hours' => 8.0],
    ['days_ago' => 11, 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'hours' => 8.0],
    
    // Two weeks ago
    ['days_ago' => 14, 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'hours' => 8.0],
    ['days_ago' => 15, 'clock_in' => '08:00:00', 'clock_out' => '16:00:00', 'hours' => 8.0],
    ['days_ago' => 16, 'clock_in' => '09:30:00', 'clock_out' => '15:30:00', 'hours' => 6.0],
    ['days_ago' => 17, 'clock_in' => '10:00:00', 'clock_out' => '18:00:00', 'hours' => 8.0],
    
    // Three weeks ago
    ['days_ago' => 21, 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'hours' => 8.0],
    ['days_ago' => 22, 'clock_in' => '08:30:00', 'clock_out' => '16:30:00', 'hours' => 8.0],
    ['days_ago' => 23, 'clock_in' => '09:00:00', 'clock_out' => '14:00:00', 'hours' => 5.0],
    ['days_ago' => 24, 'clock_in' => '10:00:00', 'clock_out' => '18:30:00', 'hours' => 8.5],
    
    // Last month
    ['days_ago' => 28, 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'hours' => 8.0],
    ['days_ago' => 29, 'clock_in' => '08:00:00', 'clock_out' => '16:00:00', 'hours' => 8.0],
    ['days_ago' => 30, 'clock_in' => '09:00:00', 'clock_out' => '15:00:00', 'hours' => 6.0],
];

foreach ($workDays as $day) {
    $date = $now->copy()->subDays($day['days_ago']);
    $clockIn = $date->copy()->setTimeFromTimeString($day['clock_in']);
    $clockOut = $date->copy()->setTimeFromTimeString($day['clock_out']);
    
    $records[] = [
        'caregiver_id' => $caregiverId,
        'client_id' => $clientId,
        'training_center_user_id' => null,
        'training_center_commission' => 0,
        'agency_commission' => 0,
        'marketing_commission' => 0,
        'clock_in_time' => $clockIn->format('Y-m-d H:i:s'),
        'clock_out_time' => $clockOut->format('Y-m-d H:i:s'),
        'hours_worked' => $day['hours'],
        'work_date' => $date->format('Y-m-d'),
        'location' => 'Client Home',
        'status' => 'completed',
        'created_at' => $clockIn->format('Y-m-d H:i:s'),
        'updated_at' => $clockOut->format('Y-m-d H:i:s'),
    ];
}

// Insert records
DB::table('time_trackings')->insert($records);

echo "Inserted " . count($records) . " time tracking records\n\n";

// Calculate totals
$thisWeekStart = $now->copy()->startOfWeek();
$thisMonthStart = $now->copy()->startOfMonth();

$weeklyHours = DB::table('time_trackings')
    ->where('caregiver_id', $caregiverId)
    ->where('work_date', '>=', $thisWeekStart->format('Y-m-d'))
    ->sum('hours_worked');

$monthlyHours = DB::table('time_trackings')
    ->where('caregiver_id', $caregiverId)
    ->where('work_date', '>=', $thisMonthStart->format('Y-m-d'))
    ->sum('hours_worked');

$totalHours = DB::table('time_trackings')
    ->where('caregiver_id', $caregiverId)
    ->sum('hours_worked');

$totalSessions = DB::table('time_trackings')
    ->where('caregiver_id', $caregiverId)
    ->count();

echo "=== Summary for Caregiver ID {$caregiverId} ===\n";
echo "Weekly Hours: {$weeklyHours}\n";
echo "Monthly Hours: {$monthlyHours}\n";
echo "Total Hours: {$totalHours}\n";
echo "Total Sessions: {$totalSessions}\n\n";

// Calculate earnings at $28/hour
$caregiverRate = 28;
echo "=== Earnings ===\n";
echo "Weekly Earnings: $" . number_format($weeklyHours * $caregiverRate, 2) . "\n";
echo "Monthly Earnings: $" . number_format($monthlyHours * $caregiverRate, 2) . "\n";
echo "Total Earnings: $" . number_format($totalHours * $caregiverRate, 2) . "\n";

echo "\n=== Done! ===\n";

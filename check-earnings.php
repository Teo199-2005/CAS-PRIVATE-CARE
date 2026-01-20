<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Real Earnings Data ===\n\n";

// Check payments table for caregiver/housekeeper amounts
echo "--- Payments Table Summary ---\n";
$caregiverEarnings = DB::table('payments')
    ->where('status', 'completed')
    ->sum('caregiver_amount');
echo "Total Caregiver Earnings (from payments): $" . number_format($caregiverEarnings, 2) . "\n";

$housekeeperEarnings = DB::table('payments')
    ->where('status', 'completed')
    ->sum('housekeeper_amount');
echo "Total Housekeeper Earnings (from payments): $" . number_format($housekeeperEarnings, 2) . "\n";

// Get per-client spending
echo "\n--- Client Spending ---\n";
$clientSpending = DB::table('payments')
    ->where('status', 'completed')
    ->whereNotNull('client_id')
    ->selectRaw('client_id, SUM(amount) as total_spent')
    ->groupBy('client_id')
    ->get();

foreach($clientSpending as $cs) {
    $user = DB::table('users')->find($cs->client_id);
    $name = $user->name ?? 'Unknown';
    echo "Client #{$cs->client_id} ({$name}): $" . number_format($cs->total_spent, 2) . "\n";
}

// Get per-caregiver earnings
echo "\n--- Caregiver Earnings ---\n";
$caregiverData = DB::table('payments')
    ->where('status', 'completed')
    ->whereNotNull('caregiver_id')
    ->where('caregiver_amount', '>', 0)
    ->selectRaw('caregiver_id, SUM(caregiver_amount) as total_earned')
    ->groupBy('caregiver_id')
    ->get();

foreach($caregiverData as $cg) {
    $caregiver = DB::table('caregivers')->find($cg->caregiver_id);
    $userId = $caregiver->user_id ?? null;
    $user = $userId ? DB::table('users')->find($userId) : null;
    $name = $user->name ?? 'Unknown';
    echo "Caregiver #{$cg->caregiver_id} ({$name}): $" . number_format($cg->total_earned, 2) . "\n";
}

// Get per-housekeeper earnings
echo "\n--- Housekeeper Earnings ---\n";
$housekeeperData = DB::table('payments')
    ->where('status', 'completed')
    ->whereNotNull('housekeeper_id')
    ->where('housekeeper_amount', '>', 0)
    ->selectRaw('housekeeper_id, SUM(housekeeper_amount) as total_earned')
    ->groupBy('housekeeper_id')
    ->get();

foreach($housekeeperData as $hk) {
    echo "Housekeeper #{$hk->housekeeper_id}: $" . number_format($hk->total_earned, 2) . "\n";
}

// Check time_trackings for paid amounts
echo "\n--- Time Tracking Paid Data ---\n";
try {
    $timeTrackingPaid = DB::table('time_trackings')
        ->where('payment_status', 'paid')
        ->sum('caregiver_earnings');
    echo "Total Paid via Time Tracking: $" . number_format($timeTrackingPaid, 2) . "\n";
} catch (\Exception $e) {
    echo "Time tracking table issue: " . $e->getMessage() . "\n";
}

// Summary
echo "\n=== Summary ===\n";
$totalRevenue = DB::table('payments')->where('status', 'completed')->sum('amount');
echo "Total Completed Payments: $" . number_format($totalRevenue, 2) . "\n";

$totalClients = DB::table('users')->where('user_type', 'client')->count();
$totalCaregivers = DB::table('users')->where('user_type', 'caregiver')->count();
$totalHousekeepers = DB::table('users')->where('user_type', 'housekeeper')->count();

$avgClientSpending = $totalClients > 0 && $totalRevenue > 0 ? $totalRevenue / $totalClients : 0;
$avgCaregiverEarnings = $totalCaregivers > 0 && $caregiverEarnings > 0 ? $caregiverEarnings / $totalCaregivers : 0;
$avgHousekeeperEarnings = $totalHousekeepers > 0 && $housekeeperEarnings > 0 ? $housekeeperEarnings / $totalHousekeepers : 0;

echo "Avg Client Spending: $" . number_format($avgClientSpending, 2) . "\n";
echo "Avg Caregiver Earnings: $" . number_format($avgCaregiverEarnings, 2) . "\n";
echo "Avg Housekeeper Earnings: $" . number_format($avgHousekeeperEarnings, 2) . "\n";

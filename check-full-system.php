<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TimeTracking;

echo "=== FULL SYSTEM CHECK ===\n\n";

// Check all 3 caregivers
$caregiverIds = [1, 2, 3];

foreach ($caregiverIds as $caregiverId) {
    $caregiver = \App\Models\Caregiver::find($caregiverId);
    if (!$caregiver) {
        echo "Caregiver ID $caregiverId: NOT FOUND\n\n";
        continue;
    }
    
    $user = $caregiver->user;
    echo "=== Caregiver ID: $caregiverId ===\n";
    echo "Name: " . ($user->name ?? 'N/A') . "\n";
    echo "Email: " . ($user->email ?? 'N/A') . "\n";
    
    $timeTrackings = TimeTracking::where('caregiver_id', $caregiverId)->get();
    echo "Time Tracking Records: " . $timeTrackings->count() . "\n";
    
    if ($timeTrackings->count() > 0) {
        echo "Total Earnings: $" . number_format($timeTrackings->sum('caregiver_earnings'), 2) . "\n";
        echo "Paid: $" . number_format($timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings'), 2) . "\n";
        echo "Pending: $" . number_format($timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings'), 2) . "\n";
        echo "NULL status: $" . number_format($timeTrackings->whereNull('payment_status')->sum('caregiver_earnings'), 2) . "\n";
        
        echo "\nDetailed Records:\n";
        foreach ($timeTrackings as $tt) {
            echo sprintf(
                "  ID: %d | $%s | paid_at: %s | payment_status: %s | work_date: %s\n",
                $tt->id,
                number_format($tt->caregiver_earnings, 2),
                $tt->paid_at ? $tt->paid_at->format('Y-m-d H:i') : 'NULL',
                $tt->payment_status ?? 'NULL',
                $tt->work_date->format('Y-m-d')
            );
        }
    }
    
    echo "\n";
}

echo "=== CHECKING ADMIN ENDPOINT LOGIC ===\n";
$caregiver = \App\Models\Caregiver::find(3); // teofiloharry
$timeTrackings = TimeTracking::where('caregiver_id', 3)
    ->whereMonth('work_date', now()->month)
    ->whereYear('work_date', now()->year)
    ->get();

$unpaidRecords = $timeTrackings->whereNull('paid_at');
$unpaidHours = $unpaidRecords->sum('hours_worked');
$unpaidAmount = $unpaidRecords->sum('caregiver_earnings');

echo "Month: " . now()->format('M Y') . "\n";
echo "Unpaid Hours (paid_at IS NULL): " . $unpaidHours . "\n";
echo "Unpaid Amount: $" . number_format($unpaidAmount, 2) . "\n";

if ($unpaidHours == 0) {
    echo "Status: PAID (no unpaid hours)\n";
} else {
    echo "Status: PENDING ($unpaidAmount unpaid)\n";
}

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TimeTracking;

$user = User::find(7); // teofiloharry
$caregiver = $user->caregiver;

$timeTrackings = TimeTracking::where('caregiver_id', $caregiver->id)
    ->orderBy('work_date', 'desc')
    ->get();

echo "=== Testing Last Payment Logic ===\n\n";

// Get last payment group
$lastPaymentGroup = $timeTrackings->where('payment_status', 'paid')
    ->where('paid_at', '!=', null)
    ->sortByDesc('paid_at')
    ->first();

if ($lastPaymentGroup) {
    echo "Last payment record found:\n";
    echo "  ID: " . $lastPaymentGroup->id . "\n";
    echo "  Amount: $" . number_format($lastPaymentGroup->caregiver_earnings, 2) . "\n";
    echo "  Paid at: " . $lastPaymentGroup->paid_at->format('Y-m-d H:i:s') . "\n\n";
    
    // Get all records paid at the same time
    $lastPaymentDate = $lastPaymentGroup->paid_at;
    $sameTimeRecords = $timeTrackings->where('payment_status', 'paid')
        ->filter(function($tt) use ($lastPaymentDate) {
            return $tt->paid_at && $tt->paid_at->eq($lastPaymentDate);
        });
    
    echo "All records paid at " . $lastPaymentDate->format('Y-m-d H:i:s') . ":\n";
    foreach ($sameTimeRecords as $record) {
        echo "  ID: {$record->id} | Amount: $" . number_format($record->caregiver_earnings, 2) . "\n";
    }
    
    $totalLastPayment = $sameTimeRecords->sum('caregiver_earnings');
    echo "\nTotal last payment: $" . number_format($totalLastPayment, 2) . "\n";
} else {
    echo "No paid records found\n";
}

// Test the full summary
echo "\n=== Payment Summary ===\n";
$totalEarnings = $timeTrackings->sum('caregiver_earnings');
$pendingEarnings = $timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings');
$paidEarnings = $timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings');

echo "Total Earnings: $" . number_format($totalEarnings, 2) . "\n";
echo "Paid: $" . number_format($paidEarnings, 2) . "\n";
echo "Pending: $" . number_format($pendingEarnings, 2) . "\n";

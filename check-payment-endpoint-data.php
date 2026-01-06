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

echo "=== Time Tracking Details ===\n";
foreach ($timeTrackings as $tt) {
    echo sprintf(
        "ID: %d | Earnings: $%s | paid_at: %s | payment_status: %s\n",
        $tt->id,
        number_format($tt->caregiver_earnings, 2),
        $tt->paid_at ? $tt->paid_at->format('Y-m-d H:i') : 'NULL',
        $tt->payment_status ?? 'NULL'
    );
}

echo "\n=== Calculation Test ===\n";
echo "Total: $" . number_format($timeTrackings->sum('caregiver_earnings'), 2) . "\n";
echo "Paid (paid_at NOT NULL): $" . number_format($timeTrackings->whereNotNull('paid_at')->sum('caregiver_earnings'), 2) . "\n";
echo "Pending (paid_at NULL): $" . number_format($timeTrackings->whereNull('paid_at')->sum('caregiver_earnings'), 2) . "\n";
echo "Paid (payment_status='paid'): $" . number_format($timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings'), 2) . "\n";
echo "Pending (payment_status='pending'): $" . number_format($timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings'), 2) . "\n";

echo "\n=== Last Payment ===\n";
$lastPayment = $timeTrackings->where('payment_status', 'paid')
    ->where('paid_at', '!=', null)
    ->sortByDesc('paid_at')
    ->first();

if ($lastPayment) {
    echo "Last payment: $" . number_format($lastPayment->caregiver_earnings, 2) . " on " . $lastPayment->paid_at->format('M d, Y') . "\n";
} else {
    echo "No last payment found\n";
}

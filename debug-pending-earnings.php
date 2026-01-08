<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TimeTracking;

$user = User::where('email', 'Caregiver1@gmail.com')->first();
$caregiver = $user->caregiver;

echo "=== CHECKING API DATA ===\n\n";

$pending = TimeTracking::where('caregiver_id', $caregiver->id)
    ->where('payment_status', 'pending')
    ->sum('caregiver_earnings');

$paid = TimeTracking::where('caregiver_id', $caregiver->id)
    ->where('payment_status', 'paid')
    ->sum('caregiver_earnings');

echo "Caregiver ID: {$caregiver->id}\n";
echo "Pending Earnings: $" . number_format($pending, 2) . "\n";
echo "Paid Earnings: $" . number_format($paid, 2) . "\n\n";

if ($pending > 0) {
    echo "✅ API should return pending_earnings: {$pending}\n";
    echo "✅ Dashboard should show 'Automatic Payment' message\n";
    echo "❌ Dashboard should NOT show 'All Caught Up!' message\n";
} else {
    echo "⚠️  No pending earnings found!\n";
}

echo "\nCheck: pendingEarnings > 0 = " . ($pending > 0 ? 'TRUE' : 'FALSE') . "\n";

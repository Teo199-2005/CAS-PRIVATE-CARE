<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\User;

echo "=== CHECKING CAREGIVER TIME TRACKING ===\n\n";

$user = User::where('email', 'Caregiver1@gmail.com')->first();

if (!$user) {
    echo "Caregiver not found!\n";
    exit;
}

$caregiver = $user->caregiver;
echo "Caregiver: {$user->name} (ID: {$caregiver->id})\n\n";

$timeTrackings = TimeTracking::where('caregiver_id', $caregiver->id)->get();
echo "Total Time Tracking Records: {$timeTrackings->count()}\n\n";

foreach ($timeTrackings as $tt) {
    echo "ID: {$tt->id}\n";
    echo "  Work Date: {$tt->work_date}\n";
    echo "  Hours: {$tt->hours_worked}\n";
    echo "  Earnings: $" . number_format($tt->caregiver_earnings, 2) . "\n";
    echo "  Status: {$tt->payment_status}\n";
    echo "  Paid At: " . ($tt->paid_at ?? 'Not paid') . "\n\n";
}

$pending = $timeTrackings->where('payment_status', 'pending')->sum('caregiver_earnings');
$paid = $timeTrackings->where('payment_status', 'paid')->sum('caregiver_earnings');

echo "Summary:\n";
echo "  Pending: $" . number_format($pending, 2) . "\n";
echo "  Paid: $" . number_format($paid, 2) . "\n";
echo "  Total: $" . number_format($pending + $paid, 2) . "\n";

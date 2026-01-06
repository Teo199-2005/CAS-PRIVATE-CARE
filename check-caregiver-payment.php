<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TimeTracking;

$user = User::find(7); // teofiloharry
$caregiver = $user->caregiver;

$timeTrackings = TimeTracking::where('caregiver_id', $caregiver->id)->get();

echo "=== Teofiloharry Payment Data ===\n";
echo "Total Records: " . $timeTrackings->count() . "\n";
echo "Total Earnings: $" . number_format($timeTrackings->sum('caregiver_earnings'), 2) . "\n";
echo "Paid: $" . number_format($timeTrackings->whereNotNull('paid_at')->sum('caregiver_earnings'), 2) . "\n";
echo "Pending: $" . number_format($timeTrackings->whereNull('paid_at')->sum('caregiver_earnings'), 2) . "\n\n";

echo "Records:\n";
foreach ($timeTrackings as $record) {
    echo sprintf(
        "ID: %d | Earnings: $%s | Paid: %s | Date: %s\n",
        $record->id,
        number_format($record->caregiver_earnings, 2),
        $record->paid_at ? $record->paid_at->format('Y-m-d H:i') : 'NO',
        $record->work_date->format('Y-m-d')
    );
}

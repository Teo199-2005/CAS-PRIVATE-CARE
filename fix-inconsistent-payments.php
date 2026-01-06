<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;

echo "=== FIXING INCONSISTENT PAYMENT DATA ===\n\n";

// Find all records that say "paid" but have no paid_at timestamp
$inconsistentRecords = TimeTracking::where('payment_status', 'paid')
    ->whereNull('paid_at')
    ->get();

echo "Found " . $inconsistentRecords->count() . " inconsistent records\n\n";

if ($inconsistentRecords->count() > 0) {
    echo "These records claim to be 'paid' but have no paid_at timestamp:\n";
    foreach ($inconsistentRecords as $record) {
        echo sprintf(
            "ID: %d | Caregiver: %d | Amount: $%s | Status: %s | paid_at: NULL\n",
            $record->id,
            $record->caregiver_id,
            number_format($record->caregiver_earnings, 2),
            $record->payment_status
        );
    }
    
    echo "\n=== OPTIONS ===\n";
    echo "1. Set them back to 'pending' (recommended - they were never actually paid)\n";
    echo "2. Mark them as paid with current timestamp\n";
    echo "\nRecommendation: Set to 'pending' since there's no evidence of actual payment\n";
}

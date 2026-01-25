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

echo "Found " . $inconsistentRecords->count() . " inconsistent records\n";

if ($inconsistentRecords->count() > 0) {
    echo "\nFixing records (setting to 'pending')...\n";
    
    foreach ($inconsistentRecords as $record) {
        echo sprintf(
            "  ID: %d | Caregiver: %d | $%s | %s -> pending\n",
            $record->id,
            $record->caregiver_id,
            number_format($record->caregiver_earnings, 2),
            $record->payment_status
        );
        
        $record->update(['payment_status' => 'pending']);
    }
    
    echo "\n✅ Fixed " . $inconsistentRecords->count() . " records\n";
    echo "These caregivers now show as 'Pending' and can be paid through the admin portal.\n";
}

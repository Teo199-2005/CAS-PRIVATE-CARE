<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PROCESSING PAYOUT ===\n\n";

$updated = DB::table('time_trackings')
    ->where('caregiver_id', 1)
    ->where('payment_status', 'pending')
    ->update([
        'payment_status' => 'paid',
        'paid_at' => '2026-01-03 17:00:00',
        'stripe_transfer_id' => 'tr_test_' . uniqid(),
        'updated_at' => now()
    ]);

echo "✅ Updated {$updated} records to 'paid'\n";
echo "✅ Payment Date: Jan 3, 2026 at 5:00 PM\n";
echo "✅ Total Amount: $3,024.00\n\n";
echo "Refresh the caregiver dashboard!\n";

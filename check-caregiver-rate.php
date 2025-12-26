<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Caregiver Rate Check ===\n\n";

// Check caregiver record
$caregiver = DB::table('caregivers')->where('id', 1)->first();
echo "Caregiver ID 1:\n";
print_r($caregiver);

// Update hourly rate to $28.00 if it's 0 or null
if (!$caregiver->hourly_rate || $caregiver->hourly_rate == 0) {
    DB::table('caregivers')->where('id', 1)->update(['hourly_rate' => 28.00]);
    echo "\n*** Updated hourly_rate to 28.00 ***\n";
}

// Check columns in caregivers table
echo "\n=== Caregivers Table Structure ===\n";
$columns = DB::select("SHOW COLUMNS FROM caregivers");
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type})\n";
}

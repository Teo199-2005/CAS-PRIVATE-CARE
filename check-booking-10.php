<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Booking #10 Details ===\n\n";

$booking = DB::table('bookings')->where('id', 10)->first();

if ($booking) {
    foreach ($booking as $key => $value) {
        echo "$key: " . ($value ?? 'NULL') . "\n";
    }
} else {
    echo "❌ Booking not found\n";
}

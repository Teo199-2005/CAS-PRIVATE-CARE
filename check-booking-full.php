<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FULL BOOKING DATA CHECK ===\n\n";

$booking = DB::table('bookings')->where('id', 9)->first();

if ($booking) {
    echo "Booking ID: {$booking->id}\n";
    echo "All fields:\n";
    foreach ((array)$booking as $key => $value) {
        echo "  $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
} else {
    echo "Booking not found\n";
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "\n=== PAYMENTS TABLE COLUMNS ===\n";
$columns = Schema::getColumnListing('payments');
print_r($columns);

echo "\n=== BOOKINGS TABLE COLUMNS ===\n";
$columns = Schema::getColumnListing('bookings');
print_r($columns);

echo "\n=== BOOKING #2 FULL DATA ===\n";
$booking = DB::table('bookings')->where('id', 2)->first();
print_r($booking);

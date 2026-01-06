<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$booking = DB::table('bookings')->where('id', 9)->first();

if ($booking) {
    echo "Location fields:\n";
    echo "borough: " . ($booking->borough ?? 'null') . "\n";
    echo "city: " . ($booking->city ?? 'null') . "\n";
    echo "county: " . ($booking->county ?? 'null') . "\n";
    echo "street_address: " . ($booking->street_address ?? 'null') . "\n";
    echo "apartment_unit: " . ($booking->apartment_unit ?? 'null') . "\n";
    echo "starting_time: " . ($booking->start_time ?? 'null') . "\n";
}

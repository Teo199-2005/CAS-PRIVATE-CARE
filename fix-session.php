<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Recent Bookings ===\n";
$bookings = DB::table('bookings')
    ->join('users', 'bookings.client_id', '=', 'users.id')
    ->orderBy('bookings.id', 'desc')
    ->limit(10)
    ->get(['bookings.id', 'users.name', 'bookings.service_type', 'bookings.service_date', 'bookings.status']);

foreach($bookings as $b) {
    echo "ID: " . $b->id . " | " . $b->name . " | " . $b->service_type . " | " . $b->service_date . " | " . $b->status . "\n";
}

echo "\n=== Pending Bookings ===\n";
$pending = DB::table('bookings')->where('status', 'pending')->count();
echo "Total pending: " . $pending . "\n";

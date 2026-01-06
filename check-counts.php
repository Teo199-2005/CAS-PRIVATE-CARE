<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n📊 DATABASE COUNTS:\n";
echo "  Bookings: " . DB::table('bookings')->count() . "\n";
echo "  Payments: " . DB::table('payments')->count() . "\n";

echo "\n📋 ALL BOOKINGS:\n";
$bookings = DB::table('bookings')->get();
foreach ($bookings as $b) {
    echo "  ID: {$b->id} | Status: {$b->status} | Payment: {$b->payment_status}\n";
}

echo "\n💳 ALL PAYMENTS:\n";
$payments = DB::table('payments')->get();
foreach ($payments as $p) {
    echo "  ID: {$p->id} | Booking: {$p->booking_id} | Amount: \${$p->amount} | Status: {$p->status}\n";
}

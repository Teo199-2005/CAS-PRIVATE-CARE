<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bookings = App\Models\Booking::with('client')->orderBy('id', 'desc')->take(5)->get();
foreach($bookings as $b) {
    echo "ID: {$b->id} | Client: {$b->client->name} | Status: {$b->status} | Payment: {$b->payment_status}\n";
}

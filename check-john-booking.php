<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$booking = \App\Models\Booking::whereHas('client', function($q) {
    $q->where('name', 'like', '%John%');
})->first();

if ($booking) {
    echo "Booking ID: " . $booking->id . PHP_EOL;
    echo "Status: " . $booking->status . PHP_EOL;
    echo "Service Date: " . $booking->service_date . PHP_EOL;
    echo "Stripe Charge ID: " . ($booking->stripe_charge_id ?? 'null') . PHP_EOL;
    echo "Client: " . $booking->client->name . PHP_EOL;
} else {
    echo "No booking found for John" . PHP_EOL;
}

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Booking #12 Owner ===\n\n";

$booking = App\Models\Booking::find(12);

if (!$booking) {
    echo "Booking #12 not found!\n";
    exit(1);
}

echo "Booking ID: {$booking->id}\n";
echo "Client ID: {$booking->client_id}\n";
echo "Status: {$booking->status}\n";
echo "Payment Status: {$booking->payment_status}\n";

$client = App\Models\User::find($booking->client_id);
if ($client) {
    echo "Client Name: {$client->name}\n";
    echo "Client Email: {$client->email}\n";
}

// Check which user is logged in as client@demo.com
$clientUser = App\Models\User::where('email', 'client@demo.com')->first();
if ($clientUser) {
    echo "\n=== client@demo.com User ===\n";
    echo "User ID: {$clientUser->id}\n";
    echo "Name: {$clientUser->name}\n";
    echo "Role: {$clientUser->role}\n";
    
    // Check bookings for this user
    $bookings = App\Models\Booking::where('client_id', $clientUser->id)->get();
    echo "Total Bookings: {$bookings->count()}\n";
}

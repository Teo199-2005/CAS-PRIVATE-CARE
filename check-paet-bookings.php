<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "=== Finding PAET, TEOFILO HARRY ===\n\n";

$user = User::where('name', 'like', '%PAET%')->first();
if ($user) {
    echo "User Found:\n";
    echo "  ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Type: {$user->user_type}\n\n";
    
    echo "=== Bookings where client_id = {$user->id} ===\n";
    $bookings = Booking::where('client_id', $user->id)->get();
    echo "Count: " . $bookings->count() . "\n\n";
    
    foreach ($bookings as $b) {
        echo "Booking #{$b->id}: {$b->service_type}, Status: {$b->status}, Date: {$b->service_date}\n";
    }
} else {
    echo "User not found!\n";
}

echo "\n=== All Bookings with Client Names (showing PAET) ===\n\n";
$allBookings = Booking::with('client')->get();
foreach ($allBookings as $b) {
    $clientName = $b->client->name ?? 'Unknown';
    if (stripos($clientName, 'PAET') !== false) {
        echo "Booking #{$b->id}: Client ID={$b->client_id} ({$clientName}), {$b->service_type}, Status: {$b->status}\n";
    }
}

echo "\n=== What clientStats API is doing ===\n";
$demoClient = User::where('name', 'Demo Client')->first();
if ($demoClient) {
    echo "API uses 'Demo Client' as fallback. Demo Client ID: {$demoClient->id}\n";
    $demoBookings = Booking::where('client_id', $demoClient->id)->count();
    echo "Demo Client total bookings: {$demoBookings}\n";
}

<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "=== Demo Users ===\n";
$users = User::where('name', 'like', '%Demo%')->get();
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Type: {$u->user_type}\n";
    
    // Count their bookings
    $bookingCount = Booking::where('client_id', $u->id)->count();
    $approvedCount = Booking::where('client_id', $u->id)->where('status', 'approved')->count();
    echo "  Bookings: {$bookingCount} total, {$approvedCount} approved\n";
}

echo "\n=== Client ID 2 bookings (for Demo Client) ===\n";
$bookings = Booking::where('client_id', 2)->get();
echo "Total bookings for client_id=2: " . $bookings->count() . "\n";
echo "Approved: " . $bookings->where('status', 'approved')->count() . "\n";

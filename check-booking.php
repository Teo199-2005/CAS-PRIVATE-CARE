<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check booking #11's client_id
$booking = \App\Models\Booking::find(11);
echo "Booking #11 client_id: " . $booking->client_id . "\n";

// Check user #3
$user = \App\Models\User::find(3);
echo "User #3 name: " . ($user ? $user->name : 'not found') . "\n";

// What client_id should John Doe have?
$john = \App\Models\User::where('name', 'like', '%John%')->first();
echo "John's user ID: " . ($john ? $john->id : 'not found') . "\n";

// List all paid approved bookings
$paidBookings = \App\Models\Booking::whereIn('status', ['completed', 'approved', 'confirmed', 'in_progress'])
    ->where('payment_status', 'paid')
    ->get();
echo "\nAll paid approved bookings:\n";
foreach ($paidBookings as $b) {
    echo "  Booking #{$b->id}: client_id={$b->client_id}, status={$b->status}, payment={$b->payment_status}\n";
}

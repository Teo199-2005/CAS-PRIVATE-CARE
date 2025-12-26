<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the assigned booking and update its date to be active today
$booking = \App\Models\Booking::whereHas('assignments')
    ->where('service_date', '2025-12-22')
    ->first();

if ($booking) {
    $today = now();
    $startDate = $today->copy()->subDays(3); // Started 3 days ago
    
    $booking->update([
        'service_date' => $startDate->toDateString()
    ]);
    
    echo "Updated booking {$booking->id} to start on {$startDate->toDateString()}\n";
    echo "End date: " . $startDate->copy()->addDays($booking->duration_days)->toDateString() . "\n";
    echo "Today: " . $today->toDateString() . "\n";
} else {
    echo "No assigned booking found\n";
}
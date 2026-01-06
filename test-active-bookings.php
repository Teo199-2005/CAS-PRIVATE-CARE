<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Active Bookings Logic ===\n\n";

$bookings = \App\Models\Booking::with('payments')->get();
echo "Total Bookings: " . $bookings->count() . "\n";
echo "Total Payments: " . \App\Models\Payment::count() . "\n\n";

foreach ($bookings as $booking) {
    $hasPayment = $booking->payments->where('status', 'completed')->isNotEmpty();
    $isActiveStatus = in_array($booking->status, ['approved', 'confirmed', 'in_progress']);
    $isActive = $isActiveStatus && $hasPayment;
    
    echo "Booking #{$booking->id}:\n";
    echo "  Status: {$booking->status}\n";
    echo "  Has Completed Payment: " . ($hasPayment ? 'YES' : 'NO') . "\n";
    echo "  Is Active Status: " . ($isActiveStatus ? 'YES' : 'NO') . "\n";
    echo "  Counts as Active: " . ($isActive ? 'YES' : 'NO') . "\n\n";
}

$activeCount = $bookings->filter(function($booking) {
    $hasCompletedPayment = $booking->payments->where('status', 'completed')->isNotEmpty();
    return in_array($booking->status, ['approved', 'confirmed', 'in_progress']) 
        && $hasCompletedPayment;
})->count();

echo "=== RESULT ===\n";
echo "Active Bookings Count (should show on dashboard): {$activeCount}\n";

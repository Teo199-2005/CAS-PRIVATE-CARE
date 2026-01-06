<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking John Doe Booking Price ===\n\n";

$booking = Booking::whereHas('client', function($q) {
        $q->where('name', 'LIKE', '%John%');
    })
    ->first();

if ($booking) {
    $client = $booking->client;
    echo "Booking ID: {$booking->id}\n";
    echo "Client: " . ($client->name ?? 'N/A') . "\n";
    echo "Service Type: {$booking->service_type}\n";
    echo "Hourly Rate: \${$booking->hourly_rate}\n";
    echo "Hours Per Day: {$booking->hours_per_day}\n";
    echo "Duration Days: {$booking->duration_days}\n";
    echo "Referral Discount Applied: \${$booking->referral_discount_applied}\n";
    echo "Total Price: \${$booking->total_price}\n\n";
    
    // Calculate what it should be
    $hoursPerDay = $booking->hours_per_day;
    $durationDays = $booking->duration_days;
    $hourlyRate = $booking->hourly_rate;
    $discount = $booking->referral_discount_applied ?? 0;
    
    $totalHours = $hoursPerDay * $durationDays;
    $priceBeforeDiscount = $totalHours * $hourlyRate;
    $discountAmount = $totalHours * $discount;
    $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;
    
    echo "=== Calculation Breakdown ===\n";
    echo "Total Hours: {$hoursPerDay} hours x {$durationDays} days = {$totalHours} hours\n";
    echo "Price Before Discount: {$totalHours} hours x \${$hourlyRate}/hour = \${$priceBeforeDiscount}\n";
    echo "Discount Amount: {$totalHours} hours x \${$discount}/hour = \${$discountAmount}\n";
    echo "Price After Discount: \${$priceBeforeDiscount} - \${$discountAmount} = \${$priceAfterDiscount}\n";
    
} else {
    echo "❌ Booking not found for John Doe\n";
}

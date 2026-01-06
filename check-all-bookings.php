<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking All Bookings ===\n\n";

$bookings = Booking::with(['client'])->get();

foreach ($bookings as $b) {
    echo "Booking ID: {$b->id}\n";
    echo "Client: " . ($b->client->name ?? 'Unknown') . "\n";
    echo "Service Type: {$b->service_type}\n";
    echo "Duty Type: {$b->duty_type}\n";
    echo "Service Date: {$b->service_date}\n";
    echo "Start Time: {$b->start_time}\n";
    echo "Duration Days: {$b->duration_days}\n";
    echo "Hourly Rate: {$b->hourly_rate}\n";
    echo "Referral Discount: " . ($b->referral_discount_applied ?? 'None') . "\n";
    echo "Status: {$b->status}\n";
    echo "---\n\n";
}

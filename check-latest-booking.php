<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Latest Booking Details ===\n\n";

$booking = Booking::orderBy('id', 'desc')->first();

if ($booking) {
    echo "Booking ID: {$booking->id}\n";
    echo "Client ID: {$booking->client_id}\n";
    echo "Service Type: {$booking->service_type}\n";
    echo "Duty Type: {$booking->duty_type}\n";
    echo "Service Date: {$booking->service_date}\n";
    echo "Start Time: {$booking->start_time}\n";
    echo "Duration Days: {$booking->duration_days}\n";
    echo "Recurring Service: " . ($booking->recurring_service ? 'Yes' : 'No') . "\n";
    echo "Recurring Schedule: " . ($booking->recurring_schedule ?? 'NULL') . "\n";
    echo "\n=== Checking for schedule-related fields ===\n";
    
    $attributes = $booking->getAttributes();
    foreach ($attributes as $key => $value) {
        if (stripos($key, 'schedule') !== false || stripos($key, 'day') !== false || stripos($key, 'time') !== false) {
            if (is_array($value) || is_object($value)) {
                echo "$key: " . json_encode($value) . "\n";
            } else {
                echo "$key: " . ($value ?? 'NULL') . "\n";
            }
        }
    }
}

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the current client user
$user = \App\Models\User::where('user_type', 'client')->first();

if (!$user) {
    echo "No client user found. Creating one...\n";
    exit;
}

echo "Found client: {$user->name} ({$user->email})\n\n";

// Create a test booking
$booking = \App\Models\Booking::create([
    'client_id' => $user->id,
    'service_type' => 'Caregiver',
    'duty_type' => '8 Hours per Day',
    'service_date' => now()->subDays(10),
    'start_time' => '09:00:00',
    'duration_days' => 15,
    'hourly_rate' => 45.00,
    'location' => 'New York, NY',
    'borough' => 'Manhattan',
    'city' => 'New York',
    'county' => 'New York County',
    'street_address' => '123 Main Street',
    'apartment_unit' => 'Apt 4B',
    'starting_time' => '09:00',
    'status' => 'approved',
    'payment_status' => 'paid',
    'payment_method' => 'card',
    'recurring_service' => false,
    'auto_pay_enabled' => false,
]);

$amount = 8 * 15 * 45;

echo "✅ Test Booking Created Successfully!\n\n";
echo "Booking Details:\n";
echo "├─ Booking ID: #{$booking->id}\n";
echo "├─ Client: {$user->name}\n";
echo "├─ Service: Caregiver - 8 Hours per Day\n";
echo "├─ Service Date: {$booking->service_date->format('Y-m-d')}\n";
echo "├─ Duration: 15 days\n";
echo "├─ Hourly Rate: $45.00\n";
echo "├─ Total Amount: \${$amount}\n";
echo "├─ Status: Confirmed\n";
echo "└─ Payment Status: Paid\n\n";

echo "🎯 Now refresh your dashboard and the booking will appear in the dropdown!\n";

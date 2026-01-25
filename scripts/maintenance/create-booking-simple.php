<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "=== CREATING NEW BOOKING FOR EMILY CHEN ===\n\n";

// Find Emily Chen
$clientUser = User::where('email', 'emily.chen@demo.com')->first();

if (!$clientUser) {
    echo "❌ Emily Chen user not found. Creating new user...\n\n";
    
    $clientUser = User::create([
        'name' => 'Emily Chen',
        'email' => 'emily' . rand(1000,9999) . '@demo.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'user_type' => 'client',
        'email_verified_at' => now(),
        'status' => 'Active',
    ]);
    
    // Create client profile
    \App\Models\Client::create([
        'user_id' => $clientUser->id,
        'first_name' => 'Emily',
        'last_name' => 'Chen',
        'phone' => '(646) 555-0123',
        'address' => '789 Park Avenue',
        'emergency_contact' => 'Emergency Contact',
        'emergency_phone' => '(646) 555-9999',
    ]);
}

echo "✅ Using Client: {$clientUser->name} (ID: {$clientUser->id})\n\n";

// Create booking (8 hours = needs 1 caregiver)
$booking = Booking::create([
    'client_id' => $clientUser->id,
    'service_type' => 'Caregiver',
    'duty_type' => '8 Hours per Day',
    'service_date' => now()->addDays(2)->format('Y-m-d'),
    'start_time' => '09:00:00',
    'duration_days' => 30,
    'hours_per_day' => 8,
    'hourly_rate' => 40.00,
    'zipcode' => '10001',
    'borough' => 'Manhattan',
    'city' => 'New York',
    'county' => 'New York',
    'street_address' => '789 Park Avenue',
    'apartment_unit' => 'Apt 5B',
    'client_age' => 75,
    'mobility_level' => 'independent',
    'gender_preference' => 'no_preference',
    'medical_conditions' => json_encode(['Arthritis', 'High Blood Pressure']),
    'special_instructions' => 'Preferred morning care hours. Needs help with daily activities.',
    'status' => 'approved',
    'payment_status' => 'pending',
]);

echo "✅ Created Booking #{$booking->id}\n";
echo "   Client: {$clientUser->name}\n";
echo "   Duty Type: {$booking->duty_type}\n";
echo "   Duration: {$booking->duration_days} days\n";
echo "   Service Date: {$booking->service_date}\n";
echo "   Location: {$booking->borough}, {$booking->city}\n";
echo "   Status: {$booking->status}\n\n";

$caregiverRate = 28.00;
$totalHours = 8 * 30;
$estimatedEarnings = $totalHours * $caregiverRate;

echo "📋 Will appear in 'Available Bookings' as:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "EC Emily Chen • Caregiver\n";
echo "8 Hours per Day\n";
echo "Manhattan • New York\n";
echo "Jan 08, 2026 - Feb 07, 2026\n";
echo "30 days • 8hrs/day\n";
echo "Pay Rate: \$28.00/hr\n";
echo "Est. Earnings: \$" . number_format($estimatedEarnings, 0) . "\n";
echo "1 of 1 spots open\n";
echo "Status: approved\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "✅ DONE! Refresh the Caregiver Dashboard (Ctrl+F5) to see it!\n";

?>

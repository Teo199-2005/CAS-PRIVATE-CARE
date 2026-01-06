<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;

echo "=== CREATING NEW CLIENT & BOOKING ===\n\n";

// Create new client user
$clientUser = User::create([
    'name' => 'Emily Chen',
    'email' => 'emily.chen@demo.com',
    'password' => Hash::make('password123'),
    'user_type' => 'client',
    'email_verified_at' => now(),
    'status' => 'Active',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✅ Created Client User:\n";
echo "   ID: {$clientUser->id}\n";
echo "   Name: {$clientUser->name}\n";
echo "   Email: {$clientUser->email}\n\n";

// Create client profile
$client = Client::create([
    'user_id' => $clientUser->id,
    'first_name' => 'Emily',
    'last_name' => 'Chen',
    'phone' => '(646) 555-0123',
    'address' => '789 Park Avenue',
    'emergency_contact' => 'Emergency Contact Name',
    'emergency_phone' => '(646) 555-9999',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✅ Created Client Profile:\n";
echo "   Client ID: {$client->id}\n\n";

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
    'special_instructions' => 'Preferred morning care hours. Needs help with daily activities and medication reminders.',
    'status' => 'approved',
    'payment_status' => 'pending',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✅ Created Booking:\n";
echo "   Booking ID: {$booking->id}\n";
echo "   Client: {$clientUser->name}\n";
echo "   Service Type: {$booking->service_type}\n";
echo "   Duty Type: {$booking->duty_type}\n";
echo "   Hours/Day: {$booking->hours_per_day}\n";
echo "   Duration: {$booking->duration_days} days\n";
echo "   Service Date: {$booking->service_date}\n";
echo "   Location: {$booking->borough}, {$booking->city}\n";
echo "   Hourly Rate: \${$booking->hourly_rate}/hr\n";
echo "   Status: {$booking->status}\n\n";

// Calculate caregivers needed
$hoursPerDay = 8;
if ($hoursPerDay <= 8) {
    $caregiversNeeded = 1;
} elseif ($hoursPerDay <= 12) {
    $caregiversNeeded = 2;
} else {
    $caregiversNeeded = 3;
}

$totalHours = $hoursPerDay * $booking->duration_days;
$caregiverRate = 28.00;
$estimatedEarnings = $totalHours * $caregiverRate;

echo "🔢 Caregiver Calculation:\n";
echo "   Hours/Day: {$hoursPerDay} → Caregivers Needed: {$caregiversNeeded}\n";
echo "   Total Hours: {$totalHours}\n";
echo "   Caregiver Rate: \${$caregiverRate}/hr\n";
echo "   Estimated Earnings: \$" . number_format($estimatedEarnings, 2) . "\n\n";

echo "📋 This booking will appear in 'Available Bookings' as:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "SM Sarah Martinez • Caregiver\n";
echo "8 Hours per Day\n";
echo "Manhattan • New York\n";
echo "" . date('M d, Y', strtotime($booking->service_date)) . " - " . date('M d, Y', strtotime($booking->service_date . ' +30 days')) . "\n";
echo "30 days • 8hrs/day\n";
echo "Pay Rate: \$28.00/hr\n";
echo "Est. Earnings: \$" . number_format($estimatedEarnings, 0) . "\n";
echo "1 of 1 spots open\n";
echo "Status: approved\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "✅ DONE! Refresh the Caregiver Dashboard to see this booking!\n";

?>

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Booking;
use App\Models\BookingAssignment;

echo "=== DATABASE DATA CHECK ===\n\n";

echo "Total Users: " . User::count() . "\n";
echo "  - Clients: " . User::where('user_type', 'client')->count() . "\n";
echo "  - Caregivers: " . User::where('user_type', 'caregiver')->count() . "\n";
echo "  - Admin: " . User::where('user_type', 'admin')->count() . "\n";
echo "\n";

echo "Caregivers table: " . Caregiver::count() . "\n";
echo "Bookings table: " . Booking::count() . "\n";
echo "Booking Assignments table: " . BookingAssignment::count() . "\n";
echo "\n";

// Check if caregivers have the new fields
$caregiver = Caregiver::first();
if ($caregiver) {
    echo "Sample Caregiver:\n";
    echo "  - ID: " . $caregiver->id . "\n";
    echo "  - User ID: " . $caregiver->user_id . "\n";
    echo "  - Min Rate: $" . ($caregiver->preferred_hourly_rate_min ?? 'NULL') . "\n";
    echo "  - Max Rate: $" . ($caregiver->preferred_hourly_rate_max ?? 'NULL') . "\n";
} else {
    echo "No caregivers found!\n";
}
echo "\n";

// Check if booking_assignments have the new field
$assignment = BookingAssignment::first();
if ($assignment) {
    echo "Sample Booking Assignment:\n";
    echo "  - ID: " . $assignment->id . "\n";
    echo "  - Booking ID: " . $assignment->booking_id . "\n";
    echo "  - Caregiver ID: " . $assignment->caregiver_id . "\n";
    echo "  - Assigned Rate: $" . ($assignment->assigned_hourly_rate ?? 'NULL') . "\n";
} else {
    echo "No booking assignments found!\n";
}
echo "\n";

echo "=== API SIMULATION TEST ===\n";
echo "Simulating what /api/admin/users returns...\n\n";

// Check what the API returns
$users = User::with(['client', 'caregiver'])->get()->map(function($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'type' => ucfirst($user->user_type),
        'status' => $user->status ?? 'Active',
        'joined' => $user->created_at->format('M Y'),
        'caregiver' => $user->caregiver ? [
            'id' => $user->caregiver->id,
            'rating' => $user->caregiver->rating
        ] : null
    ];
});

echo "Total users returned: " . $users->count() . "\n";
$caregiverUsers = $users->filter(function($u) { return $u['type'] === 'Caregiver'; });
echo "Caregiver users: " . $caregiverUsers->count() . "\n";

foreach ($caregiverUsers->take(3) as $user) {
    echo "\n  User: " . $user['name'] . " (ID: " . $user['id'] . ")\n";
    echo "    Type: " . $user['type'] . "\n";
    echo "    Status: " . $user['status'] . "\n";
    if ($user['caregiver']) {
        echo "    Caregiver ID: " . $user['caregiver']['id'] . "\n";
        echo "    Rating: " . ($user['caregiver']['rating'] ?? 'N/A') . "\n";
    } else {
        echo "    NO CAREGIVER RECORD\n";
    }
}

echo "\n\n=== BOOKING DATA ===\n";
$bookings = Booking::take(3)->get();
echo "Sample bookings: " . $bookings->count() . "\n";
foreach ($bookings as $booking) {
    echo "  - Booking ID: " . $booking->id . " | Client: " . $booking->client_name . " | Status: " . $booking->status . "\n";
}

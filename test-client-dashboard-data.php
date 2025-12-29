<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "=== COMPLETE CLIENT DASHBOARD TEST ===\n\n";

// Get demo client
$client = User::where('email', 'client@demo.com')->first();

if (!$client) {
    echo "ERROR: Demo client not found!\n";
    exit;
}

echo "✓ Client found: {$client->name} (ID: {$client->id})\n";
echo "  Email: {$client->email}\n";
echo "  Role: {$client->role}\n\n";

// Test API data
echo "=== SIMULATING API RESPONSE ===\n\n";

$bookings = Booking::where('client_id', $client->id)
    ->with([
        'assignments.caregiver.user:id,name,email,phone',
        'assignments.caregiver:id,user_id'
    ])
    ->get();

echo "Total Bookings: {$bookings->count()}\n\n";

if ($bookings->count() > 0) {
    echo "--- Bookings Detail ---\n";
    foreach ($bookings as $booking) {
        echo "\nBooking #{$booking->id}:\n";
        echo "  Status: {$booking->status}\n";
        echo "  Service: {$booking->service_type}\n";
        echo "  Date: {$booking->service_date}\n";
        echo "  Borough: {$booking->borough}\n";
        echo "  Duty Type: {$booking->duty_type}\n";
        echo "  Duration: {$booking->duration_days} days\n";
        echo "  Assignments: {$booking->assignments->count()}\n";
        
        if ($booking->assignments->count() > 0) {
            foreach ($booking->assignments as $assignment) {
                $cgUser = $assignment->caregiver?->user;
                echo "    - " . ($cgUser ? $cgUser->name : 'Unknown') . "\n";
            }
        }
    }
}

echo "\n\n--- Status Breakdown ---\n";
echo "Pending: " . $bookings->where('status', 'pending')->count() . "\n";
echo "Approved: " . $bookings->where('status', 'approved')->count() . "\n";
echo "Confirmed: " . $bookings->where('status', 'confirmed')->count() . "\n";
echo "Completed: " . $bookings->where('status', 'completed')->count() . "\n";

// Test what the Vue component should receive
echo "\n\n=== VUE COMPONENT DATA ===\n\n";

$myBookings = $bookings->map(function($booking) {
    return [
        'id' => $booking->id,
        'status' => $booking->status,
        'service_type' => $booking->service_type,
        'service_date' => $booking->service_date,
        'borough' => $booking->borough,
        'duty_type' => $booking->duty_type,
        'duration_days' => $booking->duration_days,
        'assignments' => $booking->assignments->map(function($a) {
            return [
                'caregiver' => [
                    'user' => [
                        'name' => $a->caregiver?->user?->name
                    ]
                ]
            ];
        })
    ];
});

echo "my_bookings array (what Vue receives):\n";
echo json_encode(['my_bookings' => $myBookings], JSON_PRETTY_PRINT);

echo "\n\n--- Filter Test (What confirmedBookings should show) ---\n";
$approvedBookings = $bookings->where('status', 'approved');
echo "Approved bookings count: " . $approvedBookings->count() . "\n";
if ($approvedBookings->count() > 0) {
    echo "IDs: " . $approvedBookings->pluck('id')->implode(', ') . "\n";
}

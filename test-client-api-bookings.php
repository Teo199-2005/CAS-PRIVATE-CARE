<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\User;

echo "=== CLIENT API BOOKINGS TEST ===\n\n";

// Get Demo Client (ID 4)
$client = User::where('email', 'client@demo.com')->first();

if (!$client) {
    echo "Demo client not found!\n";
    exit;
}

echo "Client: {$client->name} (ID: {$client->id})\n\n";

// Simulate what the API returns
$bookings = Booking::where('client_id', $client->id)
    ->with([
        'assignments.caregiver.user:id,name,email,phone',
        'assignments.caregiver:id,user_id'
    ])
    ->get();

echo "Total bookings: {$bookings->count()}\n\n";

foreach ($bookings as $booking) {
    echo "Booking ID: {$booking->id}\n";
    echo "  Status: {$booking->status}\n";
    echo "  Service Type: {$booking->service_type}\n";
    echo "  Service Date: {$booking->service_date}\n";
    echo "  Duration Days: {$booking->duration_days}\n";
    echo "  Borough: {$booking->borough}\n";
    echo "  Duty Type: {$booking->duty_type}\n";
    echo "  Assignments Count: " . $booking->assignments->count() . "\n";
    
    if ($booking->assignments->count() > 0) {
        echo "  Assigned Caregivers:\n";
        foreach ($booking->assignments as $assignment) {
            $caregiver = $assignment->caregiver;
            $user = $caregiver ? $caregiver->user : null;
            echo "    - " . ($user ? $user->name : 'Unknown') . "\n";
        }
    }
    echo "\n";
}

echo "\n=== API RESPONSE SIMULATION ===\n\n";

$apiData = [
    'my_bookings' => $bookings->map(function($booking) {
        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'service_type' => $booking->service_type,
            'service_date' => $booking->service_date,
            'duration_days' => $booking->duration_days,
            'borough' => $booking->borough,
            'duty_type' => $booking->duty_type,
            'assignments' => $booking->assignments->map(function($assignment) {
                return [
                    'id' => $assignment->id,
                    'caregiver' => [
                        'id' => $assignment->caregiver?->id,
                        'user' => [
                            'id' => $assignment->caregiver?->user?->id,
                            'name' => $assignment->caregiver?->user?->name,
                            'email' => $assignment->caregiver?->user?->email,
                            'phone' => $assignment->caregiver?->user?->phone,
                        ]
                    ]
                ];
            })->toArray()
        ];
    })->toArray()
];

echo json_encode($apiData, JSON_PRETTY_PRINT);

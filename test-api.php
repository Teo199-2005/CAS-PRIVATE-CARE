<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the API call directly
$caregiverId = 25;

echo "Testing caregiver stats API for ID: $caregiverId\n\n";

// Check assignments directly
$assignments = \App\Models\BookingAssignment::with(['booking.client'])
    ->where('caregiver_id', $caregiverId)
    ->where('status', 'assigned')
    ->whereHas('booking', function($query) {
        $query->where('status', 'approved');
    })
    ->get();

echo "Found " . $assignments->count() . " assignments\n";

foreach ($assignments as $assignment) {
    echo "Assignment ID: " . $assignment->id . "\n";
    echo "Booking ID: " . $assignment->booking_id . "\n";
    echo "Assignment Status: " . $assignment->status . "\n";
    echo "Booking Status: " . $assignment->booking->status . "\n";
    echo "Client Name: " . $assignment->booking->client->name . "\n";
    echo "Service Date: " . $assignment->booking->service_date . "\n";
    echo "---\n";
}

// Test the actual API response
$caregiver = \App\Models\Caregiver::find($caregiverId);
if ($caregiver) {
    $activeAssignments = $assignments->map(function($assignment) {
        return [
            'id' => $assignment->id,
            'booking' => [
                'id' => $assignment->booking->id,
                'service_type' => $assignment->booking->service_type,
                'status' => $assignment->booking->status,
                'service_date' => $assignment->booking->service_date,
                'client' => [
                    'name' => $assignment->booking->client->name
                ]
            ]
        ];
    });

    echo "\nAPI Response would be:\n";
    echo "Active assignments count: " . $activeAssignments->count() . "\n";
    if ($activeAssignments->count() > 0) {
        echo "First assignment client: " . $activeAssignments->first()['booking']['client']['name'] . "\n";
    }
}
<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the demo caregiver
$caregiver = \App\Models\Caregiver::whereHas('user', function($q) {
    $q->where('email', 'caregiver@demo.com');
})->first();

if ($caregiver) {
    echo "Demo Caregiver found:\n";
    echo "ID: {$caregiver->id}\n";
    echo "User ID: {$caregiver->user_id}\n";
    echo "Name: {$caregiver->user->name}\n";
    echo "Email: {$caregiver->user->email}\n\n";
    
    // Check assignments
    $assignments = \App\Models\BookingAssignment::with(['booking.client'])
        ->where('caregiver_id', $caregiver->id)
        ->get();
        
    echo "Total assignments: " . $assignments->count() . "\n\n";
    
    foreach ($assignments as $assignment) {
        echo "Assignment ID: {$assignment->id}\n";
        echo "Booking ID: {$assignment->booking_id}\n";
        echo "Status: {$assignment->status}\n";
        echo "Booking Status: {$assignment->booking->status}\n";
        echo "Client: {$assignment->booking->client->name}\n";
        echo "Service Date: {$assignment->booking->service_date}\n";
        echo "Duration: {$assignment->booking->duration_days} days\n";
        echo "---\n";
    }
} else {
    echo "Demo caregiver not found\n";
}
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\User;

echo "=== BOOKINGS STATUS CHECK ===\n\n";

// Get Demo Client (ID 1)
$client = User::where('email', 'client@demo.com')->first();
if ($client) {
    echo "Demo Client: {$client->name} (ID: {$client->id})\n\n";
    
    $bookings = Booking::where('client_id', $client->id)->get();
    echo "Total bookings for demo client: " . $bookings->count() . "\n\n";
    
    foreach ($bookings as $booking) {
        echo "Booking ID: {$booking->id}\n";
        echo "  Status: {$booking->status}\n";
        echo "  Service: {$booking->service_type}\n";
        echo "  Date: {$booking->service_date}\n";
        echo "  Created: {$booking->created_at}\n";
        echo "\n";
    }
    
    echo "\n--- BREAKDOWN BY STATUS ---\n";
    echo "Pending: " . $bookings->where('status', 'pending')->count() . "\n";
    echo "Approved: " . $bookings->where('status', 'approved')->count() . "\n";
    echo "Confirmed: " . $bookings->where('status', 'confirmed')->count() . "\n";
    echo "Completed: " . $bookings->where('status', 'completed')->count() . "\n";
    echo "Rejected: " . $bookings->where('status', 'rejected')->count() . "\n";
    echo "Cancelled: " . $bookings->where('status', 'cancelled')->count() . "\n";
} else {
    echo "Demo client not found!\n";
}

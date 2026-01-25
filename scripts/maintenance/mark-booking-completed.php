<?php
/**
 * Mark Booking as Completed
 * This enables the rating/review system
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════\n";
echo "    MARK BOOKING AS COMPLETED         \n";
echo "═══════════════════════════════════════\n\n";

// Get the first booking (or specify booking ID)
$bookingId = isset($argv[1]) ? (int)$argv[1] : null;

if ($bookingId) {
    $booking = App\Models\Booking::find($bookingId);
} else {
    $booking = App\Models\Booking::first();
}

if (!$booking) {
    echo "❌ Booking not found.\n";
    exit(1);
}

echo "Found booking #{$booking->id}:\n";
echo "  Client: {$booking->client->name}\n";
echo "  Service: {$booking->service_type}\n";
echo "  Current Status: {$booking->status}\n";
echo "  Payment Status: {$booking->payment_status}\n\n";

if ($booking->status === 'completed') {
    echo "ℹ️  Booking is already marked as completed.\n\n";
} else {
    $booking->update(['status' => 'completed']);
    echo "✅ Booking marked as COMPLETED!\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎯 WHAT'S NEXT:\n\n";

echo "1. Login as CLIENT:\n";
echo "   URL: http://127.0.0.1:8000/client/dashboard\n";
echo "   Email: {$booking->client->email}\n";
echo "   Password: password\n\n";

echo "2. Go to 'My Bookings' section\n\n";

echo "3. You'll now see a 'Rate Service' button on booking #{$booking->id}\n\n";

echo "4. Click it to rate the assigned caregivers:\n";
$assignments = App\Models\BookingAssignment::where('booking_id', $booking->id)->with('caregiver.user')->get();
foreach ($assignments as $assignment) {
    echo "   - {$assignment->caregiver->user->name}\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

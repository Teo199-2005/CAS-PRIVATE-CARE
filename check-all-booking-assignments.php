<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\BookingAssignment;

echo "=== ALL BOOKINGS WITH ASSIGNMENTS ===\n\n";

$bookings = Booking::with(['client', 'assignments.caregiver.user'])
    ->orderBy('id', 'desc')
    ->get();

foreach ($bookings as $booking) {
    $caregiversNeeded = max(1, ceil($booking->duration_days / 15));
    $assignedCount = $booking->assignments->where('status', 'assigned')->count();
    $spotsRemaining = $caregiversNeeded - $assignedCount;
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📋 Booking #{$booking->id}\n";
    echo "   Client: " . ($booking->client->name ?? 'N/A') . "\n";
    echo "   Service: {$booking->service_type}\n";
    echo "   Duration: {$booking->duration_days} days\n";
    echo "   Status: {$booking->status}\n";
    echo "   Duty Type: {$booking->duty_type}\n\n";
    
    echo "🔢 Assignment Status:\n";
    echo "   Caregivers Needed: {$caregiversNeeded} (formula: ceil({$booking->duration_days}/15))\n";
    echo "   Currently Assigned: {$assignedCount}\n";
    echo "   Spots Remaining: {$spotsRemaining}\n\n";
    
    if ($booking->assignments->count() > 0) {
        echo "👥 Assigned Caregivers:\n";
        foreach ($booking->assignments as $assignment) {
            $caregiverName = $assignment->caregiver && $assignment->caregiver->user 
                ? $assignment->caregiver->user->name 
                : 'Unknown';
            echo "   - {$caregiverName} (Status: {$assignment->status})\n";
        }
    } else {
        echo "👥 No caregivers assigned yet\n";
    }
    
    echo "\n✅ Display Should Show:\n";
    echo "   \"{$spotsRemaining} of {$caregiversNeeded} spots open\"\n";
    echo "\n";
}

?>

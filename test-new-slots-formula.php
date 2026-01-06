<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== TESTING NEW CAREGIVER SLOTS FORMULA ===\n\n";
echo "📋 New Logic:\n";
echo "   • 8 hours/day or less  = 1 caregiver needed\n";
echo "   • 9-12 hours/day       = 2 caregivers needed\n";
echo "   • More than 12 hours/day = 3 caregivers needed\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$bookings = Booking::with(['client', 'assignments.caregiver.user'])
    ->orderBy('id', 'desc')
    ->get();

foreach ($bookings as $booking) {
    // Extract hours per day
    $hoursPerDay = 8;
    if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
        $hoursPerDay = (int)$matches[1];
    }
    
    // Calculate caregivers needed based on NEW formula
    if ($hoursPerDay <= 8) {
        $caregiversNeeded = 1;
    } elseif ($hoursPerDay <= 12) {
        $caregiversNeeded = 2;
    } else {
        $caregiversNeeded = 3;
    }
    
    $assignedCount = $booking->assignments->where('status', 'assigned')->count();
    $spotsRemaining = $caregiversNeeded - $assignedCount;
    
    echo "📋 Booking #{$booking->id}\n";
    echo "   Client: " . ($booking->client->name ?? 'N/A') . "\n";
    echo "   Duty Type: {$booking->duty_type}\n";
    echo "   Hours/Day: {$hoursPerDay}\n";
    echo "   Duration: {$booking->duration_days} days\n";
    echo "   Status: {$booking->status}\n\n";
    
    echo "🔢 Caregiver Calculation (NEW):\n";
    echo "   Hours/Day: {$hoursPerDay} → Caregivers Needed: {$caregiversNeeded}\n";
    echo "   Currently Assigned: {$assignedCount}\n";
    echo "   Spots Remaining: {$spotsRemaining}\n\n";
    
    if ($booking->assignments->count() > 0) {
        echo "👥 Assigned:\n";
        foreach ($booking->assignments as $assignment) {
            $name = $assignment->caregiver && $assignment->caregiver->user 
                ? $assignment->caregiver->user->name 
                : 'Unknown';
            echo "   - {$name} ({$assignment->status})\n";
        }
    } else {
        echo "👥 No caregivers assigned\n";
    }
    
    echo "\n✅ Display Will Show:\n";
    echo "   \"{$spotsRemaining} of {$caregiversNeeded} spots open\"\n";
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
}

?>

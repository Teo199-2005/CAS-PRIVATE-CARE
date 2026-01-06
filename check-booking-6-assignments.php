<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\BookingAssignment;

echo "=== CHECKING BOOKING #6 ASSIGNMENTS ===\n\n";

$booking = Booking::with(['client', 'assignments.caregiver.user'])->find(6);

if (!$booking) {
    echo "❌ Booking #6 not found\n";
    exit;
}

echo "📋 Booking #6 Details:\n";
echo "   Client: " . ($booking->client->name ?? 'N/A') . "\n";
echo "   Service Type: {$booking->service_type}\n";
echo "   Duration: {$booking->duration_days} days\n";
echo "   Status: {$booking->status}\n\n";

// Calculate caregivers needed (same logic as controller)
$caregiversNeeded = max(1, ceil($booking->duration_days / 15));
echo "🔢 Caregivers Calculation:\n";
echo "   Formula: ceil({$booking->duration_days} / 15) = {$caregiversNeeded}\n";
echo "   Caregivers Needed: {$caregiversNeeded}\n\n";

// Get assignments
$assignments = $booking->assignments;
$assignedCount = $assignments->where('status', 'assigned')->count();

echo "👥 Current Assignments:\n";
echo "   Total assignments: " . $assignments->count() . "\n";
echo "   Assigned (active): {$assignedCount}\n";
echo "   Spots Remaining: " . ($caregiversNeeded - $assignedCount) . "\n\n";

if ($assignments->count() > 0) {
    echo "📌 Assignment Details:\n";
    foreach ($assignments as $assignment) {
        $caregiverName = $assignment->caregiver && $assignment->caregiver->user 
            ? $assignment->caregiver->user->name 
            : 'Unknown';
        echo "   - {$caregiverName} (Status: {$assignment->status})\n";
    }
} else {
    echo "   No assignments found\n";
}

echo "\n";
echo "✅ Expected Display:\n";
echo "   \"{$assignedCount} of {$caregiversNeeded} spots open\"\n";
echo "   OR\n";
echo "   \"" . ($caregiversNeeded - $assignedCount) . " of {$caregiversNeeded} spots open\"\n";

?>

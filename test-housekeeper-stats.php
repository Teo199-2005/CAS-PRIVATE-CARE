<?php
/**
 * Quick test for housekeeper stats API
 */
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\HousekeeperController;

$housekeeperId = 1;

echo "Testing housekeeper stats for ID: $housekeeperId\n\n";

$ctrl = new HousekeeperController();
$response = $ctrl->stats($housekeeperId);
$data = json_decode($response->getContent(), true);

echo "Response:\n";
echo json_encode($data, JSON_PRETTY_PRINT);
echo "\n\n";

echo "Active assignments count: " . count($data['active_assignments'] ?? []) . "\n";

if (count($data['active_assignments'] ?? []) > 0) {
    echo "First assignment client: " . ($data['active_assignments'][0]['booking']['client']['name'] ?? 'Unknown') . "\n";
} else {
    echo "No active assignments found!\n";
    
    // Debug - check why
    echo "\nDebug - checking raw query:\n";
    $today = now()->toDateString();
    echo "Today's date: $today\n";
    
    $assignments = \App\Models\BookingHousekeeperAssignment::with(['booking.client'])
        ->where('housekeeper_id', $housekeeperId)
        ->where('status', 'assigned')
        ->get();
    
    echo "All assignments with status=assigned: " . $assignments->count() . "\n";
    
    foreach ($assignments as $a) {
        echo "  - Booking ID: {$a->booking_id}, Booking Status: {$a->booking->status}, Service Date: {$a->booking->service_date}, Duration: {$a->booking->duration_days}\n";
        
        // Calculate end date
        $serviceDate = $a->booking->service_date;
        $durationDays = $a->booking->duration_days ?? 1;
        $endDate = \Carbon\Carbon::parse($serviceDate)->addDays($durationDays)->format('Y-m-d');
        echo "    Service Date: $serviceDate, End Date: $endDate, Today: $today\n";
        echo "    Is Active: " . ($serviceDate <= $today && $endDate >= $today ? 'Yes' : 'No') . "\n";
        echo "    Is Upcoming: " . ($serviceDate > $today ? 'Yes' : 'No') . "\n";
    }
}

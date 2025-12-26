<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

echo "=== Testing Client Stats API ===\n\n";

$controller = new DashboardController();
$response = $controller->clientStats();
$data = json_decode($response->getContent(), true);

echo "Amount Due: $" . number_format($data['amount_due'] ?? 0) . "\n";
echo "This Month Due: $" . number_format($data['this_month_amount_due'] ?? 0) . "\n";
echo "Coverage Start: " . ($data['coverage_start'] ?? 'N/A') . "\n";
echo "Coverage End: " . ($data['coverage_end'] ?? 'N/A') . "\n";
echo "Ongoing Contracts: " . ($data['ongoing_contracts'] ?? 0) . "\n";
echo "Active Bookings: " . ($data['active_bookings'] ?? 0) . "\n";
echo "Total Spent: $" . number_format($data['total_spent'] ?? 0) . "\n";
echo "Total Bookings: " . ($data['total_bookings'] ?? 0) . "\n";
echo "\nDebug Info:\n";
print_r($data['debug'] ?? []);

echo "\nActive Bookings List:\n";
$myBookings = $data['my_bookings'] ?? [];
foreach ($myBookings as $b) {
    if (in_array($b['status'], ['approved', 'confirmed', 'in_progress'])) {
        $hoursMatch = [];
        preg_match('/(\d+)/', $b['duty_type'], $hoursMatch);
        $hours = $hoursMatch[1] ?? 8;
        echo "  - #{$b['id']}: {$b['service_type']}, {$hours}hr/day x {$b['duration_days']} days = " . ($hours * $b['duration_days']) . " hours\n";
    }
}

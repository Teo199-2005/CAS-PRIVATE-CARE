<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking Client Stats ===\n\n";

// Get approved bookings
$approvedBookings = Booking::whereIn('status', ['approved', 'confirmed', 'in_progress'])->get();

echo "Approved/Active Bookings: " . $approvedBookings->count() . "\n\n";

$totalDue = 0;
foreach ($approvedBookings as $booking) {
    // Extract hours from duty type
    if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
        $hours = (int)$matches[1];
    } else {
        $hours = 8;
    }
    
    $rate = $booking->hourly_rate ?: 45;
    $total = $hours * $booking->duration_days * $rate;
    $totalDue += $total;
    
    $endDate = date('Y-m-d', strtotime($booking->service_date . ' + ' . $booking->duration_days . ' days'));
    
    echo "Booking #{$booking->id}:\n";
    echo "  - Client ID: {$booking->client_id}\n";
    echo "  - Service: {$booking->service_type}\n";
    echo "  - Duty Type: {$booking->duty_type} ({$hours} hours/day)\n";
    echo "  - Date: {$booking->service_date} to {$endDate}\n";
    echo "  - Duration: {$booking->duration_days} days\n";
    echo "  - Rate: \${$rate}/hr\n";
    echo "  - Total: \$" . number_format($total) . "\n\n";
}

echo "Total Amount Due: \$" . number_format($totalDue) . "\n";

// Get date range
if ($approvedBookings->count() > 0) {
    $coverageStart = $approvedBookings->min('service_date');
    $coverageEnd = $approvedBookings->max(function($b) {
        return date('Y-m-d', strtotime($b->service_date . ' + ' . $b->duration_days . ' days'));
    });
    echo "Coverage: {$coverageStart} to {$coverageEnd}\n";
}

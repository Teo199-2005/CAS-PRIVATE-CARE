<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        📊 CLIENT DASHBOARD DATA CHECK 📊\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get client ID from booking
$booking = DB::table('bookings')->find(2);
$clientId = $booking->client_id;

echo "🔍 CLIENT ID: $clientId\n\n";

// Get all bookings for this client
$bookings = DB::table('bookings')
    ->where('client_id', $clientId)
    ->get();

echo "📋 ALL BOOKINGS FOR CLIENT #$clientId:\n";
echo "────────────────────────────────────────────────────────────────\n";

$totalPaid = 0;
$totalPending = 0;

foreach ($bookings as $booking) {
    $amount = $booking->duration_days * 24 * $booking->hourly_rate;
    
    echo "Booking #{$booking->id}:\n";
    echo "  Service Date: {$booking->service_date}\n";
    echo "  Duration: {$booking->duration_days} days x 24 hrs x \${$booking->hourly_rate}/hr\n";
    echo "  Total Amount: \$" . number_format($amount, 2) . "\n";
    echo "  Status: {$booking->status}\n";
    echo "  Payment Status: {$booking->payment_status}\n";
    
    if ($booking->payment_status === 'paid') {
        $totalPaid += $amount;
        echo "  ✅ PAID\n";
    } else {
        $totalPending += $amount;
        echo "  ⏳ PENDING\n";
    }
    echo "\n";
}

echo "────────────────────────────────────────────────────────────────\n";
echo "💰 TOTALS:\n";
echo "  Total Paid: \$" . number_format($totalPaid, 2) . "\n";
echo "  Total Pending: \$" . number_format($totalPending, 2) . "\n";
echo "  Grand Total: \$" . number_format($totalPaid + $totalPending, 2) . "\n";

echo "\n═══════════════════════════════════════════════════════════════\n";

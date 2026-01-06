<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        📋 ALL BOOKINGS CHECK 📋\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$bookings = DB::table('bookings')->orderBy('id', 'desc')->get();

foreach ($bookings as $b) {
    echo "────────────────────────────────────────────────────────────────\n";
    echo "BOOKING #{$b->id}\n";
    echo "  Client ID: {$b->client_id}\n";
    echo "  Duration: {$b->duration_days} days\n";
    echo "  Rate: \${$b->hourly_rate}/hr\n";
    echo "  Status: {$b->status}\n";
    echo "  Payment Status: {$b->payment_status}\n";
    echo "  Stripe Payment Intent: " . ($b->stripe_payment_intent_id ?: 'N/A') . "\n";
    echo "  Payment Date: " . ($b->payment_date ?: 'N/A') . "\n";
    echo "  Created: {$b->created_at}\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "        💳 ALL PAYMENTS CHECK 💳\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$payments = DB::table('payments')->orderBy('id', 'desc')->get();

if ($payments->isEmpty()) {
    echo "❌ No payment records found!\n";
} else {
    foreach ($payments as $p) {
        echo "────────────────────────────────────────────────────────────────\n";
        echo "PAYMENT #{$p->id}\n";
        echo "  Booking ID: {$p->booking_id}\n";
        echo "  Client ID: {$p->client_id}\n";
        echo "  Amount: \$" . number_format($p->amount, 2) . "\n";
        echo "  Status: {$p->status}\n";
        echo "  Transaction ID: " . ($p->transaction_id ?: 'N/A') . "\n";
        echo "  Created: {$p->created_at}\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";

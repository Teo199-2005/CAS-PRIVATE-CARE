<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\Booking;

echo "=== STRIPE PAYMENT RECORDS CHECK ===\n\n";

$payments = Payment::with(['booking.client'])->get();

if ($payments->isEmpty()) {
    echo "❌ No payments found in database\n";
    exit;
}

echo "Total Payments: " . $payments->count() . "\n";
echo "Total Amount: $" . $payments->sum('amount') . "\n\n";

$withStripe = 0;
$withoutStripe = 0;

foreach ($payments as $payment) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Payment ID: {$payment->id}\n";
    
    if ($payment->booking && $payment->booking->client) {
        echo "Client: {$payment->booking->client->name}\n";
    } else {
        echo "Client: N/A\n";
    }
    
    echo "Amount: \${$payment->amount}\n";
    echo "Status: {$payment->status}\n";
    echo "Payment Method: " . ($payment->payment_method ?? 'NOT SET') . "\n";
    
    if ($payment->stripe_payment_intent_id) {
        echo "✅ Stripe Payment Intent: {$payment->stripe_payment_intent_id}\n";
        $withStripe++;
    } else {
        echo "❌ Stripe Payment Intent: NOT PROCESSED THROUGH STRIPE\n";
        $withoutStripe++;
    }
    
    if ($payment->stripe_customer_id) {
        echo "Stripe Customer ID: {$payment->stripe_customer_id}\n";
    }
    
    echo "Created: {$payment->created_at}\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "SUMMARY:\n";
echo "✅ Payments processed through Stripe: {$withStripe}\n";
echo "❌ Payments NOT processed through Stripe: {$withoutStripe}\n\n";

if ($withoutStripe > 0) {
    echo "⚠️  WARNING: You have {$withoutStripe} payments in your database that were NOT processed through Stripe.\n";
    echo "This is why your Stripe dashboard shows $0.00\n\n";
    echo "These are likely test/demo data that was inserted directly into the database.\n";
    echo "Real payments must go through the Stripe API to appear in your Stripe dashboard.\n";
}

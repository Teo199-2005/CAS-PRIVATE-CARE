<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 All Payment Records for Booking #17\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$payments = \App\Models\Payment::where('booking_id', 17)->get();

if ($payments->isEmpty()) {
    echo "❌ No payment records found for booking #17\n";
    exit(1);
}

echo "Found {$payments->count()} payment record(s):\n\n";

foreach ($payments as $payment) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Payment ID: {$payment->id}\n";
    echo "Amount: $" . number_format($payment->amount, 2) . "\n";
    echo "Processing Fee: $" . number_format($payment->processing_fee ?? 0, 2) . "\n";
    echo "Total: $" . number_format($payment->amount + ($payment->processing_fee ?? 0), 2) . "\n";
    echo "Status: {$payment->status}\n";
    echo "Transaction ID: {$payment->transaction_id}\n";
    echo "Paid At: {$payment->paid_at}\n";
    echo "Created At: {$payment->created_at}\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 Checking Stripe Transaction: pi_3Snx5Z1lG4GuXd6q0eCOhaJ4\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    $paymentIntent = $stripe->paymentIntents->retrieve('pi_3Snx5Z1lG4GuXd6q0eCOhaJ4');
    
    $amountCharged = $paymentIntent->amount / 100; // Convert from cents
    $currency = strtoupper($paymentIntent->currency);
    
    echo "✅ Stripe Payment Intent Found:\n";
    echo "   Amount Charged: $" . number_format($amountCharged, 2) . " {$currency}\n";
    echo "   Status: {$paymentIntent->status}\n";
    echo "   Created: " . date('Y-m-d H:i:s', $paymentIntent->created) . "\n\n";
    
    // Calculate the processing fee based on what Stripe charged
    $targetAmount = 5400.00; // The service cost
    $stripeFee = 0.029; // 2.9%
    $fixedFee = 0.30;
    
    $adjustedTotal = ($targetAmount + $fixedFee) / (1 - $stripeFee);
    $calculatedFee = $adjustedTotal - $targetAmount;
    
    echo "📊 Expected Breakdown:\n";
    echo "   Service Cost: $" . number_format($targetAmount, 2) . "\n";
    echo "   Processing Fee: $" . number_format($calculatedFee, 2) . "\n";
    echo "   Total Charged: $" . number_format($adjustedTotal, 2) . "\n\n";
    
    if (abs($amountCharged - $adjustedTotal) < 0.50) {
        echo "✅ Stripe amount matches expected total!\n";
        echo "   Processing fee should be: $" . number_format($calculatedFee, 2) . "\n\n";
    } else {
        echo "⚠️  Mismatch detected:\n";
        echo "   Stripe charged: $" . number_format($amountCharged, 2) . "\n";
        echo "   Expected total: $" . number_format($adjustedTotal, 2) . "\n";
        echo "   Difference: $" . number_format(abs($amountCharged - $adjustedTotal), 2) . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error retrieving Stripe payment: " . $e->getMessage() . "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

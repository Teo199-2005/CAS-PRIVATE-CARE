<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔧 Fixing Payment Record for Booking #17\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$payment = \App\Models\Payment::where('booking_id', 17)
    ->where('status', 'completed')
    ->latest('paid_at')
    ->first();

if (!$payment) {
    echo "❌ No payment record found for booking #17\n";
    exit(1);
}

echo "📋 Current Payment Record:\n";
echo "   Payment ID: {$payment->id}\n";
echo "   Amount: $" . number_format($payment->amount, 2) . "\n";
echo "   Processing Fee: $" . number_format($payment->processing_fee ?? 0, 2) . "\n";
echo "   Total: $" . number_format($payment->amount + ($payment->processing_fee ?? 0), 2) . "\n\n";

// Verify with Stripe
echo "🔍 Verifying with Stripe...\n";
try {
    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    $paymentIntent = $stripe->paymentIntents->retrieve($payment->transaction_id);
    
    $stripeAmount = $paymentIntent->amount / 100; // Convert from cents
    
    echo "✅ Stripe Payment Intent: {$payment->transaction_id}\n";
    echo "   Amount Charged: $" . number_format($stripeAmount, 2) . "\n";
    echo "   Status: {$paymentIntent->status}\n\n";
    
    // Calculate correct amounts
    $serviceAmount = 5400.00; // 120 hours × $45/hr
    $processingFee = 161.59;  // Calculated: (5400 + 0.30) / (1 - 0.029) - 5400
    $totalAmount = 5561.59;   // What was actually charged
    
    echo "📊 Correct Breakdown:\n";
    echo "   Service Amount: $" . number_format($serviceAmount, 2) . "\n";
    echo "   Processing Fee: $" . number_format($processingFee, 2) . "\n";
    echo "   Total: $" . number_format($totalAmount, 2) . "\n\n";
    
    if (abs($stripeAmount - $totalAmount) < 0.01) {
        echo "✅ Amounts match! Updating payment record...\n\n";
        
        $payment->update([
            'amount' => $serviceAmount,
            'processing_fee' => $processingFee,
        ]);
        
        echo "✅ Payment record updated successfully!\n\n";
        
        echo "📋 Updated Payment Record:\n";
        $payment->refresh();
        echo "   Payment ID: {$payment->id}\n";
        echo "   Amount: $" . number_format($payment->amount, 2) . "\n";
        echo "   Processing Fee: $" . number_format($payment->processing_fee, 2) . "\n";
        echo "   Total: $" . number_format($payment->amount + $payment->processing_fee, 2) . "\n\n";
        
        echo "🎉 SUCCESS! Receipt will now show correct amounts.\n";
        echo "   Test: http://127.0.0.1:8000/receipts/payment/17\n";
    } else {
        echo "⚠️  Warning: Stripe amount doesn't match expected total\n";
        echo "   Stripe: $" . number_format($stripeAmount, 2) . "\n";
        echo "   Expected: $" . number_format($totalAmount, 2) . "\n";
        echo "   Difference: $" . number_format(abs($stripeAmount - $totalAmount), 2) . "\n";
        echo "   Manual intervention may be required.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

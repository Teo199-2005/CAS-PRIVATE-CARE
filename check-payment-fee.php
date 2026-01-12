<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 Checking Payment Record for Booking #17\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$payment = \App\Models\Payment::where('booking_id', 17)
    ->where('status', 'completed')
    ->latest('paid_at')
    ->first();

if (!$payment) {
    echo "❌ No payment record found for booking #17\n";
    exit(1);
}

echo "✅ Payment Record Found:\n";
echo "   Payment ID: {$payment->id}\n";
echo "   Booking ID: {$payment->booking_id}\n";
echo "   Client ID: {$payment->client_id}\n";
echo "   Amount: $" . number_format($payment->amount, 2) . "\n";
echo "   Processing Fee: $" . number_format($payment->processing_fee ?? 0, 2) . "\n";
echo "   Total: $" . number_format($payment->amount + ($payment->processing_fee ?? 0), 2) . "\n";
echo "   Status: {$payment->status}\n";
echo "   Payment Method: {$payment->payment_method}\n";
echo "   Transaction ID: {$payment->transaction_id}\n";
echo "   Paid At: {$payment->paid_at}\n\n";

if (!$payment->processing_fee || $payment->processing_fee <= 0) {
    echo "⚠️  WARNING: Processing fee is missing or zero!\n";
    echo "   This payment was likely created before the processing fee column was added.\n\n";
    
    // Calculate what the processing fee should be
    $targetAmount = $payment->amount;
    $stripeFee = 0.029; // 2.9%
    $fixedFee = 0.30;
    
    $adjustedTotal = ($targetAmount + $fixedFee) / (1 - $stripeFee);
    $calculatedFee = $adjustedTotal - $targetAmount;
    
    echo "💡 Calculated Processing Fee: $" . number_format($calculatedFee, 2) . "\n";
    echo "   Total that should have been charged: $" . number_format($adjustedTotal, 2) . "\n\n";
    
    echo "🔧 Would you like to update this payment record? (Y/N)\n";
    echo "   This will set processing_fee = $" . number_format($calculatedFee, 2) . "\n";
    echo "   Run: php update-payment-fee.php 17 " . round($calculatedFee, 2) . "\n";
} else {
    echo "✅ Processing fee is present in the database!\n";
    echo "   Receipt should display correctly.\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

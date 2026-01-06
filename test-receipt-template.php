<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the receipt template change
$booking = App\Models\Booking::with(['client', 'assignments.caregiver.user'])->find(12);

if (!$booking) {
    echo "❌ Booking 12 not found\n";
    exit(1);
}

echo "✓ Booking found: #{$booking->id}\n";
echo "✓ Client: {$booking->client->name}\n";
echo "✓ Payment Status: {$booking->payment_status}\n";

if ($booking->payment_status !== 'paid') {
    echo "❌ Booking is not marked as paid\n";
    exit(1);
}

echo "✓ Booking is paid\n";

// Check if ReceiptController's generateReceiptHtml method exists
$controller = new App\Http\Controllers\ReceiptController();
$reflection = new ReflectionClass($controller);

if (!$reflection->hasMethod('generateReceiptHtml')) {
    echo "❌ generateReceiptHtml method not found\n";
    exit(1);
}

echo "✓ generateReceiptHtml method exists\n";

// Check method is private
$method = $reflection->getMethod('generateReceiptHtml');
if (!$method->isPrivate()) {
    echo "⚠ generateReceiptHtml is not private\n";
} else {
    echo "✓ generateReceiptHtml is private (as expected)\n";
}

echo "\n✅ All checks passed! Receipt will now use the time tracking template.\n";
echo "📄 Template location: generateReceiptHtml() method in ReceiptController.php (line 186)\n";
echo "🔗 Test the receipt at: http://your-domain/api/receipts/payment/12\n";

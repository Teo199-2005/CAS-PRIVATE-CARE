<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Admin API Payment Status ===\n\n";

// Simulate API request
$controller = new \App\Http\Controllers\AdminController();
$response = $controller->getAllBookings();
$data = json_decode($response->getContent(), true);

if (!$data['success']) {
    echo "❌ API request failed\n";
    exit(1);
}

$bookings = $data['data'];
$booking12 = collect($bookings)->firstWhere('id', 12);

if (!$booking12) {
    echo "❌ Booking #12 not found in API response\n";
    exit(1);
}

echo "✅ API Response for Booking #12:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Client:         " . ($booking12['client']['name'] ?? 'N/A') . "\n";
echo "Status:         " . ($booking12['status'] ?? 'N/A') . "\n";
echo "Payment Status: " . ($booking12['payment_status'] ?? '❌ MISSING') . "\n";
echo "Payment Intent: " . ($booking12['stripe_payment_intent_id'] ?? 'N/A') . "\n";
echo "Payment Date:   " . ($booking12['payment_date'] ?? 'N/A') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (isset($booking12['payment_status']) && $booking12['payment_status'] === 'paid') {
    echo "🎉 SUCCESS! API now returns payment_status = 'paid'\n";
    echo "✅ Admin dashboard will show green 'Paid' chip\n\n";
    
    echo "📋 WHAT CHANGED:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "File: AdminController.php → getAllBookings()\n";
    echo "Added:\n";
    echo "  - 'payment_status' => \$b->payment_status\n";
    echo "  - 'stripe_payment_intent_id' => \$b->stripe_payment_intent_id\n";
    echo "  - 'payment_date' => \$b->payment_date\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
} else {
    echo "⚠️  Payment status: " . ($booking12['payment_status'] ?? 'MISSING') . "\n";
    echo "Expected: 'paid'\n";
}

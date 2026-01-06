<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Dashboard Updates After Payment ===\n\n";

// Get John Doe's bookings
$client = App\Models\User::where('email', 'client@demo.com')->first();

if (!$client) {
    echo "❌ Client not found\n";
    exit(1);
}

echo "✓ Testing for client: {$client->name} (ID: {$client->id})\n\n";

// Get booking #12
$booking12 = App\Models\Booking::with(['client', 'assignments.caregiver.user'])->find(12);

if (!$booking12) {
    echo "❌ Booking #12 not found\n";
    exit(1);
}

echo "📋 BOOKING #12 DETAILS:\n";
echo "   Status: {$booking12->status}\n";
echo "   Payment Status: {$booking12->payment_status}\n";
echo "   Service Date: {$booking12->service_date}\n";
echo "   Duration: {$booking12->duration_days} days\n";
echo "   Duty Type: {$booking12->duty_type}\n";
echo "   Hourly Rate: \${$booking12->hourly_rate}\n";

// Extract hours
preg_match('/(\d+)/', $booking12->duty_type, $matches);
$hoursPerDay = isset($matches[1]) ? (int)$matches[1] : 8;
$totalHours = $booking12->duration_days * $hoursPerDay;
$totalAmount = $totalHours * $booking12->hourly_rate;

echo "   Total Hours: {$totalHours}\n";
echo "   Total Amount: \${$totalAmount}\n\n";

// Test the dashboard controller logic
$controller = new App\Http\Controllers\DashboardController();

// Create a mock request with client_id
$request = new \Illuminate\Http\Request();
$request->merge(['client_id' => $client->id]);

// Call clientStats
$response = $controller->clientStats($request);
$data = json_decode($response->getContent(), true);

echo "📊 DASHBOARD STATS:\n\n";

echo "1️⃣  AMOUNT DUE:\n";
echo "   Current: \${$data['amount_due']}\n";
echo "   Expected: \$0 (because booking #12 is paid)\n";
if ($data['amount_due'] == 0) {
    echo "   ✅ CORRECT - Amount due excludes paid bookings\n\n";
} else {
    echo "   ❌ WRONG - Amount due should be \$0\n\n";
}

echo "2️⃣  TOTAL SPENT:\n";
echo "   Current: \${$data['total_spent']}\n";
echo "   Expected: \${$totalAmount} (should include paid booking #12)\n";
if ($data['total_spent'] >= $totalAmount) {
    echo "   ✅ CORRECT - Total spent includes paid bookings\n\n";
} else {
    echo "   ❌ WRONG - Total spent should include paid booking #12\n\n";
}

echo "3️⃣  TOTAL HOURS:\n";
echo "   Current: {$data['total_hours']} hours\n";
echo "   Expected: {$totalHours} hours (should include paid booking #12)\n";
if ($data['total_hours'] >= $totalHours) {
    echo "   ✅ CORRECT - Total hours includes paid bookings\n\n";
} else {
    echo "   ❌ WRONG - Total hours should include paid booking #12\n\n";
}

echo "4️⃣  MY BOOKINGS:\n";
$myBooking = collect($data['my_bookings'])->firstWhere('id', 12);
if ($myBooking) {
    echo "   Booking #12 found in response\n";
    echo "   Payment Status: {$myBooking['payment_status']}\n";
    if ($myBooking['payment_status'] === 'paid') {
        echo "   ✅ CORRECT - Booking shows as paid\n\n";
    } else {
        echo "   ❌ WRONG - Booking should show as paid\n\n";
    }
} else {
    echo "   ❌ Booking #12 not found in my_bookings\n\n";
}

echo "\n=== SUMMARY ===\n";
echo "Amount Due: " . ($data['amount_due'] == 0 ? '✅' : '❌') . "\n";
echo "Total Spent: " . ($data['total_spent'] >= $totalAmount ? '✅' : '❌') . "\n";
echo "Total Hours: " . ($data['total_hours'] >= $totalHours ? '✅' : '❌') . "\n";
echo "Payment Status: " . (($myBooking && $myBooking['payment_status'] === 'paid') ? '✅' : '❌') . "\n";

$allPassed = ($data['amount_due'] == 0) && 
             ($data['total_spent'] >= $totalAmount) && 
             ($data['total_hours'] >= $totalHours) && 
             ($myBooking && $myBooking['payment_status'] === 'paid');

if ($allPassed) {
    echo "\n🎉 ALL TESTS PASSED! Dashboard will update correctly after payment.\n";
} else {
    echo "\n⚠️  Some tests failed. Review the output above.\n";
}

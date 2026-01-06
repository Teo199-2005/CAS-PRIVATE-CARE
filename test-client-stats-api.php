<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 TESTING /api/client/stats ENDPOINT\n";
echo "========================================\n\n";

// Authenticate as client
$user = App\Models\User::find(4);
if (!$user) {
    die("❌ User ID 4 not found\n");
}

Auth::login($user);

// Test the DashboardController
$controller = new App\Http\Controllers\DashboardController();
$request = Illuminate\Http\Request::create('/api/client/stats?client_id=4', 'GET');
$request->setUserResolver(function() use ($user) {
    return $user;
});

$response = $controller->clientStats($request);
$data = $response->getData(true);

echo "✅ API Response received\n\n";

if (isset($data['my_bookings'])) {
    echo "📦 my_bookings array found (" . count($data['my_bookings']) . " bookings)\n\n";
    
    // Find booking #12
    $booking12 = null;
    foreach ($data['my_bookings'] as $booking) {
        if ($booking['id'] == 12) {
            $booking12 = $booking;
            break;
        }
    }
    
    if ($booking12) {
        echo "🎯 BOOKING #12 FOUND:\n";
        echo "   ID: {$booking12['id']}\n";
        echo "   Status: {$booking12['status']}\n";
        echo "   Assignment Status: {$booking12['assignment_status']}\n";
        echo "   Payment Status: " . (isset($booking12['payment_status']) ? $booking12['payment_status'] : '❌ MISSING') . "\n";
        echo "   Payment Intent ID: " . (isset($booking12['stripe_payment_intent_id']) ? $booking12['stripe_payment_intent_id'] : '❌ MISSING') . "\n";
        echo "   Payment Date: " . (isset($booking12['payment_date']) ? $booking12['payment_date'] : '❌ MISSING') . "\n\n";
        
        echo "📋 ALL FIELDS IN BOOKING #12:\n";
        foreach ($booking12 as $key => $value) {
            if (!in_array($key, ['assignments', 'assigned_caregiver', 'client'])) {
                $displayValue = is_array($value) ? json_encode($value) : (is_string($value) ? $value : json_encode($value));
                if (strlen($displayValue) > 50) {
                    $displayValue = substr($displayValue, 0, 50) . '...';
                }
                echo "   - $key: $displayValue\n";
            }
        }
        
        echo "\n\n🔍 CRITICAL CHECK:\n";
        $hasPaymentStatus = isset($booking12['payment_status']);
        $paymentStatusValue = $hasPaymentStatus ? $booking12['payment_status'] : 'NOT SET';
        $shouldShowReceipt = ($hasPaymentStatus && $booking12['payment_status'] === 'paid');
        
        echo "   Has payment_status field? " . ($hasPaymentStatus ? '✅ YES' : '❌ NO') . "\n";
        echo "   Payment status value: '$paymentStatusValue'\n";
        echo "   Should show 'View Receipt' button? " . ($shouldShowReceipt ? '✅ YES' : '❌ NO') . "\n";
        
        if (!$shouldShowReceipt) {
            echo "\n⚠️  PROBLEM IDENTIFIED:\n";
            if (!$hasPaymentStatus) {
                echo "   The payment_status field is NOT in the API response!\n";
                echo "   Vue component checks: booking.payment_status === 'paid'\n";
                echo "   But field doesn't exist, so condition is always FALSE\n";
            } else {
                echo "   Payment status is '$paymentStatusValue', not 'paid'\n";
            }
        }
    } else {
        echo "❌ Booking #12 NOT FOUND in my_bookings array\n";
    }
} else {
    echo "❌ No 'my_bookings' key in response\n";
    echo "Available keys: " . implode(', ', array_keys($data)) . "\n";
}

echo "\n========================================\n";

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DEBUGGING DASHBOARD ISSUE\n";
echo "================================\n\n";

// 1. Check database
echo "1️⃣ DATABASE CHECK:\n";
$booking = App\Models\Booking::find(12);
echo "   Booking ID: {$booking->id}\n";
echo "   Payment Status: " . ($booking->payment_status ?? 'NULL') . "\n";
echo "   Status: {$booking->status}\n";
echo "   Assignment Status: {$booking->assignment_status}\n\n";

// 2. Check if payment_status is in fillable
echo "2️⃣ MODEL FILLABLE CHECK:\n";
$fillable = $booking->getFillable();
echo "   Is 'payment_status' fillable? " . (in_array('payment_status', $fillable) ? 'YES ✅' : 'NO ❌') . "\n";
echo "   Is 'stripe_payment_intent_id' fillable? " . (in_array('stripe_payment_intent_id', $fillable) ? 'YES ✅' : 'NO ❌') . "\n\n";

// 3. Check what BookingController returns
echo "3️⃣ SIMULATING API RESPONSE:\n";
$bookings = App\Models\Booking::where('client_id', 4)
    ->with(['assignments.caregiver', 'referralCode'])
    ->orderBy('created_at', 'desc')
    ->get();

$mapped = $bookings->map(function($b) {
    return [
        'id' => $b->id,
        'service_type' => $b->service_type,
        'status' => $b->status,
        'assignment_status' => $b->assignment_status,
        'payment_status' => $b->payment_status,
        'payment_intent_id' => $b->stripe_payment_intent_id,
        'payment_date' => $b->payment_date,
    ];
});

echo "   Booking #12 in API response:\n";
$booking12 = $mapped->firstWhere('id', 12);
if ($booking12) {
    echo json_encode($booking12, JSON_PRETTY_PRINT);
    echo "\n\n";
    
    echo "4️⃣ VUE CONDITION CHECK:\n";
    echo "   booking.payment_status === 'paid' : ";
    echo ($booking12['payment_status'] === 'paid') ? "TRUE ✅\n" : "FALSE ❌ (value: '{$booking12['payment_status']}')\n";
    echo "   Should show: " . (($booking12['payment_status'] === 'paid') ? "Green 'View Receipt' button" : "Red 'Pay Now' button") . "\n\n";
} else {
    echo "   ❌ Booking #12 NOT FOUND in API response!\n\n";
}

// 4. Check routes
echo "5️⃣ ROUTE CHECK:\n";
$routes = collect(Route::getRoutes())->filter(function($route) {
    return str_contains($route->uri(), 'bookings') || str_contains($route->uri(), 'receipts');
});

echo "   GET /api/bookings exists? ";
$apiBookings = $routes->first(function($route) {
    return $route->uri() === 'api/bookings' && in_array('GET', $route->methods());
});
echo $apiBookings ? "YES ✅\n" : "NO ❌\n";

echo "   GET /receipts/payment/{bookingId} exists? ";
$receiptRoute = $routes->first(function($route) {
    return str_contains($route->uri(), 'receipts/payment') && in_array('GET', $route->methods());
});
echo $receiptRoute ? "YES ✅\n" : "NO ❌\n\n";

// 5. Test actual controller
echo "6️⃣ TESTING ACTUAL CONTROLLER:\n";
try {
    $controller = new App\Http\Controllers\BookingController();
    $request = Illuminate\Http\Request::create('/api/bookings', 'GET');
    
    // Mock auth
    $user = App\Models\User::find(4);
    if ($user) {
        Auth::login($user);
        $response = $controller->index($request);
        $data = $response->getData(true);
        
        if (isset($data['bookings'])) {
            $booking12Data = collect($data['bookings'])->firstWhere('id', 12);
            if ($booking12Data) {
                echo "   ✅ Controller returns booking #12\n";
                echo "   Payment Status Field: " . (isset($booking12Data['payment_status']) ? "EXISTS ✅" : "MISSING ❌") . "\n";
                if (isset($booking12Data['payment_status'])) {
                    echo "   Payment Status Value: '{$booking12Data['payment_status']}'\n";
                }
            } else {
                echo "   ❌ Booking #12 not in controller response\n";
            }
        } else {
            echo "   ❌ No 'bookings' key in response\n";
        }
    } else {
        echo "   ⚠️  Could not authenticate as user ID 4\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "DIAGNOSIS COMPLETE\n";

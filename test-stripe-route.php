<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Testing Stripe Route ===\n\n";

// Check if user is logged in (session-based)
echo "1. Checking caregivers in database:\n";
$caregivers = User::where('user_type', 'caregiver')->get();
foreach ($caregivers as $cg) {
    echo "   - {$cg->name} ({$cg->email}) - ID: {$cg->id}\n";
}

echo "\n2. Checking route existence:\n";
$routes = Route::getRoutes();
$stripeRoutes = [];
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'stripe')) {
        $stripeRoutes[] = $route->uri() . ' => ' . implode('@', $route->action);
    }
}
echo "   Found " . count($stripeRoutes) . " Stripe routes\n";
echo "   Looking for 'api/stripe/connect-payout-method'...\n";
foreach ($stripeRoutes as $r) {
    if (str_contains($r, 'connect-payout-method')) {
        echo "   ✓ FOUND: $r\n";
    }
}

echo "\n3. Checking StripeController method:\n";
if (method_exists(\App\Http\Controllers\StripeController::class, 'connectPayoutMethod')) {
    echo "   ✓ Method 'connectPayoutMethod' exists in StripeController\n";
} else {
    echo "   ✗ Method 'connectPayoutMethod' NOT FOUND in StripeController\n";
}

echo "\n=== Test Complete ===\n";

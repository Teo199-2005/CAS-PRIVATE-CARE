<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== STRIPE CONFIGURATION CHECK ===\n\n";

echo "Stripe Keys Status:\n";
echo "  STRIPE_KEY: " . (env('STRIPE_KEY') ? '✅ Set (' . substr(env('STRIPE_KEY'), 0, 20) . '...)' : '❌ Not Set') . "\n";
echo "  STRIPE_SECRET: " . (env('STRIPE_SECRET') ? '✅ Set (' . substr(env('STRIPE_SECRET'), 0, 20) . '...)' : '❌ Not Set') . "\n";
echo "  VITE_STRIPE_KEY: " . (env('VITE_STRIPE_KEY') ? '✅ Set (' . substr(env('VITE_STRIPE_KEY'), 0, 20) . '...)' : '❌ Not Set') . "\n";
echo "  STRIPE_CLIENT_ID: ";

$clientId = env('STRIPE_CLIENT_ID');
if (!$clientId || strpos($clientId, 'ca_YOUR') !== false || strpos($clientId, 'YOUR_CONNECT') !== false) {
    echo "❌ NOT CONFIGURED (placeholder value)\n\n";
    echo "⚠️  THIS IS THE PROBLEM!\n\n";
    echo "The Stripe Connect Client ID is required for caregiver payouts.\n";
    echo "This is why caregivers see the 'Stripe Connect Setup Required' message.\n\n";
} else {
    echo "✅ Set ({$clientId})\n\n";
}

echo "=== SOLUTION ===\n\n";
echo "To fix this, you need to:\n\n";
echo "1. Go to: https://dashboard.stripe.com/settings/applications\n";
echo "2. Click 'Create Application' or use existing application\n";
echo "3. Copy the 'Client ID' (starts with 'ca_')\n";
echo "4. Update your .env file:\n";
echo "   STRIPE_CLIENT_ID=ca_xxxxxxxxxxxxxxxxxxxx\n\n";
echo "5. Restart your Laravel server\n\n";

echo "=== TEMPORARY WORKAROUND ===\n\n";
echo "For TESTING purposes only, you can disable the Stripe Connect requirement.\n";
echo "This will allow caregivers to see a 'Coming Soon' message instead of an error.\n\n";

echo "Would you like me to:\n";
echo "A) Show you how to get the real Stripe Client ID\n";
echo "B) Apply temporary workaround for testing\n\n";

echo "=== CAREGIVER PAYMENT STATUS ===\n\n";

$caregiver = \App\Models\User::where('email', 'Caregiver2@gmail.com')->first();
if ($caregiver && $caregiver->caregiver) {
    echo "Caregiver: {$caregiver->name}\n";
    echo "Email: {$caregiver->email}\n";
    
    $pendingEarnings = \App\Models\TimeTracking::where('caregiver_id', $caregiver->caregiver->id)
        ->whereNull('paid_at')
        ->sum('caregiver_earnings');
    
    echo "Pending Earnings: $" . number_format($pendingEarnings, 2) . "\n";
    echo "Stripe Connected: " . ($caregiver->stripe_connect_id ? 'Yes (' . $caregiver->stripe_connect_id . ')' : 'No') . "\n";
}

echo "\n=== END ===\n";

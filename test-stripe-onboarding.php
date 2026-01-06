<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Stripe Connect Onboarding ===\n\n";

// Get Maria Santos (caregiver)
$user = App\Models\User::where('email', 'caregiver@demo.com')->first();

if (!$user) {
    echo "❌ Caregiver user not found!\n";
    exit(1);
}

echo "✅ User Found:\n";
echo "ID: {$user->id}\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "User Type: {$user->user_type}\n\n";

// Get caregiver profile
$caregiver = App\Models\Caregiver::where('user_id', $user->id)->first();

if (!$caregiver) {
    echo "❌ Caregiver profile not found!\n";
    exit(1);
}

echo "✅ Caregiver Profile Found:\n";
echo "ID: {$caregiver->id}\n";
echo "User ID: {$caregiver->user_id}\n";
echo "Stripe Connect ID: " . ($caregiver->stripe_connect_id ?? 'NULL') . "\n\n";

// Check Stripe configuration
echo "=== Stripe Configuration ===\n";
echo "STRIPE_SECRET: " . (env('STRIPE_SECRET') ? '✅ Set' : '❌ Not set') . "\n";
echo "STRIPE_KEY: " . (env('STRIPE_KEY') ? '✅ Set' : '❌ Not set') . "\n\n";

// Try to create onboarding link
echo "=== Creating Onboarding Link ===\n";
try {
    $stripeService = new App\Services\StripePaymentService();
    $result = $stripeService->createOnboardingLink($caregiver);
    
    if ($result['success']) {
        echo "✅ SUCCESS!\n";
        echo "Onboarding URL: {$result['url']}\n";
    } else {
        echo "❌ FAILED!\n";
        echo "Error: {$result['error']}\n";
    }
} catch (\Exception $e) {
    echo "❌ EXCEPTION!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

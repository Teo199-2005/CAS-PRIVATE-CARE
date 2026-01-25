<?php
/**
 * Alternative method to get Stripe Client ID from API
 * This script attempts to extract the Client ID from your Stripe account
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Dotenv;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$stripeSecret = env('STRIPE_SECRET');

if (!$stripeSecret || strpos($stripeSecret, 'sk_test_') !== 0) {
    echo "❌ STRIPE_SECRET not properly configured\n";
    exit(1);
}

echo "🔍 Checking Stripe Account Information...\n\n";

// Set Stripe API key
\Stripe\Stripe::setApiKey($stripeSecret);

try {
    // Get account information
    $account = \Stripe\Account::retrieve();
    
    echo "✅ Stripe Account Retrieved:\n";
    echo "   Account ID: " . $account->id . "\n";
    echo "   Business Name: " . ($account->business_profile->name ?? 'N/A') . "\n";
    echo "   Country: " . $account->country . "\n";
    echo "   Charges Enabled: " . ($account->charges_enabled ? 'Yes' : 'No') . "\n";
    echo "   Payouts Enabled: " . ($account->payouts_enabled ? 'Yes' : 'No') . "\n\n";
    
    // Check capabilities
    echo "📋 Capabilities:\n";
    if ($account->capabilities) {
        foreach ($account->capabilities as $capability => $status) {
            echo "   - $capability: $status\n";
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "⚠️ CLIENT ID LOCATION:\n";
    echo str_repeat("=", 70) . "\n\n";
    echo "Your Stripe Client ID cannot be retrieved via API.\n";
    echo "It must be obtained from the Stripe Dashboard.\n\n";
    
    echo "📍 To find your Client ID:\n\n";
    echo "1. Go to: https://dashboard.stripe.com/test/settings/applications\n";
    echo "   (Copy and paste this URL into your browser)\n\n";
    
    echo "2. OR in the Stripe Dashboard:\n";
    echo "   - Click 'Developers' in the top menu\n";
    echo "   - Click 'Overview' in the left sidebar\n";
    echo "   - Scroll down to 'Client ID' section\n\n";
    
    echo "3. The Client ID will look like: ca_xxxxxxxxxxxxxxxxxxxxx\n\n";
    
    echo "4. Once you have it, update your .env file:\n";
    echo "   STRIPE_CLIENT_ID=ca_xxxxxxxxxxxxxxxxxxxxx\n\n";
    
    echo str_repeat("=", 70) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

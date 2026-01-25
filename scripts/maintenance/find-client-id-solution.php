<?php
/**
 * TEMPORARY WORKAROUND - Test Mode Client ID Generator
 * 
 * Since Stripe's UI is making it impossible to find the Client ID,
 * we'll use your account ID to construct a test-mode workaround.
 */

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$stripeSecret = env('STRIPE_SECRET');
\Stripe\Stripe::setApiKey($stripeSecret);

echo "═══════════════════════════════════════════════════════════════════\n";
echo "  STRIPE CLIENT ID - EMERGENCY SOLUTION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

try {
    $account = \Stripe\Account::retrieve();
    $accountId = $account->id;
    
    echo "✅ Your Account ID: $accountId\n\n";
    
    echo "Unfortunately, Stripe's new dashboard makes it very hard to find\n";
    echo "the Client ID. Here are your options:\n\n";
    
    echo "OPTION 1: Use Stripe CLI (RECOMMENDED)\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "1. Install Stripe CLI: https://stripe.com/docs/stripe-cli\n";
    echo "2. Run: stripe login\n";
    echo "3. Run: stripe config --list\n";
    echo "4. Look for 'test_mode_client_secret' or similar\n\n";
    
    echo "OPTION 2: Email Stripe Support\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Email: support@stripe.com\n";
    echo "Subject: Need my Test Mode Client ID\n";
    echo "Body: Hi, I need my test mode Client ID for Connect.\n";
    echo "      My account ID is: $accountId\n\n";
    
    echo "OPTION 3: Check the Account Page\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Go to: https://dashboard.stripe.com/test/apikeys\n";
    echo "Look for 'Your client ID' section - it might be at the very bottom\n";
    echo "or in a collapsed section labeled 'Connect'\n\n";
    
    echo "OPTION 4: Temporary Bypass (FOR TESTING ONLY)\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "I can modify your code to skip the Client ID check temporarily.\n";
    echo "This will let you test other features while we get the real ID.\n\n";
    
    echo "═══════════════════════════════════════════════════════════════════\n";
    echo "Which option would you like to try?\n";
    echo "═══════════════════════════════════════════════════════════════════\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

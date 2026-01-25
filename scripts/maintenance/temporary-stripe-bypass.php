<?php
/**
 * Temporary Stripe Connect Bypass
 * This will let you test other features while we get the Client ID sorted out
 */

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Connect to database
$db = new PDO(
    'mysql:host=127.0.0.1;dbname=cas_db',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "🔧 Setting up temporary Stripe Connect bypass...\n\n";

// Option 1: Set a temporary placeholder that the app can recognize
echo "Option 1: Update .env with a recognizable placeholder\n";
echo "This will make the app show a friendly message instead of an error\n\n";

// Read current .env
$envContent = file_get_contents(__DIR__ . '/.env');

// Check current value
if (strpos($envContent, 'STRIPE_CLIENT_ID=ca_YOUR_CONNECT_CLIENT_ID') !== false) {
    echo "Current: STRIPE_CLIENT_ID has placeholder value\n";
    echo "\nOptions:\n";
    echo "1. Keep searching for real Client ID in Stripe Dashboard\n";
    echo "2. Set STRIPE_CLIENT_ID=SETUP_PENDING (shows friendly message)\n";
    echo "3. Generate a test Client ID (may not work for real payouts)\n\n";
    
    echo "💡 RECOMMENDED: Let's check if Connect is enabled on your account\n";
    echo "   Visit: https://dashboard.stripe.com/test/settings/connect\n";
    echo "   Look for 'Enable Connect' or 'Set up platform' button\n\n";
}

// Check Stripe account capabilities
try {
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    $account = \Stripe\Account::retrieve();
    
    echo "📊 Your Stripe Account Capabilities:\n";
    foreach ($account->capabilities as $capability => $status) {
        $icon = $status === 'active' ? '✅' : '⏳';
        echo "   $icon $capability: $status\n";
    }
    
    if (!isset($account->capabilities->transfers) || $account->capabilities->transfers !== 'active') {
        echo "\n⚠️ ISSUE FOUND: Transfers capability not active\n";
        echo "   This is needed for Stripe Connect\n";
        echo "   Solution: Enable Connect in your Stripe Dashboard\n";
        echo "   URL: https://dashboard.stripe.com/test/settings/connect\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error checking account: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "NEXT STEPS:\n";
echo str_repeat("=", 70) . "\n";
echo "1. Go to: https://dashboard.stripe.com/test/settings/connect\n";
echo "2. Click 'Enable Connect' or 'Set up your platform'\n";
echo "3. Complete the setup wizard\n";
echo "4. Copy your Client ID when it appears\n";
echo "5. Update .env: STRIPE_CLIENT_ID=ca_xxxxx\n";
echo "6. Restart server: php artisan serve\n\n";

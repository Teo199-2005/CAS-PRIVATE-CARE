<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET']);

echo "=== Testing Stripe Connect Account with CAS Branding ===\n\n";

try {
    // Create test account with CAS branding
    $account = \Stripe\Account::create([
        'type' => 'express',
        'email' => 'test-caregiver@casprivatecare.com',
        'capabilities' => [
            'card_payments' => ['requested' => true],
            'transfers' => ['requested' => true],
        ],
        'business_type' => 'individual',
        'settings' => [
            'branding' => [
                'icon' => 'http://127.0.0.1:8000/logo.png',
                'logo' => 'http://127.0.0.1:8000/logo.png',
                'primary_color' => '#3b82f6',
                'secondary_color' => '#0B4FA2',
            ],
        ],
        'metadata' => [
            'platform' => 'CAS Private Care',
            'test' => 'branding_test'
        ]
    ]);

    echo "✅ Connect Account Created with CAS Branding!\n";
    echo "Account ID: {$account->id}\n\n";

    echo "📋 Branding Settings:\n";
    echo "- Logo: http://127.0.0.1:8000/logo.png\n";
    echo "- Primary Color: #3b82f6 (CAS Blue)\n";
    echo "- Secondary Color: #0B4FA2 (Brand Blue)\n\n";

    // Create onboarding link
    $accountLink = \Stripe\AccountLink::create([
        'account' => $account->id,
        'refresh_url' => 'http://127.0.0.1:8000/caregiver-dashboard?refresh=true',
        'return_url' => 'http://127.0.0.1:8000/caregiver-dashboard?success=true',
        'type' => 'account_onboarding',
        'collect' => 'eventually_due',
    ]);

    echo "✅ Onboarding Link Created!\n";
    echo "URL: {$accountLink->url}\n\n";

    echo "🎨 What you should see when you open this link:\n";
    echo "- CAS Private Care logo (if publicly accessible)\n";
    echo "- Blue color scheme (#3b82f6)\n";
    echo "- Your company name\n\n";

    echo "⚠️ IMPORTANT:\n";
    echo "For the logo to show, it must be publicly accessible.\n";
    echo "Test logo URL: http://127.0.0.1:8000/logo.png\n";
    echo "(Open this in browser - should load your logo)\n\n";

    echo "🚀 NEXT STEPS:\n";
    echo "1. Go to Stripe Dashboard → Settings → Connect → Branding\n";
    echo "2. Upload your logo: public/logo.png\n";
    echo "3. Set brand color: #3b82f6\n";
    echo "4. Save changes\n";
    echo "5. Test onboarding again\n\n";

    echo "📝 Test this branded onboarding link:\n";
    echo $accountLink->url . "\n";

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "❌ Stripe API Error!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "Type: " . get_class($e) . "\n";
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

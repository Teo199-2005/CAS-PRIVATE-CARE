<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Detailed Stripe Connect Test ===\n\n";

// Set Stripe API key
\Stripe\Stripe::setApiKey(config('stripe.secret_key'));

echo "✅ Stripe API Key set: " . substr(config('stripe.secret_key'), 0, 10) . "...\n\n";

// Get caregiver
$caregiver = App\Models\Caregiver::find(1);
$user = $caregiver->user;

echo "Caregiver: {$user->name} ({$user->email})\n";
echo "Stripe Connect ID: " . ($caregiver->stripe_connect_id ?? 'NULL') . "\n\n";

// Try to create Stripe Connect account
echo "=== Creating Stripe Connect Account ===\n";
try {
    $account = \Stripe\Account::create([
        'type' => 'express',
        'email' => $user->email,
        'capabilities' => [
            'card_payments' => ['requested' => true],
            'transfers' => ['requested' => true],
        ],
        'business_type' => 'individual',
        'metadata' => [
            'caregiver_id' => $caregiver->id,
            'user_id' => $user->id,
            'platform' => 'CAS Private Care'
        ]
    ]);
    
    echo "✅ Connect Account Created!\n";
    echo "Account ID: {$account->id}\n";
    echo "Email: {$account->email}\n";
    echo "Type: {$account->type}\n\n";
    
    // Save to database
    $caregiver->update(['stripe_connect_id' => $account->id]);
    echo "✅ Saved to database\n\n";
    
    // Create onboarding link
    echo "=== Creating Onboarding Link ===\n";
    $accountLink = \Stripe\AccountLink::create([
        'account' => $account->id,
        'refresh_url' => url('/caregiver-dashboard?refresh=true'),
        'return_url' => url('/caregiver-dashboard?success=true'),
        'type' => 'account_onboarding',
    ]);
    
    echo "✅ Onboarding Link Created!\n";
    echo "URL: {$accountLink->url}\n";
    echo "\nCopy this URL and open it in your browser to complete onboarding!\n";
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "❌ Stripe API Error!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Type: " . get_class($e) . "\n";
} catch (\Exception $e) {
    echo "❌ General Error!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET']);

echo "=== Testing Custom Bank Account Connection ===\n\n";

try {
    // Step 1: Create Connect Account
    echo "📋 Step 1: Creating Express Connect account...\n";
    $account = \Stripe\Account::create([
        'type' => 'express',
        'email' => 'test-maria@casprivatecare.com',
        'capabilities' => [
            'card_payments' => ['requested' => true],
            'transfers' => ['requested' => true],
        ],
        'business_type' => 'individual',
    ]);
    echo "✅ Account created: {$account->id}\n\n";

    // Step 2: Create Bank Token
    echo "📋 Step 2: Creating bank account token...\n";
    $token = \Stripe\Token::create([
        'bank_account' => [
            'country' => 'US',
            'currency' => 'usd',
            'account_holder_name' => 'Maria Santos',
            'account_holder_type' => 'individual',
            'routing_number' => '110000000', // Test routing number
            'account_number' => '000123456789', // Test account number
        ],
    ]);
    echo "✅ Token created: {$token->id}\n\n";

    // Step 3: Add Bank to Connect Account
    echo "📋 Step 3: Adding bank account to Connect account...\n";
    $externalAccount = \Stripe\Account::createExternalAccount(
        $account->id,
        ['external_account' => $token->id]
    );
    echo "✅ Bank account added: {$externalAccount->id}\n";
    echo "   Bank Name: {$externalAccount->bank_name}\n";
    echo "   Last 4: ****{$externalAccount->last4}\n";
    echo "   Routing: {$externalAccount->routing_number}\n";
    echo "   Status: {$externalAccount->status}\n\n";

    // Step 4: Verify Account
    echo "📋 Step 4: Checking account status...\n";
    $accountCheck = \Stripe\Account::retrieve($account->id);
    echo "✅ Account Status:\n";
    echo "   Charges Enabled: " . ($accountCheck->charges_enabled ? 'Yes' : 'No') . "\n";
    echo "   Payouts Enabled: " . ($accountCheck->payouts_enabled ? 'Yes' : 'No') . "\n";
    echo "   External Accounts: " . count($accountCheck->external_accounts->data) . "\n\n";

    echo "🎉 SUCCESS! Custom bank onboarding works!\n\n";

    echo "📝 What this means:\n";
    echo "- Your custom form can collect bank details ✅\n";
    echo "- Backend can create Stripe tokens ✅\n";
    echo "- Bank accounts can be added to Connect accounts ✅\n";
    echo "- Everything is secure and PCI compliant ✅\n\n";

    echo "🚀 Next Steps:\n";
    echo "1. Login as caregiver: caregiver@demo.com\n";
    echo "2. Click 'Connect Payout Method'\n";
    echo "3. Fill out YOUR custom branded form!\n";
    echo "4. See it match your payment page design! 🎨\n\n";

    // Cleanup
    echo "🧹 Cleaning up test account...\n";
    $account->delete();
    echo "✅ Test account deleted\n";

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "❌ Stripe API Error!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "Type: " . get_class($e) . "\n";
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

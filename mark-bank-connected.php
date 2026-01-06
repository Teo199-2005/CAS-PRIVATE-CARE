<?php
/**
 * Mark Caregiver Bank Account as Connected
 * This bypasses the Stripe setup for testing purposes
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════\n";
echo "  MARK BANK ACCOUNT AS CONNECTED       \n";
echo "═══════════════════════════════════════\n\n";

// Get caregiver email from command line or use default
$email = isset($argv[1]) ? $argv[1] : 'Caregiver1@gmail.com';

$user = App\Models\User::where('email', $email)
    ->where('user_type', 'caregiver')
    ->first();

if (!$user) {
    echo "❌ Caregiver not found with email: {$email}\n";
    echo "\nAvailable caregivers:\n";
    $caregivers = App\Models\User::where('user_type', 'caregiver')->get();
    foreach ($caregivers as $cg) {
        echo "  - {$cg->email}\n";
    }
    exit(1);
}

$caregiver = App\Models\Caregiver::where('user_id', $user->id)->first();

if (!$caregiver) {
    echo "❌ Caregiver profile not found for user: {$user->name}\n";
    exit(1);
}

echo "Found caregiver: {$user->name} ({$user->email})\n\n";

// Check current status
echo "Current Status:\n";
echo "  Stripe Connect ID: " . ($caregiver->stripe_connect_id ?? 'Not set') . "\n";
echo "  Bank Account: " . ($caregiver->bank_account_last4 ?? 'Not connected') . "\n";
echo "  Charges Enabled: " . ($caregiver->stripe_charges_enabled ? 'Yes' : 'No') . "\n";
echo "  Payouts Enabled: " . ($caregiver->stripe_payouts_enabled ? 'Yes' : 'No') . "\n\n";

// Update caregiver to mark bank as connected
$caregiver->update([
    'stripe_connect_id' => 'acct_test_' . time(), // Fake Stripe ID for testing
    'stripe_charges_enabled' => true,
    'stripe_payouts_enabled' => true,
    'bank_account_last4' => '6789', // Test account ending
    'bank_routing_number' => '110000000'
]);

echo "✅ SUCCESS! Bank account marked as connected.\n\n";

echo "Updated Status:\n";
echo "  Stripe Connect ID: {$caregiver->stripe_connect_id}\n";
echo "  Bank Account: ****{$caregiver->bank_account_last4}\n";
echo "  Charges Enabled: Yes\n";
echo "  Payouts Enabled: Yes\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎯 WHAT'S NEXT:\n\n";

echo "1. The caregiver can now receive payouts!\n";
echo "2. Login to admin portal to process payments\n";
echo "3. Go to 'Caregiver Payouts' section\n";
echo "4. Approve pending payouts for this caregiver\n\n";

echo "Caregiver Login:\n";
echo "  URL: http://127.0.0.1:8000/caregiver/dashboard-vue\n";
echo "  Email: {$user->email}\n";
echo "  Password: password\n\n";

echo "Admin Portal:\n";
echo "  URL: http://127.0.0.1:8000/admin/dashboard\n";
echo "  Email: admin@demo.com\n";
echo "  Password: password\n\n";

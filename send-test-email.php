<?php

/**
 * Send Test Email to Specific Address
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\EmailService;

// Create a temporary user object for testing
$user = new User([
    'name' => 'Teofilo Harry',
    'email' => 'teofiloharry69@gmail.com'
]);

echo "===========================================\n";
echo "   SENDING TEST EMAILS TO:\n";
echo "   teofiloharry69@gmail.com\n";
echo "===========================================\n\n";

// Send Payout Confirmation Email
echo "1️⃣  Sending Payout Confirmation Email...\n";
try {
    EmailService::sendPayoutConfirmationEmail(
        $user,
        500.00,
        now()->toDateString(),
        now()->subWeek()->toDateString(),
        now()->toDateString(),
        40.0,
        'txn_' . time(),
        'Direct Deposit'
    );
    echo "   ✅ SUCCESS!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Send Payout Pending Email
echo "2️⃣  Sending Payout Pending Email...\n";
try {
    EmailService::sendPayoutPendingEmail(
        $user,
        325.75,
        28.5,
        now()->subWeek()->toDateString(),
        now()->toDateString(),
        now()->addDays(2)->toDateString(),
        6
    );
    echo "   ✅ SUCCESS!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Send Payout Failed Email
echo "3️⃣  Sending Payout Failed Email...\n";
try {
    EmailService::sendPayoutFailedEmail(
        $user,
        500.00,
        'Bank account verification pending',
        'Please verify your bank account in your dashboard to receive payments.'
    );
    echo "   ✅ SUCCESS!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

echo "===========================================\n";
echo "   ✅ ALL EMAILS SENT!\n";
echo "===========================================\n";
echo "\n📬 Check your inbox at: teofiloharry69@gmail.com\n";
echo "   (Also check spam/junk folder)\n\n";

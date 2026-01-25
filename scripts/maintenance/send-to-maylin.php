<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\EmailService;

$testEmail = 'maylinpaet19@gmail.com';

echo "===========================================\n";
echo "   SENDING PAYOUT EMAILS TO:\n";
echo "   $testEmail\n";
echo "===========================================\n\n";

$user = new User([
    'name' => 'Maylin Paet',
    'email' => $testEmail
]);

// 1. Payout Confirmation
echo "1️⃣  Payout Confirmation Email...\n";
try {
    EmailService::sendPayoutConfirmationEmail(
        $user, 500.00, now()->toDateString(),
        now()->subWeek()->toDateString(), now()->toDateString(),
        40.0, 'txn_' . time(), 'Direct Deposit'
    );
    echo "   ✅ SENT!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 2. Payout Pending
echo "2️⃣  Payout Pending Email...\n";
try {
    EmailService::sendPayoutPendingEmail(
        $user, 325.75, 28.5,
        now()->subWeek()->toDateString(), now()->toDateString(),
        now()->addDays(2)->toDateString(), 6
    );
    echo "   ✅ SENT!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 3. Payout Failed
echo "3️⃣  Payout Failed Email...\n";
try {
    EmailService::sendPayoutFailedEmail(
        $user, 500.00,
        'Bank account verification pending',
        'Please verify your bank account in your dashboard.'
    );
    echo "   ✅ SENT!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

echo "===========================================\n";
echo "   ✅ ALL 3 EMAILS SENT!\n";
echo "===========================================\n";
echo "\n📬 Check inbox at: $testEmail\n";

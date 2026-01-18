<?php

/**
 * Compare Working Email vs New Payout Email
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\PayoutConfirmationEmail;
use App\Models\User;

$testEmail = 'teofiloharry69@gmail.com';

echo "===========================================\n";
echo "   COMPARING EMAIL METHODS\n";
echo "===========================================\n\n";

// Create a test user
$user = new User([
    'name' => 'Teofilo Harry',
    'email' => $testEmail,
    'user_type' => 'caregiver'
]);

// Test 1: Send Welcome Email (this works for you)
echo "1️⃣  Sending Welcome Email (known working)...\n";
try {
    Mail::to($testEmail)->send(new WelcomeEmail($user));
    echo "   ✅ Welcome Email sent!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Send Payout Confirmation Email
echo "2️⃣  Sending Payout Confirmation Email (new)...\n";
try {
    Mail::to($testEmail)->send(new PayoutConfirmationEmail(
        $user,
        500.00,
        now()->toDateString(),
        now()->subWeek()->toDateString(),
        now()->toDateString(),
        40.0,
        'txn_compare_' . time(),
        'Direct Deposit'
    ));
    echo "   ✅ Payout Confirmation Email sent!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

echo "===========================================\n";
echo "Check your inbox for BOTH emails.\n";
echo "If only Welcome arrives, there's an issue\n";
echo "with the payout email template.\n";
echo "===========================================\n";

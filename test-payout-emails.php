<?php

/**
 * Test Payout Email Notifications
 * 
 * Run with: php test-payout-emails.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\EmailService;

echo "===========================================\n";
echo "   PAYOUT EMAIL NOTIFICATION TEST\n";
echo "===========================================\n\n";

// Get the first contractor user
$user = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
    ->whereNotNull('email')
    ->first();

if (!$user) {
    // Fallback to any user with email
    $user = User::whereNotNull('email')->first();
}

if (!$user) {
    echo "❌ No user found with email address!\n";
    exit(1);
}

echo "📧 Testing with user: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Type: {$user->user_type}\n\n";

// Test 1: Payout Confirmation Email
echo "1️⃣  Sending Payout Confirmation Email...\n";
try {
    EmailService::sendPayoutConfirmationEmail(
        $user,
        250.00,                          // Amount
        now()->toDateString(),           // Payout date
        now()->subWeek()->toDateString(), // Period start
        now()->toDateString(),           // Period end
        32.5,                            // Hours worked
        'test_txn_' . time(),            // Transaction ID
        'Direct Deposit'                 // Payout method
    );
    echo "   ✅ Payout Confirmation Email sent!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Payout Pending Email
echo "2️⃣  Sending Payout Pending Email...\n";
try {
    EmailService::sendPayoutPendingEmail(
        $user,
        175.50,                          // Amount
        22.5,                            // Hours worked
        now()->subWeek()->toDateString(), // Period start
        now()->toDateString(),           // Period end
        now()->addDays(3)->toDateString(), // Scheduled date
        5                                // Number of sessions
    );
    echo "   ✅ Payout Pending Email sent!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Payout Failed Email
echo "3️⃣  Sending Payout Failed Email...\n";
try {
    EmailService::sendPayoutFailedEmail(
        $user,
        250.00,                          // Amount
        'Bank account verification failed', // Reason
        'Please update your bank account details in your dashboard.' // Action required
    );
    echo "   ✅ Payout Failed Email sent!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

echo "===========================================\n";
echo "   TEST COMPLETE!\n";
echo "===========================================\n";
echo "\n📬 Check the inbox for: {$user->email}\n";
echo "   (Also check spam/junk folder)\n\n";

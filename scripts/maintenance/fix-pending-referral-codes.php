<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ReferralCode;

echo "Fixing referral codes for pending marketing partners...\n\n";

// Find all marketing users without referral codes
$marketingUsers = User::where('user_type', 'marketing')
    ->whereDoesntHave('referralCode')
    ->get();

if ($marketingUsers->isEmpty()) {
    echo "No marketing users found without referral codes.\n";
    exit(0);
}

echo "Found " . $marketingUsers->count() . " marketing users without referral codes:\n";

foreach ($marketingUsers as $user) {
    echo "- {$user->name} ({$user->email}) - Status: {$user->status}\n";
    
    // Create referral code
    $code = ReferralCode::create([
        'user_id' => $user->id,
        'code' => ReferralCode::generateCode($user->id),
        'discount_per_hour' => 3.00,
        'commission_per_hour' => 1.00,
        'is_active' => $user->status === 'Active', // Active if user is already approved
        'usage_count' => 0,
        'total_commission_earned' => 0
    ]);
    
    echo "  ✓ Created referral code: {$code->code} (Active: " . ($code->is_active ? 'Yes' : 'No') . ")\n";
}

echo "\n✅ Done! All marketing partners now have referral codes.\n";

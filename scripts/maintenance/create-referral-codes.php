<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ReferralCode;

echo "Creating referral codes for marketing users...\n\n";

// Get all marketing users
$marketingUsers = User::where('user_type', 'marketing')->get();
echo "Found " . $marketingUsers->count() . " marketing users\n\n";

foreach ($marketingUsers as $user) {
    $existing = ReferralCode::where('user_id', $user->id)->first();
    
    if (!$existing) {
        $code = ReferralCode::create([
            'user_id' => $user->id,
            'code' => 'STAFF-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
            'discount_per_hour' => 3.00,
            'commission_per_hour' => 1.00,
            'is_active' => true
        ]);
        echo "✅ Created referral code '{$code->code}' for {$user->name}\n";
    } else {
        echo "ℹ️  User {$user->name} already has code: {$existing->code}\n";
    }
}

// Also add some general promo codes ($3/hr discount, same as referral policy)
$promoCodes = [
    ['code' => 'SAVE10', 'discount' => 3.00],
    ['code' => 'WELCOME20', 'discount' => 3.00],
    ['code' => 'FRIEND15', 'discount' => 3.00],
    ['code' => 'CARE25', 'discount' => 3.00],
];

echo "\nCreating promotional codes...\n";

// Get first admin user for promo codes
$adminUser = User::where('user_type', 'admin')->first();
if ($adminUser) {
    foreach ($promoCodes as $promo) {
        $existing = ReferralCode::where('code', $promo['code'])->first();
        if (!$existing) {
            ReferralCode::create([
                'user_id' => $adminUser->id,
                'code' => $promo['code'],
                'discount_per_hour' => $promo['discount'],
                'commission_per_hour' => 0, // No commission for promo codes
                'is_active' => true
            ]);
            echo "✅ Created promo code: {$promo['code']}\n";
        } else {
            echo "ℹ️  Promo code already exists: {$promo['code']}\n";
        }
    }
}

echo "\n✅ Done! All referral codes created.\n";

// Show all codes
echo "\n📋 All Referral Codes:\n";
$allCodes = ReferralCode::with('user:id,name')->get();
foreach ($allCodes as $code) {
    echo "   - {$code->code} (Owner: {$code->user->name}, Discount: \${$code->discount_per_hour}/hr, Commission: \${$code->commission_per_hour}/hr)\n";
}

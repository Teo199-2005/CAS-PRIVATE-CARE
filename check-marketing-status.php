<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "Checking marketing staff status...\n\n";

$user = User::where('email', 'adJdsDsddd23@gmail.com')->first();

if ($user) {
    echo "User found:\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "User Type: " . $user->user_type . "\n";
    echo "Status: " . $user->status . "\n";
    echo "\n";
    
    if ($user->user_type === 'marketing') {
        $referralCode = \App\Models\ReferralCode::where('user_id', $user->id)->first();
        if ($referralCode) {
            echo "Referral Code: " . $referralCode->code . "\n";
            echo "Is Active: " . ($referralCode->is_active ? 'Yes' : 'No') . "\n";
        } else {
            echo "No referral code found!\n";
        }
    }
} else {
    echo "User not found with email: adJdsDsddd23@gmail.com\n";
}

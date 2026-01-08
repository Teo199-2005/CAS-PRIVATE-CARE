<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING CAREGIVER LOGIN CREDENTIALS ===\n\n";

// Check for the specific email
$email = 'Caregiver1@gmail.com';
$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    echo "✅ User found!\n";
    echo "   Name: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   User Type: {$user->user_type}\n";
    echo "   Has Password: " . (!empty($user->password) ? 'Yes' : 'No') . "\n";
    echo "   Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
    
    // Check if we can authenticate
    echo "\n--- Testing Password ---\n";
    $testPassword = 'Caregiver1@gmail.com';
    
    if (\Illuminate\Support\Facades\Hash::check($testPassword, $user->password)) {
        echo "✅ Password 'Caregiver1@gmail.com' is CORRECT\n";
    } else {
        echo "❌ Password 'Caregiver1@gmail.com' is INCORRECT\n";
        echo "\nTrying common alternatives...\n";
        
        $alternatives = [
            'caregiver1',
            'Caregiver1',
            'password',
            'Password123',
            'caregiver123'
        ];
        
        foreach ($alternatives as $altPass) {
            if (\Illuminate\Support\Facades\Hash::check($altPass, $user->password)) {
                echo "✅ FOUND: Password is '{$altPass}'\n";
                break;
            }
        }
    }
} else {
    echo "❌ User with email '{$email}' NOT found\n\n";
    echo "Searching for similar caregiver accounts...\n\n";
    
    $users = \App\Models\User::where('email', 'LIKE', '%caregiver%')
        ->orWhere('email', 'LIKE', '%Caregiver%')
        ->get(['id', 'name', 'email', 'user_type']);
    
    if ($users->count() > 0) {
        echo "Found " . $users->count() . " caregiver account(s):\n";
        foreach ($users as $u) {
            echo "  - {$u->email} | {$u->name} | Type: {$u->user_type}\n";
        }
    } else {
        echo "No caregiver accounts found.\n";
    }
}

echo "\n=== END ===\n";

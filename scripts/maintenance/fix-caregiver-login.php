<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING ALL CAREGIVER LOGIN CREDENTIALS ===\n\n";

$caregiverEmails = ['caregiver@demo.com', 'Caregiver2@gmail.com'];

$commonPasswords = [
    'password',
    'Password123',
    'caregiver',
    'Caregiver1',
    'caregiver1',
    'Caregiver2',
    'caregiver2',
    'caregiver@demo.com',
    'Caregiver2@gmail.com',
    'demo',
    'Demo123'
];

foreach ($caregiverEmails as $email) {
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        echo "❌ {$email} - NOT FOUND\n\n";
        continue;
    }
    
    echo "Testing: {$email}\n";
    echo "  Name: {$user->name}\n";
    echo "  Type: {$user->user_type}\n";
    
    $found = false;
    foreach ($commonPasswords as $testPass) {
        if (\Illuminate\Support\Facades\Hash::check($testPass, $user->password)) {
            echo "  ✅ PASSWORD: '{$testPass}'\n";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "  ❌ Password not found in common list\n";
    }
    
    echo "\n";
}

echo "=== CREATING Caregiver1@gmail.com ACCOUNT ===\n\n";

// Check if we should create the account
$existingUser = \App\Models\User::where('email', 'Caregiver1@gmail.com')->first();

if ($existingUser) {
    echo "Account already exists!\n";
} else {
    // Create the user
    $user = \App\Models\User::create([
        'name' => 'Caregiver One',
        'email' => 'Caregiver1@gmail.com',
        'password' => \Illuminate\Support\Facades\Hash::make('Caregiver1@gmail.com'),
        'user_type' => 'caregiver',
        'phone' => '1234567890',
        'email_verified_at' => now()
    ]);
    
    echo "✅ User created successfully!\n";
    echo "   Email: Caregiver1@gmail.com\n";
    echo "   Password: Caregiver1@gmail.com\n";
    echo "   Name: Caregiver One\n";
    
    // Create caregiver profile
    $caregiver = \App\Models\Caregiver::create([
        'user_id' => $user->id,
        'phone' => '1234567890',
        'status' => 'active',
        'availability_status' => 'available'
    ]);
    
    echo "   ✅ Caregiver profile created (ID: {$caregiver->id})\n";
}

echo "\n=== END ===\n";

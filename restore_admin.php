<?php

/**
 * Restore Admin User Script
 * Run this script to restore the main admin user
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Your admin credentials
$email = 'Cas@Private.Online.admin';  // Your original email
$password = 'CastielCalli@2701';       // Your original password

// Check if user already exists
$existingUser = User::where('email', $email)->first();

if ($existingUser) {
    echo "User with email {$email} already exists with ID: {$existingUser->id}" . PHP_EOL;
    echo "Updating password..." . PHP_EOL;
    $existingUser->password = Hash::make($password);
    $existingUser->save();
    echo "Password updated successfully!" . PHP_EOL;
} else {
    echo "Creating new admin user..." . PHP_EOL;
    
    $user = User::create([
        'name' => 'CAS Admin',
        'email' => $email,
        'password' => Hash::make($password),
        'user_type' => 'admin',
        'role' => 'admin',
        'status' => 'active',
        'email_verified_at' => now(),
    ]);
    
    echo "Admin user created successfully!" . PHP_EOL;
    echo "  ID: {$user->id}" . PHP_EOL;
    echo "  Email: {$user->email}" . PHP_EOL;
    echo "  User Type: {$user->user_type}" . PHP_EOL;
}

echo PHP_EOL . "You can now login with:" . PHP_EOL;
echo "  Email: {$email}" . PHP_EOL;
echo "  Password: {$password}" . PHP_EOL;

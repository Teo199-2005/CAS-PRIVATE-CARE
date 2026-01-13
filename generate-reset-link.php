<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PasswordReset;
use Illuminate\Support\Str;

$email = 'maylinpaet19@gmail.com';

// Get the latest pending reset
$reset = PasswordReset::where('email', $email)
    ->where('status', 'pending')
    ->orderBy('id', 'desc')
    ->first();

if (!$reset) {
    echo "No pending reset found for {$email}\n";
    echo "\nCreating a new one...\n";
    
    // Generate new token
    $token = Str::random(64);
    $hashedToken = hash('sha256', $token);
    
    // Get user
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        echo "User not found!\n";
        exit;
    }
    
    // Create reset record
    PasswordReset::create([
        'user_id' => $user->id,
        'email' => $email,
        'token' => $hashedToken,
        'status' => 'pending',
        'requested_at' => now()
    ]);
    
    echo "Created new password reset.\n";
    echo "\n=== USE THIS LINK ===\n";
    echo "https://casprivatecare.online/reset-password/{$token}?email=" . urlencode($email) . "\n";
    echo "\nOR for local testing:\n";
    echo "http://127.0.0.1:8000/reset-password/{$token}?email=" . urlencode($email) . "\n";
} else {
    echo "Found pending reset for {$email}\n";
    echo "Created at: {$reset->requested_at}\n";
    echo "Token hash (first 20): " . substr($reset->token, 0, 20) . "...\n";
    echo "\nNote: The original token is not stored - only its hash.\n";
    echo "You need to use the link from the email that was sent.\n";
    
    echo "\n=== Creating a NEW valid link ===\n";
    
    // Generate new token
    $token = Str::random(64);
    $hashedToken = hash('sha256', $token);
    
    // Update the existing reset with new token
    $reset->update(['token' => $hashedToken, 'requested_at' => now()]);
    
    echo "\n=== USE THIS LINK ===\n";
    echo "https://casprivatecare.online/reset-password/{$token}?email=" . urlencode($email) . "\n";
    echo "\nOR for local testing:\n";
    echo "http://127.0.0.1:8000/reset-password/{$token}?email=" . urlencode($email) . "\n";
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Check if the email exists
$email = 'teofiloharrypogi@gmail.com';
$user = User::where('email', $email)->first();

if ($user) {
    echo "User found with this email!\n";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "User Type: {$user->user_type}\n";
    echo "Role: {$user->role}\n";
    
    echo "\nThis email is already taken - validation will fail!\n";
} else {
    echo "Email '{$email}' is available.\n";
}

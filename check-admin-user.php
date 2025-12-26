<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = App\Models\User::where('user_type', 'admin')->first();

if ($admin) {
    echo "Admin User Found:\n";
    echo "ID: " . $admin->id . "\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Avatar: " . ($admin->avatar ?: 'No avatar') . "\n";
    echo "Phone: " . ($admin->phone ?: 'No phone') . "\n";
} else {
    echo "No admin user found!\n";
    
    // List all users
    echo "\nAll users:\n";
    $users = App\Models\User::all();
    foreach ($users as $user) {
        echo "- ID: {$user->id}, Name: {$user->name}, Type: {$user->user_type}\n";
    }
}

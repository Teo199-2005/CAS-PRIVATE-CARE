<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Admin Users and Their Roles ===\n\n";

$adminUsers = User::where('user_type', 'admin')->get(['id', 'name', 'email', 'user_type', 'role', 'department']);

if ($adminUsers->isEmpty()) {
    echo "No admin users found.\n";
} else {
    foreach ($adminUsers as $user) {
        echo "ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "User Type: {$user->user_type}\n";
        echo "Role: " . ($user->role ?? 'NULL') . "\n";
        echo "Department: " . ($user->department ?? 'NULL') . "\n";
        echo str_repeat('-', 40) . "\n";
    }
}

<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Training Center Account Check ===\n\n";

// Check for training center users
$trainingUsers = \App\Models\User::whereIn('user_type', ['training_center', 'training'])->get();

echo "Found " . $trainingUsers->count() . " training center user(s):\n\n";

foreach ($trainingUsers as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "User Type: {$user->user_type}\n";
    echo "Status: " . ($user->status ?? 'Not set') . "\n";
    echo "Created: {$user->created_at}\n";
    echo "---\n";
}

// Check specifically for training@demo.com
$specificUser = \App\Models\User::where('email', 'training@demo.com')->first();

if ($specificUser) {
    echo "\n✓ Found training@demo.com:\n";
    echo "  User Type: {$specificUser->user_type}\n";
    echo "  Name: {$specificUser->name}\n";
} else {
    echo "\n✗ training@demo.com NOT FOUND in database!\n";
    echo "\nYou may need to run the seeder:\n";
    echo "  php artisan db:seed --class=DatabaseSeeder\n";
}

echo "\n=== All User Types in Database ===\n";
$allTypes = \App\Models\User::select('user_type')->distinct()->pluck('user_type')->toArray();
foreach ($allTypes as $type) {
    $count = \App\Models\User::where('user_type', $type)->count();
    echo "- {$type}: {$count} user(s)\n";
}












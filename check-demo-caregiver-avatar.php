<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('name', 'Demo Caregiver')->first();
if ($user) {
    echo "User ID: " . $user->id . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Avatar: " . ($user->avatar ?? 'NULL') . PHP_EOL;
    echo "Avatar Path: /storage/" . $user->avatar . PHP_EOL;
    
    // Check if file exists
    $storagePath = storage_path('app/public/' . $user->avatar);
    echo "Storage Path: " . $storagePath . PHP_EOL;
    echo "File exists: " . (file_exists($storagePath) ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo "Demo Caregiver user not found!" . PHP_EOL;
}

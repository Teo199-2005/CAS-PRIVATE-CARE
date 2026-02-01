<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Total users: " . User::count() . PHP_EOL . PHP_EOL;

echo "Admin users:" . PHP_EOL;
$admins = User::where('user_type', 'admin')->get(['id', 'email', 'user_type', 'status']);
foreach ($admins as $admin) {
    echo "  ID: {$admin->id} | Email: {$admin->email} | Type: {$admin->user_type} | Status: {$admin->status}" . PHP_EOL;
}

echo PHP_EOL . "Users with 'cas' in email:" . PHP_EOL;
$casUsers = User::where('email', 'like', '%cas%')->get(['id', 'email', 'user_type', 'status']);
foreach ($casUsers as $user) {
    echo "  ID: {$user->id} | Email: {$user->email} | Type: {$user->user_type} | Status: {$user->status}" . PHP_EOL;
}

echo PHP_EOL . "All users (first 20):" . PHP_EOL;
$allUsers = User::take(20)->get(['id', 'email', 'user_type', 'status']);
foreach ($allUsers as $user) {
    echo "  ID: {$user->id} | Email: {$user->email} | Type: {$user->user_type} | Status: " . ($user->status ?? 'null') . PHP_EOL;
}

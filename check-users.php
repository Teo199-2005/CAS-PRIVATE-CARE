<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== All Users ===\n\n";

$users = User::all();

foreach($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role}\n";
}

echo "\n=== Admin Users ===\n\n";
$admins = User::where('role', 'admin')->get();
echo "Found {$admins->count()} admin users\n";

foreach($admins as $admin) {
    echo "- {$admin->name} ({$admin->email})\n";
}

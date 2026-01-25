<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Fixing Admin User Role ===\n\n";

$admin = User::where('email', 'admin@demo.com')->first();

if (!$admin) {
    echo "❌ Admin user not found!\n";
    exit(1);
}

echo "Current user:\n";
echo "  ID: {$admin->id}\n";
echo "  Name: {$admin->name}\n";
echo "  Email: {$admin->email}\n";
echo "  Role: {$admin->role}\n\n";

if ($admin->role === 'admin') {
    echo "✅ User already has admin role\n";
} else {
    echo "Updating role to 'admin'...\n";
    $admin->role = 'admin';
    $admin->save();
    echo "✅ Successfully updated role to 'admin'\n";
}

echo "\n=== Verification ===\n";
$admin->refresh();
echo "Current role: {$admin->role}\n";

if ($admin->role === 'admin') {
    echo "✅ Admin user role is correct!\n";
} else {
    echo "❌ Something went wrong, role is still: {$admin->role}\n";
}

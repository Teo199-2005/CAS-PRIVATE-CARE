<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Admin Staff Users ===\n\n";

$adminStaff = DB::table('users')
    ->where('user_type', 'admin_staff')
    ->select('id', 'name', 'email', 'user_type', 'created_at')
    ->get();

if ($adminStaff->isEmpty()) {
    echo "❌ No admin staff users found!\n\n";
    
    echo "Available user types in system:\n";
    $userTypes = DB::table('users')
        ->select('user_type', DB::raw('COUNT(*) as count'))
        ->groupBy('user_type')
        ->get();
    
    foreach ($userTypes as $type) {
        echo "  • {$type->user_type}: {$type->count} users\n";
    }
} else {
    foreach ($adminStaff as $staff) {
        echo "ID: {$staff->id}\n";
        echo "Name: {$staff->name}\n";
        echo "Email: {$staff->email}\n";
        echo "User Type: {$staff->user_type}\n";
        echo "Created: {$staff->created_at}\n";
        echo "\n";
    }
    
    echo "✅ Default password for demo accounts: password\n";
}

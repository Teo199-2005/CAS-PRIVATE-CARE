<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;

echo "=== Creating Admin Staff Account ===\n\n";

// Check if admin staff already exists
$existing = DB::table('users')
    ->where('email', 'adminstaff@demo.com')
    ->first();

if ($existing) {
    echo "⚠️ Admin staff account already exists!\n";
    echo "Email: adminstaff@demo.com\n";
    echo "Updating password to: password\n\n";
    
    DB::table('users')
        ->where('email', 'adminstaff@demo.com')
        ->update([
            'password' => Hash::make('password'),
            'user_type' => 'admin_staff',
            'updated_at' => now()
        ]);
    
    echo "✅ Password updated successfully!\n";
} else {
    // Create new admin staff user
    $userId = DB::table('users')->insertGetId([
        'name' => 'Admin Staff',
        'email' => 'adminstaff@demo.com',
        'password' => Hash::make('password'),
        'user_type' => 'admin_staff',
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Admin Staff account created successfully!\n\n";
    echo "User ID: {$userId}\n";
}

echo "\n=== Login Credentials ===\n";
echo "Email: adminstaff@demo.com\n";
echo "Password: password\n";
echo "User Type: admin_staff\n\n";

echo "✅ You can now login at: http://127.0.0.1:8000/login\n";

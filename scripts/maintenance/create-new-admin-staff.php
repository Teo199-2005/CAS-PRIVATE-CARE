<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Admin Staff Account ===\n\n";

// Check if admin staff already exists
$existingStaff = User::where('email', 'staff@casprivatecare.com')->first();

if ($existingStaff) {
    echo "⚠️  Admin Staff account already exists!\n";
    echo "Email: {$existingStaff->email}\n";
    echo "Name: {$existingStaff->name}\n";
    echo "Role: {$existingStaff->role}\n\n";
    echo "Updating role to 'Admin Staff'...\n";
    $existingStaff->role = 'Admin Staff';
    $existingStaff->user_type = 'admin';
    $existingStaff->department = 'System Administration';
    $existingStaff->save();
    echo "✅ Updated successfully!\n";
    exit;
}

// Create new admin staff
try {
    $adminStaff = User::create([
        'name' => 'Admin Staff',
        'email' => 'staff@casprivatecare.com',
        'password' => Hash::make('AdminStaff@2024'),
        'user_type' => 'admin',
        'role' => 'Admin Staff',
        'department' => 'System Administration',
        'status' => 'active',
        'email_verified_at' => now(),
        'phone' => '(646) 555-0102',
    ]);

    echo "✅ Admin Staff account created successfully!\n\n";
    echo "📧 Email: staff@casprivatecare.com\n";
    echo "🔑 Password: AdminStaff@2024\n";
    echo "👤 Name: Admin Staff\n";
    echo "🎭 Role: Admin Staff\n";
    echo "🏢 Department: System Administration\n";
    echo "📱 Phone: (646) 555-0102\n\n";
    echo "🔒 Remember to change the password after first login!\n";

} catch (\Exception $e) {
    echo "❌ Error creating admin staff: " . $e->getMessage() . "\n";
}

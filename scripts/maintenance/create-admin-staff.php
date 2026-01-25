<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Admin Staff User ===\n\n";

// Check if admin staff already exists
$existingStaff = User::where('email', 'staff@demo.com')->first();

if ($existingStaff) {
    echo "✅ Admin Staff user already exists\n";
    echo "   Email: {$existingStaff->email}\n";
    echo "   Name: {$existingStaff->name}\n";
    echo "   User Type: {$existingStaff->user_type}\n";
    echo "   Role: {$existingStaff->role}\n";
    echo "\n❓ Do you want to update this user? This will reset the password to 'Password123!'\n";
    echo "Type 'yes' to continue or anything else to cancel: ";
    
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    if(trim($line) != 'yes'){
        echo "❌ Cancelled\n";
        exit(0);
    }
    fclose($handle);
    
    // Update existing user
    $existingStaff->user_type = 'admin';
    $existingStaff->role = 'Admin Staff';
    $existingStaff->status = 'Active';
    $existingStaff->password = Hash::make('Password123!');
    $existingStaff->email_verified_at = now();
    $existingStaff->save();
    
    echo "\n✅ Successfully updated Admin Staff user!\n";
} else {
    // Create new Admin Staff user
    $user = User::create([
        'name' => 'Admin Staff',
        'email' => 'staff@demo.com',
        'password' => Hash::make('Password123!'),
        'user_type' => 'admin',
        'role' => 'Admin Staff',
        'status' => 'Active',
        'email_verified_at' => now(),
    ]);
    
    echo "✅ Successfully created Admin Staff user!\n";
}

echo "\n=== Admin Staff User Details ===\n";
$staffUser = User::where('email', 'staff@demo.com')->first();
echo "ID: {$staffUser->id}\n";
echo "Name: {$staffUser->name}\n";
echo "Email: {$staffUser->email}\n";
echo "User Type: {$staffUser->user_type}\n";
echo "Role: {$staffUser->role}\n";
echo "Status: {$staffUser->status}\n";
echo "Email Verified: " . ($staffUser->email_verified_at ? 'Yes' : 'No') . "\n";
echo "\n=== Login Credentials ===\n";
echo "Email: staff@demo.com\n";
echo "Password: Password123!\n";
echo "\n🎉 You can now login and test the Admin Staff dashboard!\n";
echo "   The Admin Staff can only access:\n";
echo "   • View Users (Read-Only)\n";
echo "   • Contractors Application\n";
echo "   • Password Resets\n";
echo "   • Client Bookings\n";
echo "   • Time Tracking\n";
echo "   • Reviews & Ratings\n";
echo "   • Announcements\n";
echo "   • Profile\n";
echo "\n   Admin Staff CANNOT access:\n";
echo "   ✗ Dashboard Analytics\n";
echo "   ✗ Payments\n";
echo "   ✗ Full Admin Controls\n";

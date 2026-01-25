<?php
/**
 * Fix Housekeeper Account User Type
 * Run this file once to update the incorrectly created housekeeper account
 * 
 * Usage: php fix-housekeeper-account.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing housekeeper account user types...\n\n";

// Find users with caregiver type that should be housekeepers
// These are users who have partner_type data or who registered recently as housekeepers
$usersToFix = DB::table('users')
    ->where('user_type', 'caregiver')
    ->where('email', 'harrypogi007@gmail.com') // The email from your test
    ->get();

if ($usersToFix->count() === 0) {
    echo "No users found to fix.\n";
    exit(0);
}

foreach ($usersToFix as $user) {
    echo "Updating user: {$user->email} (ID: {$user->id})\n";
    echo "  Current user_type: {$user->user_type}\n";
    
    // Update to housekeeper type
    DB::table('users')
        ->where('id', $user->id)
        ->update(['user_type' => 'housekeeper']);
    
    echo "  ✓ Updated to user_type: housekeeper\n";
    
    // Check if there's a caregiver record we need to migrate to housekeeper table
    $caregiverRecord = DB::table('caregivers')->where('user_id', $user->id)->first();
    
    if ($caregiverRecord) {
        echo "  Found caregiver record, checking for housekeeper record...\n";
        
        // Check if housekeeper record already exists
        $housekeeperRecord = DB::table('housekeepers')->where('user_id', $user->id)->first();
        
        if (!$housekeeperRecord) {
            // Create housekeeper record from caregiver data
            DB::table('housekeepers')->insert([
                'user_id' => $user->id,
                'gender' => $caregiverRecord->gender ?? 'female',
                'availability_status' => $caregiverRecord->availability_status ?? 'available',
                'years_experience' => $caregiverRecord->years_experience ?? 0,
                'created_at' => $caregiverRecord->created_at ?? now(),
                'updated_at' => now()
            ]);
            echo "  ✓ Created housekeeper record\n";
            
            // Optionally delete the caregiver record
            // DB::table('caregivers')->where('user_id', $user->id)->delete();
            // echo "  ✓ Deleted caregiver record\n";
        } else {
            echo "  Housekeeper record already exists\n";
        }
    }
    
    echo "\n";
}

echo "Done! Fixed {$usersToFix->count()} user(s).\n";
echo "\nNow logout and login again with: harrypogi007@gmail.com\n";
echo "You should be redirected to /housekeeper/dashboard-vue\n";

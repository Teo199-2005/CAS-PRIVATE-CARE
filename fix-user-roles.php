<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "=== SYNCHRONIZING ROLE AND USER_TYPE FIELDS ===\n\n";

$users = User::all();
$updated = 0;

foreach ($users as $user) {
    $oldRole = $user->role;
    $userType = $user->user_type;
    
    // Map user_type to proper role
    $roleMapping = [
        'admin' => 'admin',
        'client' => 'client',
        'caregiver' => 'caregiver',
        'marketing' => 'marketing_staff',
        'training' => 'training_center',
    ];
    
    $newRole = $roleMapping[$userType] ?? 'client';
    
    if ($oldRole !== $newRole) {
        $user->role = $newRole;
        $user->save();
        echo "✓ Updated User #{$user->id} ({$user->name})\n";
        echo "  user_type: {$userType}\n";
        echo "  old role: {$oldRole}\n";
        echo "  new role: {$newRole}\n\n";
        $updated++;
    } else {
        echo "→ User #{$user->id} ({$user->name}) already correct: {$newRole}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total users: " . $users->count() . "\n";
echo "Updated: {$updated}\n\n";

echo "=== NEW ROLE DISTRIBUTION ===\n";
$roleDistribution = User::select('role', DB::raw('count(*) as count'))
    ->groupBy('role')
    ->get();

foreach ($roleDistribution as $role) {
    echo "{$role->role}: {$role->count}\n";
}

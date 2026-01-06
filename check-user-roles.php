<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Caregiver;

echo "=== CHECKING ACTUAL USER ROLES ===\n\n";

$users = User::all();
foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: " . ($user->role ?? 'NULL') . "\n";
    echo "User Type: " . ($user->user_type ?? 'NULL') . "\n";
    
    // Check if they have caregiver profile
    $caregiver = Caregiver::where('user_id', $user->id)->first();
    if ($caregiver) {
        echo "Has Caregiver Profile: YES (ID: {$caregiver->id})\n";
    }
    
    echo str_repeat("-", 60) . "\n\n";
}

echo "\n=== ROLE DISTRIBUTION ===\n";
$roleDistribution = User::select('role', DB::raw('count(*) as count'))
    ->groupBy('role')
    ->get();

foreach ($roleDistribution as $role) {
    echo "{$role->role}: {$role->count}\n";
}

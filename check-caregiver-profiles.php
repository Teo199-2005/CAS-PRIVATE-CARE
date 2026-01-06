<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Caregiver;
use App\Models\User;

echo "=== Checking Caregiver Profiles ===\n\n";

$caregivers = Caregiver::all();
echo "Total Caregiver Profiles: {$caregivers->count()}\n\n";

foreach ($caregivers as $cg) {
    $user = User::find($cg->user_id);
    echo "Caregiver ID: {$cg->id}\n";
    echo "  User ID: {$cg->user_id}\n";
    echo "  User Name: " . ($user ? $user->name : 'NOT FOUND') . "\n";
    echo "  User Email: " . ($user ? $user->email : 'NOT FOUND') . "\n";
    echo "\n";
}

echo "=== Check Complete ===\n";

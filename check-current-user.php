<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Authentication ===\n\n";

// Get session data
session_start();
if (isset($_SESSION['user_id'])) {
    echo "Session User ID: " . $_SESSION['user_id'] . "\n";
    
    $user = \App\Models\User::find($_SESSION['user_id']);
    if ($user) {
        echo "User: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Type: {$user->user_type}\n";
        echo "Status: {$user->status}\n";
    }
} else {
    echo "⚠ No session found - User might not be logged in\n";
}

echo "\n=== Available Caregivers ===\n";
$caregivers = \App\Models\User::where('user_type', 'caregiver')->get();
foreach ($caregivers as $cg) {
    echo "- {$cg->name} ({$cg->email})\n";
    echo "  ID: {$cg->id}, Status: {$cg->status}\n";
    echo "  Stripe Connect ID: " . ($cg->stripe_connect_id ?? 'Not connected') . "\n\n";
}

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::find(1);

echo "=== LOGGED IN USER (ID: 1) ===\n\n";
echo "Name: " . $user->name . "\n";
echo "Email: " . $user->email . "\n";
echo "user_type: " . $user->user_type . "\n";
echo "role: " . $user->role . "\n";
echo "status: " . $user->status . "\n";

echo "\n=== MIDDLEWARE CHECK ===\n";
echo "Route requires user_type = 'admin'\n";
echo "Your user_type is: '" . $user->user_type . "'\n";

if (strtolower($user->user_type) === 'admin') {
    echo "✅ MATCH! You should have access.\n";
} else {
    echo "❌ MISMATCH! Your user_type is '" . $user->user_type . "' but route expects 'admin'\n";
    echo "\n=== FIX ===\n";
    echo "Run this SQL to fix:\n";
    echo "UPDATE users SET user_type = 'admin' WHERE id = 1;\n";
}

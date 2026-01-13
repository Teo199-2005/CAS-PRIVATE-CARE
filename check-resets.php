<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PasswordReset;

echo "=== Pending Password Resets ===\n\n";

$resets = PasswordReset::where('status', 'pending')->get();

if ($resets->isEmpty()) {
    echo "No pending password resets found.\n";
} else {
    foreach ($resets as $reset) {
        echo "Email: {$reset->email}\n";
        echo "Status: {$reset->status}\n";
        echo "Requested At: {$reset->requested_at}\n";
        echo "Token (first 20 chars): " . substr($reset->token, 0, 20) . "...\n";
        echo "---\n";
    }
}

echo "\n=== All Password Resets (last 5) ===\n\n";
$allResets = PasswordReset::orderBy('id', 'desc')->take(5)->get();
foreach ($allResets as $reset) {
    echo "Email: {$reset->email} | Status: {$reset->status} | Requested: {$reset->requested_at}\n";
}

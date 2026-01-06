<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Checking Bank Account Connection ===\n\n";

$caregivers = User::where('user_type', 'caregiver')->get();

foreach ($caregivers as $cg) {
    echo "Caregiver: {$cg->name} ({$cg->email})\n";
    echo "  ID: {$cg->id}\n";
    echo "  Stripe Connect ID: " . ($cg->stripe_connect_id ?? 'NOT CONNECTED') . "\n";
    echo "  Bank Account Last 4: " . ($cg->bank_account_last_four ?? 'N/A') . "\n";
    echo "  Bank Name: " . ($cg->bank_name ?? 'N/A') . "\n";
    echo "  Status: {$cg->status}\n";
    echo "\n";
}

echo "=== Check Complete ===\n";

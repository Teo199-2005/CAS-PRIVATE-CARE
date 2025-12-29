<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== USER CHECK ===\n\n";

$clientByEmail = User::where('email', 'client@demo.com')->first();
$clientByName = User::where('name', 'Demo Client')->first();

echo "User by email (client@demo.com): " . ($clientByEmail ? $clientByEmail->name . ' (ID: ' . $clientByEmail->id . ')' : 'Not found') . "\n";
echo "User by name (Demo Client): " . ($clientByName ? $clientByName->name . ' (ID: ' . $clientByName->id . ')' : 'Not found') . "\n";

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all client users
$users = DB::table('users')
    ->where('user_type', 'client')
    ->select('id', 'name', 'email', 'user_type', 'stripe_customer_id')
    ->get();

echo "CLIENT USERS IN DATABASE:\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "User Type: {$user->user_type}\n";
    echo "Stripe Customer ID: " . ($user->stripe_customer_id ?? 'Not set') . "\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\nTotal client users: " . count($users) . "\n";

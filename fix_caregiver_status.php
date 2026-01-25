<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'Caregiver1@gmail.com')->first();
if ($user) {
    $user->status = 'pending';
    $user->save();
    echo "Fixed! Caregiver One status is now: " . $user->status . "\n";
} else {
    echo "User not found\n";
}

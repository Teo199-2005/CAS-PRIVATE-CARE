<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('name', 'Demo Caregiver')->first();
if ($user) {
    echo "User ID: " . $user->id . PHP_EOL;
    echo "Avatar: " . ($user->avatar ?: 'NULL') . PHP_EOL;
    echo "User Type: " . $user->user_type . PHP_EOL;
} else {
    echo "Demo Caregiver not found" . PHP_EOL;
}

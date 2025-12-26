<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::find(31);
echo "User ID: " . $user->id . "\n";
echo "User Name: " . $user->name . "\n";
echo "User JSON:\n" . json_encode($user->toArray(), JSON_PRETTY_PRINT) . "\n";

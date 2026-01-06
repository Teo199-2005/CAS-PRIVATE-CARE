<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Current Database Status:\n";
echo "- Time Tracking Records: " . \App\Models\TimeTracking::count() . "\n";
echo "- Bookings: " . \App\Models\Booking::count() . "\n";
echo "- Payments: " . \App\Models\Payment::count() . "\n";
echo "- Users: " . \App\Models\User::count() . "\n";

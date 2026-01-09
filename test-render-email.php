<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = view('emails.recurring-reminder', [
    'client_name' => 'Test User',
    'days_until_renewal' => 5,
    'renewal_date' => 'January 14, 2026',
    'booking_id' => 1,
    'service_type' => 'Caregiver',
    'duration_days' => 15,
    'amount' => '5,400.00',
    'hours_per_day' => 8
])->render();

file_put_contents('test-email.html', $html);
echo "HTML saved to test-email.html\n";
echo "Length: " . strlen($html) . " characters\n";
echo "\nFirst 500 chars:\n";
echo substr($html, 0, 500);

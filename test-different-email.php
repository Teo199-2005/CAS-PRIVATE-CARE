<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

// CHANGE THIS to a different email address!
$testEmail = 'ENTER_DIFFERENT_EMAIL_HERE@example.com';

if ($testEmail === 'ENTER_DIFFERENT_EMAIL_HERE@example.com') {
    echo "⚠️  Please edit this file and change the email address first!\n";
    echo "    File: test-different-email.php\n";
    echo "    Change line 10 to your other email address.\n";
    exit(1);
}

echo "Sending test to: $testEmail\n";

Mail::raw('Test email to different address - ' . date('Y-m-d H:i:s'), function($m) use ($testEmail) {
    $m->to($testEmail)
      ->subject('CAS Test - ' . date('H:i:s'));
});

echo "✅ Email sent to $testEmail!\n";
echo "Check Brevo logs to see if this one delivers.\n";

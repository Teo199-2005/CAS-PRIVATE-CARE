<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "Sending single test email...\n";

Mail::raw('Single test email - sent at ' . date('Y-m-d H:i:s') . "\n\nIf you receive this, emails are working!", function($m) {
    $m->to('teofiloharry69@gmail.com')
      ->subject('Final Test - ' . date('H:i:s'));
});

echo "✅ Email sent!\n";
echo "Check Brevo logs in 1-2 minutes to see if it's Delivered or Soft Bounced.\n";

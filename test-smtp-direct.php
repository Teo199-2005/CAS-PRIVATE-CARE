<?php

/**
 * Direct SMTP Test - Debug Email Sending
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "===========================================\n";
echo "   DIRECT SMTP EMAIL TEST\n";
echo "===========================================\n\n";

echo "Mail Configuration:\n";
echo "  MAILER: " . config('mail.default') . "\n";
echo "  HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "  PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "  FROM: " . config('mail.from.address') . "\n\n";

echo "Sending test email to: teofiloharry69@gmail.com\n\n";

try {
    Mail::raw('This is a test email from CAS Private Care to verify email delivery is working. Sent at: ' . now()->toString(), function ($message) {
        $message->to('teofiloharry69@gmail.com')
                ->subject('CAS Private Care - Email Test ' . date('H:i:s'));
    });
    
    echo "✅ Email sent successfully!\n";
    echo "\nPlease check:\n";
    echo "  1. Your inbox at teofiloharry69@gmail.com\n";
    echo "  2. Spam/Junk folder\n";
    echo "  3. Promotions tab (Gmail)\n";
    echo "  4. All Mail folder\n";
    
} catch (\Exception $e) {
    echo "❌ Error sending email:\n";
    echo $e->getMessage() . "\n\n";
    echo "Full error:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n===========================================\n";

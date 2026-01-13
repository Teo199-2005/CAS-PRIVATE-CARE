<?php
/**
 * Email Configuration Diagnostic Script
 * Run this to check your Brevo email configuration
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Brevo Email Configuration Check ===\n\n";

// Check .env file
echo "1. Checking .env configuration...\n";
$mailer = env('MAIL_MAILER');
$host = env('MAIL_HOST');
$port = env('MAIL_PORT');
$username = env('MAIL_USERNAME');
$password = env('MAIL_PASSWORD');
$encryption = env('MAIL_ENCRYPTION');
$fromAddress = env('MAIL_FROM_ADDRESS');
$fromName = env('MAIL_FROM_NAME');

echo "   MAIL_MAILER: " . ($mailer ?: 'NOT SET') . "\n";
echo "   MAIL_HOST: " . ($host ?: 'NOT SET') . "\n";
echo "   MAIL_PORT: " . ($port ?: 'NOT SET') . "\n";
echo "   MAIL_USERNAME: " . ($username ?: 'NOT SET') . "\n";
echo "   MAIL_PASSWORD: " . ($password ? 'SET (' . strlen($password) . ' chars)' : 'NOT SET') . "\n";
echo "   MAIL_ENCRYPTION: " . ($encryption ?: 'NOT SET') . "\n";
echo "   MAIL_FROM_ADDRESS: " . ($fromAddress ?: 'NOT SET') . "\n";
echo "   MAIL_FROM_NAME: " . ($fromName ?: 'NOT SET') . "\n\n";

// Validate configuration
$errors = [];
if (!$mailer || $mailer !== 'smtp') {
    $errors[] = "MAIL_MAILER should be 'smtp'";
}
if (!$host || $host !== 'smtp-relay.brevo.com') {
    $errors[] = "MAIL_HOST should be 'smtp-relay.brevo.com'";
if (!$port || $port != 587) {
    $errors[] = "MAIL_PORT should be 587";
}
if (!$username) {
    $errors[] = "MAIL_USERNAME is required";
}
if (!$password) {
    $errors[] = "MAIL_PASSWORD is required";
}
if (!$encryption || $encryption !== 'tls') {
    $errors[] = "MAIL_ENCRYPTION should be 'tls'";
}
if (!$fromAddress) {
    $errors[] = "MAIL_FROM_ADDRESS is required";
}

if (count($errors) > 0) {
    echo "❌ Configuration Errors Found:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
    echo "\n";
} else {
    echo "✅ Basic configuration looks correct\n\n";
}

// Check if password looks valid
if ($password) {
    if (strpos($password, 'xsmtpsib-') !== 0) {
        echo "⚠️  WARNING: SMTP password should start with 'xsmtpsib-'\n";
        echo "   Your password starts with: " . substr($password, 0, 20) . "...\n\n";
    } else {
        echo "✅ SMTP password format looks correct\n\n";
    }
}

// Check if username matches from address
if ($username && $fromAddress && $username !== $fromAddress) {
    echo "⚠️  WARNING: MAIL_USERNAME and MAIL_FROM_ADDRESS don't match\n";
    echo "   MAIL_USERNAME: $username\n";
    echo "   MAIL_FROM_ADDRESS: $fromAddress\n";
    echo "   For Brevo, these should usually be the same email address\n\n";
}

echo "=== Next Steps ===\n\n";
echo "1. Verify sender email in Brevo:\n";
echo "   - Go to: https://www.brevo.com\n";
echo "   - Settings → Senders, domains, IPs → Senders\n";
echo "   - Add and verify: $fromAddress\n\n";

echo "2. If sender is verified, check SMTP password:\n";
echo "   - Go to: Brevo → Settings → SMTP & API → SMTP\n";
echo "   - Verify the password matches your .env file\n";
echo "   - If not, generate a new one and update .env\n\n";

echo "3. Clear Laravel cache:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";

echo "4. Test email sending again\n\n";




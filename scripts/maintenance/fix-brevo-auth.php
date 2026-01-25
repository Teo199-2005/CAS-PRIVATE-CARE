<?php
/**
 * Fix Brevo Authentication Issue
 * This script will help identify and fix the authentication problem
 */

echo "=== Brevo Email Configuration Diagnostic ===\n\n";

// Check if .env exists
if (!file_exists('.env')) {
    echo "❌ ERROR: .env file not found!\n";
    echo "Please make sure you're running this from the project root directory.\n";
    exit(1);
}

// Read .env file
$envContent = file_get_contents('.env');
$lines = explode("\n", $envContent);

$config = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) {
        continue;
    }
    
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $config[trim($key)] = trim($value);
    }
}

echo "Current Configuration:\n";
echo "----------------------\n";
echo "MAIL_MAILER: " . ($config['MAIL_MAILER'] ?? 'NOT SET') . "\n";
echo "MAIL_HOST: " . ($config['MAIL_HOST'] ?? 'NOT SET') . "\n";
echo "MAIL_PORT: " . ($config['MAIL_PORT'] ?? 'NOT SET') . "\n";
echo "MAIL_USERNAME: " . ($config['MAIL_USERNAME'] ?? 'NOT SET') . "\n";
echo "MAIL_PASSWORD: " . (isset($config['MAIL_PASSWORD']) ? 'SET (' . strlen($config['MAIL_PASSWORD']) . ' chars)' : 'NOT SET') . "\n";
echo "MAIL_ENCRYPTION: " . ($config['MAIL_ENCRYPTION'] ?? 'NOT SET') . "\n";
echo "MAIL_FROM_ADDRESS: " . ($config['MAIL_FROM_ADDRESS'] ?? 'NOT SET') . "\n";
echo "MAIL_FROM_NAME: " . ($config['MAIL_FROM_NAME'] ?? 'NOT SET') . "\n\n";

// Check for common issues
$issues = [];

// Check if password starts with xsmtpsib
if (isset($config['MAIL_PASSWORD']) && strpos($config['MAIL_PASSWORD'], 'xsmtpsib-') !== 0) {
    $issues[] = "SMTP password should start with 'xsmtpsib-'";
}

// Check if host is correct
if (($config['MAIL_HOST'] ?? '') !== 'smtp-relay.brevo.com') {
    $issues[] = "MAIL_HOST should be 'smtp-relay.brevo.com'";
}

// Check if port is correct
if (($config['MAIL_PORT'] ?? '') !== '587') {
    $issues[] = "MAIL_PORT should be '587'";
}

// Check if encryption is correct
if (($config['MAIL_ENCRYPTION'] ?? '') !== 'tls') {
    $issues[] = "MAIL_ENCRYPTION should be 'tls'";
}

if (count($issues) > 0) {
    echo "⚠️  Configuration Issues Found:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
    echo "\n";
}

echo "=== SOLUTION STEPS ===\n\n";
echo "The authentication error is almost always caused by:\n";
echo "1. Sender email not verified in Brevo\n";
echo "2. Wrong SMTP password\n\n";

echo "STEP 1: Verify Sender Email in Brevo (REQUIRED)\n";
echo "------------------------------------------------\n";
echo "1. Go to: https://www.brevo.com\n";
echo "2. Log in to your account\n";
echo "3. Click 'Settings' → 'Senders, domains, IPs' → 'Senders'\n";
echo "4. Check if '{$config['MAIL_FROM_ADDRESS']}' is listed\n";
echo "5. If NOT, click 'Add a sender' and add it\n";
echo "6. Check your email inbox for verification email\n";
echo "7. Click the verification link\n";
echo "8. Status should show 'Verified' ✅\n\n";

echo "STEP 2: Verify SMTP Password\n";
echo "-----------------------------\n";
echo "1. Go to Brevo → Settings → SMTP & API → SMTP tab\n";
echo "2. Check your SMTP password\n";
echo "3. If it doesn't match your .env file, generate a new one\n";
echo "4. Update MAIL_PASSWORD in .env file\n\n";

echo "STEP 3: Important Note about MAIL_USERNAME\n";
echo "-------------------------------------------\n";
echo "MAIL_USERNAME should be your Brevo ACCOUNT EMAIL (the one you log in with)\n";
echo "MAIL_FROM_ADDRESS should be your VERIFIED SENDER EMAIL\n";
echo "These CAN be different emails!\n\n";

echo "Current values:\n";
echo "  MAIL_USERNAME: {$config['MAIL_USERNAME']}\n";
echo "  MAIL_FROM_ADDRESS: {$config['MAIL_FROM_ADDRESS']}\n\n";

if (($config['MAIL_USERNAME'] ?? '') !== ($config['MAIL_FROM_ADDRESS'] ?? '')) {
    echo "⚠️  These are different - this is OK if MAIL_USERNAME is your Brevo login email\n\n";
}

echo "STEP 4: Clear Laravel Cache\n";
echo "---------------------------\n";
echo "Run these commands:\n";
echo "  php artisan config:clear\n";
echo "  php artisan cache:clear\n\n";

echo "STEP 5: Test Again\n";
echo "------------------\n";
echo "Try sending a test email from the admin dashboard.\n\n";




<?php

/**
 * CAS Private Care - Production Deployment Checklist
 * 
 * Run this script before deploying to production:
 * php production-checklist.php
 */

echo "🚀 CAS PRIVATE CARE - PRODUCTION DEPLOYMENT CHECKLIST\n";
echo str_repeat("=", 60) . "\n\n";

$checks = [];
$warnings = [];
$errors = [];

// Helper function
function check($name, $condition, $errorMessage = '') {
    global $checks, $warnings, $errors;
    
    if ($condition) {
        $checks[] = "✅ {$name}";
        return true;
    } else {
        $errors[] = "❌ {$name}" . ($errorMessage ? " - {$errorMessage}" : "");
        return false;
    }
}

function warn($name, $message) {
    global $warnings;
    $warnings[] = "⚠️ {$name} - {$message}";
}

// =================================
// ENVIRONMENT CHECKS
// =================================
echo "📋 ENVIRONMENT CONFIGURATION\n";
echo str_repeat("-", 50) . "\n";

// Load .env if exists
$envFile = __DIR__ . '/.env';
$envExists = file_exists($envFile);
check('.env file exists', $envExists, 'Copy .env.example to .env');

if ($envExists) {
    $envContent = file_get_contents($envFile);
    
    // Check critical settings
    check('APP_ENV is production', str_contains($envContent, 'APP_ENV=production'), 'Set APP_ENV=production');
    check('APP_DEBUG is false', str_contains($envContent, 'APP_DEBUG=false'), 'Set APP_DEBUG=false');
    check('APP_KEY is set', preg_match('/APP_KEY=base64:.+/', $envContent), 'Run: php artisan key:generate');
    check('SESSION_ENCRYPT is true', str_contains($envContent, 'SESSION_ENCRYPT=true'), 'Set SESSION_ENCRYPT=true');
    check('LOG_LEVEL is error', str_contains($envContent, 'LOG_LEVEL=error'), 'Set LOG_LEVEL=error for production');
    
    // Database
    check('DB_CONNECTION is mysql', str_contains($envContent, 'DB_CONNECTION=mysql'), 'Use MySQL for production');
    
    // Email
    $mailLog = str_contains($envContent, 'MAIL_MAILER=log');
    if ($mailLog) {
        warn('MAIL_MAILER', 'Currently set to log - emails won\'t be sent');
    }
    
    // Stripe
    $hasLiveStripe = str_contains($envContent, 'pk_live_') || str_contains($envContent, 'sk_live_');
    $hasTestStripe = str_contains($envContent, 'pk_test_') || str_contains($envContent, 'sk_test_');
    if ($hasTestStripe && !$hasLiveStripe) {
        warn('STRIPE_KEY', 'Using test keys - switch to live keys for production');
    }
}

echo "\n";

// =================================
// FILE CHECKS
// =================================
echo "📁 FILE & DIRECTORY CHECKS\n";
echo str_repeat("-", 50) . "\n";

check('storage/logs is writable', is_writable(__DIR__ . '/storage/logs'), 'Set proper permissions');
check('storage/framework/views is writable', is_writable(__DIR__ . '/storage/framework/views'), 'Set proper permissions');
check('bootstrap/cache is writable', is_writable(__DIR__ . '/bootstrap/cache'), 'Set proper permissions');
check('public/.htaccess exists', file_exists(__DIR__ . '/public/.htaccess'), 'Apache rewrite rules needed');

echo "\n";

// =================================
// SECURITY CHECKS
// =================================
echo "🔒 SECURITY CHECKS\n";
echo str_repeat("-", 50) . "\n";

// Check for debug routes
$webRoutes = file_get_contents(__DIR__ . '/routes/web.php');
check('No test/debug routes exposed', !str_contains($webRoutes, 'Route::get(\'/test\''), 'Remove debug routes');

// Check middleware
$bootstrapApp = file_get_contents(__DIR__ . '/bootstrap/app.php');
check('SecurityHeaders middleware registered', str_contains($bootstrapApp, 'SecurityHeaders'), 'Add SecurityHeaders middleware');
check('RateLimitMiddleware registered', str_contains($bootstrapApp, 'RateLimitMiddleware'), 'Add rate limiting');

// Check for hardcoded credentials
$adminController = file_get_contents(__DIR__ . '/app/Http/Controllers/AdminController.php');
check('No hardcoded passwords', !preg_match('/password\s*[=:]\s*[\'"](?!password)[\'"123456]+/', $adminController), 'Remove hardcoded passwords');

echo "\n";

// =================================
// DEPENDENCY CHECKS
// =================================
echo "📦 DEPENDENCY CHECKS\n";
echo str_repeat("-", 50) . "\n";

check('composer.lock exists', file_exists(__DIR__ . '/composer.lock'), 'Run: composer install');
check('vendor directory exists', is_dir(__DIR__ . '/vendor'), 'Run: composer install');
check('node_modules exists', is_dir(__DIR__ . '/node_modules'), 'Run: npm install');
check('public/build exists', is_dir(__DIR__ . '/public/build'), 'Run: npm run build');

echo "\n";

// =================================
// DATABASE CHECKS
// =================================
echo "🗄️ DATABASE CHECKS\n";
echo str_repeat("-", 50) . "\n";

// Check if migrations are up to date
$migrationsDir = __DIR__ . '/database/migrations';
$migrationCount = count(glob($migrationsDir . '/*.php'));
check("Migrations exist ({$migrationCount} files)", $migrationCount > 0, 'No migrations found');

echo "\n";

// =================================
// CONFIGURATION CHECKS
// =================================
echo "⚙️ CONFIGURATION CHECKS\n";
echo str_repeat("-", 50) . "\n";

// Check timezone
$appConfig = file_get_contents(__DIR__ . '/config/app.php');
check('Timezone is America/New_York', str_contains($appConfig, "'timezone' => 'America/New_York'"), 'Fix timezone setting');

// Check session config
$sessionConfig = file_get_contents(__DIR__ . '/config/session.php');
check('Session driver is database', str_contains($sessionConfig, "'driver' => env('SESSION_DRIVER', 'database')"), 'Use database sessions');

echo "\n";

// =================================
// SUMMARY
// =================================
echo str_repeat("=", 60) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

$totalChecks = count($checks);
$passedChecks = count(array_filter($checks, fn($c) => str_starts_with($c, '✅')));
$failedChecks = count($errors);
$warningCount = count($warnings);

echo "✅ Passed: {$passedChecks}/{$totalChecks}\n";
echo "❌ Failed: {$failedChecks}\n";
echo "⚠️ Warnings: {$warningCount}\n\n";

if (!empty($errors)) {
    echo "❌ ERRORS (Must Fix):\n";
    foreach ($errors as $error) {
        echo "   {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️ WARNINGS (Review):\n";
    foreach ($warnings as $warning) {
        echo "   {$warning}\n";
    }
    echo "\n";
}

if ($failedChecks === 0) {
    echo "🎉 ALL CHECKS PASSED! Ready for deployment.\n\n";
    echo "📝 DEPLOYMENT COMMANDS:\n";
    echo "   1. php artisan config:cache\n";
    echo "   2. php artisan route:cache\n";
    echo "   3. php artisan view:cache\n";
    echo "   4. php artisan migrate --force\n";
    echo "   5. npm run build\n";
    exit(0);
} else {
    echo "❌ FIX ERRORS BEFORE DEPLOYING!\n";
    exit(1);
}

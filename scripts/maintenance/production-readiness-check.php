<?php

/**
 * CAS Private Care - Production Readiness Check
 * Run this script before deploying to production
 * 
 * Usage: php production-readiness-check.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     CAS PRIVATE CARE - PRODUCTION READINESS CHECK          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

function check($name, $condition, $message = '', $isWarning = false) {
    global $passed, $failed, $warnings;
    
    if ($condition) {
        echo "  ✅ PASS: {$name}\n";
        $passed++;
        return true;
    } else {
        if ($isWarning) {
            echo "  ⚠️  WARN: {$name}" . ($message ? " - {$message}" : "") . "\n";
            $warnings++;
        } else {
            echo "  ❌ FAIL: {$name}" . ($message ? " - {$message}" : "") . "\n";
            $failed++;
        }
        return false;
    }
}

// =================================
// ENVIRONMENT CHECKS
// =================================
echo "📦 ENVIRONMENT CONFIGURATION\n";
echo str_repeat("-", 50) . "\n";

check('APP_ENV is production', env('APP_ENV') === 'production', 'Currently: ' . env('APP_ENV'));
check('APP_DEBUG is false', env('APP_DEBUG') === false, 'Currently: ' . (env('APP_DEBUG') ? 'true' : 'false'));
check('APP_KEY is set', !empty(env('APP_KEY')), 'Generate with: php artisan key:generate');
check('APP_URL is not localhost', !str_contains(env('APP_URL', 'localhost'), 'localhost'), 'Currently: ' . env('APP_URL'));

// =================================
// DATABASE CHECKS
// =================================
echo "\n📊 DATABASE\n";
echo str_repeat("-", 50) . "\n";

try {
    $pdo = DB::connection()->getPdo();
    check('Database connection', true);
    
    $userCount = \App\Models\User::count();
    check('Users table accessible', $userCount >= 0, "Found {$userCount} users");
    
    $bookingCount = \App\Models\Booking::count();
    check('Bookings table accessible', $bookingCount >= 0, "Found {$bookingCount} bookings");
    
} catch (\Exception $e) {
    check('Database connection', false, $e->getMessage());
}

// =================================
// STRIPE CHECKS
// =================================
echo "\n💳 STRIPE INTEGRATION\n";
echo str_repeat("-", 50) . "\n";

$stripeKey = env('STRIPE_KEY', '');
$stripeSecret = env('STRIPE_SECRET', '');
$stripeWebhook = env('STRIPE_WEBHOOK_SECRET', '');

check('STRIPE_KEY is set', !empty($stripeKey), 'Required for payments');
check('STRIPE_KEY is live key', str_starts_with($stripeKey, 'pk_live_'), 'Currently using: ' . (str_starts_with($stripeKey, 'pk_test_') ? 'TEST key' : 'Unknown'), true);
check('STRIPE_SECRET is set', !empty($stripeSecret), 'Required for payments');
check('STRIPE_SECRET is live key', str_starts_with($stripeSecret, 'sk_live_'), 'Currently using: ' . (str_starts_with($stripeSecret, 'sk_test_') ? 'TEST key' : 'Unknown'), true);
check('STRIPE_WEBHOOK_SECRET is set', !empty($stripeWebhook), 'Required for payment webhooks', true);

// =================================
// EMAIL CHECKS
// =================================
echo "\n📧 EMAIL CONFIGURATION\n";
echo str_repeat("-", 50) . "\n";

$mailMailer = env('MAIL_MAILER', 'log');
check('Mail driver is not log', $mailMailer !== 'log', 'Currently: ' . $mailMailer);
check('MAIL_HOST is set', !empty(env('MAIL_HOST')), 'Required for sending emails');
check('MAIL_FROM_ADDRESS is set', !empty(env('MAIL_FROM_ADDRESS')), 'Required for sending emails');

// =================================
// SECURITY CHECKS
// =================================
echo "\n🔒 SECURITY\n";
echo str_repeat("-", 50) . "\n";

// Check for hardcoded passwords
$adminController = file_get_contents(__DIR__ . '/app/Http/Controllers/AdminController.php');
check('No hardcoded dev passwords', !str_contains($adminController, "'123456'"), 'Found hardcoded password in AdminController');

// Check for debug routes
$webRoutes = file_get_contents(__DIR__ . '/routes/web.php');
check('Login has throttle protection', str_contains($webRoutes, "Route::post('/login'") && str_contains($webRoutes, "throttle"), 'Add throttle middleware to login');
check('Register has throttle protection', str_contains($webRoutes, "Route::post('/register'") && str_contains($webRoutes, "throttle"), 'Add throttle middleware to register');

// Check session config
check('Session driver is not file in production', !(env('APP_ENV') === 'production' && env('SESSION_DRIVER') === 'file'), 'Consider using database or redis for sessions', true);

// =================================
// FILE PERMISSIONS
// =================================
echo "\n📁 FILE SYSTEM\n";
echo str_repeat("-", 50) . "\n";

check('Storage directory exists', is_dir(__DIR__ . '/storage'), 'Required for file storage');
check('Storage is writable', is_writable(__DIR__ . '/storage'), 'Required for logs and cache');
check('Bootstrap cache is writable', is_writable(__DIR__ . '/bootstrap/cache'), 'Required for config cache');

// =================================
// ADMIN ACCOUNTS
// =================================
echo "\n👤 ADMIN ACCOUNTS\n";
echo str_repeat("-", 50) . "\n";

$adminUsers = \App\Models\User::where('user_type', 'admin')->get();
check('At least one admin exists', $adminUsers->count() > 0, 'Create an admin user');

foreach ($adminUsers as $admin) {
    $hasStrongPassword = strlen($admin->password) > 50; // Bcrypt hashes are 60 chars
    check("Admin '{$admin->email}' has hashed password", $hasStrongPassword);
}

// =================================
// CACHE & OPTIMIZATION
// =================================
echo "\n⚡ OPTIMIZATION\n";
echo str_repeat("-", 50) . "\n";

$configCached = file_exists(__DIR__ . '/bootstrap/cache/config.php');
check('Config is cached', $configCached, 'Run: php artisan config:cache', true);

$routesCached = file_exists(__DIR__ . '/bootstrap/cache/routes-v7.php');
check('Routes are cached', $routesCached, 'Run: php artisan route:cache', true);

// =================================
// SUMMARY
// =================================
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                        SUMMARY                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "  ✅ Passed:   {$passed}\n";
echo "  ⚠️  Warnings: {$warnings}\n";
echo "  ❌ Failed:   {$failed}\n";
echo "\n";

if ($failed === 0 && $warnings === 0) {
    echo "  🎉 EXCELLENT! Your site is fully production-ready!\n";
} elseif ($failed === 0) {
    echo "  ✅ GOOD! Your site is production-ready with minor warnings.\n";
} else {
    echo "  ⚠️  ACTION REQUIRED: Fix the failed checks before deploying.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";

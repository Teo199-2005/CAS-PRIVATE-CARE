<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Production Security Service
 * 
 * This service validates that the application is properly configured
 * for production environments. It checks for common security misconfigurations
 * and blocks deployment if critical issues are found.
 * 
 * @package App\Services
 * @version 1.0.0
 * @since 2026-01-28
 */
class ProductionSecurityService
{
    /**
     * Critical issues that MUST be fixed before production
     */
    protected array $criticalIssues = [];

    /**
     * Warning issues that should be reviewed
     */
    protected array $warnings = [];

    /**
     * Run all security checks
     * 
     * @return array{passed: bool, critical: array, warnings: array}
     */
    public function runAllChecks(): array
    {
        $this->criticalIssues = [];
        $this->warnings = [];

        // Run all checks
        $this->checkEnvironment();
        $this->checkDebugMode();
        $this->checkAppKey();
        $this->checkStripeKeys();
        $this->checkDatabaseConfig();
        $this->checkCacheConfig();
        $this->checkSessionConfig();
        $this->checkMailConfig();
        $this->checkRecaptchaConfig();
        $this->checkSSLConfig();

        $passed = empty($this->criticalIssues);

        return [
            'passed' => $passed,
            'critical' => $this->criticalIssues,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Check environment configuration
     */
    protected function checkEnvironment(): void
    {
        $env = config('app.env');

        if ($env === 'local' || $env === 'development') {
            $this->warnings[] = [
                'check' => 'Environment',
                'issue' => 'Application is running in development mode',
                'current' => $env,
                'recommended' => 'production',
            ];
        }

        if ($env === 'production' && str_contains(config('app.url', ''), 'localhost')) {
            $this->criticalIssues[] = [
                'check' => 'Production URL',
                'issue' => 'Production environment has localhost URL',
                'current' => config('app.url'),
                'recommended' => 'Set APP_URL to your production domain',
            ];
        }
    }

    /**
     * Check debug mode
     */
    protected function checkDebugMode(): void
    {
        $isProduction = config('app.env') === 'production';
        $debugEnabled = config('app.debug');

        if ($isProduction && $debugEnabled) {
            $this->criticalIssues[] = [
                'check' => 'Debug Mode',
                'issue' => 'Debug mode is enabled in production',
                'current' => 'APP_DEBUG=true',
                'recommended' => 'Set APP_DEBUG=false in production',
            ];
        }
    }

    /**
     * Check application key
     */
    protected function checkAppKey(): void
    {
        $appKey = config('app.key');

        if (empty($appKey)) {
            $this->criticalIssues[] = [
                'check' => 'App Key',
                'issue' => 'Application key is not set',
                'current' => 'null',
                'recommended' => 'Run: php artisan key:generate',
            ];
        } elseif ($appKey === 'base64:SomeDefaultKeyHere123456789012') {
            $this->criticalIssues[] = [
                'check' => 'App Key',
                'issue' => 'Application key is using a default/example value',
                'current' => 'Default key',
                'recommended' => 'Run: php artisan key:generate',
            ];
        }
    }

    /**
     * Check Stripe configuration
     */
    protected function checkStripeKeys(): void
    {
        $isProduction = config('app.env') === 'production';
        $stripeKey = config('stripe.key', config('services.stripe.key', ''));
        $stripeSecret = config('stripe.secret', config('services.stripe.secret', ''));
        $webhookSecret = config('stripe.webhook_secret', config('services.stripe.webhook_secret', ''));

        if (empty($stripeKey) || empty($stripeSecret)) {
            $this->warnings[] = [
                'check' => 'Stripe API Keys',
                'issue' => 'Stripe API keys are not configured',
                'current' => 'Not set',
                'recommended' => 'Configure STRIPE_KEY and STRIPE_SECRET in .env',
            ];
            return;
        }

        // Check for test keys in production
        if ($isProduction) {
            if (str_starts_with($stripeKey, 'pk_test_')) {
                $this->criticalIssues[] = [
                    'check' => 'Stripe Publishable Key',
                    'issue' => 'Using test Stripe key in production',
                    'current' => 'pk_test_*****',
                    'recommended' => 'Use pk_live_***** for production',
                ];
            }

            if (str_starts_with($stripeSecret, 'sk_test_')) {
                $this->criticalIssues[] = [
                    'check' => 'Stripe Secret Key',
                    'issue' => 'Using test Stripe secret in production',
                    'current' => 'sk_test_*****',
                    'recommended' => 'Use sk_live_***** for production',
                ];
            }

            if (empty($webhookSecret)) {
                $this->criticalIssues[] = [
                    'check' => 'Stripe Webhook Secret',
                    'issue' => 'Webhook secret is not configured',
                    'current' => 'Not set',
                    'recommended' => 'Configure STRIPE_WEBHOOK_SECRET from Stripe Dashboard',
                ];
            }
        }
    }

    /**
     * Check database configuration
     */
    protected function checkDatabaseConfig(): void
    {
        $isProduction = config('app.env') === 'production';
        $dbConnection = config('database.default');

        if ($isProduction && $dbConnection === 'sqlite') {
            $this->warnings[] = [
                'check' => 'Database',
                'issue' => 'SQLite is not recommended for production',
                'current' => 'sqlite',
                'recommended' => 'Use MySQL or PostgreSQL for production',
            ];
        }

        if ($isProduction) {
            $dbHost = config("database.connections.{$dbConnection}.host");
            if ($dbHost === '127.0.0.1' || $dbHost === 'localhost') {
                // This is actually fine for many setups, just a note
            }

            // Check for default/weak database password
            $dbPassword = config("database.connections.{$dbConnection}.password");
            if (empty($dbPassword) && $dbConnection !== 'sqlite') {
                $this->criticalIssues[] = [
                    'check' => 'Database Password',
                    'issue' => 'Database password is empty',
                    'current' => 'Empty',
                    'recommended' => 'Set a strong database password',
                ];
            }
        }
    }

    /**
     * Check cache configuration
     */
    protected function checkCacheConfig(): void
    {
        $isProduction = config('app.env') === 'production';
        $cacheDriver = config('cache.default');

        if ($isProduction && in_array($cacheDriver, ['array', 'file'])) {
            $this->warnings[] = [
                'check' => 'Cache Driver',
                'issue' => "{$cacheDriver} cache is not optimal for production",
                'current' => $cacheDriver,
                'recommended' => 'Use Redis or Memcached for production',
            ];
        }
    }

    /**
     * Check session configuration
     */
    protected function checkSessionConfig(): void
    {
        $isProduction = config('app.env') === 'production';
        $sessionDriver = config('session.driver');
        $sessionSecure = config('session.secure');
        $sessionHttpOnly = config('session.http_only');
        $sessionSameSite = config('session.same_site');

        if ($isProduction) {
            if (!$sessionSecure) {
                $this->criticalIssues[] = [
                    'check' => 'Session Security',
                    'issue' => 'Session cookies are not secure',
                    'current' => 'SESSION_SECURE_COOKIE=false',
                    'recommended' => 'Set SESSION_SECURE_COOKIE=true for HTTPS',
                ];
            }

            if (!$sessionHttpOnly) {
                $this->warnings[] = [
                    'check' => 'Session HttpOnly',
                    'issue' => 'Session cookies are not HttpOnly',
                    'current' => 'http_only=false',
                    'recommended' => 'Enable http_only in config/session.php',
                ];
            }

            if ($sessionSameSite !== 'lax' && $sessionSameSite !== 'strict') {
                $this->warnings[] = [
                    'check' => 'Session SameSite',
                    'issue' => 'Session SameSite attribute not set',
                    'current' => $sessionSameSite ?? 'null',
                    'recommended' => 'Set same_site to "lax" or "strict"',
                ];
            }
        }
    }

    /**
     * Check mail configuration
     */
    protected function checkMailConfig(): void
    {
        $isProduction = config('app.env') === 'production';
        $mailMailer = config('mail.default');

        if ($isProduction && $mailMailer === 'log') {
            $this->criticalIssues[] = [
                'check' => 'Mail Configuration',
                'issue' => 'Mail is configured to log only (not sending)',
                'current' => 'log',
                'recommended' => 'Configure SMTP, Mailgun, SES, or other mail service',
            ];
        }

        if ($isProduction && $mailMailer === 'array') {
            $this->criticalIssues[] = [
                'check' => 'Mail Configuration',
                'issue' => 'Mail is configured as array (test mode)',
                'current' => 'array',
                'recommended' => 'Configure a real mail service',
            ];
        }
    }

    /**
     * Check reCAPTCHA configuration
     */
    protected function checkRecaptchaConfig(): void
    {
        $isProduction = config('app.env') === 'production';
        $siteKey = config('services.recaptcha.site_key');
        $secretKey = config('services.recaptcha.secret_key');

        if ($isProduction) {
            if (empty($siteKey) || empty($secretKey)) {
                $this->warnings[] = [
                    'check' => 'reCAPTCHA',
                    'issue' => 'reCAPTCHA keys are not configured',
                    'current' => 'Not set',
                    'recommended' => 'Configure reCAPTCHA for form protection',
                ];
            }
        }
    }

    /**
     * Check SSL/HTTPS configuration
     */
    protected function checkSSLConfig(): void
    {
        $isProduction = config('app.env') === 'production';
        $appUrl = config('app.url', '');
        $forceHttps = config('app.force_https', false);

        if ($isProduction) {
            if (!str_starts_with($appUrl, 'https://')) {
                $this->criticalIssues[] = [
                    'check' => 'HTTPS',
                    'issue' => 'APP_URL does not use HTTPS',
                    'current' => $appUrl,
                    'recommended' => 'Use HTTPS for production: https://yourdomain.com',
                ];
            }
        }
    }

    /**
     * Log security check results
     */
    public function logResults(array $results): void
    {
        if (!$results['passed']) {
            Log::critical('Production security check FAILED', [
                'critical_issues' => count($results['critical']),
                'warnings' => count($results['warnings']),
                'issues' => $results['critical'],
            ]);
        } else {
            Log::info('Production security check passed', [
                'warnings' => count($results['warnings']),
            ]);
        }
    }

    /**
     * Format results as string for CLI output
     */
    public function formatResults(array $results): string
    {
        $output = "\n";
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "  PRODUCTION SECURITY CHECK RESULTS\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";

        if ($results['passed']) {
            $output .= "  ✅ ALL CRITICAL CHECKS PASSED\n\n";
        } else {
            $output .= "  ❌ CRITICAL ISSUES FOUND - DO NOT DEPLOY\n\n";
        }

        if (!empty($results['critical'])) {
            $output .= "  🚨 CRITICAL ISSUES (" . count($results['critical']) . "):\n";
            $output .= "  ─────────────────────────────────────────────────────────────\n";
            foreach ($results['critical'] as $issue) {
                $output .= "  [{$issue['check']}]\n";
                $output .= "    Issue: {$issue['issue']}\n";
                $output .= "    Current: {$issue['current']}\n";
                $output .= "    Fix: {$issue['recommended']}\n\n";
            }
        }

        if (!empty($results['warnings'])) {
            $output .= "  ⚠️  WARNINGS (" . count($results['warnings']) . "):\n";
            $output .= "  ─────────────────────────────────────────────────────────────\n";
            foreach ($results['warnings'] as $warning) {
                $output .= "  [{$warning['check']}]\n";
                $output .= "    Issue: {$warning['issue']}\n";
                $output .= "    Recommended: {$warning['recommended']}\n\n";
            }
        }

        $output .= "═══════════════════════════════════════════════════════════════\n";

        return $output;
    }
}

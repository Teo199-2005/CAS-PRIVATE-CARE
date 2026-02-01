<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Services\ProductionSecurityService;
use Illuminate\Support\Facades\Config;

/**
 * Production Security Service Tests
 * 
 * Tests the security configuration validation service
 */
class ProductionSecurityServiceTest extends TestCase
{
    protected ProductionSecurityService $securityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = new ProductionSecurityService();
    }

    /** @test */
    public function it_passes_when_all_production_configs_are_correct()
    {
        // Set up correct production config
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        Config::set('app.url', 'https://example.com');
        Config::set('stripe.key', 'pk_live_test123');
        Config::set('stripe.secret', 'sk_live_test123');
        Config::set('stripe.webhook_secret', 'whsec_test123');
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.password', 'secure_password');
        Config::set('session.secure', true);
        Config::set('mail.default', 'smtp');

        $results = $this->securityService->runAllChecks();

        // May have warnings but should pass critical checks
        $this->assertTrue($results['passed']);
        $this->assertEmpty($results['critical']);
    }

    /** @test */
    public function it_fails_when_debug_is_enabled_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', true);

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        $this->assertNotEmpty($results['critical']);
        
        $debugIssue = collect($results['critical'])->firstWhere('check', 'Debug Mode');
        $this->assertNotNull($debugIssue);
        $this->assertStringContainsString('Debug mode is enabled', $debugIssue['issue']);
    }

    /** @test */
    public function it_fails_when_using_test_stripe_keys_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('stripe.key', 'pk_test_123456');
        Config::set('stripe.secret', 'sk_test_123456');

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $stripeKeyIssue = collect($results['critical'])->firstWhere('check', 'Stripe Publishable Key');
        $this->assertNotNull($stripeKeyIssue);
        $this->assertStringContainsString('test Stripe key', $stripeKeyIssue['issue']);

        $stripeSecretIssue = collect($results['critical'])->firstWhere('check', 'Stripe Secret Key');
        $this->assertNotNull($stripeSecretIssue);
    }

    /** @test */
    public function it_fails_when_webhook_secret_is_missing_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('stripe.key', 'pk_live_123');
        Config::set('stripe.secret', 'sk_live_123');
        Config::set('stripe.webhook_secret', null);

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $webhookIssue = collect($results['critical'])->firstWhere('check', 'Stripe Webhook Secret');
        $this->assertNotNull($webhookIssue);
    }

    /** @test */
    public function it_fails_when_session_cookies_are_not_secure()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('session.secure', false);

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $sessionIssue = collect($results['critical'])->firstWhere('check', 'Session Security');
        $this->assertNotNull($sessionIssue);
    }

    /** @test */
    public function it_fails_when_mail_is_log_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('mail.default', 'log');

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $mailIssue = collect($results['critical'])->firstWhere('check', 'Mail Configuration');
        $this->assertNotNull($mailIssue);
    }

    /** @test */
    public function it_fails_when_using_http_url_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('app.url', 'http://example.com');

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $httpsIssue = collect($results['critical'])->firstWhere('check', 'HTTPS');
        $this->assertNotNull($httpsIssue);
    }

    /** @test */
    public function it_warns_about_sqlite_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('database.default', 'sqlite');

        $results = $this->securityService->runAllChecks();

        $dbWarning = collect($results['warnings'])->firstWhere('check', 'Database');
        $this->assertNotNull($dbWarning);
        $this->assertStringContainsString('SQLite is not recommended', $dbWarning['issue']);
    }

    /** @test */
    public function it_warns_about_file_cache_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('cache.default', 'file');

        $results = $this->securityService->runAllChecks();

        $cacheWarning = collect($results['warnings'])->firstWhere('check', 'Cache Driver');
        $this->assertNotNull($cacheWarning);
    }

    /** @test */
    public function it_does_not_flag_issues_in_local_environment()
    {
        Config::set('app.env', 'local');
        Config::set('app.debug', true);
        Config::set('stripe.key', 'pk_test_123');
        Config::set('stripe.secret', 'sk_test_123');
        Config::set('mail.default', 'log');

        $results = $this->securityService->runAllChecks();

        // Local environment should pass (no critical production issues)
        $this->assertTrue($results['passed']);
    }

    /** @test */
    public function it_formats_results_correctly()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', true); // Force a failure

        $results = $this->securityService->runAllChecks();
        $formatted = $this->securityService->formatResults($results);

        $this->assertStringContainsString('PRODUCTION SECURITY CHECK RESULTS', $formatted);
        $this->assertStringContainsString('CRITICAL ISSUES', $formatted);
        $this->assertStringContainsString('Debug Mode', $formatted);
    }

    /** @test */
    public function it_fails_when_app_key_is_missing()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('app.key', null);

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $keyIssue = collect($results['critical'])->firstWhere('check', 'App Key');
        $this->assertNotNull($keyIssue);
    }

    /** @test */
    public function it_fails_when_database_password_is_empty_in_production()
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.password', '');

        $results = $this->securityService->runAllChecks();

        $this->assertFalse($results['passed']);
        
        $dbIssue = collect($results['critical'])->firstWhere('check', 'Database Password');
        $this->assertNotNull($dbIssue);
    }
}

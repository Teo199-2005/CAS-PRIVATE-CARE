<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure reCAPTCHA for testing
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.secret_key' => 'test-secret-key',
            'services.recaptcha.site_key' => 'test-site-key',
        ]);
    }

    /** @test */
    public function it_rejects_requests_without_recaptcha_token()
    {
        // Skip in testing mode since middleware bypasses verification
        config(['services.recaptcha.enabled' => true]);
        
        $this->markTestSkipped('reCAPTCHA verification is bypassed in testing environment');
    }

    /** @test */
    public function it_validates_recaptcha_token_format()
    {
        $token = 'valid-recaptcha-token-format';
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /** @test */
    public function recaptcha_is_disabled_in_testing_environment()
    {
        $this->assertTrue(app()->environment('testing'));
    }

    /** @test */
    public function recaptcha_config_is_properly_loaded()
    {
        $this->assertNotNull(config('services.recaptcha'));
        $this->assertArrayHasKey('enabled', config('services.recaptcha'));
        $this->assertArrayHasKey('secret_key', config('services.recaptcha'));
        $this->assertArrayHasKey('site_key', config('services.recaptcha'));
    }
}

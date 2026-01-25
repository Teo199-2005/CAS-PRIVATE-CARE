<?php

namespace Tests\Unit\Services;

use App\Services\LoginThrottleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Unit tests for LoginThrottleService
 */
class LoginThrottleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_tracks_failed_login_attempts(): void
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';
        
        $result = LoginThrottleService::recordFailedAttempt($email, $ip);
        
        $this->assertFalse($result['locked']);
        $this->assertEquals(1, $result['attempts']);
        $this->assertEquals(4, $result['remaining']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_locks_out_after_max_attempts(): void
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';
        
        // Record 5 failed attempts
        $result = null;
        for ($i = 0; $i < 5; $i++) {
            $result = LoginThrottleService::recordFailedAttempt($email, $ip);
        }
        
        $this->assertTrue($result['locked']);
        $this->assertEquals(0, $result['remaining']);
        
        // Verify isLockedOut also returns locked
        $lockoutStatus = LoginThrottleService::isLockedOut($email, $ip);
        $this->assertTrue($lockoutStatus['locked']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_clears_attempts_on_success(): void
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';
        
        // Record some failed attempts
        for ($i = 0; $i < 3; $i++) {
            LoginThrottleService::recordFailedAttempt($email, $ip);
        }
        
        // Clear on successful login
        LoginThrottleService::clearAttempts($email, $ip);
        
        // Remaining attempts should be back to max
        $remaining = LoginThrottleService::getRemainingAttempts($email, $ip);
        $this->assertEquals(5, $remaining);
        
        // isLockedOut should return not locked
        $lockoutStatus = LoginThrottleService::isLockedOut($email, $ip);
        $this->assertFalse($lockoutStatus['locked']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_remaining_attempts(): void
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';
        
        // Initial attempts should be max
        $this->assertEquals(5, LoginThrottleService::getRemainingAttempts($email, $ip));
        
        // After 2 failed attempts
        LoginThrottleService::recordFailedAttempt($email, $ip);
        LoginThrottleService::recordFailedAttempt($email, $ip);
        
        $this->assertEquals(3, LoginThrottleService::getRemainingAttempts($email, $ip));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_lockout_time_remaining(): void
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';
        
        // Lock out the account
        for ($i = 0; $i < 5; $i++) {
            LoginThrottleService::recordFailedAttempt($email, $ip);
        }
        
        $lockoutStatus = LoginThrottleService::isLockedOut($email, $ip);
        
        // Should have seconds_remaining around 900 (15 minutes)
        $this->assertTrue($lockoutStatus['locked']);
        $this->assertNotNull($lockoutStatus['seconds_remaining']);
        $this->assertGreaterThan(0, $lockoutStatus['seconds_remaining']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_tracks_by_email_and_ip_separately(): void
    {
        $email = 'test@example.com';
        $ip1 = '127.0.0.1';
        $ip2 = '192.168.1.1';
        
        // Lock out from IP1
        for ($i = 0; $i < 5; $i++) {
            LoginThrottleService::recordFailedAttempt($email, $ip1);
        }
        
        // Should be locked from IP1
        $lockoutStatus1 = LoginThrottleService::isLockedOut($email, $ip1);
        $this->assertTrue($lockoutStatus1['locked']);
        
        // But not from IP2 (different IP)
        $lockoutStatus2 = LoginThrottleService::isLockedOut($email, $ip2);
        $this->assertFalse($lockoutStatus2['locked']);
    }
}

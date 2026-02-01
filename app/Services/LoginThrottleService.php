<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Login Throttle Service
 * 
 * Provides account lockout functionality after multiple failed login attempts.
 * Implements progressive delays and permanent account locking for security.
 * 
 * Security Features:
 * - Progressive lockout (5 attempts = 15 min, 10 attempts = permanent)
 * - IP + Email based tracking
 * - Audit logging for security events
 * 
 * @package App\Services
 */
class LoginThrottleService
{
    /**
     * Maximum failed attempts before temporary lockout
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Maximum failed attempts before PERMANENT lockout
     */
    private const PERMANENT_LOCKOUT_ATTEMPTS = 10;

    /**
     * Lockout duration in minutes
     */
    private const LOCKOUT_DURATION = 15;

    /**
     * Cache key prefix for tracking attempts
     */
    private const CACHE_PREFIX = 'login_attempts_';

    /**
     * Cache key prefix for lockout status
     */
    private const LOCKOUT_PREFIX = 'login_lockout_';

    /**
     * Cache key prefix for permanent lockout
     */
    private const PERMANENT_LOCKOUT_PREFIX = 'login_permanent_lockout_';

    /**
     * Record a failed login attempt
     *
     * @param string $email The email that failed to login
     * @param string $ip The IP address of the request
     * @return array{locked: bool, attempts: int, remaining: int, lockout_seconds: int|null, permanent: bool}
     */
    public static function recordFailedAttempt(string $email, string $ip): array
    {
        $key = self::getKey($email, $ip);
        $lockoutKey = self::getLockoutKey($email, $ip);
        $permanentKey = self::getPermanentLockoutKey($email);

        // Check if permanently locked out
        if (Cache::has($permanentKey)) {
            return [
                'locked' => true,
                'attempts' => self::PERMANENT_LOCKOUT_ATTEMPTS,
                'remaining' => 0,
                'lockout_seconds' => null,
                'permanent' => true,
            ];
        }

        // Check if already locked out (temporary)
        if (Cache::has($lockoutKey)) {
            $lockoutSeconds = Cache::get($lockoutKey);
            return [
                'locked' => true,
                'attempts' => self::MAX_ATTEMPTS,
                'remaining' => 0,
                'lockout_seconds' => $lockoutSeconds,
                'permanent' => false,
            ];
        }

        // Increment attempts
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addHours(24)); // Track for 24 hours

        // Check if should permanently lock out (10+ attempts)
        if ($attempts >= self::PERMANENT_LOCKOUT_ATTEMPTS) {
            Cache::put($permanentKey, true, now()->addDays(30)); // 30 day permanent lockout

            Log::warning('Account PERMANENTLY locked due to excessive failed login attempts', [
                'email' => $email,
                'ip' => $ip,
                'attempts' => $attempts,
                'lockout_type' => 'permanent',
            ]);

            // Also update user record if exists
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['status' => 'locked']);
            }

            return [
                'locked' => true,
                'attempts' => $attempts,
                'remaining' => 0,
                'lockout_seconds' => null,
                'permanent' => true,
            ];
        }

        // Check if should temporarily lock out (5+ attempts)
        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutSeconds = self::LOCKOUT_DURATION * 60;
            Cache::put($lockoutKey, $lockoutSeconds, now()->addMinutes(self::LOCKOUT_DURATION));

            Log::warning('Account temporarily locked due to failed login attempts', [
                'email' => $email,
                'ip' => $ip,
                'attempts' => $attempts,
                'lockout_minutes' => self::LOCKOUT_DURATION,
            ]);

            return [
                'locked' => true,
                'attempts' => $attempts,
                'remaining' => 0,
                'lockout_seconds' => $lockoutSeconds,
                'permanent' => false,
            ];
        }

        return [
            'locked' => false,
            'attempts' => $attempts,
            'remaining' => self::MAX_ATTEMPTS - $attempts,
            'lockout_seconds' => null,
            'permanent' => false,
        ];
    }

    /**
     * Check if an account is currently locked out
     *
     * @param string $email The email to check
     * @param string $ip The IP address
     * @return array{locked: bool, seconds_remaining: int|null, message: string|null, permanent: bool}
     */
    public static function isLockedOut(string $email, string $ip): array
    {
        $lockoutKey = self::getLockoutKey($email, $ip);
        $permanentKey = self::getPermanentLockoutKey($email);

        // Check permanent lockout first
        if (Cache::has($permanentKey)) {
            return [
                'locked' => true,
                'seconds_remaining' => null,
                'message' => 'Account has been permanently locked due to excessive failed login attempts. Please contact support to unlock your account.',
                'permanent' => true,
            ];
        }

        // Check user status in database
        $user = User::where('email', $email)->first();
        if ($user && $user->status === 'locked') {
            return [
                'locked' => true,
                'seconds_remaining' => null,
                'message' => 'Account has been locked. Please contact support to unlock your account.',
                'permanent' => true,
            ];
        }

        // Check temporary lockout
        if (Cache::has($lockoutKey)) {
            return [
                'locked' => true,
                'seconds_remaining' => self::LOCKOUT_DURATION * 60,
                'message' => sprintf(
                    'Account temporarily locked. Please try again in %d minutes.',
                    self::LOCKOUT_DURATION
                ),
                'permanent' => false,
            ];
        }

        return [
            'locked' => false,
            'seconds_remaining' => null,
            'message' => null,
            'permanent' => false,
        ];
    }

    /**
     * Clear failed attempts after successful login
     *
     * @param string $email The email that logged in successfully
     * @param string $ip The IP address
     * @return void
     */
    public static function clearAttempts(string $email, string $ip): void
    {
        $key = self::getKey($email, $ip);
        $lockoutKey = self::getLockoutKey($email, $ip);

        Cache::forget($key);
        Cache::forget($lockoutKey);
    }

    /**
     * Get the number of remaining attempts
     *
     * @param string $email The email to check
     * @param string $ip The IP address
     * @return int
     */
    public static function getRemainingAttempts(string $email, string $ip): int
    {
        $key = self::getKey($email, $ip);
        $attempts = Cache::get($key, 0);
        
        return max(0, self::MAX_ATTEMPTS - $attempts);
    }

    /**
     * Generate cache key for attempts tracking
     *
     * @param string $email
     * @param string $ip
     * @return string
     */
    private static function getKey(string $email, string $ip): string
    {
        return self::CACHE_PREFIX . md5(strtolower($email) . '|' . $ip);
    }

    /**
     * Generate cache key for lockout status
     *
     * @param string $email
     * @param string $ip
     * @return string
     */
    private static function getLockoutKey(string $email, string $ip): string
    {
        return self::LOCKOUT_PREFIX . md5(strtolower($email) . '|' . $ip);
    }

    /**
     * Generate cache key for permanent lockout status (email only, no IP)
     *
     * @param string $email
     * @return string
     */
    private static function getPermanentLockoutKey(string $email): string
    {
        return self::PERMANENT_LOCKOUT_PREFIX . md5(strtolower($email));
    }

    /**
     * Unlock a permanently locked account (admin function)
     *
     * @param string $email The email to unlock
     * @return bool
     */
    public static function unlockAccount(string $email): bool
    {
        $permanentKey = self::getPermanentLockoutKey($email);
        Cache::forget($permanentKey);

        // Also unlock in database
        $user = User::where('email', $email)->first();
        if ($user && $user->status === 'locked') {
            $user->update(['status' => 'Active']);
        }

        Log::info('Account manually unlocked', ['email' => $email]);

        return true;
    }

    /**
     * Get configuration values for display
     *
     * @return array{max_attempts: int, lockout_minutes: int, permanent_lockout_attempts: int}
     */
    public static function getConfig(): array
    {
        return [
            'max_attempts' => self::MAX_ATTEMPTS,
            'lockout_minutes' => self::LOCKOUT_DURATION,
            'permanent_lockout_attempts' => self::PERMANENT_LOCKOUT_ATTEMPTS,
        ];
    }
}

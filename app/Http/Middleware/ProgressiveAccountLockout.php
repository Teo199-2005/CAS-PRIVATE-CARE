<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Progressive Account Lockout Middleware
 * 
 * Implements escalating lockout durations for failed login attempts.
 * Uses configuration from config/password.php for lockout settings.
 * 
 * Lockout progression:
 * - 1st lockout: 15 minutes
 * - 2nd lockout: 1 hour
 * - 3rd+ lockout: 24 hours
 */
class ProgressiveAccountLockout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('password.lockout.enabled', true)) {
            return $next($request);
        }

        $key = $this->getLockoutKey($request);
        
        // Check if currently locked out
        if ($this->isLockedOut($key)) {
            $remainingTime = $this->getRemainingLockoutTime($key);
            
            Log::warning('Progressive lockout: Access denied for locked account', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'remaining_seconds' => $remainingTime,
            ]);
            
            return $this->lockoutResponse($request, $remainingTime);
        }

        // Process the request
        $response = $next($request);

        // Check if login failed (401 or 422 response)
        if ($this->isFailedAttempt($response)) {
            $this->recordFailedAttempt($key, $request);
        } elseif ($this->isSuccessfulLogin($request, $response)) {
            $this->clearAttempts($key);
        }

        return $response;
    }

    /**
     * Generate a unique key for tracking login attempts.
     */
    protected function getLockoutKey(Request $request): string
    {
        $email = strtolower($request->input('email', ''));
        $ip = $request->ip();
        
        // Use both email and IP to prevent targeted attacks
        return 'lockout:' . sha1($email . '|' . $ip);
    }

    /**
     * Check if the account is currently locked out.
     */
    protected function isLockedOut(string $key): bool
    {
        return Cache::has($key . ':locked');
    }

    /**
     * Get the remaining lockout time in seconds.
     */
    protected function getRemainingLockoutTime(string $key): int
    {
        $lockedUntil = Cache::get($key . ':locked_until', 0);
        return max(0, $lockedUntil - time());
    }

    /**
     * Check if the response indicates a failed login attempt.
     */
    protected function isFailedAttempt(Response $response): bool
    {
        return in_array($response->getStatusCode(), [401, 422]);
    }

    /**
     * Check if this was a successful login.
     */
    protected function isSuccessfulLogin(Request $request, Response $response): bool
    {
        return $response->getStatusCode() === 200 && 
               $request->routeIs('login', 'api.login');
    }

    /**
     * Record a failed login attempt.
     */
    protected function recordFailedAttempt(string $key, Request $request): void
    {
        $maxAttempts = config('password.lockout.max_attempts', 5);
        $attempts = Cache::increment($key . ':attempts');
        
        // Set expiry for attempts counter (24 hours)
        Cache::put($key . ':attempts', $attempts, now()->addHours(24));
        
        Log::info('Progressive lockout: Failed attempt recorded', [
            'ip' => $request->ip(),
            'email' => $request->input('email'),
            'attempts' => $attempts,
            'max_attempts' => $maxAttempts,
        ]);

        if ($attempts >= $maxAttempts) {
            $this->lockAccount($key, $request);
        }
    }

    /**
     * Lock the account with progressive duration.
     */
    protected function lockAccount(string $key, Request $request): void
    {
        // Get current lockout count
        $lockoutCount = Cache::increment($key . ':lockout_count');
        Cache::put($key . ':lockout_count', $lockoutCount, now()->addDays(
            config('password.lockout.clear_after_days', 30)
        ));
        
        // Get progressive duration
        $durations = config('password.lockout.durations', [
            1 => 15,
            2 => 60,
            3 => 1440,
        ]);
        
        // Calculate lockout duration (use last tier for counts beyond defined)
        $lockoutMinutes = $durations[$lockoutCount] ?? end($durations);
        $lockoutSeconds = $lockoutMinutes * 60;
        
        // Set lockout
        Cache::put($key . ':locked', true, now()->addMinutes($lockoutMinutes));
        Cache::put($key . ':locked_until', time() + $lockoutSeconds, now()->addMinutes($lockoutMinutes));
        
        // Reset attempts counter
        Cache::forget($key . ':attempts');
        
        Log::warning('Progressive lockout: Account locked', [
            'ip' => $request->ip(),
            'email' => $request->input('email'),
            'lockout_count' => $lockoutCount,
            'duration_minutes' => $lockoutMinutes,
        ]);

        // Notify user if configured
        if (config('password.lockout.notify_user', true)) {
            $this->notifyUser($request);
        }

        // Notify admin if configured
        if (config('password.lockout.notify_admin', true) && $lockoutCount >= 3) {
            $this->notifyAdmin($request, $lockoutCount);
        }
    }

    /**
     * Clear all attempts for a key (on successful login).
     */
    protected function clearAttempts(string $key): void
    {
        Cache::forget($key . ':attempts');
        Cache::forget($key . ':locked');
        Cache::forget($key . ':locked_until');
        // Don't clear lockout_count - it resets after clear_after_days
    }

    /**
     * Build the lockout response.
     */
    protected function lockoutResponse(Request $request, int $remainingSeconds): Response
    {
        $minutes = ceil($remainingSeconds / 60);
        $message = $this->getTimeMessage($remainingSeconds);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$message}.",
                'locked' => true,
                'retry_after' => $remainingSeconds,
            ], 429)->header('Retry-After', $remainingSeconds);
        }
        
        return response("Too many login attempts. Please try again in {$message}.", 429)
            ->header('Retry-After', $remainingSeconds);
    }

    /**
     * Get a human-readable time message.
     */
    protected function getTimeMessage(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }
        
        $minutes = ceil($seconds / 60);
        
        if ($minutes < 60) {
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours < 24) {
            $msg = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($remainingMinutes > 0) {
                $msg .= ' and ' . $remainingMinutes . ' minute' . ($remainingMinutes > 1 ? 's' : '');
            }
            return $msg;
        }
        
        $days = floor($hours / 24);
        return $days . ' day' . ($days > 1 ? 's' : '');
    }

    /**
     * Notify the user about account lockout.
     */
    protected function notifyUser(Request $request): void
    {
        $email = $request->input('email');
        
        if (!$email) {
            return;
        }
        
        // Queue email notification
        // Note: Implement actual email sending via EmailService
        Log::info('Progressive lockout: User notification queued', [
            'email' => $email,
        ]);
    }

    /**
     * Notify admin about suspicious lockouts.
     */
    protected function notifyAdmin(Request $request, int $lockoutCount): void
    {
        Log::warning('Progressive lockout: Suspicious activity - Admin notification', [
            'ip' => $request->ip(),
            'email' => $request->input('email'),
            'lockout_count' => $lockoutCount,
            'user_agent' => $request->userAgent(),
        ]);
        
        // Note: Implement actual admin notification
    }
}

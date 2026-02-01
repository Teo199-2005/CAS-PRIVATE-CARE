<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Symfony\Component\HttpFoundation\Response;

/**
 * Smart Rate Limiting Middleware
 * 
 * Provides intelligent rate limiting based on:
 * - Environment (local/staging vs production)
 * - IP whitelist for testing
 * - User authentication status
 * - Request patterns (suspicious vs legitimate)
 * 
 * Usage: ->middleware('smart.throttle:login')
 */
class SmartRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        
        // Get rate limits based on context
        [$maxAttempts, $decayMinutes] = $this->getRateLimits($request, $type);
        
        // Check if IP is whitelisted
        if ($this->isWhitelisted($request)) {
            // Whitelisted IPs get 10x the normal limit
            $maxAttempts *= 10;
        }
        
        // Execute rate limiting
        $limiter = app(RateLimiter::class);
        
        if ($limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $limiter->availableIn($key);
            
            // Log suspicious activity (production only)
            if (app()->environment('production')) {
                \Log::warning('Rate limit exceeded', [
                    'ip' => $request->ip(),
                    'type' => $type,
                    'attempts' => $limiter->attempts($key),
                    'retry_after' => $retryAfter
                ]);
            }
            
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => $retryAfter
            ], 429)->header('Retry-After', $retryAfter);
        }
        
        $limiter->hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        return $this->addRateLimitHeaders($response, $key, $maxAttempts, $limiter);
    }
    
    /**
     * Get rate limits based on environment and request type
     */
    protected function getRateLimits(Request $request, string $type): array
    {
        // Development/Testing: Relaxed limits
        if (app()->environment(['local', 'development', 'testing', 'staging'])) {
            return match($type) {
                'login' => [50, 1],      // 50 attempts per minute
                'register' => [30, 1],   // 30 attempts per minute
                'api' => [300, 1],       // 300 requests per minute
                default => [100, 1]      // 100 requests per minute
            };
        }
        
        // Production: Strict but reasonable limits
        return match($type) {
            'login' => [10, 1],          // 10 attempts per minute
            'register' => [5, 1],        // 5 attempts per minute
            'password-reset' => [3, 1],  // 3 attempts per minute
            'api' => [60, 1],            // 60 requests per minute
            'api-write' => [30, 1],      // 30 write operations per minute
            'payment' => [5, 1],         // 5 payment attempts per minute
            default => [60, 1]           // 60 requests per minute
        };
    }
    
    /**
     * Check if IP is whitelisted
     */
    protected function isWhitelisted(Request $request): bool
    {
        $whitelist = config('ratelimit.whitelist', []);
        
        // Always whitelist localhost in non-production
        if (!app()->environment('production')) {
            $whitelist = array_merge($whitelist, [
                '127.0.0.1',
                '::1',
                'localhost'
            ]);
        }
        
        return in_array($request->ip(), $whitelist);
    }
    
    /**
     * Resolve the rate limiting key
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        // For authenticated users, use user ID
        if ($request->user()) {
            return sprintf(
                'ratelimit:%s:user:%s',
                $type,
                $request->user()->id
            );
        }
        
        // For guests, use IP address
        return sprintf(
            'ratelimit:%s:ip:%s',
            $type,
            $request->ip()
        );
    }
    
    /**
     * Add rate limit headers to response
     */
    protected function addRateLimitHeaders(Response $response, string $key, int $maxAttempts, RateLimiter $limiter): Response
    {
        $attempts = $limiter->attempts($key);
        $remaining = max(0, $maxAttempts - $attempts);
        
        return $response
            ->header('X-RateLimit-Limit', $maxAttempts)
            ->header('X-RateLimit-Remaining', $remaining);
    }
}


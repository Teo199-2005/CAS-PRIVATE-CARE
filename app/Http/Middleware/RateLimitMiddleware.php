<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request with intelligent rate limiting
     */
    public function handle(Request $request, Closure $next, string $type = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        
        $limits = $this->getLimits($type);
        $maxAttempts = $limits['max'];
        $decayMinutes = $limits['decay'];

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildRateLimitResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key, $maxAttempts);
    }

    /**
     * Get rate limits based on endpoint type
     */
    protected function getLimits(string $type): array
    {
        return match($type) {
            'auth' => ['max' => 5, 'decay' => 1],           // 5 requests per minute
            'payment' => ['max' => 10, 'decay' => 1],       // 10 requests per minute
            'api' => ['max' => 60, 'decay' => 1],           // 60 requests per minute
            'admin' => ['max' => 100, 'decay' => 1],        // 100 requests per minute
            'webhook' => ['max' => 1000, 'decay' => 1],     // 1000 requests per minute
            default => ['max' => 60, 'decay' => 1],
        };
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        if ($user = $request->user()) {
            return sprintf(
                'rate_limit:%s:%s:%s',
                $type,
                $user->id,
                $request->ip()
            );
        }

        return sprintf(
            'rate_limit:%s:%s',
            $type,
            $request->ip()
        );
    }

    /**
     * Build rate limit exceeded response
     */
    protected function buildRateLimitResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'You have exceeded the rate limit. Please try again later.',
            'retry_after' => $retryAfter,
            'retry_after_human' => gmdate('i:s', $retryAfter)
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'Retry-After' => $retryAfter,
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
        ]);
    }

    /**
     * Add rate limit headers to response
     */
    protected function addRateLimitHeaders(Response $response, string $key, int $maxAttempts): Response
    {
        $remaining = $this->limiter->remaining($key, $maxAttempts);
        $retryAfter = $this->limiter->availableIn($key);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
        ]);
    }
}

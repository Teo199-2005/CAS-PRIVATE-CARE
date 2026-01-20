<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Cache Middleware
 * 
 * Adds proper cache headers for improved performance and CDN compatibility.
 */
class HttpCache
{
    /**
     * Cache durations in seconds by route type
     */
    protected array $cacheDurations = [
        'static' => 31536000, // 1 year for static assets
        'api' => 60,          // 1 minute for API responses
        'page' => 3600,       // 1 hour for public pages
        'none' => 0,          // No caching
    ];

    /**
     * Routes that should not be cached
     */
    protected array $noCachePatterns = [
        'api/bookings/*',
        'api/payments/*',
        'api/user/*',
        'dashboard/*',
        'admin/*',
        'profile/*',
        'login',
        'register',
        'password/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'page'): Response
    {
        $response = $next($request);

        // Don't cache for authenticated users or POST requests
        if ($request->user() || !$request->isMethod('GET')) {
            return $this->setNoCacheHeaders($response);
        }

        // Check if route should not be cached
        foreach ($this->noCachePatterns as $pattern) {
            if ($request->is($pattern)) {
                return $this->setNoCacheHeaders($response);
            }
        }

        // Apply appropriate cache headers
        $duration = $this->cacheDurations[$type] ?? $this->cacheDurations['page'];

        return $this->setCacheHeaders($response, $duration, $type);
    }

    /**
     * Set cache headers on response
     */
    protected function setCacheHeaders(Response $response, int $duration, string $type): Response
    {
        if ($duration === 0) {
            return $this->setNoCacheHeaders($response);
        }

        $response->headers->set('Cache-Control', $this->getCacheControlValue($duration, $type));
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $duration) . ' GMT');
        
        // Add ETag for conditional requests
        if ($response->getContent()) {
            $etag = md5($response->getContent());
            $response->headers->set('ETag', '"' . $etag . '"');
        }

        // Vary header for proper caching with different encodings
        $response->headers->set('Vary', 'Accept-Encoding, Accept');

        return $response;
    }

    /**
     * Set no-cache headers
     */
    protected function setNoCacheHeaders(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Get Cache-Control header value
     */
    protected function getCacheControlValue(int $duration, string $type): string
    {
        $directives = [];

        if ($type === 'static') {
            $directives[] = 'public';
            $directives[] = 'max-age=' . $duration;
            $directives[] = 'immutable';
        } elseif ($type === 'api') {
            $directives[] = 'public';
            $directives[] = 'max-age=' . $duration;
            $directives[] = 's-maxage=' . ($duration * 2); // CDN can cache longer
        } else {
            $directives[] = 'public';
            $directives[] = 'max-age=' . $duration;
            $directives[] = 'stale-while-revalidate=' . min($duration, 60);
        }

        return implode(', ', $directives);
    }
}

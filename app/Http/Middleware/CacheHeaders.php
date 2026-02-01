<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache Headers Middleware
 * 
 * Adds appropriate cache headers to responses based on content type
 * and route configuration. Helps improve performance by enabling
 * browser and CDN caching.
 * 
 * @package App\Http\Middleware
 * @version 1.0.0
 */
class CacheHeaders
{
    /**
     * Cache durations in seconds
     */
    protected const CACHE_DURATIONS = [
        'static' => 31536000, // 1 year for static assets with hash
        'api_public' => 300,  // 5 minutes for public API data
        'api_private' => 0,   // No cache for private API data
        'html' => 0,          // No cache for HTML pages
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'html'): Response
    {
        $response = $next($request);

        // Skip for error responses
        if ($response->getStatusCode() >= 400) {
            return $this->addNoCacheHeaders($response);
        }

        // Skip for authenticated-only routes
        if ($request->user() && $type !== 'static') {
            return $this->addPrivateCacheHeaders($response);
        }

        return match ($type) {
            'static' => $this->addStaticCacheHeaders($response),
            'api_public' => $this->addPublicApiCacheHeaders($response),
            'api_private' => $this->addNoCacheHeaders($response),
            'immutable' => $this->addImmutableCacheHeaders($response),
            default => $this->addNoCacheHeaders($response),
        };
    }

    /**
     * Add cache headers for static assets (CSS, JS, images with hashes)
     */
    protected function addStaticCacheHeaders(Response $response): Response
    {
        $duration = self::CACHE_DURATIONS['static'];
        
        $response->headers->set('Cache-Control', "public, max-age={$duration}, immutable");
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $duration) . ' GMT');
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }

    /**
     * Add cache headers for immutable content (versioned assets)
     */
    protected function addImmutableCacheHeaders(Response $response): Response
    {
        $duration = self::CACHE_DURATIONS['static'];
        
        $response->headers->set('Cache-Control', "public, max-age={$duration}, immutable");
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $duration) . ' GMT');
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }

    /**
     * Add cache headers for public API responses
     */
    protected function addPublicApiCacheHeaders(Response $response): Response
    {
        $duration = self::CACHE_DURATIONS['api_public'];
        
        $response->headers->set('Cache-Control', "public, max-age={$duration}, s-maxage={$duration}");
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $duration) . ' GMT');
        $response->headers->set('Vary', 'Accept-Encoding, Authorization');

        return $response;
    }

    /**
     * Add cache headers for private/authenticated responses
     */
    protected function addPrivateCacheHeaders(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Vary', 'Cookie, Authorization');

        return $response;
    }

    /**
     * Add no-cache headers
     */
    protected function addNoCacheHeaders(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}

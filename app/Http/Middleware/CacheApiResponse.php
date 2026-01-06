<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Generate cache key from URL and query params
        $cacheKey = 'api_cache_' . md5($request->fullUrl() . json_encode($request->all()));

        // Check if cached response exists
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            return response()->json($cachedResponse)
                ->header('X-Cache', 'HIT');
        }

        // Process request
        $response = $next($request);

        // Cache successful responses
        if ($response->isSuccessful() && $response->headers->get('content-type') === 'application/json') {
            $content = json_decode($response->getContent(), true);
            Cache::put($cacheKey, $content, now()->addMinutes($minutes));
            
            $response->header('X-Cache', 'MISS');
        }

        return $response;
    }
}

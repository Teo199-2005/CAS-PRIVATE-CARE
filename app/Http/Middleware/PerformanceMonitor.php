<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Performance Monitoring Middleware
 * 
 * Tracks request execution time, memory usage, and database queries.
 * Logs slow requests for performance optimization.
 * 
 * @package App\Http\Middleware
 */
class PerformanceMonitor
{
    /**
     * Threshold in milliseconds for slow request warning
     */
    protected const SLOW_REQUEST_THRESHOLD = 1000;

    /**
     * Threshold in MB for high memory warning
     */
    protected const HIGH_MEMORY_THRESHOLD = 64;

    /**
     * Threshold for too many queries warning
     */
    protected const QUERY_COUNT_THRESHOLD = 50;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for health checks and static assets
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Enable query logging in local environment
        if (app()->environment('local')) {
            \DB::enableQueryLog();
        }

        $response = $next($request);

        $this->logPerformanceMetrics($request, $response, $startTime, $startMemory);

        return $response;
    }

    /**
     * Check if request should skip performance monitoring
     */
    protected function shouldSkip(Request $request): bool
    {
        $skipPatterns = [
            '/health',
            '/up',
            '/_debugbar',
            '/favicon',
            '/robots.txt',
            '/sitemap',
        ];

        foreach ($skipPatterns as $pattern) {
            if (str_starts_with($request->path(), ltrim($pattern, '/'))) {
                return true;
            }
        }

        // Skip static asset requests
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2)$/i', $request->path())) {
            return true;
        }

        return false;
    }

    /**
     * Log performance metrics
     */
    protected function logPerformanceMetrics(
        Request $request, 
        Response $response, 
        float $startTime, 
        int $startMemory
    ): void {
        $duration = (microtime(true) - $startTime) * 1000; // Convert to ms
        $memoryUsed = (memory_get_usage(true) - $startMemory) / 1024 / 1024; // Convert to MB
        $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        $queryCount = 0;
        $queryTime = 0;
        
        if (app()->environment('local')) {
            $queries = \DB::getQueryLog();
            $queryCount = count($queries);
            $queryTime = array_sum(array_column($queries, 'time'));
            \DB::disableQueryLog();
        }

        $metrics = [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'memory_mb' => round($memoryUsed, 2),
            'peak_memory_mb' => round($peakMemory, 2),
            'query_count' => $queryCount,
            'query_time_ms' => round($queryTime, 2),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ];

        // Always log slow requests
        if ($duration > self::SLOW_REQUEST_THRESHOLD) {
            Log::channel('performance')->warning('Slow request detected', $metrics);
        }
        
        // Log high memory usage
        if ($memoryUsed > self::HIGH_MEMORY_THRESHOLD) {
            Log::channel('performance')->warning('High memory usage', $metrics);
        }
        
        // Log too many queries (N+1 problem indicator)
        if ($queryCount > self::QUERY_COUNT_THRESHOLD) {
            Log::channel('performance')->warning('Too many database queries', $metrics);
        }

        // Add performance headers for debugging
        if (app()->environment('local', 'staging')) {
            $response->headers->set('X-Response-Time', round($duration, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', round($memoryUsed, 2) . 'MB');
            
            if ($queryCount > 0) {
                $response->headers->set('X-Query-Count', $queryCount);
            }
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PerformanceMonitor;

/**
 * Performance Tracking Middleware
 * 
 * Tracks request performance and adds Server-Timing headers
 * for browser DevTools integration.
 */
class TrackPerformance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start performance monitoring
        PerformanceMonitor::start();

        $response = $next($request);

        // Stop monitoring and get metrics
        $metrics = PerformanceMonitor::stop();

        // Add Server-Timing header for browser DevTools
        if (!empty($metrics) && config('app.debug')) {
            $timing = $this->buildServerTimingHeader($metrics);
            $response->headers->set('Server-Timing', $timing);
        }

        return $response;
    }

    /**
     * Build Server-Timing header value
     * 
     * This allows viewing timing metrics in browser DevTools Network tab
     * 
     * @param array $metrics
     * @return string
     */
    protected function buildServerTimingHeader(array $metrics): string
    {
        $timings = [];

        // Total response time
        $timings[] = sprintf('total;dur=%.2f;desc="Total Response Time"', $metrics['response_time_ms'] ?? 0);

        // Database query time
        $timings[] = sprintf('db;dur=%.2f;desc="Database Queries"', $metrics['total_query_time_ms'] ?? 0);

        // Query count (as a descriptor)
        $timings[] = sprintf('queries;desc="Query Count: %d"', $metrics['query_count'] ?? 0);

        // Memory usage
        $timings[] = sprintf('memory;desc="Memory: %.1fMB"', $metrics['memory_usage_mb'] ?? 0);

        return implode(', ', $timings);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for monitoring and logging slow requests and query performance.
 * 
 * Features:
 * - Tracks request duration
 * - Monitors query count and slow queries
 * - Detects N+1 query patterns
 * - Logs performance metrics for analysis
 */
class PerformanceMonitoring
{
    /**
     * Threshold for slow request in milliseconds
     */
    protected int $slowRequestThreshold = 1000; // 1 second

    /**
     * Threshold for slow query in milliseconds
     */
    protected int $slowQueryThreshold = 100; // 100ms

    /**
     * Threshold for high query count warning
     */
    protected int $highQueryCountThreshold = 20;

    /**
     * Collected queries during request
     */
    protected array $queries = [];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Enable query logging only in non-production or when debugging
        if ($this->shouldLogQueries()) {
            $this->startQueryLogging();
        }

        $response = $next($request);

        // Calculate request duration
        $duration = (microtime(true) - $startTime) * 1000;

        // Log performance metrics
        $this->logPerformanceMetrics($request, $response, $duration);

        // Add performance headers in non-production
        if (!app()->isProduction()) {
            $response = $this->addPerformanceHeaders($response, $duration);
        }

        return $response;
    }

    /**
     * Start query logging.
     */
    protected function startQueryLogging(): void
    {
        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
        });
    }

    /**
     * Log performance metrics.
     */
    protected function logPerformanceMetrics(
        Request $request,
        Response $response,
        float $duration
    ): void {
        $queryCount = count($this->queries);
        $totalQueryTime = array_sum(array_column($this->queries, 'time'));
        $slowQueries = array_filter($this->queries, fn($q) => $q['time'] > $this->slowQueryThreshold);

        $metrics = [
            'route' => $request->route()?->getName() ?? $request->path(),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'query_count' => $queryCount,
            'query_time_ms' => round($totalQueryTime, 2),
            'slow_queries' => count($slowQueries),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        // Log slow requests
        if ($duration > $this->slowRequestThreshold) {
            Log::warning('Slow request detected', array_merge($metrics, [
                'queries' => $this->queries,
            ]));
        }

        // Log high query count (potential N+1)
        if ($queryCount > $this->highQueryCountThreshold) {
            $duplicateQueries = $this->detectDuplicateQueries();
            
            Log::warning('High query count detected (potential N+1)', array_merge($metrics, [
                'duplicate_queries' => $duplicateQueries,
            ]));
        }

        // Log slow queries
        if (count($slowQueries) > 0) {
            Log::info('Slow queries detected', [
                'route' => $metrics['route'],
                'slow_queries' => array_map(function ($q) {
                    return [
                        'sql' => $q['sql'],
                        'time_ms' => $q['time'],
                    ];
                }, $slowQueries),
            ]);
        }

        // Always log performance metrics in debug level
        if ($this->shouldLogQueries()) {
            Log::debug('Request performance', $metrics);
        }
    }

    /**
     * Detect duplicate queries (N+1 pattern).
     */
    protected function detectDuplicateQueries(): array
    {
        $queryPatterns = [];

        foreach ($this->queries as $query) {
            // Normalize query by removing specific values
            $pattern = preg_replace('/\d+/', '?', $query['sql']);
            $pattern = preg_replace("/['\"][^'\"]*['\"]/", '?', $pattern);

            if (!isset($queryPatterns[$pattern])) {
                $queryPatterns[$pattern] = 0;
            }
            $queryPatterns[$pattern]++;
        }

        // Filter to show only patterns that occur more than twice
        return array_filter($queryPatterns, fn($count) => $count > 2);
    }

    /**
     * Add performance headers for debugging.
     */
    protected function addPerformanceHeaders(Response $response, float $duration): Response
    {
        $response->headers->set('X-Request-Duration', round($duration, 2) . 'ms');
        $response->headers->set('X-Query-Count', (string) count($this->queries));
        $response->headers->set('X-Memory-Peak', round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB');

        return $response;
    }

    /**
     * Determine if queries should be logged.
     */
    protected function shouldLogQueries(): bool
    {
        return !app()->isProduction() || config('logging.performance_monitoring', false);
    }
}

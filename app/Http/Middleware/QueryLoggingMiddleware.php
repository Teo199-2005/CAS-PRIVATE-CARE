<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * QueryLoggingMiddleware
 * 
 * Logs slow database queries for performance monitoring.
 * 
 * Why it matters:
 * - Identifies N+1 query problems
 * - Catches queries that need indexing
 * - Provides baseline for optimization
 * 
 * Usage:
 * Add to $middleware array in bootstrap/app.php or use as route middleware.
 * 
 * Configuration:
 * - SLOW_QUERY_THRESHOLD_MS: Queries slower than this are logged (default: 100ms)
 * - QUERY_LOG_ENABLED: Enable/disable query logging (default: true in non-production)
 */
class QueryLoggingMiddleware
{
    /**
     * Default slow query threshold in milliseconds
     */
    protected const DEFAULT_THRESHOLD_MS = 100;

    /**
     * Maximum queries per request before warning
     */
    protected const MAX_QUERIES_WARNING = 50;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only log in development/staging or when explicitly enabled
        if (!$this->shouldLogQueries()) {
            return $next($request);
        }

        $queries = [];
        $startTime = microtime(true);

        // Enable query logging
        DB::enableQueryLog();

        // Process the request
        $response = $next($request);

        // Get query log
        $queryLog = DB::getQueryLog();
        $totalTime = (microtime(true) - $startTime) * 1000; // Convert to ms

        // Analyze queries
        $this->analyzeQueries($queryLog, $request, $totalTime);

        // Disable query logging to prevent memory issues
        DB::disableQueryLog();
        DB::flushQueryLog();

        return $response;
    }

    /**
     * Determine if query logging should be enabled
     */
    protected function shouldLogQueries(): bool
    {
        // Check environment variable
        if (env('QUERY_LOG_ENABLED') === false) {
            return false;
        }

        // Enable by default in non-production
        return !app()->isProduction() || env('QUERY_LOG_ENABLED', false);
    }

    /**
     * Get slow query threshold in milliseconds
     */
    protected function getSlowQueryThreshold(): float
    {
        return (float) env('SLOW_QUERY_THRESHOLD_MS', self::DEFAULT_THRESHOLD_MS);
    }

    /**
     * Analyze queries and log issues
     */
    protected function analyzeQueries(array $queryLog, Request $request, float $totalTime): void
    {
        $threshold = $this->getSlowQueryThreshold();
        $slowQueries = [];
        $totalQueryTime = 0;
        $queryCount = count($queryLog);

        foreach ($queryLog as $query) {
            $queryTime = $query['time'] ?? 0;
            $totalQueryTime += $queryTime;

            if ($queryTime >= $threshold) {
                $slowQueries[] = [
                    'sql' => $this->formatQuery($query['query'], $query['bindings'] ?? []),
                    'time_ms' => round($queryTime, 2),
                    'connection' => $query['connection'] ?? 'default',
                ];
            }
        }

        // Log slow queries
        if (!empty($slowQueries)) {
            Log::channel('performance')->warning('Slow queries detected', [
                'request' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route' => $request->route()?->getName() ?? 'N/A',
                ],
                'slow_queries' => $slowQueries,
                'total_query_count' => $queryCount,
                'total_query_time_ms' => round($totalQueryTime, 2),
                'total_request_time_ms' => round($totalTime, 2),
            ]);
        }

        // Warn about too many queries (potential N+1)
        if ($queryCount > self::MAX_QUERIES_WARNING) {
            Log::channel('performance')->warning('High query count detected (potential N+1)', [
                'request' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route' => $request->route()?->getName() ?? 'N/A',
                ],
                'query_count' => $queryCount,
                'threshold' => self::MAX_QUERIES_WARNING,
                'total_query_time_ms' => round($totalQueryTime, 2),
            ]);
        }

        // Log summary for all requests in debug mode
        if (config('app.debug')) {
            Log::channel('performance')->debug('Request query summary', [
                'route' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->method(),
                'query_count' => $queryCount,
                'total_query_time_ms' => round($totalQueryTime, 2),
                'total_request_time_ms' => round($totalTime, 2),
                'slow_query_count' => count($slowQueries),
            ]);
        }
    }

    /**
     * Format query with bindings for readability
     */
    protected function formatQuery(string $sql, array $bindings): string
    {
        // Replace ? placeholders with actual values (for logging only)
        foreach ($bindings as $binding) {
            $value = is_string($binding) ? "'" . addslashes($binding) . "'" : $binding;
            $sql = preg_replace('/\?/', (string) $value, $sql, 1);
        }

        return $sql;
    }
}

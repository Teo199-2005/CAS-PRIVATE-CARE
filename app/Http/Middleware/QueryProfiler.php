<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Query Profiler Middleware
 * 
 * Monitors database query performance in non-production environments.
 * Logs slow requests and adds debug headers for development.
 * 
 * Features:
 * - Tracks total query count per request
 * - Measures total query execution time
 * - Logs slow requests (> 100ms or > 20 queries)
 * - Adds X-Query-Count and X-Query-Time-Ms headers in debug mode
 */
class QueryProfiler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only profile in non-production environments
        if (config('app.env') === 'production') {
            return $next($request);
        }

        // Enable query logging
        DB::enableQueryLog();

        $startTime = microtime(true);
        $response = $next($request);
        $executionTime = (microtime(true) - $startTime) * 1000;

        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $totalQueryTime = collect($queries)->sum('time');

        // Log slow requests
        if ($totalQueryTime > 100 || $queryCount > 20) {
            $this->logSlowRequest($request, $queries, $queryCount, $totalQueryTime, $executionTime);
        }

        // Add debug headers in development
        if (config('app.debug')) {
            $response->headers->set('X-Query-Count', (string) $queryCount);
            $response->headers->set('X-Query-Time-Ms', (string) round($totalQueryTime, 2));
            $response->headers->set('X-Request-Time-Ms', (string) round($executionTime, 2));
            
            // Identify potential N+1 issues
            if ($this->hasN1Problem($queries)) {
                $response->headers->set('X-N1-Warning', 'Potential N+1 query detected');
            }
        }

        // Disable query log to free memory
        DB::disableQueryLog();

        return $response;
    }

    /**
     * Log slow request details.
     */
    protected function logSlowRequest(
        Request $request, 
        array $queries, 
        int $queryCount, 
        float $totalQueryTime,
        float $executionTime
    ): void {
        $logData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'query_count' => $queryCount,
            'total_query_time_ms' => round($totalQueryTime, 2),
            'total_request_time_ms' => round($executionTime, 2),
            'user_id' => $request->user()?->id,
        ];

        // Include query details if not too many
        if ($queryCount <= 50) {
            $logData['queries'] = collect($queries)->map(function ($query) {
                return [
                    'sql' => $query['query'],
                    'time' => $query['time'],
                    'bindings_count' => count($query['bindings'] ?? []),
                ];
            })->toArray();
        } else {
            $logData['note'] = 'Too many queries to log individually';
            
            // Get top 10 slowest queries
            $logData['slowest_queries'] = collect($queries)
                ->sortByDesc('time')
                ->take(10)
                ->map(function ($query) {
                    return [
                        'sql' => $query['query'],
                        'time' => $query['time'],
                    ];
                })->values()->toArray();
        }

        Log::warning('Slow request detected', $logData);
    }

    /**
     * Detect potential N+1 query problems.
     * 
     * Looks for repeated similar queries which indicate N+1 issues.
     */
    protected function hasN1Problem(array $queries): bool
    {
        if (count($queries) < 5) {
            return false;
        }

        // Group queries by their structure (ignoring specific values)
        $patterns = [];
        foreach ($queries as $query) {
            // Normalize query by removing specific values
            $normalized = preg_replace('/\s+/', ' ', $query['query']);
            $normalized = preg_replace('/\d+/', 'N', $normalized);
            $normalized = preg_replace("/'[^']*'/", "'X'", $normalized);
            
            $patterns[$normalized] = ($patterns[$normalized] ?? 0) + 1;
        }

        // If any pattern repeats more than 5 times, likely N+1
        foreach ($patterns as $count) {
            if ($count > 5) {
                return true;
            }
        }

        return false;
    }
}

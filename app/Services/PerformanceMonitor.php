<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Performance Monitor Service
 * 
 * Monitors and tracks application performance metrics
 * including page load times, database queries, and memory usage.
 * 
 * @package App\Services
 */
class PerformanceMonitor
{
    /**
     * Start time for request tracking
     */
    protected static ?float $startTime = null;

    /**
     * Query log storage
     */
    protected static array $queryLog = [];

    /**
     * Track if monitoring is enabled
     */
    protected static bool $enabled = false;

    /**
     * Performance thresholds for alerts
     */
    protected const THRESHOLDS = [
        'response_time' => 500,   // ms
        'query_count' => 50,       // number of queries
        'query_time' => 100,       // ms per query
        'memory_usage' => 128,     // MB
    ];

    /**
     * Start performance monitoring for a request
     */
    public static function start(): void
    {
        self::$enabled = config('app.debug') || config('performance.monitoring', false);
        
        if (!self::$enabled) {
            return;
        }

        self::$startTime = microtime(true);
        self::$queryLog = [];

        // Enable query logging
        DB::enableQueryLog();
    }

    /**
     * Stop monitoring and get results
     */
    public static function stop(): array
    {
        if (!self::$enabled || self::$startTime === null) {
            return [];
        }

        $endTime = microtime(true);
        $responseTime = round(($endTime - self::$startTime) * 1000, 2);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $totalQueryTime = array_sum(array_column($queries, 'time'));

        $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $metrics = [
            'response_time_ms' => $responseTime,
            'query_count' => $queryCount,
            'total_query_time_ms' => round($totalQueryTime, 2),
            'memory_usage_mb' => $memoryUsage,
            'queries' => self::formatQueries($queries),
            'warnings' => self::checkThresholds([
                'response_time' => $responseTime,
                'query_count' => $queryCount,
                'query_time' => $totalQueryTime,
                'memory_usage' => $memoryUsage,
            ]),
        ];

        // Log slow requests
        if ($responseTime > self::THRESHOLDS['response_time']) {
            self::logSlowRequest($metrics);
        }

        // Disable query logging
        DB::disableQueryLog();

        self::$startTime = null;

        return $metrics;
    }

    /**
     * Format queries for output
     */
    protected static function formatQueries(array $queries): array
    {
        return array_map(function ($query) {
            return [
                'sql' => $query['query'],
                'time_ms' => round($query['time'], 2),
                'slow' => $query['time'] > self::THRESHOLDS['query_time'],
            ];
        }, $queries);
    }

    /**
     * Check metrics against thresholds
     */
    protected static function checkThresholds(array $metrics): array
    {
        $warnings = [];

        if ($metrics['response_time'] > self::THRESHOLDS['response_time']) {
            $warnings[] = sprintf(
                'Slow response time: %dms (threshold: %dms)',
                $metrics['response_time'],
                self::THRESHOLDS['response_time']
            );
        }

        if ($metrics['query_count'] > self::THRESHOLDS['query_count']) {
            $warnings[] = sprintf(
                'High query count: %d queries (threshold: %d)',
                $metrics['query_count'],
                self::THRESHOLDS['query_count']
            );
        }

        if ($metrics['memory_usage'] > self::THRESHOLDS['memory_usage']) {
            $warnings[] = sprintf(
                'High memory usage: %dMB (threshold: %dMB)',
                $metrics['memory_usage'],
                self::THRESHOLDS['memory_usage']
            );
        }

        return $warnings;
    }

    /**
     * Log slow requests for analysis
     */
    protected static function logSlowRequest(array $metrics): void
    {
        $request = request();

        Log::channel('performance')->warning('Slow request detected', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'response_time_ms' => $metrics['response_time_ms'],
            'query_count' => $metrics['query_count'],
            'memory_usage_mb' => $metrics['memory_usage_mb'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Track a custom metric
     */
    public static function track(string $name, $value, array $tags = []): void
    {
        if (!self::$enabled) {
            return;
        }

        $key = 'metrics:' . date('Y-m-d:H') . ':' . $name;

        Cache::increment($key);
        Cache::put($key . ':last_value', $value, 3600);

        if (!empty($tags)) {
            Cache::put($key . ':tags', $tags, 3600);
        }
    }

    /**
     * Get aggregated metrics for a time period
     */
    public static function getMetrics(string $period = 'hour'): array
    {
        $metrics = [];
        $date = now();

        switch ($period) {
            case 'hour':
                $key = 'metrics:' . $date->format('Y-m-d:H');
                break;
            case 'day':
                $key = 'metrics:' . $date->format('Y-m-d');
                break;
            default:
                $key = 'metrics:' . $date->format('Y-m-d:H');
        }

        // Get all cached metrics with matching prefix
        // Note: This is a simplified implementation
        // Production should use Redis or dedicated metrics storage

        return $metrics;
    }

    /**
     * Check database connection health
     */
    public static function checkDatabaseHealth(): array
    {
        $start = microtime(true);

        try {
            DB::select('SELECT 1');
            $connectionTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'connection_time_ms' => $connectionTime,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'connection_time_ms' => null,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache health
     */
    public static function checkCacheHealth(): array
    {
        $testKey = 'health_check_' . time();
        $testValue = 'test_value';

        try {
            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache read/write successful',
                ];
            }

            return [
                'status' => 'degraded',
                'message' => 'Cache value mismatch',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get overall system health
     */
    public static function getSystemHealth(): array
    {
        return [
            'database' => self::checkDatabaseHealth(),
            'cache' => self::checkCacheHealth(),
            'memory' => [
                'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ],
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

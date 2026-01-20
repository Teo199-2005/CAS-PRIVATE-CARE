<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

/**
 * Health Check Controller
 * 
 * Provides endpoints for monitoring application health and readiness.
 * Used by load balancers, Kubernetes, Docker health checks, and monitoring systems.
 */
class HealthController extends Controller
{
    /**
     * Basic health check - returns 200 if app is running
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'app' => config('app.name'),
            'environment' => config('app.env'),
        ]);
    }

    /**
     * Detailed health check with all service statuses
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        // Check Redis if configured
        if (config('database.redis.client')) {
            $checks['redis'] = $this->checkRedis();
        }

        $allHealthy = !in_array(false, array_column($checks, 'healthy'));
        $status = $allHealthy ? 'healthy' : 'degraded';
        $httpStatus = $allHealthy ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ], $httpStatus);
    }

    /**
     * Readiness check - is the app ready to serve traffic?
     */
    public function ready(): JsonResponse
    {
        $dbHealthy = $this->checkDatabase()['healthy'];
        
        if (!$dbHealthy) {
            return response()->json([
                'status' => 'not_ready',
                'message' => 'Database connection failed',
            ], 503);
        }

        return response()->json([
            'status' => 'ready',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Liveness check - is the app still running?
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'healthy' => true,
                'message' => 'Connected',
                'response_time_ms' => $responseTime,
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'response_time_ms' => null,
            ];
        }
    }

    /**
     * Check cache connectivity
     */
    private function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $key = 'health_check_' . uniqid();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'healthy' => $value === 'test',
                'message' => $value === 'test' ? 'Working' : 'Read/Write failed',
                'response_time_ms' => $responseTime,
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Cache failed: ' . $e->getMessage(),
                'response_time_ms' => null,
            ];
        }
    }

    /**
     * Check Redis connectivity
     */
    private function checkRedis(): array
    {
        // Check if Redis extension is available
        if (!extension_loaded('redis') && !class_exists('Predis\Client')) {
            return [
                'healthy' => false,
                'message' => 'Redis extension not installed',
                'response_time_ms' => null,
            ];
        }
        
        try {
            $startTime = microtime(true);
            $redis = Redis::connection();
            $redis->ping();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'healthy' => true,
                'message' => 'Connected',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Redis failed: ' . $e->getMessage(),
                'response_time_ms' => null,
            ];
        }
    }

    /**
     * Check storage accessibility
     */
    private function checkStorage(): array
    {
        try {
            $startTime = microtime(true);
            $filename = 'health_check_' . uniqid() . '.txt';
            Storage::put($filename, 'health check');
            $exists = Storage::exists($filename);
            Storage::delete($filename);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'healthy' => $exists,
                'message' => $exists ? 'Writable' : 'Not writable',
                'response_time_ms' => $responseTime,
                'disk' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Storage failed: ' . $e->getMessage(),
                'response_time_ms' => null,
            ];
        }
    }

    /**
     * Check queue connectivity
     */
    private function checkQueue(): array
    {
        try {
            $driver = config('queue.default');
            
            return [
                'healthy' => true,
                'message' => 'Configured',
                'driver' => $driver,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Queue check failed: ' . $e->getMessage(),
            ];
        }
    }
}

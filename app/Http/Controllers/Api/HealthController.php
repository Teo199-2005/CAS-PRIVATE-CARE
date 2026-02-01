<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PerformanceMonitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Health Check Controller
 * 
 * Provides endpoints for monitoring system health.
 * Used by load balancers, monitoring systems, and uptime checks.
 */
class HealthController extends Controller
{
    /**
     * Simple health check - returns 200 if app is running
     * 
     * Use this for basic uptime monitoring and load balancer health checks.
     * 
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Detailed health check - checks all critical services
     * 
     * Returns detailed status of database, cache, and other services.
     * Should be protected in production or accessed with a key.
     * 
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        $health = PerformanceMonitor::getSystemHealth();

        // Determine overall status
        $allHealthy = true;
        foreach (['database', 'cache'] as $service) {
            if (($health[$service]['status'] ?? '') !== 'healthy') {
                $allHealthy = false;
                break;
            }
        }

        $statusCode = $allHealthy ? 200 : 503;

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'services' => $health,
        ], $statusCode);
    }

    /**
     * Readiness check - checks if app is ready to serve requests
     * 
     * Used by Kubernetes and other orchestration systems.
     * 
     * @return JsonResponse
     */
    public function ready(): JsonResponse
    {
        $dbHealth = PerformanceMonitor::checkDatabaseHealth();

        if ($dbHealth['status'] !== 'healthy') {
            return response()->json([
                'ready' => false,
                'reason' => 'Database not ready',
            ], 503);
        }

        return response()->json([
            'ready' => true,
        ]);
    }

    /**
     * Liveness check - checks if app process is alive
     * 
     * Simple check that the PHP process is running.
     * 
     * @return JsonResponse
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'alive' => true,
        ]);
    }

    /**
     * Version information
     * 
     * Returns application version and environment info.
     * 
     * @return JsonResponse
     */
    public function version(): JsonResponse
    {
        return response()->json([
            'app' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    }
}

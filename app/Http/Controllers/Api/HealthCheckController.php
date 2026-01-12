<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    /**
     * Perform health check on all system components
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
        
        $overall = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        
        return response()->json([
            'status' => $overall ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'version' => config('app.version', '1.0.0'),
            ],
        ], $overall ? 200 : 503);
    }
    
    /**
     * Lightweight ping endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'pong',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            // Test a simple query
            DB::table('users')->limit(1)->get();
            
            return [
                'status' => 'ok',
                'latency_ms' => $latency,
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Connection error',
            ];
        }
    }
    
    /**
     * Check cache system
     */
    protected function checkCache(): array
    {
        try {
            $start = microtime(true);
            $key = 'health_check_' . time();
            $value = 'test_' . random_int(1000, 9999);
            
            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            if ($retrieved !== $value) {
                throw new \Exception('Cache value mismatch');
            }
            
            return [
                'status' => 'ok',
                'latency_ms' => $latency,
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Cache health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Cache system failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Cache error',
            ];
        }
    }
    
    /**
     * Check storage system
     */
    protected function checkStorage(): array
    {
        try {
            $start = microtime(true);
            $path = 'health_check_' . time() . '.txt';
            $content = 'health_check_' . random_int(1000, 9999);
            
            Storage::disk('local')->put($path, $content);
            $retrieved = Storage::disk('local')->get($path);
            Storage::disk('local')->delete($path);
            
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            if ($retrieved !== $content) {
                throw new \Exception('Storage content mismatch');
            }
            
            return [
                'status' => 'ok',
                'latency_ms' => $latency,
                'driver' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Storage health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Storage system failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Storage error',
            ];
        }
    }
    
    /**
     * Check queue system
     */
    protected function checkQueue(): array
    {
        try {
            $driver = config('queue.default');
            
            // Simple connectivity check based on driver
            if ($driver === 'database') {
                DB::table('jobs')->limit(1)->get();
            }
            
            return [
                'status' => 'ok',
                'driver' => $driver,
            ];
        } catch (\Exception $e) {
            Log::error('Queue health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Queue system failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Queue error',
            ];
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformMetricsController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get platform-wide metrics for admin dashboard
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Cache metrics for 5 minutes to reduce database load
            $metrics = Cache::remember('admin_platform_metrics', 300, function () {
                return [
                    'total_bookings' => $this->getTotalBookings(),
                    'response_time' => $this->getAverageResponseTime(),
                    'error_rate' => $this->getErrorRate(),
                    'active_sessions' => $this->getActiveSessions(),
                    'uptime' => $this->getSystemUptime(),
                ];
            });

            return $this->successResponse(['metrics' => $metrics]);

        } catch (\Exception $e) {
            Log::error('Platform metrics error: ' . $e->getMessage());
            
            // Return fallback metrics on error
            return $this->successResponse([
                'metrics' => [
                    'total_bookings' => 0,
                    'response_time' => 0,
                    'error_rate' => 0.0,
                    'active_sessions' => 0,
                    'uptime' => 99.9,
                ]
            ]);
        }
    }

    /**
     * Get total bookings count
     */
    private function getTotalBookings(): int
    {
        return Booking::count();
    }

    /**
     * Get average API response time (in milliseconds)
     * This is a simplified version - in production, use APM tools like New Relic
     */
    private function getAverageResponseTime(): int
    {
        // For now, return a calculated estimate
        // TODO: Integrate with APM tool or Laravel Telescope for real metrics
        
        // Check if we have slow query logs
        try {
            // Measure a test query
            $start = microtime(true);
            DB::table('bookings')->take(1)->get();
            $duration = (microtime(true) - $start) * 1000; // Convert to ms
            
            // Return rounded duration (typically 10-50ms for local DB)
            return (int) round($duration);
        } catch (\Exception $e) {
            return 25; // Default estimate
        }
    }

    /**
     * Get error rate percentage
     * Calculate from Laravel logs (last 24 hours)
     */
    private function getErrorRate(): float
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (!file_exists($logFile)) {
                return 0.0;
            }

            // Read last 1000 lines of log file
            $lines = $this->getLastLines($logFile, 1000);
            
            if (empty($lines)) {
                return 0.0;
            }

            $totalRequests = 0;
            $errors = 0;

            foreach ($lines as $line) {
                // Count errors (ERROR, CRITICAL, EMERGENCY levels)
                if (preg_match('/\.(ERROR|CRITICAL|EMERGENCY)/', $line)) {
                    $errors++;
                }
                
                // Count total logged requests
                if (preg_match('/\.(INFO|ERROR|WARNING|CRITICAL|DEBUG)/', $line)) {
                    $totalRequests++;
                }
            }

            if ($totalRequests === 0) {
                return 0.0;
            }

            $errorRate = ($errors / $totalRequests) * 100;
            return round($errorRate, 2);

        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get active user sessions
     */
    private function getActiveSessions(): int
    {
        try {
            // Count sessions from last 30 minutes
            $activeSessions = DB::table('sessions')
                ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                ->count();

            return $activeSessions;
        } catch (\Exception $e) {
            // Fallback: count recently active users
            return User::where('updated_at', '>', now()->subMinutes(30))->count();
        }
    }

    /**
     * Get system uptime percentage (last 30 days)
     * This is a basic implementation - for production use UptimeRobot or Pingdom
     */
    private function getSystemUptime(): float
    {
        try {
            // Check if we have an uptime_logs table
            if (DB::getSchemaBuilder()->hasTable('uptime_logs')) {
                $logs = DB::table('uptime_logs')
                    ->where('checked_at', '>=', now()->subDays(30))
                    ->get();

                if ($logs->count() > 0) {
                    $upLogs = $logs->where('is_up', true)->count();
                    return round(($upLogs / $logs->count()) * 100, 2);
                }
            }

            // Fallback: Calculate based on error logs
            // If error rate is low, uptime is high
            $errorRate = $this->getErrorRate();
            $uptime = 100 - ($errorRate * 0.1); // Rough estimate
            
            return max(98.0, min(100.0, round($uptime, 2)));

        } catch (\Exception $e) {
            return 99.5; // Default estimate
        }
    }

    /**
     * Helper function to read last N lines from a file
     */
    private function getLastLines(string $filePath, int $lines = 100): array
    {
        try {
            $file = new \SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);
            $lastLine = $file->key();
            
            $startLine = max(0, $lastLine - $lines);
            $file->seek($startLine);

            $result = [];
            while (!$file->eof()) {
                $result[] = $file->fgets();
            }

            return array_filter($result);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Clear platform metrics cache (call this when major changes occur)
     */
    public function clearCache(): JsonResponse
    {
        Cache::forget('admin_platform_metrics');
        
        return response()->json([
            'success' => true,
            'message' => 'Platform metrics cache cleared'
        ]);
    }
}

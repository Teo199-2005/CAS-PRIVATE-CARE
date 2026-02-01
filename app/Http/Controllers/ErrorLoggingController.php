<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Responses\ApiResponse;

/**
 * Error Logging Controller
 * 
 * Receives frontend error reports and logs them for monitoring.
 * Rate limited to prevent abuse.
 * 
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class ErrorLoggingController extends Controller
{
    /**
     * Maximum errors per minute per IP
     */
    protected const RATE_LIMIT = 30;

    /**
     * Log frontend errors
     */
    public function log(Request $request)
    {
        // Rate limiting
        $key = 'error-log:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT)) {
            return ApiResponse::tooManyRequests(
                'Too many error reports. Please try again later.',
                RateLimiter::availableIn($key)
            );
        }
        
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'errors' => 'required|array|max:50',
            'errors.*.type' => 'required|string|in:error,message',
            'errors.*.level' => 'required|string|in:error,warning,info',
            'errors.*.timestamp' => 'required|string',
            'errors.*.error' => 'nullable|array',
            'errors.*.error.name' => 'nullable|string|max:255',
            'errors.*.error.message' => 'nullable|string|max:2000',
            'errors.*.error.stack' => 'nullable|string|max:10000',
            'errors.*.environment' => 'nullable|array',
            'errors.*.user' => 'nullable|array',
            'errors.*.context' => 'nullable|array',
        ]);

        foreach ($validated['errors'] as $errorReport) {
            $this->logError($errorReport, $request);
        }

        return ApiResponse::success(null, 'Errors logged successfully');
    }

    /**
     * Log a single error
     */
    protected function logError(array $errorReport, Request $request): void
    {
        $level = $errorReport['level'] ?? 'error';
        $channel = 'frontend';

        $logData = [
            'type' => $errorReport['type'] ?? 'error',
            'error_name' => $errorReport['error']['name'] ?? 'Unknown',
            'error_message' => $errorReport['error']['message'] ?? 'Unknown error',
            'url' => $errorReport['environment']['url'] ?? $request->header('Referer'),
            'user_agent' => $errorReport['environment']['userAgent'] ?? $request->userAgent(),
            'user_id' => $errorReport['user']['id'] ?? auth()->id(),
            'session_id' => $errorReport['session']['id'] ?? null,
            'context' => $errorReport['context'] ?? [],
            'ip' => $request->ip(),
            'timestamp' => $errorReport['timestamp'] ?? now()->toISOString(),
        ];

        // Don't log sensitive data
        unset($logData['context']['password']);
        unset($logData['context']['token']);
        unset($logData['context']['card']);

        // Log based on level
        match ($level) {
            'error' => Log::channel($channel)->error('Frontend Error', $logData),
            'warning' => Log::channel($channel)->warning('Frontend Warning', $logData),
            'info' => Log::channel($channel)->info('Frontend Info', $logData),
            default => Log::channel($channel)->debug('Frontend Log', $logData),
        };

        // For critical errors, also log stack trace
        if ($level === 'error' && !empty($errorReport['error']['stack'])) {
            Log::channel($channel)->debug('Stack Trace', [
                'error_message' => $errorReport['error']['message'] ?? 'Unknown',
                'stack' => $errorReport['error']['stack'],
            ]);
        }
    }

    /**
     * Health check endpoint for error logging
     */
    public function health()
    {
        return ApiResponse::success([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
        ]);
    }
}

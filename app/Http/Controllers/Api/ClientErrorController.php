<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * Client Error Controller
 * 
 * Receives and logs errors from the frontend JavaScript.
 * Useful for tracking production errors that users encounter.
 */
class ClientErrorController extends Controller
{
    /**
     * Store a client-side error
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => 'required|string|in:DEBUG,INFO,WARN,ERROR',
            'message' => 'required|string|max:5000',
            'context' => 'nullable',
            'url' => 'nullable|string|max:2000',
            'userAgent' => 'nullable|string|max:1000',
            'timestamp' => 'nullable|string',
        ]);

        // Filter out known non-critical errors
        if ($this->shouldIgnore($validated['message'])) {
            return response()->json(['status' => 'ignored']);
        }

        // Get additional context
        $errorData = [
            'level' => $validated['level'],
            'message' => $validated['message'],
            'context' => $validated['context'] ?? null,
            'url' => $validated['url'] ?? null,
            'user_agent' => $validated['userAgent'] ?? null,
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'timestamp' => $validated['timestamp'] ?? now()->toIso8601String(),
        ];

        // Log based on level
        $logLevel = strtolower($validated['level']);
        
        if ($logLevel === 'error') {
            Log::channel('daily')->error('Client-side error', $errorData);
        } elseif ($logLevel === 'warn') {
            Log::channel('daily')->warning('Client-side warning', $errorData);
        } else {
            Log::channel('daily')->info('Client-side log', $errorData);
        }

        return response()->json(['status' => 'logged']);
    }

    /**
     * Check if error should be ignored
     * 
     * @param string $message
     * @return bool
     */
    protected function shouldIgnore(string $message): bool
    {
        $ignorePatterns = [
            // Browser extension errors
            'chrome-extension://',
            'moz-extension://',
            'safari-extension://',
            
            // Common false positives
            'ResizeObserver loop',
            'Script error.',
            'Non-Error promise rejection',
            
            // Network errors that are expected
            'Failed to fetch',
            'NetworkError',
            'AbortError',
            
            // Third-party script errors
            'googletag',
            'gtag',
            'fbq',
            'analytics',
        ];

        foreach ($ignorePatterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}

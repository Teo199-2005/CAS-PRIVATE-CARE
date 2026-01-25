<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * WebVitalsController
 * 
 * Receives and logs Core Web Vitals metrics from the frontend.
 * 
 * Why it matters:
 * - Real User Monitoring (RUM) provides actual performance data
 * - Google uses Core Web Vitals for search rankings
 * - Helps identify performance issues affecting real users
 * 
 * Metrics tracked:
 * - LCP (Largest Contentful Paint): Loading performance
 * - FID (First Input Delay): Interactivity
 * - CLS (Cumulative Layout Shift): Visual stability
 * - TTFB (Time to First Byte): Server response time
 * - FCP (First Contentful Paint): First paint
 * - INP (Interaction to Next Paint): Responsiveness
 */
class WebVitalsController extends Controller
{
    /**
     * Google's recommended thresholds for Core Web Vitals
     */
    protected const THRESHOLDS = [
        'LCP' => ['good' => 2500, 'poor' => 4000],
        'FID' => ['good' => 100, 'poor' => 300],
        'CLS' => ['good' => 0.1, 'poor' => 0.25],
        'FCP' => ['good' => 1800, 'poor' => 3000],
        'TTFB' => ['good' => 800, 'poor' => 1800],
        'INP' => ['good' => 200, 'poor' => 500],
    ];

    /**
     * Receive Web Vitals metric from frontend
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|in:LCP,FID,CLS,FCP,TTFB,INP',
            'value' => 'required|numeric',
            'rating' => 'sometimes|string|in:good,needs-improvement,poor',
            'delta' => 'sometimes|numeric',
            'id' => 'sometimes|string',
            'navigationType' => 'sometimes|string',
            'url' => 'sometimes|url',
            'timestamp' => 'sometimes|date',
            'userAgent' => 'sometimes|string',
            'connection' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        // Calculate rating if not provided
        if (!isset($data['rating'])) {
            $data['rating'] = $this->calculateRating($data['name'], $data['value']);
        }

        // Log to performance channel
        $this->logMetric($data, $request);

        // Alert on poor performance (optional: integrate with Sentry/Slack)
        if ($data['rating'] === 'poor') {
            $this->alertPoorPerformance($data);
        }

        return response()->json([
            'success' => true,
            'received' => $data['name'],
        ]);
    }

    /**
     * Get aggregated Web Vitals summary (for admin dashboard)
     */
    public function summary(): JsonResponse
    {
        // In production, this would query from a time-series database
        // For now, return placeholder showing the expected format
        
        return response()->json([
            'success' => true,
            'period' => 'last_24_hours',
            'metrics' => [
                'LCP' => [
                    'p75' => null, // 75th percentile value
                    'samples' => 0,
                    'good_percent' => null,
                ],
                'FID' => [
                    'p75' => null,
                    'samples' => 0,
                    'good_percent' => null,
                ],
                'CLS' => [
                    'p75' => null,
                    'samples' => 0,
                    'good_percent' => null,
                ],
                'INP' => [
                    'p75' => null,
                    'samples' => 0,
                    'good_percent' => null,
                ],
            ],
            'note' => 'Metrics will populate as users visit the site.',
        ]);
    }

    /**
     * Calculate performance rating based on Google thresholds
     */
    protected function calculateRating(string $name, float $value): string
    {
        $thresholds = self::THRESHOLDS[$name] ?? null;
        
        if (!$thresholds) {
            return 'unknown';
        }

        if ($value <= $thresholds['good']) {
            return 'good';
        }
        
        if ($value <= $thresholds['poor']) {
            return 'needs-improvement';
        }
        
        return 'poor';
    }

    /**
     * Log metric to performance channel
     */
    protected function logMetric(array $data, Request $request): void
    {
        $context = [
            'metric' => $data['name'],
            'value' => $data['value'],
            'rating' => $data['rating'],
            'url' => $data['url'] ?? $request->header('Referer'),
            'user_agent' => $data['userAgent'] ?? $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
        ];

        // Add connection info if available
        if (isset($data['connection'])) {
            $context['connection'] = $data['connection'];
        }

        // Log level based on rating
        $logLevel = match ($data['rating']) {
            'poor' => 'warning',
            'needs-improvement' => 'info',
            default => 'debug',
        };

        Log::channel('performance')->{$logLevel}(
            "Web Vitals: {$data['name']} = {$data['value']} ({$data['rating']})",
            $context
        );
    }

    /**
     * Alert on poor performance (can integrate with Sentry, Slack, etc.)
     */
    protected function alertPoorPerformance(array $data): void
    {
        // Only alert on critical metrics
        $criticalMetrics = ['LCP', 'FID', 'CLS'];
        
        if (!in_array($data['name'], $criticalMetrics)) {
            return;
        }

        // Log as warning for monitoring systems to pick up
        Log::channel('performance')->warning(
            "Poor Core Web Vital detected: {$data['name']}",
            [
                'metric' => $data['name'],
                'value' => $data['value'],
                'threshold_good' => self::THRESHOLDS[$data['name']]['good'] ?? 'N/A',
                'url' => $data['url'] ?? 'unknown',
            ]
        );

        // Optional: Send to Sentry as performance issue
        // if (app()->bound('sentry')) {
        //     \Sentry\captureMessage("Poor {$data['name']}: {$data['value']}", 
        //         \Sentry\Severity::warning()
        //     );
        // }
    }
}

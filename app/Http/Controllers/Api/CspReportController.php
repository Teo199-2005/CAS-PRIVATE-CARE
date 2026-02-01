<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * CSP Report Controller
 * 
 * Handles Content Security Policy violation reports for security monitoring.
 * These reports help identify XSS attempts and CSP misconfigurations.
 * 
 * @package App\Http\Controllers\Api
 */
class CspReportController extends Controller
{
    /**
     * Maximum reports to store per day (prevent DoS)
     */
    private const MAX_REPORTS_PER_DAY = 1000;

    /**
     * Handle CSP violation report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Get the CSP report (sent as JSON in request body)
            $report = $request->input('csp-report') ?? $request->all();
            
            if (empty($report)) {
                return response()->json(['status' => 'empty'], 204);
            }

            // Rate limit check - prevent DoS through excessive reports
            $todayCount = DB::table('csp_reports')
                ->whereDate('created_at', today())
                ->count();

            if ($todayCount >= self::MAX_REPORTS_PER_DAY) {
                Log::warning('CSP report rate limit exceeded', [
                    'count' => $todayCount,
                    'ip' => $request->ip()
                ]);
                return response()->json(['status' => 'rate_limited'], 429);
            }

            // Extract relevant fields
            $violationData = [
                'document_uri' => $report['document-uri'] ?? null,
                'referrer' => $report['referrer'] ?? null,
                'violated_directive' => $report['violated-directive'] ?? null,
                'effective_directive' => $report['effective-directive'] ?? null,
                'original_policy' => $report['original-policy'] ?? null,
                'blocked_uri' => $report['blocked-uri'] ?? null,
                'source_file' => $report['source-file'] ?? null,
                'line_number' => $report['line-number'] ?? null,
                'column_number' => $report['column-number'] ?? null,
                'status_code' => $report['status-code'] ?? null,
                'script_sample' => $report['script-sample'] ?? null,
                'disposition' => $report['disposition'] ?? 'enforce',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ];

            // Filter out false positives (browser extensions, etc.)
            if ($this->isFalsePositive($violationData)) {
                return response()->json(['status' => 'ignored'], 200);
            }

            // Log to file for immediate visibility
            Log::channel('security')->warning('CSP Violation Detected', $violationData);

            // Store in database if table exists
            if (\Schema::hasTable('csp_reports')) {
                DB::table('csp_reports')->insert($violationData);
            }

            return response()->json(['status' => 'received'], 200);

        } catch (\Exception $e) {
            Log::error('Failed to process CSP report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Get CSP violation summary for admin dashboard
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!\Schema::hasTable('csp_reports')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'CSP reports table not configured'
            ]);
        }

        $days = $request->get('days', 7);
        
        $reports = DB::table('csp_reports')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $summary = DB::table('csp_reports')
            ->where('created_at', '>=', now()->subDays($days))
            ->select('violated_directive', DB::raw('count(*) as count'))
            ->groupBy('violated_directive')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => $reports,
                'summary' => $summary,
                'total' => $reports->count(),
                'period_days' => $days
            ]
        ]);
    }

    /**
     * Check if the violation is a known false positive
     * 
     * @param array $violation
     * @return bool
     */
    private function isFalsePositive(array $violation): bool
    {
        $blockedUri = $violation['blocked_uri'] ?? '';
        $sourceFile = $violation['source_file'] ?? '';
        
        // Browser extension patterns
        $extensionPatterns = [
            'chrome-extension://',
            'moz-extension://',
            'safari-extension://',
            'ms-browser-extension://',
            'webkit-masked-url://',
        ];

        foreach ($extensionPatterns as $pattern) {
            if (str_contains($blockedUri, $pattern) || str_contains($sourceFile, $pattern)) {
                return true;
            }
        }

        // Common false positives
        $falsePositivePatterns = [
            'about:blank',
            'data:application/x-unknown',
            'blob:null',
        ];

        foreach ($falsePositivePatterns as $pattern) {
            if (str_contains($blockedUri, $pattern)) {
                return true;
            }
        }

        return false;
    }
}

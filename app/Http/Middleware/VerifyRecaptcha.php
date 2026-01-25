<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    /**
     * Verification endpoint
     */
    protected const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Minimum score for reCAPTCHA v3 (0.0 to 1.0)
     */
    protected const MIN_SCORE = 0.5;

    /**
     * Routes that should bypass reCAPTCHA verification
     */
    protected array $except = [
        'api/webhooks/*',
        'api/health',
        'sanctum/csrf-cookie',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = ''): Response
    {
        // Skip verification if disabled or in testing
        if ($this->shouldSkipVerification($request)) {
            return $next($request);
        }

        $token = $request->input('g-recaptcha-response') ?? $request->header('X-Recaptcha-Token');

        if (empty($token)) {
            return $this->failedResponse($request, 'reCAPTCHA verification required');
        }

        $verificationResult = $this->verifyToken($token, $action, $request->ip());

        if (!$verificationResult['success']) {
            return $this->failedResponse($request, $verificationResult['message']);
        }

        // Add verification result to request for logging
        $request->merge(['recaptcha_verified' => true]);

        return $next($request);
    }

    /**
     * Check if verification should be skipped
     */
    protected function shouldSkipVerification(Request $request): bool
    {
        // Disabled in config
        if (!config('services.recaptcha.enabled', true)) {
            return true;
        }

        // Testing environment
        if (app()->environment('testing')) {
            return true;
        }

        // No secret key configured
        if (empty(config('services.recaptcha.secret_key'))) {
            Log::warning('reCAPTCHA secret key not configured');
            return true;
        }

        // Check excluded routes
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify the reCAPTCHA token
     */
    protected function verifyToken(string $token, string $action, string $ip): array
    {
        try {
            $response = Http::timeout(10)
                ->asForm()
                ->post(self::VERIFY_URL, [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (!$response->successful()) {
                Log::warning('reCAPTCHA API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['success' => false, 'message' => 'Verification service unavailable'];
            }

            $result = $response->json();

            // Check basic success
            if (!($result['success'] ?? false)) {
                $errors = $result['error-codes'] ?? [];
                Log::info('reCAPTCHA verification failed', ['errors' => $errors]);
                return ['success' => false, 'message' => $this->mapErrorCode($errors)];
            }

            // For reCAPTCHA v3, check score
            if (isset($result['score'])) {
                if ($result['score'] < self::MIN_SCORE) {
                    Log::info('reCAPTCHA score too low', [
                        'score' => $result['score'],
                        'action' => $result['action'] ?? 'unknown'
                    ]);
                    return ['success' => false, 'message' => 'Verification score too low'];
                }

                // Check action matches if specified
                if (!empty($action) && isset($result['action']) && $result['action'] !== $action) {
                    Log::warning('reCAPTCHA action mismatch', [
                        'expected' => $action,
                        'received' => $result['action']
                    ]);
                    return ['success' => false, 'message' => 'Verification action mismatch'];
                }
            }

            return ['success' => true, 'message' => 'Verified', 'score' => $result['score'] ?? null];

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fail open in case of service issues (configurable)
            if (config('services.recaptcha.fail_open', false)) {
                return ['success' => true, 'message' => 'Verification bypassed'];
            }
            
            return ['success' => false, 'message' => 'Verification failed'];
        }
    }

    /**
     * Map reCAPTCHA error codes to user-friendly messages
     */
    protected function mapErrorCode(array $errors): string
    {
        $errorMap = [
            'missing-input-secret' => 'Configuration error',
            'invalid-input-secret' => 'Configuration error',
            'missing-input-response' => 'Please complete the reCAPTCHA',
            'invalid-input-response' => 'Invalid reCAPTCHA response',
            'bad-request' => 'Invalid request',
            'timeout-or-duplicate' => 'reCAPTCHA expired, please try again',
        ];

        foreach ($errors as $error) {
            if (isset($errorMap[$error])) {
                return $errorMap[$error];
            }
        }

        return 'Verification failed';
    }

    /**
     * Return failed verification response
     */
    protected function failedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => ['recaptcha' => [$message]]
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors(['recaptcha' => $message]);
    }
}

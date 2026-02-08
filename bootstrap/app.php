<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Register versioned API routes
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
            
            // Register Stripe routes (API with Sanctum)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/stripe.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Redirect HTTP to HTTPS in production when APP_URL is https (run first)
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        // Register global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\SanitizeInput::class);
        $middleware->append(\App\Http\Middleware\PerformanceMonitor::class);
        $middleware->append(\App\Http\Middleware\OptimizedSessionManagement::class);
        
        // Query profiler for development (only logs slow queries)
        if (env('APP_ENV') !== 'production') {
            $middleware->append(\App\Http\Middleware\QueryProfiler::class);
        }
        
        // Enable session authentication for API routes
        $middleware->statefulApi();
        
        // Exclude Stripe webhook and auth-protected API from CSRF verification
        // (these routes are same-origin and protected by auth / user.type middleware)
        $middleware->validateCsrfTokens(except: [
            'api/stripe/webhook',
            'api/webhooks/stripe',
            'stripe/webhook',
            'api/training/caregivers/*/approve',
            'api/training/caregivers/*/reject',
            'api/profile/update',
        ]);
        
        // Register route middleware aliases
        $middleware->alias([
            'user.type' => \App\Http\Middleware\EnsureUserType::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'cache.api' => \App\Http\Middleware\CacheApiResponse::class,
            'rate-limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'smart.throttle' => \App\Http\Middleware\SmartRateLimit::class,
            'csp' => \App\Http\Middleware\ContentSecurityPolicy::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
            'performance' => \App\Http\Middleware\PerformanceMonitor::class,
            '2fa' => \App\Http\Middleware\TwoFactorAuthentication::class,
            'verify.recaptcha' => \App\Http\Middleware\VerifyRecaptcha::class,
            'lockout' => \App\Http\Middleware\ProgressiveAccountLockout::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

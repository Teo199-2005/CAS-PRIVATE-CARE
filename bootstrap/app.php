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
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\SanitizeInput::class);
        $middleware->append(\App\Http\Middleware\PerformanceMonitor::class);
        
        // Enable session authentication for API routes
        $middleware->statefulApi();
        
        // Register route middleware aliases
        $middleware->alias([
            'user.type' => \App\Http\Middleware\EnsureUserType::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'cache.api' => \App\Http\Middleware\CacheApiResponse::class,
            'rate-limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'csp' => \App\Http\Middleware\ContentSecurityPolicy::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
            'performance' => \App\Http\Middleware\PerformanceMonitor::class,
            '2fa' => \App\Http\Middleware\TwoFactorAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

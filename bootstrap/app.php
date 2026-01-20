<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        // Enable session authentication for API routes
        $middleware->statefulApi();
        
        // Register route middleware aliases
        $middleware->alias([
            'user.type' => \App\Http\Middleware\EnsureUserType::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'cache.api' => \App\Http\Middleware\CacheApiResponse::class,
            'rate-limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'csp' => \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

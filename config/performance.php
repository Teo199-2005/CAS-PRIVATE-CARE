<?php

/**
 * CAS Private Care - Performance Configuration
 * 
 * Settings for caching, CDN, and optimization
 */

return [

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your CDN for static assets. Set ASSET_CDN_URL in your .env
    | file to enable CDN delivery of static assets.
    |
    */

    'cdn' => [
        'enabled' => env('CDN_ENABLED', true),
        'url' => env('ASSET_CDN_URL', null),
        'paths' => [
            'images' => env('CDN_IMAGES_PATH', '/images'),
            'css' => env('CDN_CSS_PATH', '/css'),
            'js' => env('CDN_JS_PATH', '/js'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Caching
    |--------------------------------------------------------------------------
    |
    | Configure page-level caching for improved performance.
    |
    */

    'response_cache' => [
        'enabled' => env('RESPONSE_CACHE_ENABLED', true),
        'lifetime' => env('RESPONSE_CACHE_LIFETIME', 3600), // seconds
        'exclude_urls' => [
            '/api/*',
            '/admin/*',
            '/login',
            '/register',
            '/profile/*',
            '/dashboard/*',
            '/client/*',
            '/caregiver/*',
            '/housekeeper/*',
            '/marketing/*',
            '/training/*',
            '/payment*',
            '/book-service',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Optimization
    |--------------------------------------------------------------------------
    |
    | Configure asset minification and bundling settings.
    |
    */

    'assets' => [
        'minify_html' => env('MINIFY_HTML', true),
        'minify_css' => env('MINIFY_CSS', true),
        'minify_js' => env('MINIFY_JS', true),
        'inline_critical_css' => env('INLINE_CRITICAL_CSS', true),
        'defer_js' => env('DEFER_JS', true),
        'lazy_load_images' => env('LAZY_LOAD_IMAGES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Configure image optimization settings.
    |
    */

    'images' => [
        'optimize' => env('OPTIMIZE_IMAGES', true),
        'quality' => env('IMAGE_QUALITY', 85),
        'webp_conversion' => env('CONVERT_TO_WEBP', true),
        'max_width' => env('IMAGE_MAX_WIDTH', 2000),
        'max_height' => env('IMAGE_MAX_HEIGHT', 2000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Query Optimization
    |--------------------------------------------------------------------------
    |
    | Configure query caching and optimization settings.
    |
    */

    'database' => [
        'query_cache_enabled' => env('QUERY_CACHE_ENABLED', true),
        'query_cache_duration' => env('QUERY_CACHE_DURATION', 60), // minutes
        'eager_loading' => true,
        'chunk_size' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP/2 Push
    |--------------------------------------------------------------------------
    |
    | Configure HTTP/2 server push for critical assets.
    |
    */

    'http2_push' => [
        'enabled' => env('HTTP2_PUSH_ENABLED', false),
        'assets' => [
            '/build/assets/app.css',
            '/build/assets/app.js',
            '/logo.png',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression
    |--------------------------------------------------------------------------
    |
    | Configure response compression settings.
    |
    */

    'compression' => [
        'enabled' => env('COMPRESSION_ENABLED', true),
        'level' => env('COMPRESSION_LEVEL', 6), // 1-9
        'min_size' => 1024, // bytes - don't compress responses smaller than this
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Optimization
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting thresholds for different routes.
    |
    */

    'rate_limits' => [
        'api' => env('API_RATE_LIMIT', 60), // requests per minute
        'web' => env('WEB_RATE_LIMIT', 120), // requests per minute
        'auth' => env('AUTH_RATE_LIMIT', 5), // login attempts per minute
        'search' => env('SEARCH_RATE_LIMIT', 30), // search requests per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloading and Prefetching
    |--------------------------------------------------------------------------
    |
    | Configure resource hints for improved page load performance.
    |
    */

    'preload' => [
        'fonts' => [
            '/fonts/sora.woff2',
            '/fonts/plus-jakarta-sans.woff2',
        ],
        'preconnect' => [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
            'https://js.stripe.com',
        ],
        'dns_prefetch' => [
            'https://www.google-analytics.com',
            'https://www.googletagmanager.com',
        ],
    ],

];

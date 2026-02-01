<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Whitelist
    |--------------------------------------------------------------------------
    |
    | IP addresses in this list will receive relaxed rate limiting.
    | Useful for:
    | - Your office/home IP during testing
    | - CI/CD servers
    | - Monitoring services
    | - Trusted API consumers
    |
    | Note: Localhost is automatically whitelisted in non-production environments
    |
    */

    'whitelist' => array_filter(explode(',', env('RATE_LIMIT_WHITELIST', ''))),

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for different environments and endpoints.
    | These can be overridden via environment variables.
    |
    */

    'limits' => [
        'production' => [
            'login' => env('RATE_LIMIT_LOGIN_PROD', 10),
            'register' => env('RATE_LIMIT_REGISTER_PROD', 5),
            'api' => env('RATE_LIMIT_API_PROD', 60),
            'payment' => env('RATE_LIMIT_PAYMENT_PROD', 5),
        ],
        'testing' => [
            'login' => env('RATE_LIMIT_LOGIN_TEST', 50),
            'register' => env('RATE_LIMIT_REGISTER_TEST', 30),
            'api' => env('RATE_LIMIT_API_TEST', 300),
            'payment' => env('RATE_LIMIT_PAYMENT_TEST', 20),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bypass Rate Limiting for Testing
    |--------------------------------------------------------------------------
    |
    | When true, rate limiting is completely disabled.
    | ONLY use this in local/testing environments!
    |
    */

    'bypass_in_testing' => env('BYPASS_RATE_LIMIT', false),

];


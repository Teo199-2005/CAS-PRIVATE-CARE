<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | Your Stripe publishable and secret keys. Get these from:
    | https://dashboard.stripe.com/apikeys
    |
    | Use test keys for development (starts with pk_test_ and sk_test_)
    | Use live keys for production (starts with pk_live_ and sk_live_)
    |
    */

    'key' => env('STRIPE_KEY', ''),
    'publishable_key' => env('STRIPE_KEY', ''), // Same as key for backward compatibility
    'secret' => env('STRIPE_SECRET', ''),
    'secret_key' => env('STRIPE_SECRET', ''), // Same as secret for backward compatibility

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Your webhook signing secret. Get this when you create a webhook endpoint:
    | https://dashboard.stripe.com/webhooks
    |
    | This is used to verify webhook requests are genuinely from Stripe.
    |
    */

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The currency to use for all transactions.
    |
    */

    'currency' => 'usd',

    /*
    |--------------------------------------------------------------------------
    | Connect Settings
    |--------------------------------------------------------------------------
    |
    | Settings for Stripe Connect (used for paying caregivers and partners)
    |
    */

    'connect' => [
        'refresh_url' => env('APP_URL') . '/payment-settings?refresh=true',
        'return_url' => env('APP_URL') . '/payment-settings?success=true',
    ],
];

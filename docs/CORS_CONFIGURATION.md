# CORS Configuration

## Overview

Cross-Origin Resource Sharing (CORS) is configured to allow the CAS Private Care frontend to communicate with the Laravel API backend and integrate with Stripe's JavaScript SDK.

## Current Settings

The application uses Laravel's built-in CORS middleware with the following configuration:

### config/cors.php

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'stripe/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('APP_URL', 'http://localhost'),
        'https://js.stripe.com',
        'https://m.stripe.network',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-Query-Count',
        'X-Query-Time-Ms',
    ],

    'max_age' => 0,

    'supports_credentials' => true,
];
```

## Production Recommendations

### 1. Restrict Allowed Methods

Only allow the HTTP methods actually used by your API:

```php
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
```

### 2. Explicitly List Allowed Origins

Never use `*` in production. List all valid origins:

```php
'allowed_origins' => [
    'https://casprivatecare.com',
    'https://www.casprivatecare.com',
    'https://app.casprivatecare.com',
    'https://js.stripe.com',
    'https://m.stripe.network',
],
```

### 3. Set max_age for Caching

Cache preflight requests to reduce OPTIONS calls:

```php
'max_age' => 86400, // 24 hours
```

### 4. Limit Exposed Headers

Only expose headers that the frontend needs:

```php
'exposed_headers' => [
    'X-RateLimit-Limit',
    'X-RateLimit-Remaining',
],
```

### 5. Restrict Allowed Headers

Specify only the headers your API accepts:

```php
'allowed_headers' => [
    'Accept',
    'Authorization',
    'Content-Type',
    'X-Requested-With',
    'X-CSRF-TOKEN',
    'X-XSRF-TOKEN',
],
```

## Environment-Specific Configuration

### Development (.env.local)

```env
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173,http://localhost:8000
```

### Staging (.env.staging)

```env
CORS_ALLOWED_ORIGINS=https://staging.casprivatecare.com,https://js.stripe.com
```

### Production (.env.production)

```env
CORS_ALLOWED_ORIGINS=https://casprivatecare.com,https://www.casprivatecare.com,https://js.stripe.com
```

### Dynamic Configuration

```php
// config/cors.php
'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', ''))),
```

## Stripe Integration Requirements

Stripe requires specific CORS settings for their JavaScript SDK:

1. **js.stripe.com** - Required for Stripe.js to load
2. **m.stripe.network** - Required for Stripe's fraud detection (Radar)
3. **api.stripe.com** - Direct API calls (if used from frontend)

### Content Security Policy Alignment

Ensure your CSP headers allow Stripe:

```
Content-Security-Policy: 
    script-src 'self' https://js.stripe.com;
    frame-src 'self' https://js.stripe.com https://hooks.stripe.com;
    connect-src 'self' https://api.stripe.com;
```

## Troubleshooting

### Common CORS Errors

#### "No 'Access-Control-Allow-Origin' header"

**Cause:** Origin not in allowed list
**Solution:** Add the requesting origin to `allowed_origins`

#### "Method not allowed"

**Cause:** HTTP method not in `allowed_methods`
**Solution:** Add the method or use `*` in development

#### "Credentials not supported"

**Cause:** Mismatch between `credentials: 'include'` and CORS config
**Solution:** Ensure `supports_credentials` is `true`

### Debugging Tips

1. Check browser DevTools Network tab for CORS errors
2. Verify the `Origin` header in the request
3. Check server logs for preflight (OPTIONS) requests
4. Use curl to test CORS headers:

```bash
curl -I -X OPTIONS \
  -H "Origin: https://casprivatecare.com" \
  -H "Access-Control-Request-Method: POST" \
  https://api.casprivatecare.com/api/bookings
```

## Security Considerations

1. **Never use `*` for origins in production**
2. **Always validate the Origin header server-side**
3. **Use HTTPS for all origins**
4. **Regularly audit allowed origins**
5. **Remove unused origins promptly**

## Related Documentation

- [Laravel CORS Documentation](https://laravel.com/docs/10.x/routing#cors)
- [MDN CORS Guide](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
- [Stripe CORS Requirements](https://stripe.com/docs/js/including)

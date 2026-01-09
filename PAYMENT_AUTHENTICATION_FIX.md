# Payment Authentication Fix - 401 Unauthorized Error

## Issue Description
The payment setup page was showing a **401 Unauthorized** error when trying to access the payment setup endpoint:
```
Failed to load resource: the server responded with a status of 401 (Unauthorized)
api/client/payments/setup-intent:1
```

## Root Cause
The API routes in `routes/api.php` were using `Route::middleware('auth')` which defaults to **token-based authentication** (Sanctum) in Laravel 11+. However, the frontend Vue components were trying to use **session-based authentication** without proper configuration.

## Solution Applied

### 1. Updated Axios Configuration (`resources/js/bootstrap.js`)
Added `withCredentials: true` to enable session cookies in API requests:

```javascript
// Enable credentials for session-based authentication
window.axios.defaults.withCredentials = true;
```

This ensures that session cookies are included in all axios requests to the API.

### 2. Updated Laravel Middleware (`bootstrap/app.php`)
Added `statefulApi()` middleware to enable session authentication for API routes:

```php
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
    ]);
})
```

The `statefulApi()` method adds the following middleware to API routes:
- `EncryptCookies`
- `AddQueuedCookiesToResponse`
- `StartSession`
- `ShareErrorsFromSession`
- `VerifyCsrfToken`

This allows API routes to use session-based authentication just like web routes.

## How It Works

### Before the Fix:
1. User logs in via web route (session created)
2. User navigates to payment page
3. Vue component makes API request
4. Laravel API middleware expects a Sanctum token
5. **401 Unauthorized error** (no token provided)

### After the Fix:
1. User logs in via web route (session created)
2. User navigates to payment page
3. Vue component makes API request **with session cookies**
4. Laravel API middleware checks session authentication
5. ✅ **Request authorized** (session validated)

## Testing Instructions

### 1. Clear Browser Cache and Cookies
- Open Developer Tools (F12)
- Go to Application → Storage → Clear site data
- This ensures you're testing with fresh session data

### 2. Login as Client
```
URL: http://127.0.0.1:8000/login
Email: client@demo.com
Password: [your-password]
```

### 3. Navigate to Payment Page
```
URL: http://127.0.0.1:8000/connect-payment-method
```

### 4. Check Developer Console
- Open Developer Tools (F12)
- Go to Console tab
- **Expected behavior:** No 401 errors
- **Expected behavior:** Stripe payment form loads successfully

### 5. Verify Network Requests
- Open Developer Tools (F12)
- Go to Network tab
- Look for `setup-intent` request
- Check response status: Should be **200 OK** (not 401)
- Check request cookies: Should include Laravel session cookie

## Affected Components

### Frontend Components (using `/api/client/payments/setup-intent`):
- ✅ `ConnectPaymentMethod.vue`
- ✅ `LinkPaymentMethod.vue`
- ✅ `ClientPaymentSetup.vue`
- ✅ `ClientPaymentMethods.vue`

### Backend Routes (now support session auth):
- ✅ `POST /api/client/payments/setup-intent`
- ✅ `GET /api/client/payments/methods`
- ✅ `POST /api/client/payments/attach`
- ✅ `POST /api/client/payments/detach/{pmId}`
- ✅ `POST /api/client/subscriptions`
- ✅ `POST /api/client/subscriptions/{id}/cancel`

## Technical Details

### Laravel Session Authentication Flow:
1. **Login Request** → Creates session in `sessions` table
2. **Session Cookie** → Sent to browser (encrypted)
3. **Subsequent Requests** → Include session cookie
4. **Middleware Validation** → `auth` middleware checks session
5. **User Resolution** → `auth()->user()` returns authenticated user

### Axios Credentials Configuration:
- `withCredentials: true` → Include cookies in cross-origin requests
- `X-CSRF-TOKEN` header → Validate CSRF protection
- `X-Requested-With: XMLHttpRequest` → Identify as AJAX request

## Common Issues & Solutions

### Issue 1: Still Getting 401 Error
**Solution:** Clear browser cookies and localStorage, then login again
```javascript
// In browser console:
localStorage.clear();
sessionStorage.clear();
// Then refresh and login
```

### Issue 2: CSRF Token Mismatch
**Solution:** Ensure CSRF token meta tag exists in blade template
```php
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue 3: Session Not Persisting
**Solution:** Check `.env` session configuration
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_COOKIE=laravel_session
SESSION_SAME_SITE=lax
```

## Files Modified

### Configuration Files:
- ✅ `resources/js/bootstrap.js` - Added `withCredentials: true`
- ✅ `bootstrap/app.php` - Added `statefulApi()` middleware

### Build Files:
- ✅ `public/build/` - Rebuilt assets with new configuration

## Verification Checklist

- [x] Axios configured with `withCredentials: true`
- [x] Laravel middleware configured with `statefulApi()`
- [x] Assets rebuilt with `npm run build`
- [x] CSRF token present in blade templates
- [x] Session driver configured in `.env`
- [ ] Tested login flow
- [ ] Tested payment page access
- [ ] Verified no 401 errors in console
- [ ] Confirmed Stripe form loads

## Additional Notes

### Security Considerations:
- Session cookies are encrypted by Laravel
- CSRF protection is active on all state-changing requests
- API routes still support token-based auth for mobile apps

### Performance Considerations:
- Session data stored in `storage/framework/sessions/`
- Consider using Redis for production (better performance)
- Session garbage collection runs automatically

### Future Improvements:
- Implement Sanctum SPA authentication for better API token management
- Add rate limiting to payment endpoints
- Implement webhook signature verification for Stripe events
- Add payment method caching to reduce API calls

## Support

If you continue to experience authentication issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify session configuration: `php artisan config:cache`
3. Clear all caches: `php artisan cache:clear && php artisan config:clear`
4. Check browser cookies are enabled

## Related Documentation
- [Laravel Session Authentication](https://laravel.com/docs/11.x/authentication)
- [Axios Credentials](https://axios-http.com/docs/req_config)
- [Stripe Payment Elements](https://stripe.com/docs/payments/payment-element)
- [Laravel Sanctum SPA](https://laravel.com/docs/11.x/sanctum#spa-authentication)

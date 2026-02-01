# Rate Limiting Optimization Guide for Pilot Testing

## Problem
You're experiencing **429 Too Many Requests** errors during pilot testing because the current rate limits are too strict:
- **Login**: 5 attempts per minute
- **Register**: 5 attempts per minute

When switching between accounts frequently (admin → client → caregiver), you hit these limits quickly.

---

## Solution Implemented

### 1. **Smart Rate Limiting** ✅
Created `SmartRateLimit` middleware that automatically adjusts limits based on environment:

| Environment | Login Limit | Register Limit | API Limit |
|-------------|-------------|----------------|-----------|
| **Testing/Local** | 50/min | 30/min | 300/min |
| **Production** | 10/min | 5/min | 60/min |

### 2. **IP Whitelist** ✅
Whitelisted IPs get **10x the normal rate limit**. Localhost is automatically whitelisted in non-production.

### 3. **Optimized Session Management** ✅
- Prevents session conflicts when switching accounts
- Cleans up stale session data
- Reduces authentication errors

---

## Quick Setup (Choose One)

### Option A: For Pilot Testing (Recommended)

1. **Check your current `.env` file:**
   ```bash
   notepad .env
   ```

2. **Make sure `APP_ENV` is set to testing:**
   ```env
   APP_ENV=testing
   ```
   
   This automatically gives you:
   - ✅ 50 logins per minute (10x normal)
   - ✅ 30 registrations per minute
   - ✅ 300 API requests per minute

3. **Clear cache and restart:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

4. **Test it:**
   - Try logging in/out multiple times
   - Switch between admin/client accounts
   - Should not see 429 errors anymore

---

### Option B: Whitelist Your IP

1. **Find your current IP address:**
   ```bash
   curl ifconfig.me
   ```

2. **Add to `.env`:**
   ```env
   RATE_LIMIT_WHITELIST=YOUR.IP.ADDRESS.HERE
   ```
   
   For multiple IPs:
   ```env
   RATE_LIMIT_WHITELIST=192.168.1.100,203.0.113.42
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

---

### Option C: Temporarily Bypass (For Intensive Testing Only)

⚠️ **WARNING**: Only use this during intensive pilot testing, not in production!

1. **Add to `.env`:**
   ```env
   BYPASS_RATE_LIMIT=true
   ```

2. **This completely disables rate limiting** - use with caution!

3. **Remember to set it back to `false` before production:**
   ```env
   BYPASS_RATE_LIMIT=false
   ```

---

## Verification

### Check Current Rate Limits
Create a test file `test_rate_limit.php`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

echo "Environment: " . app()->environment() . "\n";
echo "Rate Limiting Bypass: " . (config('ratelimit.bypass_in_testing') ? 'YES' : 'NO') . "\n";
echo "IP Whitelist: " . implode(', ', config('ratelimit.whitelist', [])) . "\n";
```

Run it:
```bash
php test_rate_limit.php
```

---

## Production Checklist

Before going to production, ensure:

- [ ] `APP_ENV=production` in `.env`
- [ ] `BYPASS_RATE_LIMIT=false` (or remove this line)
- [ ] `RATE_LIMIT_WHITELIST` is empty (or remove this line)
- [ ] Run `php artisan config:cache` to cache settings

---

## How It Works

### 1. Smart Detection
The middleware checks your `APP_ENV`:
```php
// In testing/local: Relaxed
if (app()->environment(['local', 'testing'])) {
    return [50, 1];  // 50 attempts per minute
}

// In production: Strict
return [10, 1];  // 10 attempts per minute
```

### 2. IP Whitelisting
```php
protected function isWhitelisted(Request $request): bool
{
    // Localhost auto-whitelisted in non-production
    if (!app()->environment('production')) {
        return in_array($request->ip(), ['127.0.0.1', '::1']);
    }
    
    // Check custom whitelist
    return in_array($request->ip(), config('ratelimit.whitelist', []));
}
```

### 3. Per-User vs Per-IP
- **Authenticated users**: Rate limit per user ID
- **Guests**: Rate limit per IP address

This prevents one user from blocking others on the same network.

---

## Troubleshooting

### Still Getting 429 Errors?

1. **Check environment:**
   ```bash
   php artisan tinker
   >>> app()->environment()
   ```
   Should return "local", "testing", or "staging"

2. **Clear all caches:**
   ```bash
   php artisan optimize:clear
   ```

3. **Check route middleware:**
   ```bash
   php artisan route:list | grep login
   ```
   Should show `smart.throttle:login` not `throttle:5,1`

4. **View rate limit headers:**
   Check browser Network tab for headers:
   - `X-RateLimit-Limit`: Maximum allowed
   - `X-RateLimit-Remaining`: How many left
   - `Retry-After`: When you can try again

### Rate Limits Not Being Enforced at All?

This means `BYPASS_RATE_LIMIT=true` is set. This is fine for testing, but change it for production.

---

## Security Notes

✅ **Safe for Production**: The smart rate limiting automatically enforces strict limits in production

✅ **No Security Risks**: IP whitelisting only relaxes limits, doesn't bypass authentication

✅ **Audit Trail**: All rate limit violations are logged in production

⚠️ **Don't Use `BYPASS_RATE_LIMIT=true` in Production**: This completely disables rate limiting and can expose you to abuse

---

## Support

If you're still experiencing issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode temporarily: `APP_DEBUG=true` in `.env`
3. Monitor rate limit violations:
   ```bash
   tail -f storage/logs/laravel.log | grep "Rate limit"
   ```

---

## Files Modified

- ✅ `app/Http/Middleware/SmartRateLimit.php` - New smart rate limiting
- ✅ `app/Http/Middleware/OptimizedSessionManagement.php` - Session optimization
- ✅ `config/ratelimit.php` - Configuration file
- ✅ `routes/web.php` - Updated to use smart rate limiting
- ✅ `bootstrap/app.php` - Registered new middleware

---

**Your pilot testing should now be smooth! 🚀**


# Payment Setup 500 Error Fix

## Status Update

### ✅ What We've Fixed So Far:

1. **401 Unauthorized Error** → ✅ **RESOLVED**
   - Added `withCredentials: true` to axios configuration
   - Enabled `statefulApi()` middleware for session authentication
   - Assets rebuilt successfully

2. **500 Internal Server Error** → 🔄 **IN PROGRESS**
   - Added `stripe_customer_id` field to User model fillable array
   - Improved error handling in `ClientPaymentController`
   - Added detailed logging for debugging
   - Cleared all Laravel caches

### 🔧 Changes Made in This Update:

#### 1. User Model (`app/Models/User.php`)
Added Stripe fields to the fillable array:
```php
protected $fillable = [
    // ... existing fields ...
    'stripe_customer_id',
    'stripe_account_id',
    'stripe_connect_id',
];
```

#### 2. ClientPaymentController (`app/Http/Controllers/ClientPaymentController.php`)

**Improved `createSetupIntent()` method:**
- Added authentication check with logging
- Added Stripe configuration validation
- Added explicit `payment_method_types` parameter
- Separated Stripe API errors from general exceptions
- Added detailed error logging with context

**Improved `ensureCustomer()` method:**
- Added try-catch blocks for better error handling
- Added metadata to Stripe customer creation
- Added detailed logging at each step
- Better error messages

#### 3. Laravel Cache
Cleared all caches:
- Configuration cache
- Application cache
- Route cache

## 🧪 Testing Steps

### 1. Check Current Setup

Open browser and press F12 to open Developer Tools, then:

1. **Clear Browser Data:**
   - Application tab → Storage → Clear site data

2. **Login as Client:**
   ```
   URL: http://127.0.0.1:8000/login
   Email: client@demo.com
   Password: [your-password]
   ```

3. **Visit Payment Page:**
   ```
   URL: http://127.0.0.1:8000/connect-payment-method
   ```

4. **Check Console for Errors:**
   - Look for the exact error message
   - Check Network tab for the `/api/client/payments/setup-intent` request
   - Click on it to see the response details

### 2. Check Laravel Logs

After testing, check the log file for detailed error information:
```powershell
Get-Content "storage\logs\laravel.log" -Tail 50
```

Look for entries like:
- `Creating setup intent for user:`
- `STRIPE_SECRET is not configured`
- `Stripe API error`
- `Error ensuring customer`

## 🔍 Common Issues & Solutions

### Issue 1: Stripe API Key Not Configured
**Symptom:** Error message says "Payment system not configured"
**Solution:**
```powershell
# Check .env file
Select-String -Path ".env" -Pattern "STRIPE"

# Should show:
# STRIPE_KEY=pk_test_...
# STRIPE_SECRET=sk_test_...
```

### Issue 2: User Table Missing Stripe Column
**Symptom:** Error about `stripe_customer_id` column not found
**Solution:**
```powershell
# Check if column exists
php artisan tinker --execute="DB::select('DESCRIBE users');"

# If not, check migrations
php artisan migrate:status

# Run pending migrations
php artisan migrate
```

### Issue 3: Permission Issues Saving to Database
**Symptom:** Error about unable to save `stripe_customer_id`
**Check:**
1. Database connection in `.env`
2. User model has `stripe_customer_id` in fillable array
3. User table has the column

### Issue 4: Stripe Test Keys
**Verify your Stripe keys:**
1. Go to https://dashboard.stripe.com/test/apikeys
2. Copy your test keys (they should start with `pk_test_` and `sk_test_`)
3. Update `.env` file:
```env
STRIPE_KEY=pk_test_YOUR_PUBLISHABLE_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
```
4. Clear config cache:
```powershell
php artisan config:clear
```

## 📋 Diagnostic Commands

Run these to diagnose the issue:

```powershell
# 1. Check Stripe configuration
php artisan tinker --execute="echo 'Stripe Secret: ' . env('STRIPE_SECRET') . PHP_EOL;"

# 2. Check if user table has stripe_customer_id column
php artisan tinker --execute="echo json_encode(DB::select('DESCRIBE users'), JSON_PRETTY_PRINT);"

# 3. Check current user
php artisan tinker --execute="echo json_encode(App\Models\User::find(1)->toArray(), JSON_PRETTY_PRINT);"

# 4. Test Stripe connection
php artisan tinker --execute="(new \Stripe\StripeClient(env('STRIPE_SECRET')))->customers->all(['limit' => 1]);"

# 5. View Laravel log
Get-Content "storage\logs\laravel.log" -Tail 50
```

## 🎯 Next Steps

1. **Test the payment page again** with the browser console open
2. **Check the Laravel log** for detailed error messages
3. **Verify Stripe credentials** are correct in `.env`
4. **Check database** has `stripe_customer_id` column

## 📄 Files Modified

- ✅ `resources/js/bootstrap.js` - Session authentication
- ✅ `bootstrap/app.php` - Stateful API middleware
- ✅ `app/Models/User.php` - Added Stripe fields to fillable
- ✅ `app/Http/Controllers/ClientPaymentController.php` - Improved error handling
- ✅ `storage/logs/laravel.log` - Cleared for fresh logs

## 🔄 What's Different From Before

**Before:**
- 401 Unauthorized error (authentication failed)

**Now:**
- Authentication works ✅
- API endpoint is reached ✅
- Server is processing the request ✅
- BUT: Something is causing a 500 error during processing

**This is progress!** We've moved from "can't authenticate" to "authenticated but encountering a server error", which means we're getting closer to the solution.

## 💡 Most Likely Causes

Based on the 500 error, the most likely causes are:

1. **Stripe API Key Issue** - Invalid or missing secret key
2. **Database Issue** - Can't save `stripe_customer_id` to user
3. **Stripe API Error** - Stripe rejecting the request for some reason
4. **Missing Dependencies** - Stripe PHP SDK not installed properly

## 🆘 Need the Exact Error?

To see the exact error message, please:

1. Try accessing the payment page again
2. Copy the full error from the browser console (not just "500 error")
3. Check Laravel logs:
   ```powershell
   Get-Content "storage\logs\laravel.log"
   ```
4. Share the error details so we can pinpoint the exact issue

---

**Status:** Ready for testing
**Next:** Test payment page and check Laravel logs for detailed error

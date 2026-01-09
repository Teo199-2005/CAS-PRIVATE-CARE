# Check Authentication Status

## ⚠️ 401 Unauthorized Error - What This Means

The error `401 Unauthorized` means:
- ✅ The page is loading correctly
- ✅ The API endpoint exists and is working
- ❌ **You are NOT logged in as a client**

---

## 🔍 Quick Diagnosis

### Are you logged in?
1. Open a new tab and go to: http://127.0.0.1:8000/client-dashboard
2. **If you see the dashboard:** You ARE logged in ✅
3. **If redirected to login page:** You are NOT logged in ❌

### Are you logged in as the right user type?
Check your user type in the database:
```sql
SELECT id, name, email, user_type FROM users WHERE id = YOUR_ID;
```

The `user_type` must be `'client'` (not `'caregiver'` or `'admin'`)

---

## 🔐 Solution: Login as Client

### Step 1: Go to Login Page
```
http://127.0.0.1:8000/login
```

### Step 2: Login with Client Credentials
Use a test client account:
- **Email:** Your client email
- **Password:** Your client password

### Step 3: Verify Login Works
After logging in, you should see:
```
http://127.0.0.1:8000/client-dashboard
```

### Step 4: Access Payment Page
Now try the payment page:
```
http://127.0.0.1:8000/connect-payment-method
```

**OR** from dashboard:
1. Click "Payment Information" tab
2. Click "Link Bank Account or Card" button

---

## 🆘 Don't Have a Client Account?

### Option 1: Register New Client
```
http://127.0.0.1:8000/register
```

### Option 2: Create Via Database
If you have database access:

```sql
INSERT INTO users (name, email, password, user_type, email_verified_at, created_at, updated_at)
VALUES (
    'Test Client',
    'testclient@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5aeGU.C0eVmW2', -- password: password
    'client',
    NOW(),
    NOW(),
    NOW()
);
```

Then login with:
- Email: `testclient@example.com`
- Password: `password`

---

## 🔧 Technical Details

### Why API Shows 401
The API routes have `auth` middleware:
```php
Route::middleware('auth')->group(function () {
    Route::post('/client/payments/setup-intent', ...);
});
```

This means:
- ✅ **Logged in:** API returns data
- ❌ **Not logged in:** API returns 401 Unauthorized

### How Authentication Works
1. When you login, Laravel creates a session
2. Session cookie is stored in browser
3. API requests include the session cookie
4. Laravel validates the session
5. If valid: request proceeds
6. If invalid/missing: 401 error

### Check Session Cookie
In browser console (F12):
```javascript
document.cookie
```

Look for:
```
laravel_session=eyJpdiI6...
```

If missing → not logged in!

---

## 🎯 Quick Test Commands

### Test 1: Check if logged in
Open browser console (F12) and run:
```javascript
fetch('/api/profile')
  .then(r => r.json())
  .then(data => console.log('Logged in as:', data))
  .catch(err => console.log('Not logged in:', err));
```

**Result:**
- ✅ Shows user data → Logged in
- ❌ 401 error → Not logged in

### Test 2: Check user type
```javascript
fetch('/api/profile')
  .then(r => r.json())
  .then(data => console.log('User type:', data.user_type));
```

**Must show:** `user_type: "client"`

---

## ✅ Success Checklist

Before accessing payment page:
- [ ] Logged in successfully
- [ ] Can access `/client-dashboard`
- [ ] User type is 'client'
- [ ] Session cookie exists
- [ ] `/api/profile` returns user data (no 401)

Once all checked:
- [ ] Navigate to `/connect-payment-method`
- [ ] Stripe form loads (no errors)
- [ ] Can add test card
- [ ] Can save payment method

---

## 🐛 Still Getting 401?

### Check Laravel Logs
```powershell
Get-Content 'storage\logs\laravel.log' -Tail 30
```

Look for authentication errors.

### Check Session Configuration
In `.env`:
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
```

### Clear All Caches
```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Then restart Laravel server:
```powershell
php artisan serve
```

### Check Sanctum (if using SPA)
If using Sanctum for API auth, ensure:
```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

---

## 📞 Need More Help?

### View Full Error in Console
In browser console (F12):
```javascript
axios.post('/api/client/payments/setup-intent')
  .then(r => console.log('Success:', r.data))
  .catch(err => {
    console.log('Error:', err);
    console.log('Status:', err.response?.status);
    console.log('Message:', err.response?.data);
  });
```

### Check Auth Middleware
Create test route in `routes/web.php`:
```php
Route::get('/test-auth', function () {
    if (auth()->check()) {
        return response()->json([
            'authenticated' => true,
            'user' => auth()->user(),
            'user_type' => auth()->user()->user_type
        ]);
    }
    return response()->json(['authenticated' => false]);
});
```

Then visit: http://127.0.0.1:8000/test-auth

---

**Last Updated:** January 9, 2026
**Error Code:** 401 Unauthorized
**Root Cause:** Not logged in as client user
**Solution:** Login at `/login` before accessing payment page

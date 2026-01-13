# Password Reset Email Fix - RESOLVED ✅

## 🐛 Issue
Password reset emails were not being sent on production server (`https://casprivatecare.online/login`), while other emails (welcome, verification, OTP) worked fine.

**Error Message:**
```
Permission denied
Failed to send password reset email
File does not exist at path storage/framework/views/115feb562843c175f7bd78f9a9f6e06e.php
```

---

## 🔍 Root Cause
**File permission issues** on the production server prevented Laravel from:
1. Compiling the password reset Blade template
2. Writing the compiled view file to `storage/framework/views/`
3. Logging errors to `storage/logs/laravel.log`

The password reset email template (`resources/views/emails/password-reset.blade.php`) had never been compiled on the production server, so when a user requested a password reset, Laravel couldn't create the compiled view file due to insufficient permissions.

**Why other emails worked:** Welcome, verification, and OTP email templates were already compiled during user registration, so they didn't encounter permission errors.

---

## ✅ Solution Applied

### Fix Command (Production Server)
```bash
# SSH into production server
ssh ubuntu@15.204.248.209

# Navigate to Laravel app
cd /var/www/casprivatecare

# Fix permissions for storage and bootstrap/cache
sudo chmod -R 777 /var/www/casprivatecare/storage
sudo chmod -R 777 /var/www/casprivatecare/bootstrap/cache

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 🧪 Testing

### Test in Tinker (Production)
```bash
php artisan tinker

# Get a user
$user = \App\Models\User::where('email', 'test@example.com')->first();

# Generate token
$token = \Illuminate\Support\Str::random(64);

# Send password reset email
\App\Services\EmailService::sendPasswordResetEmail($user, $token);

# Should return: = null (success)
exit
```

### Test via Browser
1. Go to: `https://casprivatecare.online/login`
2. Click **"Forgot Password?"**
3. Enter email address
4. Submit
5. Check email inbox (including spam folder)
6. Email should arrive within 1-2 minutes

---

## 📧 Email Configuration (Brevo SMTP)

**Production .env settings:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=9ede11001@smtp-brevo.com
MAIL_PASSWORD=[REDACTED - use your Brevo SMTP password in .env]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=casprivatecare@casprivatecare.com
MAIL_FROM_NAME="CAS Private Care"
```

**Brevo Dashboard:** https://app.brevo.com/
- Free tier: 300 emails/day
- SMTP credentials location: Settings → SMTP & API

---

## 🔧 Technical Details

### Files Involved

1. **AuthController** (`app/Http/Controllers/AuthController.php`)
   - Method: `sendResetLinkEmail()` (line 197)
   - Validates email, generates token, calls EmailService

2. **EmailService** (`app/Services/EmailService.php`)
   - Method: `sendPasswordResetEmail()` (line 51)
   - Sends email via Mail facade

3. **PasswordResetEmail Mailable** (`app/Mail/PasswordResetEmail.php`)
   - Builds email with reset URL
   - Uses Blade template: `resources/views/emails/password-reset.blade.php`

4. **Email Template** (`resources/views/emails/password-reset.blade.php`)
   - Extends: `emails.layout`
   - Contains reset link button and instructions

### Password Reset Flow

1. User enters email on forgot password page
2. `AuthController::sendResetLinkEmail()` validates email
3. Token generated and stored in `password_resets` table
4. `EmailService::sendPasswordResetEmail()` called
5. `PasswordResetEmail` mailable compiles Blade template
6. Email sent via Brevo SMTP
7. User receives email with reset link: `https://casprivatecare.online/reset-password/{token}?email={email}`

---

## 🚨 Important Notes

### Permission Settings
The fix uses `chmod 777` for **immediate resolution**. For production best practices, use:

```bash
# Better permissions (recommended for production)
sudo chown -R www-data:www-data /var/www/casprivatecare/storage
sudo chown -R www-data:www-data /var/www/casprivatecare/bootstrap/cache
sudo chmod -R 775 /var/www/casprivatecare/storage
sudo chmod -R 775 /var/www/casprivatecare/bootstrap/cache
```

### Email Delivery
- **Brevo sender verification:** `casprivatecare@casprivatecare.com` must be verified in Brevo dashboard
- **Spam folder:** Password reset emails often land in spam - advise users to check there
- **Link expiration:** Reset links expire after 60 minutes (configurable)

### Logging
Check Laravel logs for email issues:
```bash
tail -100 storage/logs/laravel.log | grep -i "password\|email\|mail"
```

Successful log entry:
```
[2026-01-11 XX:XX:XX] local.INFO: Password reset email sent to user@example.com
```

---

## ✅ Verification Checklist

- [x] Storage directory permissions fixed (777 or 775 with www-data owner)
- [x] Bootstrap/cache directory permissions fixed
- [x] Laravel caches cleared
- [x] Blade template compiled successfully
- [x] Test email sent via tinker (returned `null`)
- [x] Password reset email received in inbox
- [x] Reset link works and points to correct domain
- [x] Brevo SMTP configuration correct
- [x] No permission errors in Laravel logs

---

## 🎯 Success Criteria

✅ **Password reset emails now working on production**
- User can request password reset
- Email arrives within 1-2 minutes
- Reset link is valid and functional
- No errors in Laravel logs
- All emails (welcome, verification, OTP, password reset) working

---

## 📝 Related Issues Fixed

1. **Receipt Processing Fee Display** - Fixed in previous session
   - File: `app/Http/Controllers/ReceiptController.php`
   - Commit: `7a4a2f7`

2. **Production Email Configuration** - Resolved
   - Brevo SMTP configured and working
   - All email types sending successfully

---

## 🚀 Future Improvements

1. **Automated Deployment:** Include permission fixes in deployment script
2. **Monitoring:** Set up email delivery monitoring/alerts
3. **Custom Domain Email:** Consider setting up email from `@casprivatecare.online` instead of `@casprivatecare.com`
4. **Queue Jobs:** Move email sending to queue for better performance
5. **Email Templates:** Consider using Brevo's template builder for easier updates

---

**Issue Resolved:** January 11, 2026  
**Environment:** Ubuntu 24.04.3 LTS (Production)  
**Server:** 15.204.248.209  
**Domain:** https://casprivatecare.online  
**Status:** ✅ WORKING

---

© 2026 CAS Private Care LLC

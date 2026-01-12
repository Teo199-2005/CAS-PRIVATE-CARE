# Production Email Setup Guide - casprivatecare.online

## 🐛 Issue
Password reset emails work on `http://127.0.0.1:8000` (local) but NOT on `https://casprivatecare.online` (production).

---

## 🔍 Step 1: Diagnose the Issue

### SSH into your server:
```bash
ssh ubuntu@15.204.248.209
# Enter your password
```

### Navigate to your Laravel application:
```bash
cd /var/www/casprivatecare.online
# Or wherever your app is located
```

### Check your current email configuration:
```bash
cat .env | grep MAIL
```

**Expected output should look like:**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@casprivatecare.online
MAIL_FROM_NAME="CAS Private Care"
```

### Check Laravel logs for errors:
```bash
tail -100 storage/logs/laravel.log
```

Look for errors like:
- `Connection refused`
- `SMTP connection failed`
- `Authentication failed`
- `Could not connect to host`

---

## 🚨 Common Issues & Solutions

### Issue 1: `.env` file not configured on production

**Symptoms:** No MAIL_* variables in .env or using default values

**Solution:**
```bash
# Edit the .env file on the server
nano .env

# Add/Update these lines (use your Brevo credentials):
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-brevo-smtp-key-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@casprivatecare.online
MAIL_FROM_NAME="CAS Private Care"

# Save: Ctrl+O, Enter, Ctrl+X

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

---

### Issue 2: Brevo SMTP credentials not set or incorrect

**Symptoms:** `Authentication failed` in logs

**Solution:**

1. **Get your Brevo SMTP credentials:**
   - Go to: https://app.brevo.com/settings/keys/smtp
   - Copy your **SMTP Server**, **Login**, and **Password**

2. **Update .env on server:**
   ```bash
   nano .env
   
   # Update with YOUR Brevo credentials:
   MAIL_HOST=smtp-relay.brevo.com
   MAIL_PORT=587
   MAIL_USERNAME=your-actual-brevo-email@domain.com
   MAIL_PASSWORD=your-actual-brevo-smtp-key
   
   # Save and exit
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

### Issue 3: Firewall blocking outbound SMTP (Port 587)

**Symptoms:** `Connection refused` or `Connection timeout`

**Solution:**

Check if port 587 is open:
```bash
telnet smtp-relay.brevo.com 587
# or
nc -zv smtp-relay.brevo.com 587
```

If connection fails, open the port:
```bash
# Check firewall status
sudo ufw status

# Allow outbound SMTP
sudo ufw allow out 587/tcp
sudo ufw allow out 25/tcp

# Reload firewall
sudo ufw reload
```

---

### Issue 4: Wrong APP_URL in production

**Symptoms:** Email links point to localhost or wrong domain

**Solution:**
```bash
nano .env

# Make sure APP_URL is set correctly:
APP_URL=https://casprivatecare.online

# NOT http://127.0.0.1:8000

# Save and clear cache
php artisan config:clear
```

---

### Issue 5: Queue worker not running

**Symptoms:** Emails not sent but no errors in logs

**Check if queue worker is running:**
```bash
ps aux | grep queue
```

**Start/Restart queue worker:**
```bash
# Kill existing workers
pkill -f "artisan queue:work"

# Start new worker
nohup php artisan queue:work --daemon &

# Or if using supervisor:
sudo supervisorctl restart laravel-worker:*
```

---

## ✅ Step 2: Test Email on Production

### Method 1: Using Artisan Tinker
```bash
php artisan tinker

# In tinker, run:
Mail::raw('This is a test email from production', function($msg) {
    $msg->to('your-email@example.com')
        ->subject('Production Email Test');
});

# Exit tinker
exit
```

**Check your email inbox** (including spam folder)

---

### Method 2: Using Password Reset
1. Go to: `https://casprivatecare.online/login`
2. Click "Forgot Password?"
3. Enter your email
4. Check if email arrives

---

### Method 3: Create a test route (temporary)
```bash
nano routes/web.php

# Add at the end (before the closing brace):
Route::get('/test-email', function() {
    try {
        Mail::raw('Test email from casprivatecare.online', function($msg) {
            $msg->to('your-email@example.com')
                ->subject('Production Email Test');
        });
        return 'Email sent! Check your inbox.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

# Save and visit: https://casprivatecare.online/test-email
```

**Remove this route after testing!**

---

## 🔧 Step 3: Set Up Brevo (Sendinblue) on Production

### If you haven't set up Brevo yet:

1. **Sign up for Brevo:**
   - Go to: https://www.brevo.com/
   - Create a free account (300 emails/day free)

2. **Get SMTP credentials:**
   - Login to Brevo dashboard
   - Go to: **Settings** → **SMTP & API**
   - Click **"SMTP"** tab
   - Copy:
     - SMTP Server: `smtp-relay.brevo.com`
     - Login: Your email address
     - Password: Click "Generate" to get SMTP key

3. **Add to production .env:**
   ```bash
   ssh ubuntu@15.204.248.209
   cd /var/www/casprivatecare.online
   nano .env
   
   # Paste your Brevo credentials:
   MAIL_MAILER=smtp
   MAIL_HOST=smtp-relay.brevo.com
   MAIL_PORT=587
   MAIL_USERNAME=your-brevo-email@example.com
   MAIL_PASSWORD=your-brevo-smtp-key
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@casprivatecare.online
   MAIL_FROM_NAME="CAS Private Care"
   
   # Save: Ctrl+O, Enter, Ctrl+X
   ```

4. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

5. **Test email:**
   ```bash
   php artisan tinker
   Mail::raw('Test', function($msg) { $msg->to('your@email.com')->subject('Test'); });
   exit
   ```

---

## 📋 Quick Commands Reference

### On Production Server (SSH):

```bash
# 1. SSH into server
ssh ubuntu@15.204.248.209

# 2. Navigate to app
cd /var/www/casprivatecare.online

# 3. Check email config
cat .env | grep MAIL

# 4. Edit .env
nano .env

# 5. Clear cache after changes
php artisan config:clear && php artisan cache:clear

# 6. Check logs
tail -50 storage/logs/laravel.log

# 7. Test email
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('test@email.com')->subject('Test'); });
exit

# 8. Restart queue worker (if using queues)
sudo supervisorctl restart laravel-worker:*
# or
pkill -f "artisan queue:work" && nohup php artisan queue:work --daemon &

# 9. Exit SSH
exit
```

---

## 🔍 Debugging Checklist

- [ ] SSH into production server successfully
- [ ] Navigate to Laravel app directory
- [ ] `.env` file exists on production
- [ ] `MAIL_*` variables are set in `.env`
- [ ] Brevo credentials are correct
- [ ] `APP_URL` is set to `https://casprivatecare.online`
- [ ] Config cache cleared with `php artisan config:clear`
- [ ] No errors in `storage/logs/laravel.log`
- [ ] Port 587 is open (outbound)
- [ ] Test email sent successfully
- [ ] Queue worker running (if using queues)

---

## 🆘 Still Not Working?

### Check Application Logs:
```bash
tail -100 storage/logs/laravel.log | grep -i mail
tail -100 storage/logs/laravel.log | grep -i error
```

### Check Web Server Error Logs:
```bash
# Nginx
sudo tail -50 /var/log/nginx/error.log

# Apache
sudo tail -50 /var/log/apache2/error.log
```

### Verify Brevo Account Status:
- Login to Brevo dashboard
- Check if account is active
- Check sending limits (free: 300 emails/day)
- Verify sender email is validated

### Test SMTP Connection Manually:
```bash
telnet smtp-relay.brevo.com 587

# Should show:
# Trying 185.107.232.1...
# Connected to smtp-relay.brevo.com.
# 220 smtp-relay.brevo.com
```

### Enable Debug Mode (temporarily):
```bash
nano .env

# Add/Update:
APP_DEBUG=true
LOG_LEVEL=debug

# Clear cache
php artisan config:clear

# Test again, then check logs
tail -100 storage/logs/laravel.log

# REMEMBER: Set APP_DEBUG=false after debugging!
```

---

## 🎯 Expected Working Configuration

### Production .env (casprivatecare.online):
```env
APP_NAME="CAS Private Care"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://casprivatecare.online

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@casprivatecare.online
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_KEY=pk_test_your_key_here
STRIPE_SECRET=sk_test_your_secret_here
```

---

## ✅ Success Verification

1. ✅ Password reset email received in inbox
2. ✅ Email shows sender as "CAS Private Care <noreply@casprivatecare.online>"
3. ✅ Reset link works and points to `https://casprivatecare.online`
4. ✅ No errors in Laravel logs
5. ✅ Brevo dashboard shows email sent successfully

---

## 📝 Notes

- **Local works, production doesn't:** This confirms the code is correct, it's a server configuration issue
- **Most common cause:** Missing or incorrect `.env` configuration on production
- **Brevo free tier:** 300 emails/day (sufficient for password resets)
- **Email delivery time:** Usually instant, check spam folder if delayed

---

## 🚀 After Fixing

1. Test password reset on production
2. Verify email arrives within 1-2 minutes
3. Check email content and links work correctly
4. Set `APP_DEBUG=false` in production `.env`
5. Monitor Brevo dashboard for any issues
6. Consider setting up email notifications for other features

---

**Last Updated:** January 11, 2026  
**Environment:** Ubuntu 24.04 on VPS (15.204.248.209)  
**Domain:** casprivatecare.online  
**Email Service:** Brevo (Sendinblue)

---

© 2026 CAS Private Care LLC

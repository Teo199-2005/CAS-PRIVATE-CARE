# Fix Google OAuth Redirect URI Mismatch Error

## Problem
```
Error 400: redirect_uri_mismatch
```

Google OAuth is configured for a different URL than what your app is using.

---

## 🔍 Quick Diagnosis

Your app is trying to redirect to: **`https://casprivatecare.online/login`**

But Google OAuth Console has a different URL configured.

---

## ✅ Solution - Update Google Cloud Console

### Step 1: Go to Google Cloud Console
1. Visit: https://console.cloud.google.com/
2. Select your project (CAS Private Care or similar)
3. Go to **APIs & Services** → **Credentials**

### Step 2: Find Your OAuth 2.0 Client ID
1. Look for "OAuth 2.0 Client IDs" section
2. Click on your Web application client (e.g., "Web client 1")

### Step 3: Update Authorized Redirect URIs

Add BOTH of these URIs:

```
https://casprivatecare.online/auth/google/callback
https://casprivatecare.com/auth/google/callback
```

**Also add these for completeness:**
```
https://www.casprivatecare.online/auth/google/callback
https://www.casprivatecare.com/auth/google/callback
http://localhost:8000/auth/google/callback
http://127.0.0.1:8000/auth/google/callback
```

### Step 4: Save Changes
Click **SAVE** at the bottom

---

## 🔧 Check Your Laravel Configuration

### 1. Check .env file on server:

```bash
# SSH into your server
ssh ubuntu@your-server-ip

# Check Google OAuth settings
cd /var/www/casprivatecare
cat .env | grep GOOGLE
```

### Expected values:
```env
GOOGLE_CLIENT_ID=your_client_id_here.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URL=https://casprivatecare.online/auth/google/callback
```

### 2. If GOOGLE_REDIRECT_URL is wrong, update it:

```bash
# Edit .env file
nano .env

# Find the line:
GOOGLE_REDIRECT_URL=

# Change it to:
GOOGLE_REDIRECT_URL=https://casprivatecare.online/auth/google/callback

# Save: Ctrl+X, then Y, then Enter
```

### 3. Clear config cache:

```bash
php artisan config:clear
php artisan config:cache
sudo systemctl restart apache2
```

---

## 📋 Complete Google OAuth Setup Checklist

### In Google Cloud Console:

**Authorized JavaScript origins:**
```
https://casprivatecare.online
https://casprivatecare.com
https://www.casprivatecare.online
https://www.casprivatecare.com
```

**Authorized redirect URIs:**
```
https://casprivatecare.online/auth/google/callback
https://casprivatecare.com/auth/google/callback
https://www.casprivatecare.online/auth/google/callback
https://www.casprivatecare.com/auth/google/callback
http://localhost:8000/auth/google/callback (for development)
```

### In Laravel (.env file):

```env
# Google OAuth Settings
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_secret_key
GOOGLE_REDIRECT_URL=https://casprivatecare.online/auth/google/callback
```

---

## 🎯 Quick Fix Summary

1. **Go to:** https://console.cloud.google.com/apis/credentials
2. **Click:** Your OAuth 2.0 Client ID
3. **Add to "Authorized redirect URIs":**
   - `https://casprivatecare.online/auth/google/callback`
4. **Click:** Save
5. **Wait:** 5-10 minutes for Google to propagate changes
6. **Try again:** Sign in with Google

---

## 🔍 How to Find Your Current Redirect URI

### Check what URI your app is sending:

Look at the error URL in your browser when the error happens. It will show:
```
redirect_uri=https://casprivatecare.online/auth/google/callback
```

This is the exact URI you need to add to Google Cloud Console.

---

## 🐛 Troubleshooting

### Still getting the error?

**1. Check for typos:**
- Make sure URIs are EXACT matches
- Check for trailing slashes
- Check http vs https

**2. Wait for propagation:**
- Google changes can take 5-10 minutes
- Try in incognito/private window

**3. Check multiple domains:**
- If you have both .com and .online domains
- Add redirect URIs for BOTH

**4. Verify OAuth is enabled:**
```bash
# On your server, check config
php artisan tinker

# Then run:
config('services.google')

# Should show:
// [
//   "client_id" => "your_id.apps.googleusercontent.com",
//   "client_secret" => "your_secret",
//   "redirect" => "https://casprivatecare.online/auth/google/callback"
// ]
```

---

## 📱 If You're Using Different Domains

### Primary domain: casprivatecare.online
### Secondary domain: casprivatecare.com

**Option 1: Support both domains**
- Add both redirect URIs to Google Console
- App will work on both

**Option 2: Force one domain**
- Redirect all traffic to one domain
- Only need one redirect URI

### To force redirect to .online:

Add this to your `.htaccess` or Nginx config:

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{HTTP_HOST} ^casprivatecare\.com$ [NC]
RewriteRule ^(.*)$ https://casprivatecare.online/$1 [R=301,L]
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name casprivatecare.com www.casprivatecare.com;
    return 301 https://casprivatecare.online$request_uri;
}
```

---

## 🎬 Video Guide

If you need visual help, search YouTube for:
"Google OAuth redirect_uri_mismatch fix"

Common solution video: https://www.youtube.com/results?search_query=google+oauth+redirect_uri_mismatch

---

## 📞 Alternative: Use Email/Password Login

While you fix Google OAuth, users can still log in with:
- **Email and password**
- **Regular registration**

Google Sign-In is optional.

---

## ✅ After You Fix It

Test the flow:
1. Go to: https://casprivatecare.online/login
2. Click "Sign in with Google"
3. Select your Google account
4. Should redirect back successfully
5. User should be logged in

---

## 🔐 Security Note

Make sure your `.env` file is NOT in your git repository:
```bash
# Check if .env is ignored
cat .gitignore | grep .env

# Should show:
# .env
# .env.backup
# .env.production
```

Never commit API keys or secrets to GitHub!

---

## Need Help?

If still stuck after following these steps, check:
1. Google Cloud Console → APIs & Services → Credentials
2. Your OAuth 2.0 Client ID settings
3. The "Authorized redirect URIs" section
4. Make sure the URI matches EXACTLY what your app sends

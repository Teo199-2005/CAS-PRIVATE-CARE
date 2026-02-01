# Email Configuration Guide - Fix OTP Email Sending

## Problem
Your application is configured to use `MAIL_MAILER=log`, which means emails are only being written to the Laravel log file instead of actually being sent to users' email addresses.

## Solution: Configure SMTP with Brevo (Free Email Service)

### Step 1: Get Brevo Account (Free Plan Available)

1. Go to https://www.brevo.com/ (formerly Sendinblue)
2. Sign up for a **FREE** account
   - Free plan includes: **300 emails per day**
3. Verify your email address

### Step 2: Get Your SMTP Credentials

1. Log in to your Brevo dashboard
2. Go to **Settings** → **SMTP & API**
3. Click on **SMTP** tab
4. You'll see:
   - **SMTP Server:** `smtp-relay.brevo.com`
   - **Port:** `587`
   - **Login:** Your Brevo account email
   - **Password/API Key:** Click "Create a new SMTP key" or use existing one

### Step 3: Update Your `.env` File

Open your `.env` file and update these lines:

```env
# Change from 'log' to 'smtp'
MAIL_MAILER=smtp

# Brevo SMTP Settings
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com      # Your Brevo login email
MAIL_PASSWORD=your-brevo-smtp-key               # The SMTP key from Step 2
MAIL_ENCRYPTION=tls

# From Address (must be verified in Brevo)
MAIL_FROM_ADDRESS="noreply@casprivatecare.com"  # Or use your Brevo email
MAIL_FROM_NAME="CAS Private Care"
```

### Step 4: Verify Sender Email (Important!)

For production use, you need to verify your sender email in Brevo:

1. Go to **Senders, Domains & Dedicated IPs** → **Senders**
2. Click **Add a sender**
3. Enter the email you want to use (e.g., `noreply@casprivatecare.com`)
4. Brevo will send you a verification email
5. Click the verification link

**Tip:** For testing, you can use your Brevo account email as the sender address.

### Step 5: Restart Laravel Server

After updating `.env`:

```bash
php artisan config:clear
php artisan cache:clear
```

Then restart your Laravel development server.

### Step 6: Test OTP Email

1. Log in to your application
2. Go to any dashboard
3. The OTP modal should appear
4. Click "Send Verification Code"
5. Check your email inbox (and spam folder!)

---

## Alternative: Quick Testing with Mailtrap

If you just want to test WITHOUT sending real emails:

### Mailtrap Setup (Free for Testing)

1. Go to https://mailtrap.io
2. Sign up (free account)
3. Get your inbox credentials
4. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="test@example.com"
MAIL_FROM_NAME="CAS Private Care"
```

Mailtrap will capture all emails in a fake inbox - perfect for testing!

---

## Troubleshooting

### Issue: "Connection could not be established with host"
- Check your firewall/antivirus isn't blocking port 587
- Verify SMTP credentials are correct
- Try changing `MAIL_ENCRYPTION=tls` to `MAIL_ENCRYPTION=ssl` and `MAIL_PORT=465`

### Issue: "530 Authentication Required"
- Double-check your MAIL_USERNAME and MAIL_PASSWORD
- Make sure you're using the SMTP key, not your Brevo login password

### Issue: Still logging instead of sending
- Run `php artisan config:clear`
- Check if `.env` changes took effect by running:
  ```bash
  php artisan tinker
  >>> config('mail.default')
  ```
  Should return `"smtp"` not `"log"`

---

## Current Status

✅ **OTP Generation:** Working (confirmed in logs)  
✅ **OTP Storage:** Working (database)  
❌ **Email Sending:** Currently set to 'log' mode  
⚠️ **Fix Needed:** Change `MAIL_MAILER=smtp` and configure SMTP credentials

Your OTP emails are being created successfully - they're just not leaving the server!


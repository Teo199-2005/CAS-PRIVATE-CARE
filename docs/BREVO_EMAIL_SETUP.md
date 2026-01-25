# Brevo Email Setup Guide

This guide will help you configure Brevo (formerly Sendinblue) for email sending in your CAS Private Care application.

## 📧 What You'll Get from Your Brevo Account

When you sign up for Brevo and verify your account, you'll receive:

### 1. **SMTP Credentials** (Recommended for Laravel)

To get your SMTP credentials:

1. Log in to your Brevo account at https://www.brevo.com
2. Go to **Settings** → **SMTP & API**
3. Click on **SMTP** tab
4. You'll see:
   - **SMTP Server:** `smtp-relay.brevo.com`
   - **Port:** `587` (TLS) or `465` (SSL)
   - **SMTP Login:** Your Brevo account email address
   - **SMTP Password:** Click "Generate" to create a new SMTP password (this is different from your login password)

**Important:** 
- The SMTP password is a randomly generated key, not your account password
- You can generate multiple SMTP passwords for different applications
- Keep your SMTP password secure - treat it like an API key

### 2. **API Key** (Optional - for advanced features)

If you want to use Brevo's Transactional API:

1. Go to **Settings** → **SMTP & API**
2. Click on **API Keys** tab
3. Click **Generate a new API key**
4. Copy the API key (you'll only see it once)

**Note:** For this Laravel application, we're using SMTP which is simpler and works out of the box with Laravel's mail system. You don't need the API key unless you want to use advanced features like templates, webhooks, or statistics.

## 🚀 Setup Instructions

### Step 1: Configure Environment Variables

Add the following to your `.env` file:

```env
# Brevo SMTP Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=your_smtp_password_from_brevo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Important Notes:**
- Replace `your_brevo_email@example.com` with your actual Brevo account email
- Replace `your_smtp_password_from_brevo` with the SMTP password you generated
- Replace `noreply@yourdomain.com` with your verified sender email address in Brevo
- For port 465 (SSL), use `MAIL_ENCRYPTION=ssl` instead of `tls`

### Step 2: Verify Your Sender Email in Brevo

Before you can send emails, you need to verify your sender email address:

1. Go to **Settings** → **Senders**
2. Click **Add a sender**
3. Enter your email address (e.g., `noreply@yourdomain.com`)
4. Click **Save**
5. Check your email inbox and click the verification link
6. Once verified, you can use this email in `MAIL_FROM_ADDRESS`

### Step 3: Run Database Migrations

The email system requires some database tables. Run the migrations:

```bash
php artisan migrate
```

This will create:
- `email_verification_tokens` table (for email verification)
- Add `token` column to `password_resets_custom` table

### Step 4: Test Email Sending

You can test if emails are working by:

1. **Register a new user** - This will send a welcome email and verification email
2. **Request a password reset** - This will send a password reset email
3. **Approve a booking** (as admin) - This will send a booking approval email
4. **Approve a contractor application** (as admin) - This will send an approval email

Check your Brevo dashboard under **Statistics** → **Emails** to see sent emails.

## 📨 Email Types Implemented

The following emails are automatically sent:

### 1. **Welcome Email**
- **When:** New user registration
- **Recipient:** New user
- **Purpose:** Welcome message and next steps

### 2. **Email Verification**
- **When:** User registration (non-OAuth users)
- **Recipient:** New user
- **Purpose:** Verify email address
- **Action:** Click link to verify

### 3. **Password Reset**
- **When:** User requests password reset
- **Recipient:** User who requested reset
- **Purpose:** Reset password
- **Action:** Click link to reset password (expires in 60 minutes)

### 4. **Booking Approved**
- **When:** Admin approves a booking
- **Recipient:** Client who made the booking
- **Purpose:** Notify client that booking is approved
- **Includes:** Booking details (service type, date, location, etc.)

### 5. **Contractor Application Approved**
- **When:** Admin approves a contractor application
- **Recipient:** Contractor
- **Purpose:** Notify contractor they can now access the platform
- **Includes:** Login instructions

### 6. **Announcements**
- **When:** Admin sends an announcement
- **Recipients:** All users, caregivers only, or clients only (based on selection)
- **Purpose:** Important updates and notifications
- **Includes:** Announcement title and message

## 🔧 Troubleshooting

### Emails Not Sending

1. **Check Brevo Dashboard:**
   - Go to **Statistics** → **Emails**
   - Check if emails are being sent
   - Look for any error messages

2. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for email-related errors

3. **Verify SMTP Credentials:**
   - Double-check your SMTP username and password in `.env`
   - Make sure there are no extra spaces
   - Regenerate SMTP password in Brevo if needed

4. **Check Sender Verification:**
   - Ensure your `MAIL_FROM_ADDRESS` is verified in Brevo
   - Check **Settings** → **Senders** in Brevo dashboard

5. **Test SMTP Connection:**
   ```bash
   php artisan tinker
   ```
   Then in tinker:
   ```php
   Mail::raw('Test email', function($message) {
       $message->to('your-email@example.com')->subject('Test');
   });
   ```

### Common Issues

**Issue:** "Connection refused" or "Connection timeout"
- **Solution:** Check your firewall settings. Port 587 (TLS) or 465 (SSL) should be open

**Issue:** "Authentication failed"
- **Solution:** Verify your SMTP username and password are correct. Make sure you're using the SMTP password, not your account password.

**Issue:** "Sender not verified"
- **Solution:** Go to Brevo → Settings → Senders and verify your sender email address

**Issue:** Emails going to spam
- **Solution:** 
  - Set up SPF, DKIM, and DMARC records for your domain
  - Use a verified domain in Brevo
  - Keep email content professional and avoid spam trigger words

## 📊 Brevo Free Plan Limits

Brevo's free plan includes:
- 300 emails per day
- Unlimited contacts
- Basic email templates
- Email statistics

If you need more:
- **Lite Plan:** 10,000 emails/month - $25/month
- **Premium Plan:** 20,000 emails/month - $65/month
- Check https://www.brevo.com/pricing for current plans

## 🔒 Security Best Practices

1. **Never commit `.env` file** - It contains sensitive credentials
2. **Use environment-specific credentials** - Different credentials for dev/staging/production
3. **Rotate SMTP passwords regularly** - Generate new passwords every few months
4. **Monitor email usage** - Check Brevo dashboard regularly for unusual activity
5. **Set up email alerts** - Configure alerts in Brevo for failed sends or quota limits

## 📝 Additional Configuration

### Custom Email Templates

Email templates are located in `resources/views/emails/`:
- `layout.blade.php` - Base email template
- `verification.blade.php` - Email verification template
- `password-reset.blade.php` - Password reset template
- `booking-approved.blade.php` - Booking approval template
- `contractor-approved.blade.php` - Contractor approval template
- `announcement.blade.php` - Announcement template
- `welcome.blade.php` - Welcome email template

You can customize these templates to match your branding.

### Queue Email Sending (Optional)

For better performance, you can queue emails:

1. In `config/queue.php`, configure your queue driver
2. In `EmailService.php`, emails are already using `Queueable` trait
3. Run queue worker:
   ```bash
   php artisan queue:work
   ```

## ✅ Checklist

- [ ] Created Brevo account
- [ ] Verified sender email in Brevo
- [ ] Generated SMTP password in Brevo
- [ ] Added SMTP credentials to `.env` file
- [ ] Ran database migrations
- [ ] Tested email sending (registration, password reset, etc.)
- [ ] Verified emails are being received
- [ ] Checked Brevo dashboard for email statistics
- [ ] Customized email templates (optional)

## 📞 Support

- **Brevo Support:** https://help.brevo.com
- **Laravel Mail Documentation:** https://laravel.com/docs/mail
- **Email Issues:** Check `storage/logs/laravel.log` for error messages

---

**Last Updated:** January 2025




# How to Add Brevo Configuration to Your .env File

## Your Brevo Account Information:
- **Email:** teofiloharry69@gmail.com
- **Website:** https://casprivatecare.online/
- **Company:** CasPrivateCare

## Quick Setup Steps:

### Step 1: Verify Your Sender Email in Brevo

1. Log in to Brevo at https://www.brevo.com
2. Go to **Settings** → **Senders, domains, IPs** → **Senders**
3. Click **"Add a sender"**
4. Add your email: `teofiloharry69@gmail.com`
5. Check your email inbox and click the verification link
6. Once verified, you're ready to use it!

### Step 2: Add to Your .env File

**Option A: If you already have a .env file:**
1. Open your `.env` file in the project root
2. Find or add these lines (replace any existing MAIL_* entries):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=teofiloharry69@gmail.com
MAIL_PASSWORD=xsmtpsib-be767ee79aa324f3b695a4670157c63326cc464a3161d7fc34bc1e94c7f05f60-vNg0zUZpBjeh2eI4
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=teofiloharry69@gmail.com
MAIL_FROM_NAME="CAS Private Care"
```

**Option B: If you don't have a .env file:**
1. Copy the entire content from `BREVO_ENV_CONFIG.txt`
2. Create a new file called `.env` in your project root
3. Paste the MAIL_* configuration lines above
4. Add other required variables (APP_NAME, APP_URL, DB settings, etc.)

### Step 3: Run Migrations

```bash
php artisan migrate
```

This will create the necessary database tables for email verification.

### Step 4: Test Email Sending

Test by:
- Registering a new user account
- Requesting a password reset
- Approving a booking (as admin)

## Using a Custom Domain Email (Optional)

If you want to send emails from `noreply@casprivatecare.online` instead:

1. In Brevo → Settings → Senders → Add a sender
2. Add: `noreply@casprivatecare.online`
3. You may need to verify your domain (add DNS records)
4. Once verified, update `MAIL_FROM_ADDRESS=noreply@casprivatecare.online` in your .env

For now, using `teofiloharry69@gmail.com` will work fine!

## Security Reminder

- ✅ The `.env` file should be in `.gitignore` (don't commit it!)
- ✅ Delete `BREVO_ENV_CONFIG.txt` after copying to .env
- ✅ Keep your SMTP password secure

## Need Help?

Check the logs if emails aren't sending:
```bash
tail -f storage/logs/laravel.log
```

Or check your Brevo dashboard → Statistics → Emails to see sent emails.



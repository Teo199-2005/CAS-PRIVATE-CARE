# Quick Start: Brevo Email Setup

## 🎯 What You'll Get from Brevo

After signing up at https://www.brevo.com, you'll need:

### 1. SMTP Credentials

1. Log in to Brevo → **Settings** → **SMTP & API** → **SMTP** tab
2. You'll see:
   - **SMTP Server:** `smtp-relay.brevo.com`
   - **Port:** `587` (TLS) or `465` (SSL)
   - **SMTP Login:** Your Brevo account email
   - **SMTP Password:** Click "Generate" to create one (this is NOT your login password!)

### 2. Verify Sender Email

1. Go to **Settings** → **Senders**
2. Click **Add a sender**
3. Enter your email (e.g., `noreply@yourdomain.com`)
4. Verify via email link

## ⚡ Quick Setup

Add to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=your_generated_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="CAS Private Care"
```

Then run:
```bash
php artisan migrate
```

## ✅ That's It!

Your application will now send:
- ✅ Welcome emails on registration
- ✅ Email verification (for new users)
- ✅ Password reset emails
- ✅ Booking approval emails
- ✅ Contractor approval emails
- ✅ Announcement emails

**See `BREVO_EMAIL_SETUP.md` for detailed instructions and troubleshooting.**




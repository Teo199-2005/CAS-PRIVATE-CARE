# Update Your .env File - Quick Instructions

## ✅ Updated Brevo Credentials

Your Brevo account email: **teofiloharry69@gmail.com**  
New SMTP password: **xsmtpsib-be767ee79aa324f3b695a4670157c63326cc464a3161d7fc34bc1e94c7f05f60-vNg0zUZpBjeh2eI4**

## 📝 Steps to Update .env File

### Step 1: Open Your .env File
- Open the `.env` file in your project root directory

### Step 2: Find and Update These Lines

Find the `#BREVO` section (around line 78) and update or add these lines:

```env
#BREVO
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=teofiloharry69@gmail.com
MAIL_PASSWORD=xsmtpsib-be767ee79aa324f3b695a4670157c63326cc464a3161d7fc34bc1e94c7f05f60-vNg0zUZpBjeh2eI4
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=teofiloharry69@gmail.com
MAIL_FROM_NAME="CAS Private Care"
```

### Step 3: Important Notes

**Make sure:**
- ❌ NO spaces around `=` sign: `MAIL_PASSWORD = value` ← WRONG
- ✅ CORRECT format: `MAIL_PASSWORD=value` ← CORRECT
- ❌ NO quotes around the password value
- ✅ Password should be all on ONE line

### Step 4: Save the File

Save your `.env` file after making the changes.

### Step 5: Clear Laravel Cache

Run these commands in your terminal:

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 6: Verify Sender Email in Brevo

**IMPORTANT:** Before testing, you MUST verify the sender email:

1. Go to: https://www.brevo.com
2. Settings → Senders, domains, IPs → Senders
3. Check if `teofiloharry69@gmail.com` is listed
4. If NOT listed: Click "Add a sender" → Enter `teofiloharry69@gmail.com` → Save
5. Check your email inbox (`teofiloharry69@gmail.com`)
6. Look for email from Brevo: "Verify your sender email"
7. Click the verification link
8. Status should show "Verified" ✅

### Step 7: Test Email

After verification, try the test email button in your admin dashboard.

---

## 🎯 Quick Copy-Paste

If you want to just copy-paste, here's the exact configuration:

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

---

## ⚠️ What Changed

- ✅ MAIL_USERNAME: Changed from `spensys2025@gmail.com` to `teofiloharry69@gmail.com`
- ✅ MAIL_PASSWORD: Updated to new password
- ✅ MAIL_FROM_ADDRESS: Changed to `teofiloharry69@gmail.com` (your Brevo account email)


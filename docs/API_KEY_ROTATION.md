# API Key Rotation Procedures

## Overview

Regular rotation of API keys and secrets is a critical security practice. This document outlines the procedures for rotating keys used by CAS Private Care.

## Rotation Schedule

| Secret Type | Rotation Frequency | Emergency Rotation |
|-------------|-------------------|-------------------|
| Stripe API Keys | Every 90 days | Immediately after suspected breach |
| Stripe Webhook Secrets | Every 90 days | Immediately after suspected breach |
| Laravel APP_KEY | Annually | After suspected breach |
| OAuth Client Secrets | Every 180 days | After suspected breach |
| Database Passwords | Every 90 days | After suspected breach |

## Stripe Keys Rotation

### Step 1: Generate New Keys in Stripe Dashboard

1. Log in to [Stripe Dashboard](https://dashboard.stripe.com)
2. Navigate to **Developers > API keys**
3. Click **Create secret key**
4. Give it a descriptive name (e.g., "Production Key - Jan 2026")
5. Copy the new secret key immediately (you won't see it again)
6. **Keep the old key active during transition**

### Step 2: Update Environment Variables

```bash
# SSH into production server
ssh user@production-server

# Put application in maintenance mode
cd /var/www/casprivatecare
php artisan down --retry=60 --secret="maintenance-bypass-token"

# Backup current .env
cp .env .env.backup.$(date +%Y%m%d)

# Update .env with new keys
nano .env
```

Update these values:
```env
STRIPE_KEY=pk_live_NEW_PUBLISHABLE_KEY
STRIPE_SECRET=sk_live_NEW_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_NEW_WEBHOOK_SECRET
```

### Step 3: Clear Configuration Cache

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild configuration cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart
```

### Step 4: Update Webhook Endpoints

1. Go to [Stripe Dashboard > Webhooks](https://dashboard.stripe.com/webhooks)
2. Click **Add endpoint**
3. Enter your endpoint URL: `https://casprivatecare.com/api/stripe/webhook`
4. Select events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `account.updated`
   - `payout.paid`
   - `payout.failed`
5. Copy the new webhook signing secret
6. Update `STRIPE_WEBHOOK_SECRET` in .env

### Step 5: Verify New Keys Work

```bash
# Bring application back online
php artisan up

# Test payment processing
php artisan stripe:test-connection

# Check logs for errors
tail -f storage/logs/laravel.log
```

### Step 6: Revoke Old Keys

**Wait 24-48 hours before revoking old keys** to ensure:
- All cached configurations are cleared
- No errors in logs
- Payments are processing normally
- Webhooks are being received

Then:
1. Go to Stripe Dashboard > API keys
2. Click the old key's menu (...)
3. Select **Delete key**
4. For webhooks, delete the old endpoint

## Laravel APP_KEY Rotation

⚠️ **Warning:** Rotating APP_KEY will invalidate:
- All encrypted cookies
- All sessions (users will be logged out)
- All encrypted database fields
- All signed URLs

### Procedure

```bash
# Generate new key (note: don't apply yet)
php artisan key:generate --show

# The output will be something like:
# base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=

# Put application in maintenance mode
php artisan down

# Update .env with new key
nano .env
# Change APP_KEY=base64:NEW_KEY_HERE

# Clear all caches
php artisan config:clear
php artisan cache:clear

# If you have encrypted data, you need to re-encrypt it
# This requires a custom migration script

# Bring application back online
php artisan up
```

## OAuth Client Secrets (Google, etc.)

### Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Navigate to **APIs & Services > Credentials**
3. Find your OAuth 2.0 Client ID
4. Click **Reset Secret**
5. Update `.env`:

```env
GOOGLE_CLIENT_SECRET=new_secret_here
```

6. Clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

## Post-Rotation Monitoring

After any key rotation, monitor these for 48 hours:

### Check Lists

- [ ] Application error logs are clean
- [ ] Stripe webhook events are being received
- [ ] Payments are processing successfully
- [ ] Payouts to contractors are working
- [ ] User logins are working
- [ ] Email sending is functioning

### Monitoring Commands

```bash
# Watch Laravel logs
tail -f storage/logs/laravel.log | grep -i "error\|exception\|stripe"

# Check Stripe webhook delivery
# In Stripe Dashboard > Webhooks > Select endpoint > View recent deliveries

# Test a small payment
php artisan tinker
>>> \App\Services\StripePaymentService::testConnection();
```

## Automated Rotation (Future Implementation)

Consider implementing automated key rotation using:

1. **AWS Secrets Manager** with rotation Lambda
2. **HashiCorp Vault** with dynamic secrets
3. **Laravel Vapor** with environment secrets

### Example: AWS Secrets Manager Integration

```php
// config/services.php
'stripe' => [
    'key' => fn() => \AWS\SecretsManager::getSecret('stripe/publishable_key'),
    'secret' => fn() => \AWS\SecretsManager::getSecret('stripe/secret_key'),
],
```

## Emergency Rotation Procedure

If a key is suspected to be compromised:

1. **Immediately** generate new keys in the respective dashboard
2. **Do not** wait for maintenance window
3. Update production immediately:
   ```bash
   ssh production
   php artisan down --secret="emergency123"
   # Update .env
   php artisan config:clear && php artisan config:cache
   php artisan up
   ```
4. Revoke old keys immediately (don't wait 24 hours)
5. Check logs for unauthorized access
6. Notify security team
7. Document the incident

## Related Documentation

- [Stripe Key Security](https://stripe.com/docs/keys)
- [Laravel Encryption](https://laravel.com/docs/10.x/encryption)
- [OWASP Key Management](https://cheatsheetseries.owasp.org/cheatsheets/Key_Management_Cheat_Sheet.html)

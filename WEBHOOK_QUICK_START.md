# 🚀 Quick Start: Stripe Webhooks Setup (5 Minutes)

## Step 1: Get Your Webhook Secret (2 minutes)

### Option A: For Local Testing (Recommended First)

1. **Install Stripe CLI** (Windows PowerShell):
   ```powershell
   # If you have Scoop installed:
   scoop bucket add stripe https://github.com/stripe/scoop-stripe-cli.git
   scoop install stripe
   
   # Or download from: https://github.com/stripe/stripe-cli/releases
   ```

2. **Login to Stripe**:
   ```bash
   stripe login
   ```

3. **Forward webhooks to localhost**:
   ```bash
   stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
   ```

4. **Copy the webhook secret** that appears (starts with `whsec_`):
   ```
   Ready! Your webhook signing secret is whsec_abc123xyz...
   ```

5. **Add to `.env`**:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_abc123xyz...
   ```

### Option B: For Production Webhook

1. Go to [Stripe Dashboard → Webhooks](https://dashboard.stripe.com/test/webhooks)
2. Click **"Add endpoint"**
3. Enter: `https://yourdomain.com/api/webhooks/stripe`
4. Select events:
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
   - `customer.subscription.deleted`
   - `customer.subscription.updated`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
5. Click **"Add endpoint"**
6. Click **"Reveal"** to see signing secret
7. Add to `.env`:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_your_secret_here
   ```

---

## Step 2: Test It Works (2 minutes)

### Start your Laravel server:
```bash
php artisan serve
```

### In another terminal, trigger a test event:
```bash
stripe trigger invoice.payment_succeeded
```

### Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

You should see:
```
[INFO] Stripe webhook received: invoice.payment_succeeded
```

---

## Step 3: Test Complete Flow (1 minute)

### In your browser:

1. **Login as client**
2. **Go to Payment Information tab**
3. **Add a test card**: `4242 4242 4242 4242`
4. **Save card** ✅

Now you're ready to enable auto-pay on bookings!

---

## 🎯 That's It!

Your webhook system is now live and listening for Stripe events.

### What happens automatically:
- ✅ Payment succeeds → Database updated, email sent
- ✅ Payment fails → Client notified, status updated
- ✅ Subscription canceled → Auto-pay disabled
- ✅ All events logged in `storage/logs/laravel.log`

---

## 🐛 Troubleshooting

### Webhooks not working?

**Check 1: Is webhook secret set?**
```bash
php artisan tinker
>>> env('STRIPE_WEBHOOK_SECRET')
# Should return: "whsec_..."
```

**Check 2: Is route registered?**
```bash
php artisan route:list | grep webhook
# Should show: POST /api/webhooks/stripe
```

**Check 3: Are events coming through?**
```bash
tail -f storage/logs/laravel.log
# Should show: "[INFO] Stripe webhook received: ..."
```

### Still not working?

1. Restart Laravel server: `php artisan serve`
2. Restart Stripe CLI: `stripe listen --forward-to http://localhost:8000/api/webhooks/stripe`
3. Clear config cache: `php artisan config:clear`
4. Check webhook deliveries in [Stripe Dashboard](https://dashboard.stripe.com/test/webhooks)

---

## 📚 Full Documentation

For detailed setup and production deployment:
- **Setup Guide**: `CLIENT_RECURRING_PAYMENTS_SETUP.md`
- **Webhook Guide**: `STRIPE_WEBHOOKS_GUIDE.md`
- **Implementation Status**: `IMPLEMENTATION_COMPLETE.md`

---

## ✅ Next Steps

After webhooks are working:
1. Test creating a subscription on a booking
2. Test canceling a subscription
3. Monitor webhook events in Stripe Dashboard
4. Deploy to production with live webhook endpoint
5. Enjoy automated recurring payments! 🎉

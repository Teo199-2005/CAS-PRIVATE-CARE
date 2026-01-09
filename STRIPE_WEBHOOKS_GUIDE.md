# 🎯 Stripe Webhooks Setup Guide

## What We've Implemented

✅ **Database Migration** - Added subscription fields to bookings table
✅ **Webhook Controller** - `StripeWebhookController.php` with event handlers
✅ **Webhook Route** - `/api/webhooks/stripe` (no auth, Stripe verifies signature)
✅ **Auto-Pay Component** - `BookingAutoPayToggle.vue` for managing subscriptions
✅ **Updated Controller** - `ClientPaymentController.php` creates subscriptions per booking
✅ **Frontend Compiled** - All changes built successfully

---

## 🚀 How to Set Up Stripe Webhooks

### Step 1: Add Webhook Secret to `.env`

First, you need to get your webhook signing secret from Stripe.

#### For Test Mode (Development):

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/test/webhooks)
2. Click **"Add endpoint"**
3. Enter your webhook URL: 
   ```
   https://your-domain.com/api/webhooks/stripe
   ```
   (Replace `your-domain.com` with your actual domain)
   
4. Select events to listen for:
   - ✅ `invoice.payment_succeeded`
   - ✅ `invoice.payment_failed`
   - ✅ `customer.subscription.deleted`
   - ✅ `customer.subscription.updated`
   - ✅ `payment_intent.succeeded`
   - ✅ `payment_intent.payment_failed`

5. Click **"Add endpoint"**

6. Click on the webhook you just created

7. Click **"Reveal"** next to "Signing secret"

8. Copy the secret (starts with `whsec_...`)

9. Add to your `.env` file:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_your_test_webhook_secret_here
   ```

#### For Production:

Repeat the same steps in **Live Mode**:
1. Go to [Stripe Dashboard (Live)](https://dashboard.stripe.com/webhooks)
2. Add endpoint with your production URL
3. Select the same events
4. Get the **live** signing secret
5. Update `.env` with live secret when deploying

---

### Step 2: Test Webhooks Locally with Stripe CLI

If you're developing locally, you can use the Stripe CLI to forward webhooks:

#### Install Stripe CLI:

**Windows (PowerShell):**
```powershell
scoop bucket add stripe https://github.com/stripe/scoop-stripe-cli.git
scoop install stripe
```

Or download from: https://github.com/stripe/stripe-cli/releases

**Verify installation:**
```bash
stripe --version
```

#### Forward Webhooks to Localhost:

```bash
stripe login
```

Then forward webhooks:
```bash
stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
```

This will output a webhook signing secret like:
```
Ready! Your webhook signing secret is whsec_abc123...
```

Add this to your `.env`:
```env
STRIPE_WEBHOOK_SECRET=whsec_abc123...
```

#### Trigger Test Events:

In another terminal, trigger test events:

```bash
# Test successful payment
stripe trigger invoice.payment_succeeded

# Test failed payment
stripe trigger invoice.payment_failed

# Test subscription cancellation
stripe trigger customer.subscription.deleted
```

Check your Laravel logs to see the events being processed:
```bash
tail -f storage/logs/laravel.log
```

---

### Step 3: Verify Webhooks Are Working

#### Check Logs:

Look for these log messages in `storage/logs/laravel.log`:

```
[INFO] Stripe webhook received: invoice.payment_succeeded
[INFO] Invoice payment succeeded
[INFO] Booking payment updated (booking_id: 123)
```

#### Test Real Flow:

1. **Add a payment method** (client dashboard → Payment Information)
2. **Enable auto-pay** for a booking (toggle switch in booking details)
3. **Check webhook events** in Stripe Dashboard → Webhooks → Click on your endpoint → See events
4. **Verify database** updates:
   ```php
   php artisan tinker
   >>> Booking::where('auto_pay_enabled', true)->get(['id', 'stripe_subscription_id', 'next_payment_date']);
   ```

---

## 📋 What Each Webhook Does

### `invoice.payment_succeeded`
✅ **Triggered:** When a subscription payment succeeds
📧 **Action:** 
- Updates booking `payment_status` to `paid`
- Sets `payment_date` to now
- Updates `next_payment_date`
- Sends success email to client

### `invoice.payment_failed`
❌ **Triggered:** When a subscription payment fails
📧 **Action:**
- Updates booking `payment_status` to `failed`
- Sends failure notification to client
- Client needs to update their payment method

### `customer.subscription.deleted`
🚫 **Triggered:** When a subscription is canceled
📧 **Action:**
- Sets `auto_pay_enabled` to `false`
- Changes `payment_type` to `one-time`
- Sends cancellation confirmation email

### `customer.subscription.updated`
🔄 **Triggered:** When subscription status changes
📧 **Action:**
- Updates `next_payment_date`
- Syncs `auto_pay_enabled` with subscription status

### `payment_intent.succeeded`
✅ **Triggered:** One-time payment succeeds
📧 **Action:**
- Updates booking `payment_status` to `paid`

### `payment_intent.payment_failed`
❌ **Triggered:** One-time payment fails
📧 **Action:**
- Updates booking `payment_status` to `failed`

---

## 🔒 Security Notes

### Webhook Signature Verification

The webhook controller automatically verifies Stripe's signature:

```php
$event = \Stripe\Webhook::constructEvent($payload, $sig, $webhookSecret);
```

This prevents:
- ❌ Fake webhook requests
- ❌ Replay attacks
- ❌ Man-in-the-middle attacks

### No Authentication Required

The webhook route (`/api/webhooks/stripe`) does **NOT** use authentication middleware because:
1. Stripe signs requests with a secret
2. Signature verification is more secure than session auth
3. Webhooks come from Stripe's servers, not user browsers

---

## 🧪 Testing Checklist

### Before Going Live:

- [ ] Webhook endpoint added in Stripe Dashboard
- [ ] `STRIPE_WEBHOOK_SECRET` set in `.env`
- [ ] Test events triggered successfully
- [ ] Logs show webhook events being processed
- [ ] Database updates confirmed after events
- [ ] Email notifications received (check spam folder)
- [ ] Try canceling a subscription - auto-pay should disable
- [ ] Try failed payment scenario (use test card `4000 0000 0000 0341`)

### Test Cards for Webhooks:

```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
Insufficient Funds: 4000 0000 0000 9995
Expired Card: 4000 0000 0000 0069
Processing Error: 4000 0000 0000 0119
```

---

## 🛠️ Troubleshooting

### Webhook not receiving events?

1. **Check URL is publicly accessible**
   - Localhost won't work unless using Stripe CLI
   - Use ngrok or similar for local testing

2. **Verify webhook secret**
   ```bash
   php artisan tinker
   >>> env('STRIPE_WEBHOOK_SECRET')
   ```

3. **Check Laravel logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **View Stripe webhook logs**
   - Dashboard → Webhooks → Click endpoint → See delivery attempts

### Events not processing?

1. **Check for errors in Stripe Dashboard**
   - Failed deliveries show red X
   - Click to see error details

2. **Verify route is registered**
   ```bash
   php artisan route:list | grep webhooks
   ```
   Should show: `POST /api/webhooks/stripe`

3. **Test manually with curl**
   ```bash
   curl -X POST http://your-domain.com/api/webhooks/stripe \
     -H "Content-Type: application/json" \
     -d '{"type":"test"}'
   ```

### Emails not sending?

Check mail configuration in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
```

---

## 📧 Email Customization

Current emails are basic text. To create HTML templates:

1. **Create Mailable class**:
   ```bash
   php artisan make:mail PaymentSuccessEmail
   ```

2. **Update webhook controller**:
   ```php
   Mail::to($client->email)->send(new PaymentSuccessEmail($booking, $invoice));
   ```

3. **Create Blade template**:
   `resources/views/emails/payment-success.blade.php`

---

## 🎉 You're All Set!

Your recurring payment system is now fully functional with:
- ✅ Payment method storage
- ✅ Subscription creation per booking
- ✅ Auto-pay toggle for clients
- ✅ Webhook event handling
- ✅ Email notifications
- ✅ Database synchronization

### Next Steps:

1. Add webhook secret to `.env`
2. Test with Stripe CLI locally
3. Deploy to production
4. Add webhook endpoint in live Stripe Dashboard
5. Monitor webhook deliveries in Stripe Dashboard

### Questions?

Check the main documentation: `CLIENT_RECURRING_PAYMENTS_SETUP.md`

---

## 🔗 Useful Links

- [Stripe Webhooks Docs](https://stripe.com/docs/webhooks)
- [Stripe CLI Docs](https://stripe.com/docs/stripe-cli)
- [Stripe Test Cards](https://stripe.com/docs/testing)
- [Webhook Best Practices](https://stripe.com/docs/webhooks/best-practices)

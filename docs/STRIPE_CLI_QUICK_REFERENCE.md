# 🎯 Stripe CLI Commands - Quick Reference for Your Project

## 🚀 Getting Started (Do This First)

### 1. Login to Stripe
```bash
stripe login
```
This will open your browser to authenticate with Stripe.

### 2. Start Webhook Forwarding
```bash
stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
```
**Important:** Copy the `whsec_...` secret that appears and add it to your `.env` file:
```env
STRIPE_WEBHOOK_SECRET=whsec_abc123xyz...
```

Keep this terminal window open while testing!

---

## 🧪 Testing Your Implementation

### Test Webhook Events

**Test successful payment:**
```bash
stripe trigger invoice.payment_succeeded
```

**Test failed payment:**
```bash
stripe trigger invoice.payment_failed
```

**Test subscription cancellation:**
```bash
stripe trigger customer.subscription.deleted
```

**Test subscription update:**
```bash
stripe trigger customer.subscription.updated
```

**Test one-time payment success:**
```bash
stripe trigger payment_intent.succeeded
```

**Test one-time payment failure:**
```bash
stripe trigger payment_intent.payment_failed
```

### Watch Your Laravel Logs
In another terminal window:
```bash
# PowerShell
Get-Content storage\logs\laravel.log -Wait -Tail 20

# Or use this
php artisan tail
```

You should see:
```
[INFO] Stripe webhook received: invoice.payment_succeeded
[INFO] Invoice payment succeeded
[INFO] Booking payment updated (booking_id: 123)
```

---

## 🔍 Inspect Your Stripe Data

### View Customers
```bash
stripe customers list
```

### View a Specific Customer
```bash
stripe customers retrieve cus_abc123
```

### View Subscriptions
```bash
stripe subscriptions list
```

### View a Specific Subscription
```bash
stripe subscriptions retrieve sub_abc123
```

### View Payment Methods for a Customer
```bash
stripe payment_methods list --customer cus_abc123 --type card
```

### View Recent Events
```bash
stripe events list --limit 10
```

---

## 🎨 Advanced Testing

### Create a Test Customer
```bash
stripe customers create --email test@example.com --name "Test User"
```

### Create a Test Subscription (Manual)
```bash
stripe subscriptions create \
  --customer cus_abc123 \
  --items[0][price]=price_abc123
```

### Cancel a Subscription
```bash
stripe subscriptions cancel sub_abc123
```

### Retrieve Event Details
```bash
stripe events retrieve evt_abc123
```

### Resend a Webhook Event
```bash
stripe events resend evt_abc123
```

---

## 📊 Real-Time Monitoring

### Tail API Request Logs
```bash
stripe logs tail
```

### Filter by HTTP Method
```bash
stripe logs tail --filter-http-method POST
```

### Filter by Status Code
```bash
stripe logs tail --filter-status-code-type 4XX
```

### Filter by Path
```bash
stripe logs tail --filter-request-path /v1/subscriptions
```

---

## 🛠️ Your Workflow for Testing

### Terminal 1: Laravel Server
```bash
php artisan serve
```

### Terminal 2: Stripe Webhook Forwarding
```bash
stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
```

### Terminal 3: Watch Laravel Logs
```bash
Get-Content storage\logs\laravel.log -Wait -Tail 20
```

### Terminal 4: Trigger Events
```bash
# Test subscription payment
stripe trigger invoice.payment_succeeded

# Test failed payment
stripe trigger invoice.payment_failed

# View what happened
stripe events list --limit 5
```

---

## 🎯 Testing Your Complete Flow

### Step 1: Add Payment Method (Browser)
1. Login as client
2. Go to Payment Information tab
3. Add test card: `4242 4242 4242 4242`
4. Click "Save Card"

### Step 2: Check in Stripe CLI
```bash
# See the customer created
stripe customers list --limit 1

# See payment methods
stripe payment_methods list --customer cus_YOUR_CUSTOMER_ID --type card
```

### Step 3: Enable Auto-Pay (Browser)
1. Go to My Bookings
2. Find a booking
3. Toggle Auto-Pay ON
4. Enter amount (e.g., 100.00)
5. Click "Enable Auto-Pay"

### Step 4: Verify Subscription Created
```bash
# List subscriptions
stripe subscriptions list --limit 1

# View subscription details
stripe subscriptions retrieve sub_YOUR_SUBSCRIPTION_ID
```

### Step 5: Simulate Monthly Payment
```bash
# Trigger payment success
stripe trigger invoice.payment_succeeded
```

### Step 6: Check Your Database
```bash
php artisan tinker
>>> Booking::where('auto_pay_enabled', true)->get(['id', 'stripe_subscription_id', 'payment_status', 'next_payment_date']);
```

### Step 7: Cancel Subscription (Browser)
1. Click "Cancel" button in booking auto-pay section
2. Confirm

### Step 8: Verify Cancellation
```bash
# Check subscription status
stripe subscriptions retrieve sub_YOUR_SUBSCRIPTION_ID

# Should show: "status": "canceled"
```

---

## 🐛 Debugging Commands

### Check Webhook Endpoint Configuration
```bash
stripe webhook_endpoints list
```

### View Webhook Delivery Attempts
Go to: https://dashboard.stripe.com/test/webhooks

### Test Connection to Stripe
```bash
stripe config --list
```

### Verify API Key
```bash
stripe customers list --limit 1
```
If this works, your API key is valid!

### Check CLI Version
```bash
stripe version
```

---

## 📝 Useful Test Card Numbers

```
✅ Success: 4242 4242 4242 4242
❌ Decline: 4000 0000 0000 0002
💰 Insufficient Funds: 4000 0000 0000 9995
⏰ Expired: 4000 0000 0000 0069
🔄 Processing Error: 4000 0000 0000 0119
🔒 Requires Auth: 4000 0025 0000 3155
```

**For all cards:**
- Expiry: Any future date (e.g., 12/34)
- CVC: Any 3 digits (e.g., 123)
- ZIP: Any 5 digits (e.g., 12345)

---

## 🎉 Quick Test Script

Copy and paste this into your terminal to test everything:

```bash
# Start webhook listener (Terminal 1)
stripe listen --forward-to http://localhost:8000/api/webhooks/stripe

# In another terminal (Terminal 2):
# Test successful payment
stripe trigger invoice.payment_succeeded
sleep 2

# Test failed payment
stripe trigger invoice.payment_failed
sleep 2

# Test subscription canceled
stripe trigger customer.subscription.deleted
sleep 2

# View recent events
stripe events list --limit 5

# Check Laravel logs
Get-Content storage\logs\laravel.log -Wait -Tail 10
```

---

## 🔗 Quick Links

- **Stripe Dashboard (Test):** https://dashboard.stripe.com/test
- **Webhooks:** https://dashboard.stripe.com/test/webhooks
- **Events:** https://dashboard.stripe.com/test/events
- **Customers:** https://dashboard.stripe.com/test/customers
- **Subscriptions:** https://dashboard.stripe.com/test/subscriptions
- **Logs:** https://dashboard.stripe.com/test/logs

---

## ✅ Success Indicators

You'll know it's working when you see:

1. **Stripe CLI shows:**
   ```
   > Ready! Your webhook signing secret is whsec_...
   2026-01-09 15:30:00   --> invoice.payment_succeeded [evt_abc123]
   2026-01-09 15:30:01   <--  [200] POST http://localhost:8000/api/webhooks/stripe [evt_abc123]
   ```

2. **Laravel logs show:**
   ```
   [INFO] Stripe webhook received: invoice.payment_succeeded
   [INFO] Invoice payment succeeded
   [INFO] Booking payment updated (booking_id: 123)
   ```

3. **Database shows:**
   ```
   payment_status: 'paid'
   next_payment_date: '2026-02-09 15:30:00'
   auto_pay_enabled: true
   ```

---

## 🆘 Need Help?

1. **Check logs:** `Get-Content storage\logs\laravel.log -Wait -Tail 20`
2. **Check Stripe Dashboard:** Webhooks section shows delivery status
3. **Verify secret:** Make sure `STRIPE_WEBHOOK_SECRET` is in `.env`
4. **Restart everything:** Stop all terminals and start fresh

---

**You're all set!** Start with `stripe listen` and then trigger some test events. Happy testing! 🚀

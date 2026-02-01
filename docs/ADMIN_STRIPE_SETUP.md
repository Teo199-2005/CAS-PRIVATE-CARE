# Admin Stripe Account Setup Guide

## Overview

CAS Private Care uses Stripe as the payment processor. The system uses a **Platform Model** where:
- **Platform Account (Admin)**: Receives all client payments directly
- **Connect Accounts (Caregivers/Housekeepers)**: Receive payouts from the platform

## How Payments Work

```
Client Payment → Admin's Stripe Account → Payouts to:
                                          ├── Admin's Bank (company profits)
                                          └── Caregivers/Housekeepers via Connect
```

## Setting Up Admin Stripe Account

### Step 1: Create a Stripe Account

1. Go to [https://stripe.com](https://stripe.com)
2. Click "Start Now" or "Create Account"
3. Complete the registration with your business information
4. Verify your email address

### Step 2: Get Your API Keys

1. Log into [Stripe Dashboard](https://dashboard.stripe.com)
2. Navigate to **Developers → API Keys**
3. Copy your keys:
   - **Publishable Key** (starts with `pk_live_` or `pk_test_`)
   - **Secret Key** (starts with `sk_live_` or `sk_test_`)

### Step 3: Configure Your .env File

Add or update these values in your `.env` file:

```env
# Stripe API Keys
STRIPE_KEY=pk_live_your_publishable_key_here
STRIPE_SECRET=sk_live_your_secret_key_here

# Stripe Webhook Secret (optional but recommended)
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### Step 4: Connect Your Bank Account

**This is the critical step for receiving payouts!**

1. Go to [Stripe Dashboard - Payouts](https://dashboard.stripe.com/settings/payouts)
2. Click **"Add bank account"** or **"Add external account"**
3. Enter your bank details:
   - Bank name
   - Routing number
   - Account number
4. Verify the bank account (may require micro-deposits)
5. Configure payout schedule (daily, weekly, monthly)

### Step 5: Set Up Webhooks (Recommended)

Webhooks notify your application about payment events.

1. Go to [Stripe Dashboard - Webhooks](https://dashboard.stripe.com/webhooks)
2. Click **"Add endpoint"**
3. Enter your webhook URL: `https://yourdomain.com/stripe/webhook`
4. Select events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
   - `account.updated`
5. Copy the **Signing Secret** and add it to your `.env` file

## Verifying Your Setup

After completing the setup, check the Admin Dashboard:

1. Log in as Admin
2. Go to **Payments → Company Account** tab
3. You should see:
   - ✅ Stripe Account: Active
   - ✅ Company Bank Account: Connected (shows last 4 digits)
   - ✅ Available Balance

## Troubleshooting

### "No Bank Connected" in Dashboard

**Solution**: Connect a bank account in Stripe Dashboard
- Go to [https://dashboard.stripe.com/settings/payouts](https://dashboard.stripe.com/settings/payouts)
- Click "Add bank account"

### "Account Pending" Status

**Solution**: Complete Stripe verification
- Check your Stripe Dashboard for required information
- Verify your identity and business details
- Submit any requested documents

### Payments Not Appearing

**Solution**: Check webhook configuration
- Verify webhook URL is correct
- Check webhook signing secret matches `.env`
- Review webhook logs in Stripe Dashboard

## Security Notes

⚠️ **Never share your Secret Key** - It provides full access to your Stripe account

⚠️ **Use Test Keys for Development** - Keys starting with `pk_test_` and `sk_test_`

⚠️ **Use Live Keys for Production** - Keys starting with `pk_live_` and `sk_live_`

## Quick Links

- [Stripe Dashboard](https://dashboard.stripe.com)
- [Payout Settings](https://dashboard.stripe.com/settings/payouts)
- [API Keys](https://dashboard.stripe.com/developers/api-keys)
- [Webhook Settings](https://dashboard.stripe.com/webhooks)
- [Stripe Documentation](https://stripe.com/docs)

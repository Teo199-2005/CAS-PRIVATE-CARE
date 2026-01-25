# 🔌 STRIPE INTEGRATION GUIDE - CAS PRIVATE CARE

**Date:** January 4, 2026  
**Purpose:** Connect your existing time-tracking system with Stripe payments

---

## 📋 OVERVIEW

This integration adds a complete financial layer to your existing website WITHOUT breaking your UI. It connects your clock-in/clock-out system to Stripe for:

1. ✅ **Client Payment Collection** (Setup Intents + Charges)
2. ✅ **Caregiver Payouts** (Stripe Connect)
3. ✅ **Partner Commissions** (Marketing & Training)
4. ✅ **Late-Check Prevention** (Minute-by-minute calculation)
5. ✅ **Payment Dashboard** (Admin oversight)

---

## 🚀 INSTALLATION STEPS

### **Step 1: Install Stripe PHP Library**

```bash
composer require stripe/stripe-php
```

### **Step 2: Add Stripe Keys to .env**

Add these to your `.env` file:

```env
# Stripe API Keys (Get from https://dashboard.stripe.com/test/apikeys)
STRIPE_KEY=pk_test_YOUR_PUBLISHABLE_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET

# Stripe Connect
STRIPE_CLIENT_ID=ca_YOUR_CLIENT_ID
```

### **Step 3: Run Database Migration**

```bash
php artisan migrate
```

This adds Stripe-related fields to your tables:
- `users.stripe_account_id` (for caregivers, marketing, training)
- `users.stripe_customer_id` (for clients)
- `time_trackings.stripe_transfer_id`
- `time_trackings.stripe_charge_id`

### **Step 4: Update Routes**

Routes are already added in the implementation files below.

### **Step 5: Register Vue Components**

Add to `resources/js/app.js`:

```javascript
import StripePaymentSetup from './components/StripePaymentSetup.vue';
import StripeCaregiverOnboarding from './components/StripeCaregiverOnboarding.vue';
import StripePaymentDashboard from './components/StripePaymentDashboard.vue';

// Register components
app.component('stripe-payment-setup', StripePaymentSetup);
app.component('stripe-caregiver-onboarding', StripeCaregiverOnboarding);
app.component('stripe-payment-dashboard', StripePaymentDashboard);
```

### **Step 6: Build Assets**

```bash
npm run build
```

---

## 📁 FILES CREATED

### **1. Backend (PHP/Laravel)**
- ✅ `app/Services/StripePaymentService.php` - Core payment logic
- ✅ `app/Http/Controllers/StripeController.php` - API endpoints
- ✅ `database/migrations/xxxx_add_stripe_fields.php` - Database updates
- ✅ `config/stripe.php` - Configuration file

### **2. Frontend (Vue.js)**
- ✅ `resources/js/components/StripePaymentSetup.vue` - Client payment method
- ✅ `resources/js/components/StripeCaregiverOnboarding.vue` - Caregiver bank setup
- ✅ `resources/js/components/StripePaymentDashboard.vue` - Admin payment control

### **3. Documentation**
- ✅ `STRIPE_INTEGRATION_GUIDE.md` (this file)
- ✅ `PAYMENT_DISTRIBUTION_ANALYSIS.md` (already exists)

---

## 💻 HOW IT WORKS

### **Workflow Overview**

```
1. CLIENT BOOKS SERVICE
   ↓
2. CLIENT ADDS PAYMENT METHOD (Stripe Setup Intent)
   ↓
3. CAREGIVER CLOCKS IN (existing system)
   ↓
4. CAREGIVER CLOCKS OUT (existing system)
   ↓
5. SYSTEM CALCULATES EXACT PAY (minute-by-minute)
   ↓
6. ADMIN CLICKS "PAY" BUTTON
   ↓
7. STRIPE CHARGES CLIENT
   ↓
8. STRIPE TRANSFERS TO CAREGIVER (& partners)
   ↓
9. EVERYONE GETS PAID ✅
```

---

## 🔐 SECURITY FEATURES

### **✅ Client Security**
- Card details never touch your server
- Stripe Elements for PCI compliance
- Setup Intents for future charges
- 3D Secure authentication

### **✅ Caregiver Security**
- No manual bank entry on your site
- Stripe Connect Express for onboarding
- Encrypted account storage
- Direct transfers (not through you)

### **✅ Late Check Prevention**
```php
// If scheduled for 8 hours but only worked 7h 15min:
$scheduledMinutes = 480; // 8 hours
$actualMinutes = 435;    // 7h 15min

if ($actualMinutes < $scheduledMinutes) {
    $payableHours = $actualMinutes / 60; // 7.25 hours
    $payment = $payableHours * 28; // $203.00 (not $224)
}
```

---

## 📊 PAYMENT CALCULATION LOGIC

### **Minute-by-Minute Calculation**

```php
// Example: Caregiver clocks in/out
Clock In:  09:00:00
Clock Out: 16:45:00
Duration:  7 hours 45 minutes = 465 minutes

// Calculate pay
$minutes = 465;
$hourlyRate = 28.00;
$payableHours = $minutes / 60; // 7.75 hours
$caregiverPay = $payableHours * $hourlyRate; // $217.00

// Marketing commission (if referral used)
$marketingPay = $payableHours * 1.00; // $7.75

// Training commission (if caregiver trained)
$trainingPay = $payableHours * 0.50; // $3.88

// Client charge
$clientRate = 40.00; // (with referral)
$clientCharge = $payableHours * $clientRate; // $310.00

// Agency keeps remainder
$agencyAmount = $clientCharge - ($caregiverPay + $marketingPay + $trainingPay);
// $310 - ($217 + $7.75 + $3.88) = $81.37
```

---

## 🎯 USAGE INSTRUCTIONS

### **For Clients**

1. **Add Payment Method**
   - Go to dashboard → "Payment Settings"
   - Click "Add Payment Method"
   - Enter card details in Stripe form
   - Card is saved but NOT charged yet

2. **Automatic Charging**
   - After caregiver completes work
   - Admin reviews and approves payment
   - You're charged for actual hours worked
   - Receive email receipt

### **For Caregivers**

1. **Connect Bank Account**
   - Go to dashboard → "Payment Settings"
   - Click "Connect Bank Account"
   - Redirected to Stripe to add bank info
   - Complete verification

2. **Receive Payouts**
   - Work hours are tracked automatically
   - Admin processes payments weekly
   - Money arrives in bank 1-2 business days
   - View payment history in dashboard

### **For Admin**

1. **Payment Dashboard**
   - View all pending payments
   - See exact breakdown (caregiver, marketing, training, agency)
   - Click "Pay" to process
   - System handles all transfers automatically

---

## 🧪 TESTING WITH STRIPE SANDBOX

### **Test Card Numbers**

```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
3D Secure: 4000 0025 0000 3155

Expiry: Any future date (e.g., 12/28)
CVC: Any 3 digits (e.g., 123)
ZIP: Any 5 digits (e.g., 10001)
```

### **Test Bank Accounts**

```
Routing: 110000000
Account: 000123456789
```

### **Testing Workflow**

1. **Setup Test Mode**
   - Use `pk_test_` and `sk_test_` keys
   - All transactions are fake
   - No real money moves

2. **Test Client Payment**
   ```bash
   # Client adds card
   → Use test card 4242 4242 4242 4242
   → System saves payment method
   
   # Caregiver works
   → Clock in/out normally
   
   # Admin processes payment
   → Click "Pay" button
   → See charge in Stripe Dashboard (test mode)
   ```

3. **Test Caregiver Payout**
   ```bash
   # Caregiver onboards
   → Click "Connect Bank"
   → Use test routing/account
   
   # Admin processes payment
   → Click "Pay"
   → See transfer in Stripe Dashboard
   ```

---

## ⚠️ IMPORTANT NOTES

### **1. Stripe Connect Setup**

Before going live, you must:
- Submit your platform for Stripe review
- Provide business details
- Verify your identity
- Enable Stripe Connect in dashboard

### **2. Payment Timing**

Current setup:
- ✅ Client charged: After work completed
- ✅ Caregiver paid: 1-2 business days
- ✅ Marketing paid: Monthly batch
- ✅ Training paid: Monthly batch

### **3. Fees**

Stripe fees (deducted from your platform):
- Card charge: 2.9% + $0.30
- Transfer to Connect account: No fee
- Payout to bank: No fee

Example:
```
Client charged: $320.00
Stripe fee: $9.58
You receive: $310.42
You transfer: $217.00 (caregiver)
You keep: $93.42 (minus other commissions)
```

### **4. Webhooks**

For production, set up webhooks:
```
payment_intent.succeeded
transfer.created
account.updated
```

URL: `https://yoursite.com/api/stripe/webhook`

---

## 🐛 TROUBLESHOOTING

### **"No such customer"**
- Client hasn't added payment method yet
- Run client onboarding first

### **"No such account"**
- Caregiver hasn't completed Stripe Connect
- Send them onboarding link

### **"Insufficient funds"**
- You need balance in platform account
- Wait for client charges to settle (2-7 days)
- Or add funds to Stripe account

### **"Transfer amount exceeds balance"**
- Client payment hasn't cleared yet
- Use `destination` charges instead of transfers
- Or wait for settlement

---

## 🚀 GOING LIVE

### **Before Launch Checklist**

- [ ] Replace test keys with live keys
- [ ] Complete Stripe business verification
- [ ] Set up webhook endpoints
- [ ] Test with real $1 transaction
- [ ] Update Terms of Service
- [ ] Add refund policy
- [ ] Train admin staff
- [ ] Document support procedures

### **Switching to Live Mode**

1. Update `.env`:
```env
STRIPE_KEY=pk_live_YOUR_LIVE_KEY
STRIPE_SECRET=sk_live_YOUR_LIVE_SECRET
```

2. Clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

3. Test with small real transaction

---

## 📞 SUPPORT

### **Stripe Documentation**
- https://stripe.com/docs/connect
- https://stripe.com/docs/payments/setup-intents

### **CAS Integration Help**
- Check `StripePaymentService.php` for logic
- Check `StripeController.php` for API endpoints
- Check Vue components for UI

### **Common Issues**
- API key errors → Check `.env` file
- Connection errors → Check internet/firewall
- UI not loading → Run `npm run build`

---

## ✅ SUCCESS CRITERIA

You'll know it's working when:

1. ✅ Client can add card without charge
2. ✅ Caregiver can connect bank account
3. ✅ Clock in/out calculates minutes correctly
4. ✅ Admin sees "Pay" button for pending payments
5. ✅ One click transfers money to all parties
6. ✅ Late clock-ins result in reduced pay
7. ✅ All transactions appear in Stripe Dashboard

---

**Ready to integrate? Follow the steps above and use the code files provided!**

*Generated: January 4, 2026*  
*Integration Version: 1.0*  
*Compatible with: Laravel 12.x, Vue 3.x, Stripe API 2024*

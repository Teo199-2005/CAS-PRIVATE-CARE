# 🔌 STRIPE INTEGRATION - COMPLETE IMPLEMENTATION GUIDE

**Date:** January 4, 2026  
**Purpose:** Integrate Stripe payment system with existing time-tracking infrastructure

---

## 📋 OVERVIEW

This implementation connects your **existing time-tracking system** with **Stripe** to:
1. ✅ Collect payments from clients
2. ✅ Pay out caregivers, marketing partners, and training centers
3. ✅ Handle minute-accurate calculations (prevent overpayment)
4. ✅ Check for late clock-ins
5. ✅ Automate weekly/monthly payouts

---

## 🎯 KEY FEATURES

### **1. Reconciliation Logic (Late Check)**
- **Calculates by the minute**: If caregiver works 7h 15min, they're paid for 7.25 hours
- **Late penalty tracking**: Records scheduled vs. actual clock-in time
- **Exact calculations**: 7 hours = exactly $196, not $196.01

### **2. Stripe Connect Onboarding**
- Secure bank account collection (no custom forms needed)
- Caregiver redirected to Stripe-hosted page
- Identity verification handled by Stripe

### **3. Client Payment Collection**
- Save card using Stripe Setup Intent (NO immediate charge)
- Charge AFTER caregiver clocks out (based on actual hours)
- Support for $40/hr (with referral) or $45/hr (without)

### **4. Automated Payouts**
- **Caregivers**: Weekly (every Friday)
- **Marketing Partners**: Monthly (1st of month)
- **Training Centers**: Monthly (1st of month)
- **Agency**: Keeps remainder automatically

---

## 📊 PAYMENT CALCULATION LOGIC

### **Formula: Minute-Accurate Payments**

```javascript
Total Minutes = clock_out_time - clock_in_time (in minutes)
Hours Worked = Total Minutes / 60 (decimal)

Caregiver Earnings = (Total Minutes / 60) * $28.00
Marketing Commission = (Total Minutes / 60) * $1.00 (if referral)
Training Commission = (Total Minutes / 60) * $0.50 (if training)
Agency Commission = Total Collected - (above 3)
```

### **Example 1: Exactly 8 Hours**
```
Clock In:  09:00:00
Clock Out: 17:00:00
Minutes: 480

Hours = 480 / 60 = 8.00
Caregiver = 8.00 * $28 = $224.00 (exact)
```

### **Example 2: 7 Hours 15 Minutes**
```
Clock In:  09:00:00
Clock Out: 16:15:00
Minutes: 435

Hours = 435 / 60 = 7.25
Caregiver = 7.25 * $28 = $203.00
```

### **Example 3: Late Clock-In**
```
Scheduled: 09:00:00
Actual:    09:12:00
Late by: 12 minutes

Work Time: 09:12:00 to 17:00:00 = 468 minutes
Hours = 468 / 60 = 7.80
Caregiver = 7.80 * $28 = $218.40 (NOT $224)
```

---

## 🏗️ ARCHITECTURE

```
┌─────────────────┐
│  Client Books   │
│   Service       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Save Payment   │
│  Method (Setup  │
│  Intent - NO    │
│  CHARGE YET)    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Caregiver      │
│  Assigned       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Clock In       │
│  (Record Time)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Clock Out      │
│  (Calculate     │
│   Minutes)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Charge Client  │
│  (Actual $)     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Distribute     │
│  Funds via      │
│  Stripe         │
│  Transfers      │
└─────────────────┘
```

---

## 📦 FILES TO CREATE

### **Backend (Laravel)**
1. `app/Services/StripePaymentService.php` - Core Stripe logic
2. `app/Http/Controllers/StripeController.php` - API endpoints
3. `app/Console/Commands/ProcessWeeklyPayouts.php` - Caregiver payouts
4. `app/Console/Commands/ProcessMonthlyPayouts.php` - Partner payouts
5. `config/stripe.php` - Configuration
6. `.env` - Add Stripe keys

### **Frontend (Vue.js)**
7. `resources/js/components/StripePaymentSetup.vue` - Client card setup
8. `resources/js/components/StripeOnboarding.vue` - Caregiver bank setup
9. `resources/js/components/PaymentHistory.vue` - Transaction history

### **Database Migrations**
10. `database/migrations/xxxx_add_stripe_fields.php` - Stripe IDs

---

## 🔑 STRIPE METADATA STRUCTURE

Every Stripe transaction will include metadata for tracking:

```json
{
  "time_tracking_id": "123",
  "caregiver_id": "45",
  "client_id": "67",
  "booking_id": "89",
  "hours_worked": "7.25",
  "minutes_worked": "435",
  "rate_type": "with_referral",
  "late_minutes": "12",
  "payment_type": "caregiver|marketing|training|client_charge"
}
```

---

## 🚀 IMPLEMENTATION STEPS

### **PHASE 1: Setup & Configuration**
- Install Stripe PHP SDK
- Add environment variables
- Create Stripe accounts (test mode)

### **PHASE 2: Client Payment Collection**
- Build "Save Card" component
- Create Setup Intent endpoint
- Store payment method ID

### **PHASE 3: Caregiver Onboarding**
- Create Connect onboarding endpoint
- Build redirect flow
- Verify bank account

### **PHASE 4: Payment Processing**
- Hook into clock-out event
- Calculate minute-accurate amounts
- Charge client
- Create transfers

### **PHASE 5: Automated Payouts**
- Weekly caregiver cron job
- Monthly partner cron job
- Email notifications

---

## 💰 PAYMENT FLOW DIAGRAM

```
CLIENT PAYMENT:
SetupIntent → Save PM → Clock Out → Calculate → Charge → Distribute
   (Book)    (Secure)    (Track)    (Minutes)   (Actual)  (Splits)

CAREGIVER PAYOUT:
Weekly Job → Query Pending → Calculate Total → Stripe Transfer → Mark Paid
  (Friday)    (time_trackings)   (Sum $)         (Bank)        (Update DB)

PARTNER PAYOUT:
Monthly Job → Query by Partner → Sum Commission → Transfer → Mark Paid
  (1st)        (Filter)            (Total)        (Bank)    (Update)
```

---

## 🧪 TESTING SCENARIOS

### **Scenario 1: Perfect 8-Hour Shift**
```
Input:
- Clock In: 09:00:00
- Clock Out: 17:00:00
- Scheduled: 09:00:00

Expected:
- Minutes: 480
- Hours: 8.00
- Caregiver: $224.00
- Late: No
```

### **Scenario 2: 7h 15min Shift**
```
Input:
- Clock In: 09:00:00
- Clock Out: 16:15:00
- Scheduled: 09:00:00

Expected:
- Minutes: 435
- Hours: 7.25
- Caregiver: $203.00
- Late: No
```

### **Scenario 3: Late Clock-In**
```
Input:
- Clock In: 09:12:00 (12 min late)
- Clock Out: 17:00:00
- Scheduled: 09:00:00

Expected:
- Minutes: 468
- Hours: 7.80
- Caregiver: $218.40
- Late: Yes (12 min)
- Client NOT charged for late time
```

### **Scenario 4: With Referral Code**
```
Input:
- Hours: 8.00
- Has Referral: Yes
- Has Training: Yes

Expected:
- Client Charged: $320 (8 × $40)
- Caregiver: $224
- Marketing: $8
- Training: $4
- Agency: $84
```

---

## 📝 NEXT STEPS

1. **Review this document** completely
2. **Set up Stripe account** (test mode)
3. **Install dependencies**: `composer require stripe/stripe-php`
4. **Create files** in order (I'll generate them next)
5. **Test with Stripe test cards**
6. **Deploy to production** when ready

---

## 🔐 SECURITY NOTES

- ✅ Never store card numbers in your database
- ✅ Use Stripe Customer IDs only
- ✅ All payments in test mode until verified
- ✅ Webhook signatures verified
- ✅ Sensitive data encrypted

---

## 📞 SUPPORT

- Stripe Dashboard: https://dashboard.stripe.com
- Stripe Docs: https://stripe.com/docs
- Test Cards: https://stripe.com/docs/testing

---

**Ready to implement? Say "generate the code" and I'll create all the files!**

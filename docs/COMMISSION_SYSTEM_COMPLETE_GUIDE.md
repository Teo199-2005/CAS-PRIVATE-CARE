# 💰 COMMISSION SYSTEM - COMPLETE GUIDE

## Overview

Your website has a **fully automated commission tracking system** that calculates and distributes earnings to **Marketing Staff** and **Training Centers** based on actual hours worked by caregivers.

---

## 🎯 **HOW IT WORKS**

### **Commission Flow**

```
1. Client books caregiver (with or without referral code)
   ↓
2. Admin assigns caregiver to booking
   ↓
3. Caregiver clocks in/out (records actual hours)
   ↓
4. System AUTOMATICALLY calculates commissions:
   - Caregiver: $28/hour
   - Marketing (if referral used): tier-based $1.00–$1.50/hr (see Partner Tiers below)
   - Training Center (if caregiver trained): $2/hour (old) or $0.50/hour (new hourly system)
   - Agency: Remainder
   ↓
5. Commissions stored in `time_trackings` table
   ↓
6. Marketing/Training dashboards show real-time earnings
   ↓
7. Admin processes payments (weekly/monthly)
```

---

## 💵 **PRICING BREAKDOWN**

### **Scenario 1: With Referral Code + Training Center**
**Client Pays:** `$40/hour`

```
Caregiver:       $28.00/hr  (70%)
Marketing Staff: $ 1.00/hr  (2.5%)  ← Earns commission
Training Center: $ 0.50/hr  (1.25%) ← Earns commission
Agency:          $10.50/hr  (26.25%)
────────────────────────────
TOTAL:           $40.00/hr
```

**Example:** Caregiver works 8 hours
- Client charged: **$320**
- Marketing earns: **$8**
- Training earns: **$4**

---

### **Scenario 2: No Referral + Training Center**
**Client Pays:** `$45/hour`

```
Caregiver:       $28.00/hr  (62%)
Training Center: $ 0.50/hr  (1.1%)  ← Earns commission
Agency:          $16.50/hr  (36.9%)
────────────────────────────
TOTAL:           $45.00/hr
```

**Example:** Caregiver works 8 hours
- Client charged: **$360**
- Marketing earns: **$0** (no referral)
- Training earns: **$4**

---

### **Scenario 3: With Referral + NO Training Center**
**Client Pays:** `$40/hour`

```
Caregiver:       $28.00/hr  (70%)
Marketing Staff: $ 1.00/hr  (2.5%)  ← Earns commission
Agency:          $11.00/hr  (27.5%)  ← Gets training's $0.50
────────────────────────────
TOTAL:           $40.00/hr
```

---

### **Scenario 4: No Referral + NO Training Center**
**Client Pays:** `$45/hour`

```
Caregiver:       $28.00/hr  (62%)
Agency:          $17.00/hr  (38%)  ← Gets training's $0.50
────────────────────────────
TOTAL:           $45.00/hr
```

---

## 🔄 **COMMISSION TRACKING**

### **Marketing Staff Commissions**

#### **How Marketing Earns:**
1. Marketing staff gets a **referral code** (e.g., `STAFF-001`)
2. Client uses code when booking → Client gets **$3/hour discount** ($45 → $42 with referral)
3. Caregiver assigned to booking clocks in/out
4. System **automatically** calculates: `hours_worked × tier_rate` → Marketing commission (see **Partner Tiers** below)
5. Stored in `time_trackings.marketing_partner_commission`

#### **Marketing Partner Tiers (commission per hour)**

| Tier              | Paid clients   | Commission rate |
|-------------------|----------------|-----------------|
| **Silver Partner**| 1–5            | **$1.00/hr**    |
| **Gold Partner**  | 6–10           | **$1.25/hr**    |
| **Platinum Partner** | 11+          | **$1.50/hr**    |

- “Paid clients” = unique clients who have actually paid (charge recorded or booking `payment_status = paid`). This prevents fake clients from inflating tier.
- Tier is calculated automatically from the partner’s paid client count; commission uses the current tier rate at clock-out/payment.

#### **Database Structure:**
```sql
time_trackings table:
├── marketing_partner_id        (FK to users table)
├── marketing_partner_commission (tier_rate × hours_worked)
└── payment_status              (pending/paid)
```

#### **Commission Rate:**
- **Tier-based per hour** ($1.00 / $1.25 / $1.50) for every hour worked by referred client's caregiver
- Tracked **per time tracking entry** (not per booking)

#### **Example:**
```
Marketing staff "Sarah" has referral code: STAFF-247
Client "John" uses STAFF-247 when booking
Caregiver "Maria" assigned to John
Week 1: Maria works 40 hours → Sarah earns $40
Week 2: Maria works 35 hours → Sarah earns $35
Week 3: Maria works 42 hours → Sarah earns $42
──────────────────────────────────────────
Total earnings: $117
```

---

### **Training Center Commissions**

#### **How Training Centers Earn:**
1. Caregiver **completes training** at a training center
2. `caregivers.training_center_id` = Training Center User ID
3. `caregivers.has_training_center` = true
4. Caregiver gets assigned to bookings and clocks in/out
5. System **automatically** calculates: `hours_worked × $0.50` → Training commission
6. Stored in `time_trackings.training_center_commission`

#### **Database Structure:**
```sql
caregivers table:
├── training_center_id               (FK to users table)
├── has_training_center              (boolean)
└── training_center_approval_status  (pending/approved)

time_trackings table:
├── training_center_user_id          (FK to users table)
├── training_center_commission       ($0.50 × hours_worked)
└── payment_status                   (pending/paid)
```

#### **Commission Rate:**
- **$0.50 per hour** for every hour worked by trained caregivers
- **NOTE:** Some documentation mentions **$2.00/hour** - need to verify which is current

#### **Example:**
```
Training Center "NYC Care Academy" trains caregivers:
├── Maria (Caregiver #1)
├── John (Caregiver #2)
└── Sarah (Caregiver #3)

Maria works 40 hours → Training earns $20
John works 35 hours  → Training earns $17.50
Sarah works 38 hours → Training earns $19
──────────────────────────────────────────
Total earnings: $56.50 for the week
```

---

## 📊 **WHERE COMMISSIONS ARE DISPLAYED**

### **Marketing Dashboard**
**Location:** `MarketingDashboard.vue` → "Payments" section

**Shows:**
1. **Total Commission Earned** (all time)
2. **This Month's Commission**
3. **Commission per Client** (in client list)
4. **Payment Settings** (Weekly payout, $1/hour rate)
5. **Bank Account Connection** (Stripe Connect)

**API Endpoint:**
```php
GET /api/referral-codes/commission-stats
Returns:
{
  total_commission: 1250.00,
  pending_commission: 180.00,
  account_balance: 1250.00,
  clients: [
    { name: "John Doe", totalHours: 120, commission: 120.00 },
    ...
  ]
}
```

---

### **Training Dashboard**
**Location:** `TrainingDashboard.vue` → "Payments" section

**Shows:**
1. **Total Revenue** (from `time_trackings.training_center_commission`)
2. **Monthly Revenue**
3. **Commission per Caregiver**
4. **Payment Settings** (Weekly payout, $2/hour or $0.50/hour rate)
5. **Bank Account Connection** (Stripe Connect)

**Calculation:**
```php
// AdminController.php - getTrainingCenters()
$commissionEarned = DB::table('time_trackings')
    ->where('training_center_user_id', $trainingCenterId)
    ->whereNotNull('training_center_commission')
    ->sum('training_center_commission');
```

---

### **Admin Dashboard**
**Location:** `AdminDashboard.vue` → "Marketing Staff" and "Training Centers" tabs

**Marketing Tab Shows:**
- Staff Name, Email, Status
- **Commission Earned** (from `time_trackings.marketing_partner_commission`)
- Referral Code
- Clients Referred

**Training Tab Shows:**
- Center Name, Email, Status
- **Commission Earned** (from `time_trackings.training_center_commission`)
- Caregivers Trained
- Total Hours

---

## 🔧 **BACKEND IMPLEMENTATION**

### **Automatic Commission Calculation**
**File:** `app/Http/Controllers/TimeTrackingController.php`

**When Caregiver Clocks Out:**
```php
private function calculateEarnings(TimeTracking $timeTracking)
{
    $hoursWorked = $timeTracking->hours_worked;
    
    // Get booking and check for referral code
    $booking = $assignment->booking;
    $marketingCommission = 0;
    $marketingPartnerId = null;
    
    if ($booking->referral_code_id) {
        $referralCode = $booking->referralCode;
        $marketingPartnerId = $referralCode->user_id;
        $marketingCommission = $hoursWorked * 1.00; // $1/hr
    }
    
    // Check if caregiver has training center
    $trainingCommission = 0;
    $trainingCenterId = null;
    
    if ($caregiver->has_training_center && $caregiver->training_center_id) {
        $trainingCenterId = $caregiver->training_center_id;
        $trainingCommission = $hoursWorked * 0.50; // $0.50/hr
    }
    
    // Update time tracking
    $timeTracking->update([
        'marketing_partner_id' => $marketingPartnerId,
        'marketing_partner_commission' => $marketingCommission,
        'training_center_user_id' => $trainingCenterId,
        'training_center_commission' => $trainingCommission,
        'payment_status' => 'pending'
    ]);
}
```

---

### **Commission Retrieval**
**File:** `app/Http/Controllers/AdminController.php`

**Marketing Commissions:**
```php
public function getMarketingStaff()
{
    $commissionEarned = TimeTracking::where('marketing_partner_id', $userId)
        ->whereNotNull('marketing_partner_commission')
        ->sum('marketing_partner_commission');
}
```

**Training Commissions:**
```php
public function getTrainingCenters()
{
    $commissionEarned = TimeTracking::where('training_center_user_id', $userId)
        ->whereNotNull('training_center_commission')
        ->sum('training_center_commission');
}
```

---

## 💳 **PAYMENT PROCESSING**

### **Current Payment Integration**

**✅ COMPLETED:**
- Stripe Connect bank account onboarding for:
  - Caregivers (`/connect-bank-account`)
  - Marketing Staff (`/connect-bank-account-marketing`)
  - Training Centers (`/connect-bank-account-training`)

**⏳ PENDING:**
- Automated payout processing
- Admin "Pay" button functionality
- Payment history tracking

---

### **Payment Flow (To Be Implemented)**

```
1. Admin Dashboard → "Marketing Staff" or "Training Centers" tab
   ↓
2. View pending commissions
   ↓
3. Click "Pay" button
   ↓
4. System uses Stripe Transfer API:
   POST /api/stripe/pay-marketing-commission/{userId}
   POST /api/stripe/pay-training-commission/{userId}
   ↓
5. Transfer funds from platform balance to partner's bank account
   ↓
6. Update time_trackings.payment_status = 'paid'
   ↓
7. Update time_trackings.paid_at = NOW()
```

---

## 📁 **KEY FILES**

### **Frontend Components**
1. **`resources/js/components/MarketingDashboard.vue`**
   - Lines 280-370: Payment section with Stripe Connect
   - Lines 708-712: Commission calculations
   - Shows real-time commission totals

2. **`resources/js/components/TrainingDashboard.vue`**
   - Lines 478-560: Payment section with Stripe Connect
   - Shows commission summary and payment settings

3. **`resources/js/components/AdminDashboard.vue`**
   - Lines 800-950: Marketing staff management
   - Lines 1300-1450: Training center management
   - Displays commission earned for each partner

4. **`resources/js/components/CustomBankOnboarding.vue`**
   - Role-aware bank onboarding
   - Lines 386-407: Role-specific configuration

---

### **Backend Controllers**
1. **`app/Http/Controllers/TimeTrackingController.php`**
   - Line 94-170: `calculateEarnings()` method
   - Automatically calculates all commissions on clock out

2. **`app/Http/Controllers/AdminController.php`**
   - Line 850-920: `getMarketingStaff()` - Shows marketing commissions
   - Line 1040-1110: `getTrainingCenters()` - Shows training commissions

3. **`app/Services/StripePaymentService.php`**
   - Line 610-650: `transferToMarketing()` - Transfers money to marketing staff
   - Line 798-820: Monthly marketing payout processing
   - **NEEDS:** `transferToTraining()` method (similar to caregiver transfer)

---

### **Database Migrations**
1. **`database/migrations/2025_12_29_000001_add_hourly_payment_tracking_to_time_trackings.php`**
   - Adds `marketing_partner_id`, `marketing_partner_commission`
   - Adds `training_center_user_id`, `training_center_commission`
   - Adds `agency_commission`, `total_client_charge`
   - Adds `payment_status`, `paid_at`

2. **`database/migrations/2025_12_21_110000_add_training_center_to_caregivers.php`**
   - Adds `training_center_id` to caregivers
   - Links caregivers to training centers

---

### **Routes**
```php
// Marketing Commission Stats
GET /api/referral-codes/commission-stats

// Bank Onboarding
GET /connect-bank-account-marketing
GET /connect-bank-account-training

// Payment Processing (To Be Added)
POST /api/stripe/pay-marketing-commission/{userId}
POST /api/stripe/pay-training-commission/{userId}
```

---

## 🧪 **TESTING THE SYSTEM**

### **Test Marketing Commissions**

1. **Create referral code:**
   - Login as marketing@demo.com
   - Go to dashboard → View referral code (e.g., `STAFF-001`)

2. **Client uses referral code:**
   - Create booking with referral code
   - Client pays $40/hour instead of $45/hour

3. **Caregiver works:**
   - Caregiver clocks in/out for 8 hours

4. **Check commission:**
   - Marketing dashboard shows: **$8.00** earned
   - Database query:
   ```sql
   SELECT marketing_partner_commission 
   FROM time_trackings 
   WHERE marketing_partner_id = [marketing_user_id]
   -- Should show: 8.00
   ```

---

### **Test Training Commissions**

1. **Assign training center to caregiver:**
   - Admin dashboard → Caregivers → Edit caregiver
   - Set training center = "NYC Care Academy"
   - Approve training center approval

2. **Caregiver works:**
   - Caregiver clocks in/out for 8 hours

3. **Check commission:**
   - Training dashboard shows: **$4.00** earned (if $0.50/hr)
   - Database query:
   ```sql
   SELECT training_center_commission 
   FROM time_trackings 
   WHERE training_center_user_id = [training_center_id]
   -- Should show: 4.00
   ```

---

## 🚀 **NEXT STEPS TO COMPLETE SYSTEM**

### **1. Add Training Center Payout Method** ✅ DONE
- ✅ Created `/connect-bank-account-training` route
- ✅ Created training bank onboarding blade view
- ✅ Updated `CustomBankOnboarding.vue` to be role-aware

### **2. Add Marketing Payout Method** ✅ DONE
- ✅ Created `/connect-bank-account-marketing` route
- ✅ Created marketing bank onboarding blade view
- ✅ Updated app.js to mount marketing/training bank apps

### **3. Add Backend Payment Controllers** ⏳ PENDING
```php
// app/Http/Controllers/StripeController.php

public function payMarketingCommission($userId)
{
    // Sum all pending marketing commissions
    $totalCommission = TimeTracking::where('marketing_partner_id', $userId)
        ->where('payment_status', 'pending')
        ->sum('marketing_partner_commission');
    
    // Transfer to marketing staff's bank account
    $result = $this->stripeService->transferToMarketing($user, $totalCommission);
    
    // Mark as paid
    TimeTracking::where('marketing_partner_id', $userId)
        ->where('payment_status', 'pending')
        ->update([
            'payment_status' => 'paid',
            'paid_at' => now()
        ]);
}

public function payTrainingCommission($userId)
{
    // Similar to marketing payment
}
```

### **4. Add "Pay" Button in Admin Dashboard** ⏳ PENDING
```vue
<!-- AdminDashboard.vue -->
<v-btn 
  color="success" 
  @click="payCommission(staff.id, 'marketing')"
>
  Pay ${{ staff.commissionEarned }}
</v-btn>
```

### **5. Build Frontend** ⏳ PENDING
```bash
npm run build
```

### **6. Test Complete Flow** ⏳ PENDING
- Marketing staff connects bank account
- Admin pays commission
- Verify Stripe transfer
- Verify database updated (payment_status = 'paid')

---

## 📝 **SUMMARY**

### **How Marketing Earns:**
1. Create referral code
2. Client uses code → Gets $5/hour discount
3. Caregiver works → Marketing earns $1/hour
4. Commission stored in `time_trackings` table
5. Dashboard shows real-time earnings
6. Admin pays weekly/monthly via Stripe Connect

### **How Training Centers Earn:**
1. Train caregivers
2. Caregiver gets assigned to bookings
3. Caregiver works → Training earns $0.50/hour
4. Commission stored in `time_trackings` table
5. Dashboard shows real-time earnings
6. Admin pays weekly/monthly via Stripe Connect

---

## ✅ **WHAT'S WORKING NOW**

✅ Commission calculation (automatic on clock out)  
✅ Commission tracking in database  
✅ Commission display in marketing dashboard  
✅ Commission display in training dashboard  
✅ Commission display in admin dashboard  
✅ Bank account onboarding (Stripe Connect)  
✅ Referral code system  
✅ Training center assignment  

---

## ⏳ **WHAT NEEDS TO BE ADDED**

⏳ Admin "Pay" button functionality  
⏳ Automated weekly/monthly payouts  
⏳ Payment history tracking  
⏳ Commission reports/exports  
⏳ Email notifications for payments  

---

**Last Updated:** January 5, 2026  
**Status:** Commission tracking fully functional, payout processing pending implementation

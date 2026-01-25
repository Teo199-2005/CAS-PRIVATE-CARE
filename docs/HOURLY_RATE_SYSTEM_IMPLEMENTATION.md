# HOURLY RATE SYSTEM IMPLEMENTATION

**Implementation Date:** December 29, 2025

---

## 📊 OVERVIEW

The system has been updated to calculate earnings based on **actual hours worked** rather than per-booking totals. This ensures accurate, hourly-based payment distribution across all stakeholders.

---

## 💰 HOURLY RATE STRUCTURE

### **Base Rates**
- **Caregiver:** $28.00/hour
- **Marketing Partner:** $1.00/hour (only if booking used referral code)
- **Training Center:** $0.50/hour (only if caregiver has training center)
- **Agency (CAS):** Remainder after all splits

### **Client Pricing**
- **With Referral Code:** $40.00/hour
  - Caregiver: $28.00
  - Marketing: $1.00
  - Training: $0.50
  - Agency: $10.50
  
- **Without Referral Code:** $45.00/hour
  - Caregiver: $28.00
  - Training: $0.50
  - Agency: $16.50

---

## 🔄 HOW IT WORKS

### **1. Assignment Order (Multiple Caregivers)**

When a booking requires multiple caregivers (e.g., 45-day booking = 3 caregivers):

```
Booking Duration: 45 days
Caregivers Needed: 3 (1 per 15 days)

Assignment Order:
├─ Caregiver 1 (ORDER: 1, ACTIVE: ✅)
│  ├─ Start Date: Service Date
│  ├─ End Date: Service Date + 14 days
│  ├─ Expected Days: 15
│  └─ Status: Can clock in/out for time tracking
│
├─ Caregiver 2 (ORDER: 2, ACTIVE: ❌)
│  ├─ Start Date: Service Date + 15 days
│  ├─ End Date: Service Date + 29 days
│  ├─ Expected Days: 15
│  └─ Status: Waiting for Caregiver 1 to complete
│
└─ Caregiver 3 (ORDER: 3, ACTIVE: ❌)
   ├─ Start Date: Service Date + 30 days
   ├─ End Date: Service Date + 44 days
   ├─ Expected Days: 15
   └─ Status: Waiting for Caregiver 2 to complete
```

**Key Points:**
- Only the **active caregiver** (is_active = true) can clock in/out
- The first caregiver (assignment_order = 1) is automatically set as active
- When Caregiver 1 completes their 15 days, Caregiver 2 becomes active
- This prevents multiple caregivers from clocking in simultaneously

---

### **2. Time Tracking & Earnings Calculation**

#### **Clock In**
```php
POST /api/time-tracking/clock-in
{
  "caregiver_id": 1,
  "client_id": 1,
  "location": "Client Home"
}
```

Creates active session:
- `status`: 'active'
- `clock_in_time`: Current timestamp
- `work_date`: Today's date

#### **Clock Out**
```php
POST /api/time-tracking/clock-out
{
  "caregiver_id": 1
}
```

Automatically calculates:
1. **Hours Worked** = clock_out_time - clock_in_time
2. **Caregiver Earnings** = hours_worked × $28.00
3. **Marketing Commission** = hours_worked × $1.00 (if applicable)
4. **Training Commission** = hours_worked × $0.50 (if applicable)
5. **Agency Commission** = total_client_charge - (caregiver + marketing + training)
6. **Total Client Charge** = hours_worked × ($40 or $45)

#### **Example Calculation**

**Scenario:** Caregiver works 8 hours, booking has referral code, caregiver has training center

```
Hours Worked: 8.0 hours

Caregiver Earnings:
  8.0 × $28.00 = $224.00 ✅

Marketing Commission:
  8.0 × $1.00 = $8.00 ✅

Training Commission:
  8.0 × $0.50 = $4.00 ✅

Total Client Charge:
  8.0 × $40.00 = $320.00

Agency Commission:
  $320.00 - $224.00 - $8.00 - $4.00 = $84.00 ✅
```

**Result stored in time_trackings table:**
```sql
caregiver_earnings: 224.00
marketing_partner_commission: 8.00
training_center_commission: 4.00
agency_commission: 84.00
total_client_charge: 320.00
payment_status: 'pending'
```

---

### **3. Weekly Earnings Calculation**

Caregiver's weekly earnings = **SUM** of all `caregiver_earnings` from `time_trackings` for the current week.

**OLD SYSTEM (Wrong):**
```php
// ❌ Weekly Earnings = Total Booking Price immediately on assignment
$weeklyEarnings = $booking->total_budget;
```

**NEW SYSTEM (Correct):**
```php
// ✅ Weekly Earnings = Sum of actual hours worked × $28/hr
$weeklyEarnings = TimeTracking::where('caregiver_id', $id)
    ->whereBetween('work_date', [$weekStart, $weekEnd])
    ->sum('caregiver_earnings');
```

---

## 🗄️ DATABASE CHANGES

### **New Tables/Columns**

#### **time_trackings** (updated)
```sql
- caregiver_earnings DECIMAL(8,2)          -- $28 × hours_worked
- marketing_partner_id BIGINT (FK users)   -- Who gets marketing commission
- marketing_partner_commission DECIMAL     -- $1 × hours_worked
- training_center_user_id BIGINT (FK users)-- Training center
- training_center_commission DECIMAL       -- $0.50 × hours_worked  
- agency_commission DECIMAL                -- Remainder
- total_client_charge DECIMAL              -- Total charged to client
- paid_at TIMESTAMP                        -- Payment timestamp
- payment_status ENUM                      -- pending/processing/paid/failed
- booking_id BIGINT (FK bookings)          -- Link to booking
```

#### **booking_assignments** (updated)
```sql
- assignment_order INT                     -- 1st, 2nd, 3rd caregiver
- is_active BOOLEAN                        -- Can this caregiver clock in?
- start_date DATE                          -- When this caregiver starts
- end_date DATE                            -- When this caregiver ends
- expected_days INT                        -- Expected number of days (usually 15)
```

---

## 📱 AFFECTED COMPONENTS

### **Backend Controllers**

✅ **TimeTrackingController.php**
- `clockOut()` - Calculates all earnings and commissions
- `calculateEarnings()` - NEW: Hourly rate calculation logic

✅ **AdminController.php**
- `assignCaregivers()` - Sets assignment_order and activates first caregiver
- `getCaregiverSalaries()` - Already uses time_trackings (correct)

✅ **CaregiverController.php**
- `getEarningsReport()` - Already uses time_trackings (correct)
- `getAvailableJobs()` - Shows "estimated" earnings, not actual

### **Frontend Components**

✅ **CaregiverDashboard.vue**
- Weekly Earnings card - Loads from `/api/caregiver/${id}/earnings-report`
- Already correct, uses time_trackings API

🔄 **TrainingDashboard.vue** (Needs Update)
- Should show sum of `training_center_commission` from time_trackings

🔄 **MarketingDashboard.vue** (Needs Update)
- Should show sum of `marketing_partner_commission` from time_trackings

✅ **AdminDashboard.vue**
- Salary reports - Already uses time_trackings (correct)

---

## 🎯 PAYMENT WORKFLOW

### **For Caregivers**
1. Accept assignment (no earnings yet)
2. Clock in when arriving at client
3. Clock out when leaving (earnings calculated)
4. View pending earnings in dashboard
5. Receive weekly payout (Friday)

### **For Training Centers**
1. Caregiver completes training
2. Caregiver gets assigned to booking
3. Each time caregiver clocks out, $0.50/hr added to training center
4. Training center sees commission in dashboard
5. Receives monthly payout

### **For Marketing Partners**
1. Create referral code
2. Client uses code when booking
3. Each time assigned caregiver clocks out, $1.00/hr added to marketing partner
4. Marketing partner sees commission in dashboard
5. Receives monthly payout

---

## 🚀 BENEFITS

✅ **Accurate Payment** - Only pay for hours actually worked, not estimated  
✅ **Fair Distribution** - Marketing and training earn based on real work  
✅ **Flexibility** - If caregiver works 4 hrs instead of 8, payment reflects that  
✅ **Transparency** - All stakeholders see exact hourly breakdown  
✅ **Scalability** - Easy to add new commission types or adjust rates  

---

## 🔒 PAYMENT TRACKING

### **Payment Status Flow**
```
pending → processing → paid
                    ↓
                  failed (retry)
```

### **Admin Payment Interface** (To Be Built)
- View all pending time_trackings
- Filter by caregiver/date/status
- Mark multiple entries as "Paid"
- Export payment reports
- Track payment history

---

## 📊 EXAMPLE SCENARIOS

### **Scenario 1: Full Week (40 hours)**
```
Mon: 8 hrs × $28 = $224
Tue: 8 hrs × $28 = $224
Wed: 8 hrs × $28 = $224
Thu: 8 hrs × $28 = $224
Fri: 8 hrs × $28 = $224
─────────────────────────
Weekly Earnings: $1,120
```

### **Scenario 2: Partial Week (Sick Day)**
```
Mon: 8 hrs × $28 = $224
Tue: 0 hrs × $28 = $0    (called in sick)
Wed: 8 hrs × $28 = $224
Thu: 8 hrs × $28 = $224
Fri: 8 hrs × $28 = $224
─────────────────────────
Weekly Earnings: $896
```

### **Scenario 3: Overtime (10 hours/day)**
```
Mon: 10 hrs × $28 = $280
Tue: 10 hrs × $28 = $280
Wed: 10 hrs × $28 = $280
Thu: 10 hrs × $28 = $280
Fri: 10 hrs × $28 = $280
─────────────────────────
Weekly Earnings: $1,400
```

---

## 🔧 MAINTENANCE

### **Adjusting Rates**
To change hourly rates, update:
```php
// TimeTrackingController.php - calculateEarnings()
$caregiverRate = 28.00;  // Change here
$marketingRate = 1.00;   // Change here
$trainingRate = 0.50;    // Change here
```

### **Changing Days Per Caregiver**
To change from 15 days to another value:
```php
// AdminController.php - assignCaregivers()
$daysPerCaregiver = 15;  // Change here
```

---

## ✅ IMPLEMENTATION STATUS

**Database:**
- ✅ Migration: time_trackings hourly payment fields
- ✅ Migration: booking_assignments order tracking
- ✅ Models updated with new fillable fields

**Backend:**
- ✅ TimeTrackingController: Earnings calculation on clock-out
- ✅ AdminController: Assignment order handling
- ✅ CaregiverController: Already using time_trackings

**Frontend:**
- ✅ CaregiverDashboard: Already correct
- 🔄 TrainingDashboard: Needs time_trackings integration
- 🔄 MarketingDashboard: Needs time_trackings integration
- ✅ AdminDashboard: Already correct

**Testing:**
- ⏳ Test multi-caregiver assignment order
- ⏳ Test clock-in/out earnings calculation
- ⏳ Test weekly earnings aggregation
- ⏳ Test commission distribution

---

## 🎓 DEVELOPER NOTES

**Key Files:**
- `/database/migrations/2025_12_29_000001_add_hourly_payment_tracking_to_time_trackings.php`
- `/database/migrations/2025_12_29_000002_add_assignment_order_to_booking_assignments.php`
- `/app/Http/Controllers/TimeTrackingController.php` - Line 92-170 (calculateEarnings)
- `/app/Http/Controllers/AdminController.php` - Line 701-734 (assignCaregivers)

**Testing Checklist:**
1. Create booking with 3 caregivers needed
2. Assign 3 caregivers → Check assignment_order (1,2,3)
3. Verify only first caregiver has is_active = true
4. First caregiver clock in/out → Check earnings calculated
5. Verify marketing/training commissions if applicable
6. Check weekly earnings = sum of all caregiver_earnings
7. Verify other caregivers cannot clock in yet

---

**End of Documentation**

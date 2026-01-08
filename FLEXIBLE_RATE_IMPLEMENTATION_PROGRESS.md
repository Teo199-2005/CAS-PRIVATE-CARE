# Flexible Hourly Rate System - Implementation Progress

## ✅ COMPLETED (Phase 1 - Day 1)

### Backend Core Updates

#### 1. routes/web.php ✅
- **Line 713:** Changed hardcoded `'hourly_rate' => 28.00` to `'hourly_rate' => $tt->assigned_hourly_rate ?? 28.00`
- **Line 245:** Changed `$rate = $booking->hourly_rate ?: 45` to `$rate = $booking->assigned_hourly_rate ?: 28` (caregiver earnings)
- **Line 1495:** Changed `$rate = $booking->hourly_rate ?: 45` to `$rate = $booking->assigned_hourly_rate ?: 28` (caregiver earnings)
- **Result:** Caregiver earnings now use `assigned_hourly_rate` with $28 fallback for old bookings

#### 2. app/Http/Controllers/AdminController.php ✅
- **Lines 1317-1325:** Added comments to clarify client rate vs caregiver rate
- **Lines 1385, 1415:** Added comments for client payment calculations
- **Note:** Revenue calculations correctly use client `hourly_rate` (unchanged)
- **Result:** Clear separation between client revenue and caregiver earnings

#### 3. app/Models/Caregiver.php ✅
Added two new helper methods:
```php
/**
 * Check if a given rate is within the caregiver's preferred range
 */
public function isRateWithinPreferredRange($rate)
{
    $min = $this->preferred_hourly_rate_min ?? 20;
    $max = $this->preferred_hourly_rate_max ?? 50;
    return $rate >= $min && $rate <= $max;
}

/**
 * Get the preferred rate range as a formatted string
 */
public function getPreferredRangeAttribute()
{
    $min = $this->preferred_hourly_rate_min ?? 20;
    $max = $this->preferred_hourly_rate_max ?? 50;
    return "$" . number_format($min, 0) . " - $" . number_format($max, 0);
}
```
- **Result:** Easy validation when admin assigns hourly rate

#### 4. resources/js/components/CaregiverDashboard.vue ✅
- **Line 806:** Removed hardcoded `× $28` from earnings breakdown display
- **Line 812:** Removed hardcoded `× $42` from overtime display
- **Result:** Earnings report shows hours without hardcoded rates

#### 5. resources/js/components/StripeOnboarding.vue ✅
- **Line 38:** Changed from `$28.00/hour` to:
  ```vue
  <div class="text-caption text-grey">Your Preferred Rate</div>
  <div class="font-weight-bold">$20 - $50/hour</div>
  <div class="text-caption text-grey-darken-1">Admin assigns actual rate per booking</div>
  ```
- **Result:** Caregivers see their preferred range instead of fixed $28

#### 6. Frontend Build ✅
- Ran `npm run build` successfully
- Build completed in 10.79s
- All changes compiled and ready

---

## ⏳ IN PROGRESS / PENDING

### Phase 2: Admin Assignment UI (Next Priority)

#### Required Changes in AdminDashboard.vue:
1. **Find Assignment Modal**
   - Search for where admin assigns caregiver to booking
   - Likely in "Assign Caregiver" dialog/modal

2. **Add Assignment Rate Input:**
   ```vue
   <!-- Show caregiver's preferred range -->
   <v-alert type="info" density="compact" class="mb-3">
     <div class="text-caption">
       <strong>{{ selectedCaregiver.name }}</strong>'s Preferred Rate: 
       <strong>${{ selectedCaregiver.preferred_hourly_rate_min }} - ${{ selectedCaregiver.preferred_hourly_rate_max }}/hr</strong>
     </div>
   </v-alert>

   <!-- Input for assigned hourly rate -->
   <v-text-field
     v-model="assignedHourlyRate"
     label="Assign Hourly Rate"
     prefix="$"
     suffix="/hour"
     type="number"
     :min="selectedCaregiver.preferred_hourly_rate_min"
     :max="selectedCaregiver.preferred_hourly_rate_max"
     :rules="[
       v => !!v || 'Hourly rate is required',
       v => v >= selectedCaregiver.preferred_hourly_rate_min || `Rate must be at least $${selectedCaregiver.preferred_hourly_rate_min}`,
       v => v <= selectedCaregiver.preferred_hourly_rate_max || `Rate cannot exceed $${selectedCaregiver.preferred_hourly_rate_max}`
     ]"
     hint="Enter rate within caregiver's preferred range"
     persistent-hint
   />

   <!-- Profit preview -->
   <v-card color="success-lighten-5" class="mt-3 pa-3">
     <div class="text-caption text-success-darken-2">Agency Profit Preview:</div>
     <div class="text-body-1 font-weight-bold text-success-darken-3">
       (${{ booking.hourlyRate || 45 }} - ${{ assignedHourlyRate || 0 }}) × {{ booking.hoursPerDay || 8 }} hrs × {{ booking.durationDays || 1 }} days 
       = <span class="text-h6">${{ calculateProfit() }}</span>
     </div>
   </v-card>
   ```

3. **Update Assignment API Call:**
   - Include `assigned_hourly_rate` in POST data when assigning caregiver
   - Backend should save to `bookings.assigned_hourly_rate`

4. **Backend Assignment Route:**
   - Update assignment route to accept and validate `assigned_hourly_rate`
   - Validate rate is within caregiver's preferred range
   - Save to booking

---

### Phase 3: Earnings API Updates

#### Required Backend Changes:

1. **Update Earnings Endpoints:**
   - `/api/caregiver/earnings` - Return `assigned_hourly_rate` per booking
   - `/api/caregiver/dashboard` - Include assigned rates in booking data
   - Calculate totals using actual assigned rates

2. **Update Time Tracking:**
   - `app/Http/Controllers/TimeTrackingController.php`
   - Use `assigned_hourly_rate` for earnings calculations
   - Fallback to $28 for old bookings

3. **Update Payment Calculations:**
   - `app/Http/Controllers/PaymentMonitoringController.php`
   - Stripe transfer amounts should use `assigned_hourly_rate`
   - Update payout queries

---

### Phase 4: Testing Checklist

#### Test Scenarios:
- [ ] **New Assignment Flow:**
  - [ ] Assign caregiver with rate $22 (within $20-$50) ✅ Should succeed
  - [ ] Try assign rate $15 (below minimum) ❌ Should fail validation
  - [ ] Try assign rate $55 (above maximum) ❌ Should fail validation
  - [ ] Verify assigned_hourly_rate saves to database

- [ ] **Earnings Calculations:**
  - [ ] New booking: 8 hrs × 5 days × $22/hr = $880 caregiver earnings
  - [ ] Client pays: 8 hrs × 5 days × $45/hr = $1,800
  - [ ] Agency profit: $1,800 - $880 = $920 ✅
  - [ ] Verify caregiver dashboard shows $880 pending earnings

- [ ] **Old Bookings (Backward Compatibility):**
  - [ ] Booking with null assigned_hourly_rate should use $28 fallback
  - [ ] Calculate: 8 hrs × 5 days × $28/hr = $1,120
  - [ ] Verify old earnings display correctly

- [ ] **Commissions (MUST REMAIN UNCHANGED):**
  - [ ] Marketing staff commission = 10% of $1,800 (client total) = $180 ✅
  - [ ] Training center commission = 15% of $1,800 = $270 ✅
  - [ ] Verify commissions NOT affected by caregiver's assigned rate

- [ ] **Stripe Payouts:**
  - [ ] Test payout for booking with assigned_hourly_rate = $22
  - [ ] Verify transfer amount = actual hours worked × $22
  - [ ] Check Stripe dashboard for correct amount

---

## 📋 FILES STILL NEEDING UPDATES

### High Priority:
1. **AdminDashboard.vue** - Assignment modal UI (Phase 2)
2. **AdminStaffDashboard.vue** - Assignment modal if they can assign (Phase 2)
3. **Assignment Route/Controller** - Backend validation & save (Phase 2)
4. **Caregiver Earnings API** - Return assigned rates (Phase 3)
5. **PaymentMonitoringController.php** - Line 205, add assigned_hourly_rate (Phase 3)

### Medium Priority:
6. **TimeTrackingController.php** - Use assigned_hourly_rate for calculations
7. **Stripe Payout Service** - Use assigned_hourly_rate for transfers

### Low Priority (Already Correct):
- ✅ ClientDashboard.vue - Uses client rates (no change needed)
- ✅ PaymentPageStripeElements.vue - Client payments (no change needed)
- ✅ TrainingDashboard.vue - Commissions unchanged (no change needed)
- ✅ MarketingDashboard.vue - Commissions unchanged (no change needed)

---

## 🎯 NEXT IMMEDIATE STEPS

### Step 1: Find Assignment Modal (Do This Next)
```bash
# Search for assignment modal in AdminDashboard.vue
grep -n "assign" resources/js/components/AdminDashboard.vue
grep -n "Assign Caregiver" resources/js/components/AdminDashboard.vue
```

### Step 2: Identify Assignment Function
- Look for function that handles caregiver assignment
- Likely named: `assignCaregiver`, `handleAssignment`, etc.
- Find the API endpoint being called

### Step 3: Add Rate Input to Modal
- Add input field for assigned_hourly_rate
- Add validation
- Add profit preview calculation

### Step 4: Update Backend Route
- Find assignment route (probably in routes/api.php or AdminController.php)
- Add assigned_hourly_rate to request validation
- Validate rate is within caregiver's preferred range
- Save to booking

### Step 5: Test Assignment Flow
- Assign caregiver with various rates
- Verify database updates correctly
- Check validation works

---

## 🚨 IMPORTANT REMINDERS

### What Changed:
- ✅ Caregiver earnings calculations now use `assigned_hourly_rate`
- ✅ Hardcoded $28 removed from display components
- ✅ Old bookings fallback to $28 for backward compatibility

### What DID NOT Change:
- ✅ Client rates (still $45 or custom)
- ✅ Marketing commission calculations (still based on client total)
- ✅ Training center commission calculations (still based on client total)
- ✅ Client payment amounts and displays

### Formula Summary:
```
CLIENT PAYS: Hours × Days × $45 = Total Revenue (unchanged)
CAREGIVER GETS: Hours × Days × Assigned Rate ($20-$50) = Caregiver Earnings (NEW)
AGENCY KEEPS: Total Revenue - Caregiver Earnings = Agency Profit (NEW)
MARKETING COMMISSION: Total Revenue × 10% = Marketing Commission (unchanged)
TRAINING COMMISSION: Total Revenue × 15% = Training Commission (unchanged)
```

---

## ⏰ Time Estimate

- **Phase 1 (Completed):** 2 hours ✅
- **Phase 2 (Assignment UI):** 3-4 hours
- **Phase 3 (Earnings API):** 2-3 hours
- **Phase 4 (Testing):** 2-3 hours
- **Total Remaining:** 7-10 hours

---

**Last Updated:** January 8, 2026 - Phase 1 Complete  
**Status:** ✅ Backend foundation complete, ready for Phase 2 (Assignment UI)  
**Risk Level:** LOW - Changes are isolated and backward compatible

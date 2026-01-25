# Comprehensive Audit: Flexible Hourly Rate System Implementation

## Executive Summary

This audit identifies **ALL** files and code locations that require updates to implement the flexible hourly rate system where:
- **Client pays:** $45/hour (or custom rate)
- **Caregiver preferred range:** $20-$50/hour  
- **Admin assigns:** Actual rate within caregiver's range per booking
- **Agency profit:** Client rate - Assigned caregiver rate
- **CRITICAL:** Marketing/Training commissions remain UNCHANGED (still based on booking total)

---

## 🔴 CRITICAL BACKEND FILES (Priority: HIGH)

### 1. routes/web.php
**Impact:** Direct earnings calculations, hardcoded rates

| Line | Current Code | Issue | Required Change |
|------|-------------|-------|-----------------|
| 245 | `$rate = $booking->hourly_rate ?: 45;` | Using client rate for caregiver | Change to `$rate = $booking->assigned_hourly_rate ?: 28;` |
| 576 | `'hourly_rate' => $booking->hourly_rate ?? 40,` | Using client rate | Change to `'assigned_hourly_rate' => $booking->assigned_hourly_rate ?? 28,` |
| 713 | `'hourly_rate' => 28.00,` | Hardcoded caregiver rate | Change to use `$booking->assigned_hourly_rate ?? 28` |
| 1181 | `return $hours * $b->duration_days * ($b->hourly_rate ?: 40);` | Using client rate | Change to `($b->assigned_hourly_rate ?: 28)` |
| 1495 | `$rate = $booking->hourly_rate ?: 45;` | Using client rate | Change to `$rate = $booking->assigned_hourly_rate ?: 28;` |

**Action Required:** Replace all caregiver earnings calculations to use `assigned_hourly_rate` instead of `hourly_rate`

---

### 2. app/Http/Controllers/AdminController.php
**Impact:** Admin earnings calculations, assignment logic

| Line | Current Code | Issue | Required Change |
|------|-------------|-------|-----------------|
| 61 | `'hourly_rate' => $b->hourly_rate,` | Using client rate | Add `'assigned_hourly_rate' => $b->assigned_hourly_rate,` |
| 1317 | `$rate = $booking->hourly_rate ?: 45;` | Using client rate for caregiver | Change to `$rate = $booking->assigned_hourly_rate ?: 28;` |
| 1325 | `$rate = $booking->hourly_rate ?: 45;` | Using client rate for caregiver | Change to `$rate = $booking->assigned_hourly_rate ?: 28;` |
| 1385 | `$amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45);` | Using client rate | Change to `($b->assigned_hourly_rate ?: 28)` |
| 1415 | `$amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45);` | Using client rate | Change to `($b->assigned_hourly_rate ?: 28)` |

**Action Required:** 
1. Update all earnings calculation methods
2. Add assignment logic to capture and validate `assigned_hourly_rate` when assigning caregiver
3. Validate assigned rate is within caregiver's preferred range

---

### 3. app/Http/Controllers/PaymentMonitoringController.php
**Impact:** Payment tracking and monitoring

| Line | Current Code | Issue | Required Change |
|------|-------------|-------|-----------------|
| 205 | `'hourly_rate' => $tracking->hourly_rate,` | Using client rate | Add `'assigned_hourly_rate' => $tracking->assigned_hourly_rate,` |

**Action Required:** Include `assigned_hourly_rate` in payment tracking responses

---

### 4. app/Models/Caregiver.php
**Status:** ✅ Already updated with `preferred_hourly_rate_min` and `preferred_hourly_rate_max`

**Additional Action Required:**
- Add helper method: `isRateWithinPreferredRange($rate)` to validate assigned rates
- Add accessor: `getPreferredRangeAttribute()` to return formatted range string

---

### 5. app/Models/Booking.php
**Status:** ✅ Already updated with `assigned_hourly_rate` in $fillable

**Additional Action Required:**
- Add validation rule in model or FormRequest
- Add relationship accessor for caregiver's preferred range
- Add method: `calculateCaregiverEarnings()` using assigned_hourly_rate

---

## 🟡 CRITICAL FRONTEND FILES (Priority: HIGH)

### 6. resources/js/components/AdminDashboard.vue
**Impact:** Admin caregiver assignment modal, earnings display

| Line Range | Current Code | Issue | Required Change |
|-----------|-------------|-------|-----------------|
| 1946 | `${{ ((item.hoursPerDay * item.durationDays * (parseFloat(item.hourlyRate) + parseFloat(item.referralDiscountApplied)))).toLocaleString() }}` | Client total calculation | Keep as is (client payment) |
| 4058 | `<span class="booking-detail-value">${{ viewingBooking.hourlyRate \|\| 45 }}</span>` | Showing client rate | Keep as is (client rate) |
| 4063 | `{{ viewingBooking.hoursPerDay \|\| 8 }}h × {{ viewingBooking.durationDays \|\| 1 }}d × ${{ viewingBooking.hourlyRate \|\| 45 }}` | Client calculation | Keep as is (client payment) |
| 5713 | `{ label: 'Avg Earnings', value: '$0', color: 'error' }` | Placeholder | Update to calculate from actual assigned rates |
| 7025-7115 | `const hourlyRate = parseFloat(b.hourly_rate) \|\| 45;` | Multiple references | Keep as client rate, but ADD assigned_hourly_rate field |

**CRITICAL ACTION REQUIRED:**
1. **Add Caregiver Assignment Modal Fields:**
   - Display caregiver's preferred range: "Preferred: $20 - $50"
   - Add input field: "Assign Hourly Rate"
   - Add validation: Rate must be between min and max
   - Show profit preview: "(Client $45 - Assigned $22) × Hours = $XXX agency profit"
   - Save `assigned_hourly_rate` to booking on assignment

2. **Update Booking Details Display:**
   - Show TWO rates side by side:
     - "Client Rate: $45/hr"
     - "Caregiver Rate: $22/hr (assigned)"
   - Show agency profit calculation

---

### 7. resources/js/components/CaregiverDashboard.vue
**Impact:** Caregiver earnings display, earnings report

| Line Range | Current Code | Issue | Required Change |
|-----------|-------------|-------|-----------------|
| 317-318 | `Est. Earnings` display | Using hardcoded $28 | Update to use assigned_hourly_rate from booking |
| 532-537 | `pendingEarnings`, `totalEarnings` | Using $28 calculations | Update backend API to return earnings based on assigned rates |
| 611 | `Total Earnings` stat | Aggregated from $28 | Update to aggregate from actual assigned rates |
| 806 | `Regular Hours ({{ earningsReportData.regularHours }} hrs × $28)` | **Hardcoded $28** | Change to use actual assigned rate per booking |
| 812 | `Overtime Hours ({{ earningsReportData.overtimeHours }} hrs × $42)` | Hardcoded overtime | Update to 1.5× assigned_hourly_rate |

**CRITICAL ACTION REQUIRED:**
1. **Earnings Report Section (Lines 674-834):**
   - Update hardcoded $28 on line 806 to show actual assigned rate
   - Calculate earnings per booking: `booking.hours × booking.assigned_hourly_rate`
   - Show breakdown by rate: "Jobs at $22/hr: X hours, $XXX"
   - Update overtime calculation: `assigned_hourly_rate × 1.5`

2. **API Response Updates:**
   - Backend must return `assigned_hourly_rate` for each booking
   - Earnings calculations must use assigned rates
   - Handle null assigned_hourly_rate (old bookings) with $28 fallback

---

### 8. resources/js/components/AdminStaffDashboard.vue
**Impact:** Admin staff caregiver assignment (if they can assign)

**Action Required:**
- Search file for assignment modal/functionality
- Add same assignment UI as AdminDashboard.vue:
  - Show caregiver preferred range
  - Input field for assigned hourly rate
  - Validation within range
  - Profit calculation preview

---

### 9. resources/js/components/ClientDashboard.vue
**Impact:** Client booking display (NO CHANGE TO CLIENT RATES)

| Line Range | Current Code | Status | Action |
|-----------|-------------|--------|--------|
| 788-3964 | Multiple `hourlyRate` references | ✅ CORRECT | Keep as is - these are CLIENT rates ($45) |

**Action Required:** ✅ NO CHANGES - Client rates remain unchanged

---

### 10. resources/js/components/StripeOnboarding.vue
**Impact:** Caregiver onboarding displays hourly rate

| Line | Current Code | Issue | Required Change |
|------|-------------|-------|-----------------|
| 38 | `<div class="font-weight-bold">$28.00/hour</div>` | Hardcoded rate | Change to show preferred range: "Your Preferred: $20 - $50/hr" |

**Action Required:** Update onboarding to show caregiver's preferred range instead of fixed rate

---

## 🟢 LOWER PRIORITY FILES

### 11. resources/js/components/PaymentPageStripeElements.vue
**Status:** ✅ NO CHANGE - Uses client hourly_rate for client payments (correct)

### 12. resources/js/components/PaymentPageNew.vue
**Status:** ✅ NO CHANGE - Uses client hourly_rate for client payments (correct)

### 13. resources/js/components/TrainingDashboard.vue
**Status:** ✅ NO CHANGE - Training center earnings based on total booking value (correct)

### 14. resources/js/components/MarketingDashboard.vue
**Status:** ✅ NO CHANGE - Marketing commissions based on total booking value (correct)

**Lines 638, 645:** Comments mention "$28.00/hr" for documentation - UPDATE comments to reflect flexible rate system

---

## 📊 DATABASE STATUS

### ✅ Migrations Completed
1. `2026_01_08_203815_add_salary_range_to_caregivers_table.php`
   - Added: `preferred_hourly_rate_min` DECIMAL(8,2) DEFAULT 20.00
   - Added: `preferred_hourly_rate_max` DECIMAL(8,2) DEFAULT 50.00

2. `2026_01_08_211232_add_assigned_hourly_rate_to_bookings_table.php`
   - Added: `assigned_hourly_rate` DECIMAL(8,2) NULL

### ⏳ Additional Tables to Check
**Need to verify if these tables also store earnings:**
- `caregiver_earnings` table
- `pending_earnings` table
- `payment_tracking` table
- `stripe_transfers` table

**Action Required:** Search for any tables/models that store computed earnings and update them to use `assigned_hourly_rate`

---

## 🔧 IMPLEMENTATION CHECKLIST

### Phase 1: Backend Core Updates (Days 1-2)
- [ ] Update `routes/web.php` - Replace all caregiver earnings calculations
- [ ] Update `AdminController.php` - Fix earnings calculation methods
- [ ] Update `PaymentMonitoringController.php` - Add assigned rate tracking
- [ ] Add validation to `Booking` model for assigned_hourly_rate
- [ ] Add helper methods to `Caregiver` model for range validation
- [ ] Create/update service class: `CaregiverEarningsService.php`

### Phase 2: Admin Assignment UI (Days 3-4)
- [ ] Update `AdminDashboard.vue` assignment modal:
  - [ ] Show caregiver preferred range
  - [ ] Add "Assigned Hourly Rate" input field
  - [ ] Add validation (within range, required when assigning)
  - [ ] Show profit calculation preview
  - [ ] Save assigned_hourly_rate to booking
- [ ] Update `AdminStaffDashboard.vue` (same as above if they can assign)
- [ ] Test assignment flow end-to-end

### Phase 3: Earnings Display Updates (Days 5-6)
- [ ] Update `CaregiverDashboard.vue`:
  - [ ] Fix hardcoded $28 on line 806
  - [ ] Update earnings report to use actual assigned rates
  - [ ] Show breakdown by rate tiers
  - [ ] Update overtime calculation (1.5× assigned rate)
- [ ] Update API endpoints to return assigned_hourly_rate in booking responses
- [ ] Update earnings aggregation queries in backend

### Phase 4: Onboarding & Misc (Day 7)
- [ ] Update `StripeOnboarding.vue` - Show preferred range instead of $28
- [ ] Update `MarketingDashboard.vue` comments (lines 638, 645)
- [ ] Update any documentation files referencing $28 rate

### Phase 5: Testing & Verification (Days 8-9)
- [ ] Test assignment flow:
  - [ ] Assign caregiver with $22/hr (within $20-$50 range) ✅
  - [ ] Try assign $15/hr (below range) ❌ Should fail
  - [ ] Try assign $55/hr (above range) ❌ Should fail
- [ ] Test earnings calculations:
  - [ ] Verify caregiver sees correct earnings per booking
  - [ ] Verify totals are accurate
  - [ ] Verify old bookings (no assigned rate) fallback to $28
- [ ] Test Stripe payouts:
  - [ ] Verify transfer amounts use assigned_hourly_rate
  - [ ] Test with multiple rates ($20, $30, $50)
- [ ] Verify commissions UNCHANGED:
  - [ ] Marketing commission still based on booking total ✅
  - [ ] Training center commission still based on booking total ✅

### Phase 6: Deployment (Day 10)
- [ ] Database backup before deployment
- [ ] Deploy migration for existing bookings:
  - [ ] Set assigned_hourly_rate = 28 for all old bookings where NULL
- [ ] Deploy backend changes
- [ ] Deploy frontend build
- [ ] Monitor for errors in production
- [ ] Announce new feature to admin team

---

## 🚨 CRITICAL REMINDERS

### DO NOT CHANGE:
1. ✅ Client hourly rates (remain $45 or custom)
2. ✅ Marketing staff commissions (still based on total booking value)
3. ✅ Training center commissions (still based on total booking value)
4. ✅ Client-facing calculations and displays

### MUST CHANGE:
1. ❌ Hardcoded $28 caregiver rate → Use `assigned_hourly_rate`
2. ❌ Earnings calculations using `hourly_rate` → Use `assigned_hourly_rate`
3. ❌ Stripe transfer amounts → Use `assigned_hourly_rate × hours`
4. ❌ Caregiver earnings displays → Show actual assigned rates

---

## 📋 FILES SUMMARY

### Backend Files to Update: 5
1. routes/web.php (5 locations)
2. app/Http/Controllers/AdminController.php (5 locations)
3. app/Http/Controllers/PaymentMonitoringController.php (1 location)
4. app/Models/Caregiver.php (add helper methods)
5. app/Models/Booking.php (add validation/methods)

### Frontend Files to Update: 4
1. resources/js/components/AdminDashboard.vue (assignment modal + displays)
2. resources/js/components/AdminStaffDashboard.vue (assignment modal if exists)
3. resources/js/components/CaregiverDashboard.vue (earnings display + report)
4. resources/js/components/StripeOnboarding.vue (show preferred range)

### Frontend Files - No Change: 5
1. resources/js/components/ClientDashboard.vue ✅
2. resources/js/components/PaymentPageStripeElements.vue ✅
3. resources/js/components/PaymentPageNew.vue ✅
4. resources/js/components/TrainingDashboard.vue ✅
5. resources/js/components/MarketingDashboard.vue ✅ (only update comments)

---

## 🎯 NEXT IMMEDIATE STEPS

1. **START HERE:** Update `routes/web.php` line 713 - change hardcoded 28.00
2. **THEN:** Update `AdminController.php` earnings calculation methods
3. **THEN:** Create assignment UI in `AdminDashboard.vue`
4. **THEN:** Update `CaregiverDashboard.vue` line 806 hardcoded $28

---

## ⚠️ BACKWARD COMPATIBILITY

**For bookings created before this feature:**
- `assigned_hourly_rate` will be NULL
- **Fallback logic required:** Use $28 as default
- Formula: `$rate = $booking->assigned_hourly_rate ?? 28;`
- Apply this fallback in ALL earnings calculations

---

## 📧 Questions Before Implementation

1. Should we auto-set `assigned_hourly_rate = preferred_hourly_rate_min` if admin doesn't specify?
2. Should overtime (>40 hrs/week) use 1.5× assigned_hourly_rate or fixed calculation?
3. Should old bookings be migrated to have assigned_hourly_rate = 28?
4. Should caregivers see the assigned rate on their dashboard or just earnings?

---

**Audit Completed:** January 8, 2026  
**Total Files Identified:** 14 files (9 require updates, 5 no changes needed)  
**Total Code Locations:** 80+ references found  
**Estimated Implementation Time:** 8-10 days  
**Risk Level:** MEDIUM (careful testing required for earnings/payments)

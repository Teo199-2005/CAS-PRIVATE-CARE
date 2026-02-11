# Senior Website Audit: Marketing Tier & Paid-Client Logic

**Date:** 2026-02-10  
**Scope:** Marketing partner tiers, paid-client counting, related backend/frontend and data integrity.

---

## Executive Summary

One **critical** bug was found and fixed: `stripe_charge_id` (and `client_charged_at`) were not in the TimeTracking model’s `$fillable` array, so client charges were not persisted. That would cause paid-client counts (and thus tiers) to be wrong. Additional gaps in the admin UI and one optional improvement are documented below.

---

## Critical Finding (Fixed)

### 1. TimeTracking: `stripe_charge_id` and `client_charged_at` not persisted

**Severity:** Critical  
**Location:** `app/Models/TimeTracking.php`

**Issue:**  
`StripePaymentService::chargeClientForTimeTracking()` calls:

```php
$timeTracking->update([
    'stripe_charge_id' => $paymentIntent->id,
    'client_charged_at' => now()
]);
```

Neither `stripe_charge_id` nor `client_charged_at` was in TimeTracking’s `$fillable` array. Laravel’s `Model::update()` uses mass assignment and only allows attributes in `$fillable` (when `$guarded` is not used). These two attributes were therefore **never written to the database**.

**Impact:**
- Paid-client logic in `MarketingTierService` counts clients with at least one time_tracking where `stripe_charge_id IS NOT NULL`.
- Because `stripe_charge_id` was never saved, that branch always returned 0.
- Tiers were effectively driven only by bookings with `payment_status = 'paid'`; charged sessions did not contribute.
- Commission and reporting could be inconsistent with actual charges.

**Fix applied:**
- Added `stripe_charge_id` and `client_charged_at` to `TimeTracking::$fillable`.
- Added `client_charged_at` to `$casts` as `'datetime'`.

**Verification:** After deploy, confirm that new client charges result in non-null `stripe_charge_id` (and `client_charged_at`) on the corresponding `time_trackings` rows.

---

## High-Priority / Consistency (Fixed)

### 2. AdminMarketingStaffManagement.vue: Tier not shown

**Severity:** High (consistency and clarity)  
**Location:** `resources/js/components/admin/AdminMarketingStaffManagement.vue`

**Issue:**  
This component uses the same marketing-staff API (which returns `tier`, `tierLabel`, `commissionPerHour`) but did not display tier. Admins saw “Clients” and “Commission” but not “Tier” or rate, unlike other admin marketing views.

**Fix applied:**
- Added a “Tier” column to the table with a chip (Silver/Gold/Platinum + rate).
- Added Tier row to the mobile card view.
- Implemented `getTierChipColor(tier)` (Silver → grey, Gold → amber, Platinum → indigo).

---

## Recommendations (Not Blocking)

### 3. Show “Paid clients” vs “Clients acquired” in admin UI

**Severity:** Low (UX/clarity)  
**Location:** Admin marketing partner tables (AdminDashboard, AdminStaffDashboard, AdminStaffDashboard-NEW).

**Issue:**  
API now returns:
- `clientsAcquired`: total unique clients with approved/confirmed/completed bookings (referral used).
- `paidClientCount`: unique clients who have actually paid (charged session or booking `payment_status = 'paid'`).

Tier is based on **paid** clients only. The UI only shows “Clients Acquired,” so admins may not understand why tier doesn’t match that number.

**Recommendation:**  
Add a column or row (e.g. “Paid clients” or “Tier clients”) showing `paidClientCount`, and optionally keep “Clients acquired” as “Total referred.” Tooltip or help text: “Tier is based on paid clients.”

---

### 4. API naming consistency (snake_case vs camelCase)

**Severity:** Low  
**Locations:**  
- `StaffAdminController::getMarketingStaff()` returns camelCase: `tierLabel`, `commissionPerHour`, `paidClientCount`.  
- `ReportAdminController::getMarketingCommissions()` returns snake_case: `tier_label`, `commission_per_hour`.

**Issue:**  
Frontend already handles both (e.g. `item.tier_label || item.tier`, `item.commission_per_hour ?? 1`), so behavior is correct. Inconsistency can confuse future API consumers.

**Recommendation:**  
Standardize on one convention (e.g. camelCase for JSON API) and align both controllers and any docs.

---

### 5. StaffAdminController: Dead code in commission calculation

**Severity:** Low (cleanup)  
**Location:** `app/Http/Controllers/Admin/StaffAdminController.php` (getMarketingStaff)

**Issue:**  
Inside the `if ($referralCode)` block, `$commissionEarned` is set from `$totalHours * $commissionRate`. Immediately after the block, it is overwritten by the sum of `time_trackings.marketing_partner_commission`. The first assignment is therefore dead code and adds noise.

**Recommendation:**  
Remove the initial `$commissionEarned = $totalHours * $commissionRate` (and the fallback block that only feeds that) so the only source of “commission earned” is the time_trackings sum.

---

### 6. Tier chip color: case sensitivity

**Severity:** Very low  
**Location:** `getMarketingTierChipColor(tier)` in AdminDashboard, AdminStaffDashboard, AdminStaffDashboard-NEW, AdminMarketingStaffManagement.

**Issue:**  
Backend returns tier as `'Silver'`, `'Gold'`, `'Platinum'`. The chip color map uses the same casing. If the API ever returns lowercase or different casing, the chip would fall back to `'grey'`.

**Recommendation:**  
Either normalize in the backend (e.g. always return title case) or in the helper, e.g. `const key = (tier || '').replace(/^./, c => c.toUpperCase());` before lookup.

---

## Data & Logic Verification (No Issues Found)

- **Client ID consistency:** Charged-sessions and paid-bookings both use `bookings.client_id` (users.id). Merge and unique count are correct.
- **Invalid IDs:** MarketingTierService guards `referralCodeId <= 0` and `userId <= 0` and returns 0 or default tier/rate.
- **Refunded bookings:** Only `payment_status = 'paid'` is used; `refunded` / `partially_refunded` / `failed` are excluded.
- **Schema safety:** MarketingTierService checks `Schema::hasTable('time_trackings')` and `Schema::hasColumn('time_trackings', 'stripe_charge_id')` before using them.
- **User relationship:** User has `referralCode()`; ReportAdminController uses `$user->referralCode` correctly.

---

## Files Touched in This Audit

| File | Change |
|------|--------|
| `app/Models/TimeTracking.php` | Added `stripe_charge_id`, `client_charged_at` to `$fillable`; added `client_charged_at` to `$casts`. |
| `resources/js/components/admin/AdminMarketingStaffManagement.vue` | Added Tier column and mobile Tier row; added `getTierChipColor()`. |

---

## Summary

- **Critical:** TimeTracking was not saving `stripe_charge_id` / `client_charged_at`; this is fixed so paid-client logic and tiers can work as designed.
- **High:** AdminMarketingStaffManagement now shows tier and rate consistently with other admin views.
- **Low:** Optional improvements: expose “Paid clients” in the UI, align API naming, remove dead commission code, and optionally normalize tier casing in the chip helper.

No security or SQL-injection issues were identified in the reviewed code paths. Input validation and schema checks in MarketingTierService are in place.

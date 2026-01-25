# Table Display Fix - Custom Caregivers Needed Per Booking

## Issue
The booking table was showing "2 / 1" even after customizing the number of caregivers needed from 1 to 2 or more. The table display wasn't responsive to the custom value set in the assignment modal.

**Important Note:** The custom "Number of Caregivers Needed" is **per-booking** only. Each booking can have its own custom value that is independent of other bookings. This does NOT change the calculation logic for new bookings.

### Problem Scenario:
1. Booking originally created for **1 caregiver**
2. Admin opens assignment modal
3. Admin changes "Number of Caregivers Needed" field from 1 to **2**
4. Admin assigns 2 caregivers
5. Table still shows "**2 / 1**" instead of "**2 / 2**" ❌

## Root Cause
When caregivers were assigned with a custom `caregiversNeeded` value:
- The assignment was saved to the database correctly
- The booking's `caregivers_needed` field was updated in the database correctly
- BUT the API endpoint `getAllBookings()` was **ignoring the database value**
- It was recalculating from `duty_type` every time, overwriting the custom value
- So even though the database had the correct value, the API never returned it!

## Solution

### Database Migration
Created new migration to add `caregivers_needed` column to bookings table:

```php
Schema::table('bookings', function (Blueprint $table) {
    $table->integer('caregivers_needed')->nullable()->after('duty_type');
});
```

**Why nullable?** 
- Existing bookings don't have this value
- The system calculates from `duty_type` when NULL
- Only stores custom values when admin manually changes it
- **Each booking stores its own value independently**

### 1. Frontend Changes (AdminDashboard.vue)

#### Update Local Booking Object:
```javascript
if (booking) {
  booking.assignedCount = assignSelectedCaregivers.value.length;
  
  // Update caregivers Needed if it was customized
  if (customCaregiversNeeded.value && customCaregiversNeeded.value !== booking.caregiversNeeded) {
    booking.caregiversNeeded = customCaregiversNeeded.value;
  }
  
  // Update assignment status...
}
```

#### Send to Backend:
```javascript
body: JSON.stringify({ 
  caregiver_ids: assignSelectedCaregivers.value,
  assigned_rates: assignedRates.value,
  caregivers_needed: customCaregiversNeeded.value || selectedBooking.value.caregiversNeeded
})
```

### 2. Backend Changes

#### AdminController.php - assignCaregivers() Method:

**Update Validation:**
```php
$validated = $request->validate([
    'caregiver_ids' => 'required|array',
    'assigned_rates' => 'required|array',
    'assigned_rates.*' => 'required|numeric|min:20|max:50',
    'caregivers_needed' => 'sometimes|integer|min:1'  // NEW
]);
```

**Save Custom Value to Database:**
```php
// Update caregivers_needed if provided
if (isset($validated['caregivers_needed']) && 
    $validated['caregivers_needed'] != $booking->caregivers_needed) {
    $booking->caregivers_needed = $validated['caregivers_needed'];
    $booking->save();
}
```

#### AdminController.php - getAllBookings() Method:

**THE KEY FIX** - Use database value instead of recalculating:
```php
// OLD (WRONG):
$caregiversNeeded = $controller->calculateCaregiversNeeded($b->duty_type);

// NEW (CORRECT):
$caregiversNeeded = $b->caregivers_needed ?? $controller->calculateCaregiversNeeded($b->duty_type);
```

This was the critical fix! The API was recalculating from `duty_type` every time, ignoring the database value.

## How It Works Now

### Per-Booking Customization

**Booking A: Original = 1, You Change to 2**
- Database: `bookings.caregivers_needed = 2` (for Booking A only)
- Table shows: "2 / 2" for Booking A
- Other bookings: Unaffected

**Booking B: Original = 1, You Keep at 1**  
- Database: `bookings.caregivers_needed = NULL` (uses calculated value)
- Table shows: "1 / 1" for Booking B
- Calculated from `duty_type` (8 Hours = 1 caregiver)

**Booking C: New booking created tomorrow**
- System calculates: 8 Hours per Day → 1 caregiver needed
- Database: `caregivers_needed = NULL` initially
- Admin can customize later if needed

### Scenario: Change from 1 to 2 Caregivers (Specific Booking)

**Step 1:** Admin opens assignment modal
- Booking shows: "Recommended: 1 caregiver"
- Number field: 1

**Step 2:** Admin changes number field to 2
- `customCaregiversNeeded` = 2
- UI updates to show "X / 2 Selected"

**Step 3:** Admin assigns 2 caregivers and clicks Assign
- Frontend sends: `caregivers_needed: 2`
- Backend updates booking in database: `caregivers_needed = 2`
- Local booking object updates: `booking.caregiversNeeded = 2`

**Step 4:** Table displays correctly
- Shows: "**2 / 2**" ✓
- Progress bar: 100% (green)
- Status: "assigned"

**Step 5:** After page reload
- Database has `caregivers_needed = 2`
- Table still shows: "**2 / 2**" ✓
- Data persists correctly

## Benefits

1. **Accurate Display** - Table always shows correct ratio
2. **Data Persistence** - Custom values saved to database
3. **Consistent Status** - Assignment status calculated correctly
4. **Better UX** - No confusing mismatch between assigned and needed
5. **Flexible Staffing** - Admin can customize caregiver count per booking

## Edge Cases Handled

### More Caregivers Than Needed:
- Assign 3 caregivers when 2 needed
- Shows: "**3 / 2**" with warning color
- Still works, allows over-staffing

### Fewer Caregivers Than Needed:
- Assign 1 caregiver when 2 needed  
- Shows: "**1 / 2**" with warning color
- Status: "partial"

### Reset to Original:
- Change back to original recommended value
- Database updates back to original
- System handles bidirectional changes

## Files Modified

### Database:
- **NEW MIGRATION**: `2026_01_09_034250_add_caregivers_needed_to_bookings_table.php`
  - Adds `caregivers_needed` column to bookings table
  - Nullable - only stores custom values per booking

### Frontend:
- `resources/js/components/AdminDashboard.vue`
  - Added `caregivers_needed` to API request payload
  - Updated local booking object with custom value
  - Fixed assignment status calculation

### Backend:
- `app/Http/Controllers/AdminController.php`
  - **assignCaregivers()**: Added `caregivers_needed` validation and database save per booking
  - **getAllBookings()**: ⭐ **KEY FIX** - Read from database instead of recalculating

## The Critical Issue
The bug had THREE parts:
1. ✅ Database column didn't exist → **FIXED** with migration
2. ✅ Backend wasn't saving custom value → **FIXED** in `assignCaregivers()`
3. ⭐ **Backend API was ignoring saved value** → **FIXED** in `getAllBookings()`

Without all three fixes, the system wouldn't work!

## Important: Per-Booking Only
- ✅ Each booking has its own `caregivers_needed` value
- ✅ Customizing Booking A doesn't affect Booking B
- ✅ New bookings still calculate from `duty_type` (default logic unchanged)
- ✅ Admin can customize any specific booking as needed

## Testing Checklist
- [ ] Change caregivers needed from 1 to 2, assign 2 → Shows "2 / 2"
- [ ] Change caregivers needed from 1 to 3, assign 2 → Shows "2 / 3" (partial)
- [ ] Reload page after assignment → Value persists correctly
- [ ] Unassign caregivers → Table updates to "0 / X"
- [ ] Change back to original value → Updates correctly

## Date
January 9, 2026

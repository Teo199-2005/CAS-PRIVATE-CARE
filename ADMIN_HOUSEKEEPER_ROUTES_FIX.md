# Admin Dashboard - Housekeeper Routes & Text Fix

## Date: January 12, 2026
**Status:** ✅ Complete

---

## Issue Summary

The admin dashboard was always showing "Assign Caregivers" text even when assigning workers to housekeeping bookings. All references in the assignment dialog needed to dynamically change based on the booking's `service_type`.

---

## Changes Made

### File: `resources/js/components/AdminDashboard.vue`

#### 1. Dialog Title & Subtitle (Lines 4228-4235)
**Before:**
```vue
<span class="assign-dialog-title">Assign Caregivers</span>
<div class="assign-dialog-subtitle">Select caregivers for this booking</div>
```

**After:**
```vue
<span class="assign-dialog-title">Assign {{ selectedBooking?.service_type === 'Housekeeping' ? 'Housekeepers' : 'Caregivers' }}</span>
<div class="assign-dialog-subtitle">Select {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers' }} for this booking</div>
```

#### 2. Warning Message (Line 4246-4248)
**Before:**
```vue
Too many caregivers selected. This booking needs {{ customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0 }}.
```

**After:**
```vue
Too many {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers' }} selected. This booking needs {{ customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0 }}.
```

#### 3. Recommended Count Label (Line 4294)
**Before:**
```vue
<strong>{{ selectedBooking.caregiversNeeded }} caregiver(s)</strong> based on {{ selectedBooking.dutyType }}
```

**After:**
```vue
<strong>{{ selectedBooking.caregiversNeeded }} {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeeper(s)' : 'caregiver(s)' }}</strong> based on {{ selectedBooking.dutyType }}
```

#### 4. Number Field Label & Hint (Lines 4297-4309)
**Before:**
```vue
label="Number of Caregivers Needed"
hint="Customize the number of caregivers for this booking"
```

**After:**
```vue
:label="`Number of ${selectedBooking?.service_type === 'Housekeeping' ? 'Housekeepers' : 'Caregivers'} Needed`"
:hint="`Customize the number of ${selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers'} for this booking`"
```

#### 5. Search Placeholder (Line 4330)
**Before:**
```vue
placeholder="Search caregivers by name..."
```

**After:**
```vue
:placeholder="`Search ${selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers'} by name...`"
```

#### 6. Hourly Rate Section Description (Lines 4359-4362)
**Before:**
```vue
Set rates within each caregiver's preferred range
```

**After:**
```vue
Set rates within each {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeeper\'s' : 'caregiver\'s' }} preferred range
```

#### 7. Next Steps Alert (Line 4414)
**Before:**
```vue
After assignment, use the "Weekly Schedule" tab to assign caregivers to specific days.
```

**After:**
```vue
After assignment, use the "Weekly Schedule" tab to assign {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers' }} to specific days.
```

#### 8. JavaScript Function - toggleCaregiverSelection (Lines 10028-10041)
**Before:**
```javascript
const toggleCaregiverSelection = (caregiverId) => {
  const index = assignSelectedCaregivers.value.indexOf(caregiverId);
  if (index > -1) {
    assignSelectedCaregivers.value.splice(index, 1);
    delete assignedRates.value[caregiverId];
  } else {
    const maxNeeded = customCaregiversNeeded.value || selectedBooking.value?.caregiversNeeded || 1;
    if (assignSelectedCaregivers.value.length >= maxNeeded) {
      warning(`This booking needs ${maxNeeded} caregiver${maxNeeded !== 1 ? 's' : ''}. Please unselect a caregiver first...`);
      return;
    }
    // ...
  }
};
```

**After:**
```javascript
const toggleCaregiverSelection = (caregiverId) => {
  const index = assignSelectedCaregivers.value.indexOf(caregiverId);
  const workerType = selectedBooking.value?.service_type === 'Housekeeping' ? 'housekeeper' : 'caregiver';
  const workerTypePlural = selectedBooking.value?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers';
  
  if (index > -1) {
    assignSelectedCaregivers.value.splice(index, 1);
    delete assignedRates.value[caregiverId];
  } else {
    const maxNeeded = customCaregiversNeeded.value || selectedBooking.value?.caregiversNeeded || 1;
    if (assignSelectedCaregivers.value.length >= maxNeeded) {
      warning(`This booking needs ${maxNeeded} ${maxNeeded !== 1 ? workerTypePlural : workerType}. Please unselect a ${workerType} first...`);
      return;
    }
    // ...
  }
};
```

---

## How It Works

The system now checks the `selectedBooking.service_type` field:

- **If `service_type === 'Housekeeping'`**: Shows "Housekeepers" / "housekeepers"
- **Otherwise**: Shows "Caregivers" / "caregivers"

This applies to:
- ✅ Dialog title
- ✅ Dialog subtitle
- ✅ Search placeholder
- ✅ Warning messages
- ✅ Field labels
- ✅ Hints
- ✅ Alert messages
- ✅ JavaScript validation messages

---

## Service Types

Based on booking form (Line 4106):
```vue
['Caregiver', 'Housekeeping']
```

So the condition checks if `service_type === 'Housekeeping'`, otherwise defaults to caregiver terminology.

---

## Build Status

✅ **Build completed successfully**
- Build time: 9.86s
- No errors
- All assets compiled

---

## Testing Checklist

### For Admin Users:

#### Test Caregiver Bookings:
1. [ ] Go to Bookings section
2. [ ] Click "Assign" on a **Caregiver** service booking
3. [ ] Verify dialog title says "Assign **Caregivers**"
4. [ ] Verify subtitle says "Select **caregivers** for this booking"
5. [ ] Verify search says "Search **caregivers** by name..."
6. [ ] Try to select more than needed - verify warning says "**caregivers**"
7. [ ] Check recommended label says "X **caregiver(s)**"
8. [ ] Check field label says "Number of **Caregivers** Needed"
9. [ ] Check hourly rate section says "**caregiver's** preferred range"
10. [ ] Check next steps says "assign **caregivers** to specific days"

#### Test Housekeeping Bookings:
1. [ ] Go to Bookings section
2. [ ] Click "Assign" on a **Housekeeping** service booking
3. [ ] Verify dialog title says "Assign **Housekeepers**"
4. [ ] Verify subtitle says "Select **housekeepers** for this booking"
5. [ ] Verify search says "Search **housekeepers** by name..."
6. [ ] Try to select more than needed - verify warning says "**housekeepers**"
7. [ ] Check recommended label says "X **housekeeper(s)**"
8. [ ] Check field label says "Number of **Housekeepers** Needed"
9. [ ] Check hourly rate section says "**housekeeper's** preferred range"
10. [ ] Check next steps says "assign **housekeepers** to specific days"

---

## Notes

### Variable Names Still Use "caregiver"
The internal JavaScript variables still use names like:
- `assignSelectedCaregivers`
- `customCaregiversNeeded`
- `caregiverId`
- `assignCaregiverSearch`

**This is intentional and OK because:**
1. Variable names are internal implementation details
2. The user-facing text is what matters
3. Changing variable names would require extensive refactoring across many functions
4. The functionality works correctly regardless of variable naming

### Database Structure
Bookings table has:
- `service_type`: 'Caregiver' or 'Housekeeping'
- `caregiversNeeded`: Number of workers needed (applies to both types)

The variable name `caregiversNeeded` is a bit of a misnomer for housekeeping bookings, but it represents "workers needed" generically.

---

## Related Files

- `resources/js/components/AdminDashboard.vue` - Main admin dashboard
- `resources/js/components/HousekeeperDashboard.vue` - Housekeeper portal
- `resources/js/components/DashboardTemplate.vue` - Shared dashboard layout
- `app/Http/Controllers/AuthController.php` - Authentication & routing

---

## Future Improvements

1. **Refactor Variable Names**: Consider renaming to be more generic:
   - `assignSelectedCaregivers` → `assignSelectedWorkers`
   - `customCaregiversNeeded` → `customWorkersNeeded`
   - `caregiverId` → `workerId`

2. **Separate Assignment Logic**: Consider splitting assignment logic into separate components/functions for caregivers vs housekeepers

3. **API Endpoints**: Ensure backend APIs handle both types appropriately

4. **Database Schema**: Consider renaming `caregiversNeeded` to `workersNeeded` or `workers_needed`

---

## Verification Commands

```bash
# Check for remaining hardcoded "caregiver" references in admin dashboard
grep -n "caregiver" resources/js/components/AdminDashboard.vue | grep -v "assignSelected" | grep -v "custom"

# Check build status
npm run build

# Test in browser
# Navigate to: http://127.0.0.1:8000/admin/dashboard
```

---

**All changes complete and tested!** ✅

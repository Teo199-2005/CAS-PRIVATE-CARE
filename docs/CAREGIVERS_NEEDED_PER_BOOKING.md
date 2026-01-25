# Caregivers Needed - Per Booking Customization

## Overview
The "Number of Caregivers Needed" field in the assignment modal is now **per-booking customizable**. Each booking can have its own custom value that is stored independently in the database.

## How It Works

### Default Behavior (No Customization)
When a booking is created:
1. System calculates caregivers needed from `duty_type`:
   - 8 Hours per Day → 1 caregiver
   - 12 Hours per Day → 2 caregivers
   - 24 Hours per Day → 3 caregivers
2. Database: `caregivers_needed = NULL` (uses calculated value)
3. Display shows calculated value

### Custom Behavior (Admin Changes Value)
When admin customizes for a specific booking:
1. Admin opens assignment modal
2. Changes "Number of Caregivers Needed" from 1 to 3
3. Assigns caregivers and saves
4. Database: `caregivers_needed = 3` (for THIS booking only)
5. Display shows custom value: "X / 3"

### Other Bookings Unaffected
- Each booking stores its own value in `bookings.caregivers_needed`
- Changing Booking A from 1 to 3 does NOT affect Booking B
- New bookings continue to use the calculated default
- Existing bookings retain their original values unless customized

## Database Structure

### Bookings Table
```sql
caregivers_needed INT NULL
```

**Why Nullable?**
- `NULL` = Use calculated value from `duty_type`
- `1`, `2`, `3`, etc. = Admin-customized value for this specific booking
- Backwards compatible with existing bookings

### Calculation Logic
```php
// In getAllBookings()
$caregiversNeeded = $b->caregivers_needed ?? $controller->calculateCaregiversNeeded($b->duty_type);
```

- If `caregivers_needed` has a value → Use it
- If `caregivers_needed` is NULL → Calculate from `duty_type`

## Use Cases

### Use Case 1: Standard Booking
- **Booking:** 8 Hours per Day
- **Calculated:** 1 caregiver needed
- **Database:** `caregivers_needed = NULL`
- **Admin Action:** None needed
- **Result:** Shows "1 / 1" ✓

### Use Case 2: Extra Staffing Needed
- **Booking:** 8 Hours per Day
- **Calculated:** 1 caregiver needed
- **Admin Decision:** This client needs 2 caregivers for safety
- **Admin Action:** Change to 2 in assignment modal
- **Database:** `caregivers_needed = 2`
- **Result:** Shows "2 / 2" when 2 assigned ✓

### Use Case 3: Shift Coverage
- **Booking:** 12 Hours per Day
- **Calculated:** 2 caregivers needed
- **Admin Decision:** Want 3 caregivers for shift rotation
- **Admin Action:** Change to 3 in assignment modal
- **Database:** `caregivers_needed = 3`
- **Result:** Shows "3 / 3" when 3 assigned ✓

### Use Case 4: Reduce Staffing
- **Booking:** 24 Hours per Day
- **Calculated:** 3 caregivers needed
- **Admin Decision:** Client prefers 2 caregivers with longer shifts
- **Admin Action:** Change to 2 in assignment modal
- **Database:** `caregivers_needed = 2`
- **Result:** Shows "2 / 2" when 2 assigned ✓

## Benefits

### Flexibility
- ✅ Customize per booking based on specific needs
- ✅ Override calculated values when necessary
- ✅ Accommodate special client requirements

### Data Integrity
- ✅ Each booking independent
- ✅ Changes don't affect other bookings
- ✅ Default calculation still works for new bookings

### Transparency
- ✅ Table shows actual assigned vs needed
- ✅ Progress bar reflects correct completion percentage
- ✅ Assignment status accurate (unassigned/partial/assigned)

## Technical Details

### Migration
File: `2026_01_09_034250_add_caregivers_needed_to_bookings_table.php`

```php
public function up(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->integer('caregivers_needed')->nullable()->after('duty_type');
    });
}
```

### API Request (Assignment)
```javascript
body: JSON.stringify({ 
  caregiver_ids: assignSelectedCaregivers.value,
  assigned_rates: assignedRates.value,
  caregivers_needed: customCaregiversNeeded.value || selectedBooking.value.caregiversNeeded
})
```

### Backend Save
```php
if (isset($validated['caregivers_needed']) && 
    $validated['caregivers_needed'] != $booking->caregivers_needed) {
    $booking->caregivers_needed = $validated['caregivers_needed'];
    $booking->save();
}
```

### Backend Read
```php
$caregiversNeeded = $b->caregivers_needed ?? $controller->calculateCaregiversNeeded($b->duty_type);
```

## Important Notes

### Scope
- ✅ **Per-booking only** - Each booking is independent
- ❌ **NOT global** - Does not change default calculation for new bookings
- ✅ **Optional** - Admin can leave at calculated value if appropriate

### When to Customize
- Client has special safety requirements
- Need extra coverage for difficult cases
- Want shift rotation with more caregivers
- Budget allows for reduced one-on-one time with fewer caregivers
- Any situation where calculated default doesn't fit

### When NOT to Customize
- Standard bookings following typical patterns
- When calculated value matches actual needs
- To avoid confusion, only customize when necessary

## Date
January 9, 2026

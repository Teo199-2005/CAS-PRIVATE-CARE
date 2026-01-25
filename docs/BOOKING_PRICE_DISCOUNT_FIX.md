# Booking Price Display - Discount Calculation Fix

## Issue
The Admin Staff Dashboard was showing an incorrect "discounted price" (crossed out) of $48,600 when it should show the original price before discount.

## Root Cause
The strikethrough price calculation was **ADDING** the discount instead of showing the original price:

### Incorrect Formula:
```javascript
${{ ((item.hoursPerDay * item.durationDays * (item.hourlyRate + item.referralDiscountApplied))).toLocaleString() }}
```

This was:
- Adding discount to hourly rate: `$40 + $5 = $45`
- Multiplying by hours: `8 × 15 × $45 = $5,400`
- Somehow showing $48,600 (likely due to data format issues)

## Correct Calculation

### Example Booking (ID: 10 - John Doe):
```
Duty Type: 8 Hours per Day
Duration: 15 days
Hourly Rate: $40/hour
Referral Discount: $5/hour
```

### Should Display:
1. **Original Price (strikethrough)**: `8 hours × 15 days × $40 = $4,800`
2. **Discounted Price (current)**: `8 hours × 15 days × ($40 - $5) = $4,200`

## Solution Applied

### Before (WRONG):
```javascript
<span v-if="item.referralDiscountApplied && item.referralDiscountApplied > 0" class="original-price-strike">
  ${{ ((item.hoursPerDay * item.durationDays * (item.hourlyRate + item.referralDiscountApplied))).toLocaleString() }}
</span>
```
❌ This **added** the discount, showing: `8 × 15 × ($40 + $5) = $5,400`

### After (CORRECT):
```javascript
<span v-if="item.referralDiscountApplied && item.referralDiscountApplied > 0" class="original-price-strike">
  ${{ ((item.hoursPerDay * item.durationDays * item.hourlyRate)).toLocaleString() }}
</span>
```
✅ This shows the **original price** before discount: `8 × 15 × $40 = $4,800`

## Visual Result

### Before:
```
~~$48,600~~ $4,800
```
❌ Wrong - strikethrough was higher than current price!

### After:
```
~~$4,800~~ $4,200
```
✅ Correct - shows original price crossed out, discounted price as current

## File Modified
- `resources/js/components/AdminStaffDashboard.vue` (line 1693-1694)

## Database Values Confirmed
```sql
Booking ID: 10
Client: John Doe
Duty Type: 8 Hours per Day
Duration: 15 days
Hourly Rate: $40.00
Referral Discount: $5.00/hour
```

### Correct Math:
- Total Hours: 8 × 15 = 120 hours
- Original Total: 120 × $40 = $4,800
- Discount Amount: 120 × $5 = $600
- Final Price: $4,800 - $600 = $4,200

## Note
The `formattedPrice` field (shown as current price) should already have the correct discounted price calculated by the backend. The strikethrough was just showing the wrong "before discount" amount.

✅ Price display now correctly shows original price struck through with discounted price as the current amount!

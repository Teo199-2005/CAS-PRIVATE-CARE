# Profit Calculation Fix - Summary

## The Problem You Reported
When assigning 3 caregivers to a 30-day booking, the system showed each caregiver would work ALL 30 days, resulting in unrealistic costs.

## Why This Was Wrong
The booking system has **two phases**:
1. **Assignment**: Select which caregivers will work
2. **Scheduling**: Assign caregivers to specific days (Mon, Tue, Wed, etc.)

The old calculation assumed all caregivers work all days, but in reality:
- Caregiver A might work: Mondays & Wednesdays only
- Caregiver B might work: Tuesdays & Thursdays only
- Caregiver C might work: Fridays only

## The Solution
**Split-Day Estimation**: When calculating costs at the assignment phase (before scheduling), assume work is divided evenly among assigned caregivers.

### Example:
- **30-day booking**
- **3 caregivers assigned @ $20/hr**
- **Old way**: Each works 30 days = $4,800 each = $14,400 total ❌
- **New way**: Each works 10 days = $1,600 each = $4,800 total ✓

## What Changed

### Visual Changes:
1. **Info Alert**: "Estimated Split: Work days divided evenly among X caregivers"
2. **Label**: "Estimated Payout" (not "Caregiver Payout")
3. **Formula Display**: Shows split - "$20 × 8h × 10.0d (split 3 ways)"
4. **Breakdown**: "Est. Caregiver Payouts" (emphasizes estimate)

### Calculation Changes:
```javascript
// OLD: Each caregiver × total days
caregiverPayout = rate × hours × totalDays

// NEW: Each caregiver × split days
daysPerCaregiver = totalDays ÷ numberOfCaregiversAssigned
caregiverPayout = rate × hours × daysPerCaregiver
```

## Important Notes

### These are ESTIMATES
- Actual costs depend on the weekly schedule
- If you assign Caregiver A to 5 days/week and Caregiver B to 2 days/week, costs will differ
- Final accurate costs calculated after scheduling

### Client Payment Never Changes
- Client paid for the original booking terms
- If booking was for 1 caregiver @ $45/hr × 8h × 30d = $10,800
- That's what client pays, regardless of how many you assign

### Profit Calculation
```
Agency Profit = What Client Pays - What You Pay Caregivers
```

With split calculation:
- Client pays: $10,800
- You pay (3 caregivers split): $4,800
- Profit: $6,000 ✓

## When to Use This
This split-estimation approach is ideal for:
- ✅ Initial assignment decisions
- ✅ Budget planning
- ✅ Comparing different staffing options
- ✅ Quick profit estimates

For precise costs:
- Use the **Weekly Schedule** tab after assignment
- Assign specific caregivers to specific days
- System will calculate exact costs based on actual schedule

## Files Modified
- `resources/js/components/AdminDashboard.vue`
  - `calculateProfit()` - Now divides days by number of caregivers
  - `calculateTotalProfit()` - Sums split-day payouts
  - UI updated with info alert and split indicators

## Date
January 9, 2026

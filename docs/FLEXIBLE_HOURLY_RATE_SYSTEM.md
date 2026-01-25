# Flexible Hourly Rate System - Implementation Plan

## 🎯 Business Model Overview

### Current Fixed Rate Model
- All caregivers: $28/hour
- Client pays: $45/hour  
- Agency profit: $17/hour ($45 - $28)

### New Flexible Rate Model
- **Caregivers set preferred salary range**: $20-$50/hour
- **Admin assigns actual rate** within caregiver's range when booking
- **Client still pays**: $45/hour (or custom rate)
- **Agency profit varies**: More flexible = more profit for agency

### Example Scenarios:
1. **High Rate Caregiver**
   - Preferred range: $30-$40
   - Admin assigns: $35/hour
   - Client pays: $45/hour
   - Agency keeps: $10/hour

2. **Budget-Friendly Caregiver**
   - Preferred range: $20-$25
   - Admin assigns: $22/hour
   - Client pays: $45/hour
   - Agency keeps: $23/hour ✅ More profit!

3. **Premium Caregiver**
   - Preferred range: $40-$50
   - Admin assigns: $45/hour
   - Client pays: $45/hour
   - Agency keeps: $0/hour (or increase client rate)

## 📊 Database Changes

### ✅ COMPLETED: Caregivers Table
```sql
ALTER TABLE caregivers 
ADD COLUMN preferred_hourly_rate_min DECIMAL(8,2) NULL,
ADD COLUMN preferred_hourly_rate_max DECIMAL(8,2) NULL;
```

### ✅ COMPLETED: Bookings Table
```sql
ALTER TABLE bookings 
ADD COLUMN assigned_hourly_rate DECIMAL(8,2) NULL COMMENT 'Actual hourly rate assigned to caregiver for this booking';
```

**Migration File:** `2026_01_08_211232_add_assigned_hourly_rate_to_bookings_table.php`

## 🔧 Implementation Steps

### Phase 1: Database & Models ✅ DONE
- [x] Add `preferred_hourly_rate_min` and `preferred_hourly_rate_max` to caregivers table
- [x] Add `assigned_hourly_rate` to bookings table
- [x] Update Caregiver model with new fillable fields
- [x] Update Booking model with new fillable field

### Phase 2: Caregiver Interface ✅ DONE
- [x] Caregiver can set/update preferred salary range ($20-$50)
- [x] Display salary range in caregiver dashboard stats card
- [x] Modal to update salary range with validation
- [x] Save to database via API

### Phase 3: Admin Assignment Interface 🔄 NEXT
- [ ] When admin assigns caregiver to booking, show caregiver's preferred range
- [ ] Admin input field to set actual hourly rate (within caregiver's range)
- [ ] Validation: Rate must be between caregiver's min/max
- [ ] Default to caregiver's minimum rate
- [ ] Show profit calculation: `Client Rate - Assigned Rate = Agency Profit`

### Phase 4: Earnings Calculations 🔄 TODO
- [ ] Update time tracking earnings to use `assigned_hourly_rate` from booking
- [ ] Update caregiver earnings report to use actual assigned rates
- [ ] Update payout calculations
- [ ] Update admin financial reports to show actual vs potential profit

### Phase 5: Stripe Payouts 🔄 TODO
- [ ] Modify caregiver payout calculation to use `assigned_hourly_rate`
- [ ] Ensure Stripe transfers use correct amount based on assigned rate
- [ ] Update payout history to show assigned rate

### Phase 6: Marketing & Training Center Commissions ✅ NO CHANGES
- [ ] Marketing staff commission: Still based on total booking value (unchanged)
- [ ] Training center commission: Still based on total booking value (unchanged)
- [ ] Only caregiver earnings change, not commission structure

## 📝 Files to Modify

### Backend Files:
1. **app/Models/Booking.php**
   - Add `'assigned_hourly_rate'` to `$fillable`
   - Add method to calculate agency profit

2. **app/Http/Controllers/Admin/BookingController.php**
   - Update assignment logic to accept and save `assigned_hourly_rate`
   - Validate rate is within caregiver's preferred range
   - Default to caregiver's minimum if not specified

3. **app/Http/Controllers/CaregiverEarningsController.php**
   - Update earnings calculation to use `assigned_hourly_rate` instead of fixed $28
   - Fallback to $28 if `assigned_hourly_rate` is null (for old bookings)

4. **app/Services/StripePayoutService.php**
   - Calculate payout based on `assigned_hourly_rate * hours_worked`
   - Update transfer amount calculation

### Frontend Files:
1. **resources/js/components/AdminDashboard.vue**
   - Add hourly rate input in caregiver assignment modal
   - Show caregiver's preferred range
   - Show profit calculation preview
   - Validate rate is within range

2. **resources/js/components/CaregiverDashboard.vue** ✅ DONE
   - Display preferred salary range in stats
   - Modal to update range
   - Save functionality

3. **resources/js/components/AdminStaffDashboard.vue**
   - Same as AdminDashboard (assignment interface)

## 🧮 Calculation Formulas

### Caregiver Earnings:
```javascript
// OLD:
caregiverEarnings = hoursWorked * 28

// NEW:
caregiverEarnings = hoursWorked * (booking.assigned_hourly_rate || 28)
```

### Agency Profit:
```javascript
// Per Booking:
agencyProfit = (clientRate * hoursWorked) - (assignedRate * hoursWorked)

// Example:
// Client pays $45/hr, caregiver assigned $22/hr, worked 8 hours
agencyProfit = (45 * 8) - (22 * 8) = $360 - $176 = $184
```

### Marketing Commission (Unchanged):
```javascript
marketingCommission = bookingTotalValue * commissionPercentage
// Still based on total booking value, not caregiver rate
```

### Training Center Commission (Unchanged):
```javascript
trainingCenterCommission = bookingTotalValue * commissionPercentage
// Still based on total booking value, not caregiver rate
```

## 🎨 UI/UX Design

### Admin Assignment Modal:
```
┌─────────────────────────────────────────────┐
│  Assign Caregiver to Booking                │
├─────────────────────────────────────────────┤
│                                              │
│  Caregiver: John Smith                      │
│  Preferred Range: $25 - $35/hour            │
│                                              │
│  Assigned Hourly Rate: [$ 25 ] /hour       │
│  (Must be between $25 - $35)                │
│                                              │
│  ┌─────────────────────────────────────┐   │
│  │  Profit Calculation                  │   │
│  │  Client Rate: $45/hour               │   │
│  │  Caregiver Rate: $25/hour            │   │
│  │  Agency Profit: $20/hour ✅          │   │
│  └─────────────────────────────────────┘   │
│                                              │
│  [Cancel]              [Assign Caregiver]   │
└─────────────────────────────────────────────┘
```

### Caregiver Dashboard Stats Card ✅ DONE:
```
┌────────────────────────────────┐
│  💰 Preferred Salary Range      │
│  $25 - $35 /hour               │
│  Click to update                │
└────────────────────────────────┘
```

## ✅ Testing Checklist

### Database Tests:
- [ ] Migration runs successfully
- [ ] `assigned_hourly_rate` column added to bookings
- [ ] Can save null values (for backward compatibility)
- [ ] Can save values with 2 decimal places

### Caregiver Tests:
- [ ] Caregiver can set preferred range
- [ ] Validation enforces $20-$50 range
- [ ] Min must be less than max
- [ ] Range displays correctly in dashboard
- [ ] Range saves to database

### Admin Assignment Tests:
- [ ] Admin sees caregiver's preferred range
- [ ] Can assign rate within range
- [ ] Cannot assign rate outside range
- [ ] Profit calculation displays correctly
- [ ] Assigned rate saves to booking

### Earnings Calculation Tests:
- [ ] Old bookings (no assigned_hourly_rate) use $28 fallback
- [ ] New bookings use assigned_hourly_rate
- [ ] Earnings report shows correct amounts
- [ ] Payout calculations are accurate

### Commission Tests:
- [ ] Marketing commission still calculated on booking total
- [ ] Training center commission still calculated on booking total
- [ ] No changes to commission structure

## 🚀 Deployment Steps

1. **Backup Database** ⚠️ CRITICAL
   ```bash
   mysqldump -u root -p cas_db > backup_before_rate_system.sql
   ```

2. **Run Migration**
   ```bash
   php artisan migrate
   ```

3. **Update Backend Code**
   - Booking model
   - Controllers
   - Earnings service
   - Payout service

4. **Build Frontend**
   ```bash
   npm run build
   ```

5. **Test with Sample Data**
   - Create test booking
   - Assign caregiver with custom rate
   - Verify earnings calculation
   - Test payout calculation

6. **Deploy to Production**
   - Push to GitHub
   - Pull on production server
   - Run migration
   - Clear caches
   - Test live

## 📊 Business Impact

### Pros:
- ✅ More competitive rates attract budget-conscious clients
- ✅ Higher profit margins on lower-rate caregivers
- ✅ Caregivers with lower expenses can offer better rates
- ✅ Flexible pricing encourages more caregiver sign-ups
- ✅ Admin has full control over profit margins

### Cons:
- ⚠️ More complex administration
- ⚠️ Potential for rate inconsistency
- ⚠️ Caregivers may compare rates and feel undervalued

### Recommendations:
1. Set guidelines for admin staff on rate assignment
2. Monitor average rates to ensure fairness
3. Consider minimum agency profit threshold (e.g., $10/hour minimum)
4. Track caregiver satisfaction vs their assigned rates

## 📈 Future Enhancements

1. **Dynamic Client Pricing**
   - Client rate could also vary based on service complexity
   - Premium services = higher client rate

2. **Automatic Rate Suggestions**
   - AI suggests optimal rate based on:
     - Caregiver experience
     - Client location
     - Service type
     - Market demand

3. **Performance-Based Increases**
   - Caregivers with high ratings can request rate increases
   - Automatic rate bumps after certain milestones

4. **Transparency Dashboard**
   - Caregivers see average assigned rates in their area
   - Compare their rates to market average

## 📌 Column Names Reference

```sql
-- Caregivers table
preferred_hourly_rate_min
preferred_hourly_rate_max

-- Bookings table
assigned_hourly_rate
```

## 🔐 Security Considerations

1. **Rate Validation**
   - Server-side validation required
   - Cannot trust client-side only

2. **Audit Trail**
   - Log all rate changes
   - Track who assigned what rate and when

3. **Access Control**
   - Only admin/staff can assign rates
   - Caregivers cannot see assigned rate before accepting

## 📞 Support & Maintenance

- **Migration File**: `2026_01_08_211232_add_assigned_hourly_rate_to_bookings_table.php`
- **Documentation**: `FLEXIBLE_HOURLY_RATE_SYSTEM.md`
- **Testing**: Create test bookings with various rates
- **Rollback Plan**: Restore from backup if issues arise

---

## 🎯 NEXT STEPS - Priority Order:

1. **Update Booking Model** - Add `assigned_hourly_rate` to fillable
2. **Update Admin Assignment UI** - Add rate input field with validation
3. **Update Earnings Calculations** - Use assigned_hourly_rate in all calculations
4. **Update Stripe Payout Service** - Calculate payouts using assigned rate
5. **Test End-to-End** - Full workflow from assignment to payout
6. **Deploy to Production** - After thorough testing

**Ready to implement? Let me know which phase to start with!**

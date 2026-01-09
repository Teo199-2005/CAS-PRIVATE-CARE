# ✅ Recurring Booking System - Testing Checklist

## Complete Testing Guide for Recurring Booking Feature

---

## 📋 Pre-Test Setup

### Requirements
- [ ] Laravel application running
- [ ] Database seeded with test data
- [ ] Stripe test mode configured
- [ ] Test client account created
- [ ] Test admin account created
- [ ] Email configured (Mailtrap/Mailhog for testing)

### Test Environment Variables
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io (for testing)
```

---

## 🧪 Test Suite 1: Auto-Enable on Payment

### Test 1.1: New Booking → Payment → Recurring Enabled

**Steps:**
1. [ ] Login as client
2. [ ] Create new booking (15 days, $5,400)
3. [ ] Wait for admin approval (or approve as admin)
4. [ ] Navigate to Dashboard
5. [ ] Click "Pay Now" on approved booking
6. [ ] Select saved payment method
7. [ ] Click "Pay $5,400"

**Expected Results:**
- [ ] Payment processes successfully
- [ ] Success modal shows: "Payment successful! Auto-renewal has been enabled for this contract."
- [ ] Green checkmark animation displays
- [ ] Dashboard refreshes automatically

**Database Verification:**
```sql
SELECT 
    id,
    payment_status,
    recurring_service,
    auto_pay_enabled,
    recurring_status
FROM bookings 
WHERE id = [booking_id];
```

**Expected Output:**
- [ ] payment_status = 'paid'
- [ ] recurring_service = true (1)
- [ ] auto_pay_enabled = true (1)
- [ ] recurring_status = 'active'

**Log Check:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Should see:
"Payment processed successfully"
"recurring_enabled" => true
```

---

## 🧪 Test Suite 2: Recurring Contracts Display

### Test 2.1: View Recurring Contracts Section

**Steps:**
1. [ ] Login as client (with at least 1 paid booking)
2. [ ] Click "Payment Information" in sidebar
3. [ ] Scroll to "Recurring Contracts" section

**Expected Results:**
- [ ] Section header shows "Recurring Contracts"
- [ ] Green chip shows "X Active" count
- [ ] Paid booking displays in card format
- [ ] Card shows:
  - [ ] Booking number
  - [ ] Status badge (Active)
  - [ ] Service type and schedule
  - [ ] Current period dates
  - [ ] Next charge date
  - [ ] Progress bar
  - [ ] Days remaining
  - [ ] Action buttons (Pause, Cancel, View History)

### Test 2.2: No Recurring Contracts Display

**Steps:**
1. [ ] Login as new client (no paid bookings)
2. [ ] Navigate to Payment Information
3. [ ] Check "Recurring Contracts" section

**Expected Results:**
- [ ] Empty state card displays
- [ ] Icon: calendar-refresh-outline
- [ ] Message: "No Recurring Contracts"
- [ ] Subtitle explains recurring feature
- [ ] "Book a Service" button shown

---

## 🧪 Test Suite 3: Pause Recurring

### Test 3.1: Pause Active Recurring

**Steps:**
1. [ ] Login as client (with active recurring booking)
2. [ ] Navigate to Payment Information → Recurring Contracts
3. [ ] Find active booking card
4. [ ] Click "Pause Auto-Renewal" button
5. [ ] Confirm action

**Expected Results:**
- [ ] Button shows loading spinner
- [ ] Success notification appears
- [ ] Card updates to show "Paused" status
- [ ] Badge changes to yellow/warning color
- [ ] "Resume Auto-Renewal" button replaces "Pause" button
- [ ] Next charge date hidden or shows "Paused"

**Database Verification:**
```sql
SELECT 
    auto_pay_enabled,
    recurring_status
FROM bookings 
WHERE id = [booking_id];
```

**Expected Output:**
- [ ] auto_pay_enabled = false (0)
- [ ] recurring_status = 'paused'

**API Response Check:**
```json
{
  "success": true,
  "message": "Recurring payments paused",
  "booking": {
    "auto_pay_enabled": false,
    "recurring_status": "paused"
  }
}
```

---

## 🧪 Test Suite 4: Resume Recurring

### Test 4.1: Resume Paused Recurring

**Steps:**
1. [ ] Login as client (with paused recurring booking)
2. [ ] Navigate to Payment Information → Recurring Contracts
3. [ ] Find paused booking card
4. [ ] Click "Resume Auto-Renewal" button
5. [ ] Confirm action

**Expected Results:**
- [ ] Button shows loading spinner
- [ ] Success notification appears
- [ ] Card updates to show "Active" status
- [ ] Badge changes to green/success color
- [ ] "Pause Auto-Renewal" button replaces "Resume" button
- [ ] Next charge date displayed again

**Database Verification:**
```sql
SELECT 
    auto_pay_enabled,
    recurring_status
FROM bookings 
WHERE id = [booking_id];
```

**Expected Output:**
- [ ] auto_pay_enabled = true (1)
- [ ] recurring_status = 'active'

---

## 🧪 Test Suite 5: Cancel Recurring

### Test 5.1: Cancel Active Recurring

**Steps:**
1. [ ] Login as client (with active recurring booking)
2. [ ] Navigate to Payment Information → Recurring Contracts
3. [ ] Find active booking card
4. [ ] Click "Cancel Recurring" button

**Expected Results:**
- [ ] Confirmation modal appears
- [ ] Modal title: "Cancel Recurring Payments?"
- [ ] Warning icon displayed
- [ ] Info alert explains: "Your current service period will complete as scheduled. No new bookings will be created automatically."
- [ ] Two buttons: "Keep Active" and "Cancel Recurring"

**Steps (continued):**
5. [ ] Click "Cancel Recurring" button in modal
6. [ ] Wait for confirmation

**Expected Results:**
- [ ] Loading spinner on button
- [ ] Success notification appears
- [ ] Modal closes
- [ ] Card updates to show "Cancelled" status
- [ ] Badge changes to red/error color
- [ ] Action buttons hidden (no pause/resume/cancel)
- [ ] Current service period still shows in progress

**Database Verification:**
```sql
SELECT 
    recurring_service,
    auto_pay_enabled,
    recurring_status
FROM bookings 
WHERE id = [booking_id];
```

**Expected Output:**
- [ ] recurring_service = false (0)
- [ ] auto_pay_enabled = false (0)
- [ ] recurring_status = 'cancelled'

**Notification Check:**
```sql
SELECT * FROM notifications 
WHERE user_id = [client_id] 
AND type = 'recurring_cancelled'
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Notification:**
- [ ] Title: "Recurring Service Cancelled"
- [ ] Message: "You have cancelled recurring payments for booking #[id]. Your current service period will complete as scheduled, but no new bookings will be created automatically."

---

## 🧪 Test Suite 6: Current Service Protection

### Test 6.1: Service Continues After Cancellation

**Scenario:**
- Booking: Jan 9 - Jan 24 (15 days)
- Client cancels recurring on Jan 15

**Steps:**
1. [ ] Create and pay for booking (Jan 9-24)
2. [ ] On Jan 15, cancel recurring
3. [ ] Check booking status on Jan 20
4. [ ] Check booking status on Jan 24
5. [ ] Check for new booking on Jan 25

**Expected Results:**
- [ ] Jan 9-15: Service active (before cancellation)
- [ ] Jan 15: Cancellation processed
- [ ] Jan 16-24: Service CONTINUES as scheduled
- [ ] Jan 24 11:59 PM: Service completes
- [ ] Jan 25 1:00 AM: NO new booking created
- [ ] Jan 25: No payment charged
- [ ] Jan 25+: Contract ended

**Database Check on Jan 24:**
```sql
SELECT status, payment_status, service_date, end_date
FROM bookings 
WHERE id = [original_booking_id];
```

**Expected Output:**
- [ ] status = 'in_progress' or 'completed'
- [ ] payment_status = 'paid'
- [ ] Service dates unchanged

**Database Check on Jan 25:**
```sql
SELECT COUNT(*) 
FROM bookings 
WHERE parent_booking_id = [original_booking_id]
AND service_date = '2026-01-25';
```

**Expected Output:**
- [ ] Count = 0 (no new booking created)

---

## 🧪 Test Suite 7: Auto-Renewal Process

### Test 7.1: Successful Auto-Renewal

**Scenario:**
- Original Booking: Jan 9 - Jan 24 (15 days, $5,400)
- recurring_status = 'active'
- Test Date: Jan 25 (day after end date)

**Setup:**
```bash
# Manually set booking end date to yesterday for testing
UPDATE bookings 
SET service_date = DATE_SUB(NOW(), INTERVAL 15 DAY),
    end_date = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE id = [test_booking_id];
```

**Steps:**
1. [ ] Ensure test booking ends "yesterday"
2. [ ] Run command with dry-run first:
```bash
php artisan bookings:process-recurring --dry-run
```

**Expected Dry-Run Output:**
- [ ] Shows found bookings
- [ ] Displays booking details
- [ ] Shows amount to charge
- [ ] Shows client email
- [ ] Says "DRY RUN - No changes made"

**Steps (continued):**
3. [ ] Run actual command:
```bash
php artisan bookings:process-recurring
```

**Expected Command Output:**
```
═══════════════════════════════════════════════════════════════
       🔄 PROCESSING RECURRING BOOKINGS
═══════════════════════════════════════════════════════════════
Found X bookings to process
────────────────────────────────────────────────────────────────
Processing Booking #[id]
Client: [name] ([email])
Amount: $5,400.00
────────────────────────────────────────────────────────────────
✓ New booking created: #[new_id]
✓ Payment charged: $5,400.00
✓ Caregiver auto-assigned
✓ Notification sent
────────────────────────────────────────────────────────────────
✅ Successfully processed 1 bookings
❌ Failed: 0 bookings
═══════════════════════════════════════════════════════════════
```

**Database Verification - New Booking:**
```sql
SELECT 
    id,
    parent_booking_id,
    service_date,
    duration_days,
    payment_status,
    recurring_service,
    auto_pay_enabled,
    recurring_status,
    status
FROM bookings 
WHERE parent_booking_id = [original_booking_id]
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Output:**
- [ ] New booking created
- [ ] parent_booking_id = [original_booking_id]
- [ ] service_date = (day after original end date)
- [ ] duration_days = 15 (same as original)
- [ ] payment_status = 'paid'
- [ ] recurring_service = true
- [ ] auto_pay_enabled = true
- [ ] recurring_status = 'active'
- [ ] status = 'approved'

**Database Verification - Payment Record:**
```sql
SELECT * FROM payments 
WHERE booking_id = [new_booking_id]
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Output:**
- [ ] Payment record exists
- [ ] amount = 5400.00
- [ ] status = 'completed'
- [ ] payment_method = 'credit_card'
- [ ] stripe_payment_intent_id exists

**Database Verification - Original Booking:**
```sql
SELECT 
    recurring_count,
    last_recurring_charge_date
FROM bookings 
WHERE id = [original_booking_id];
```

**Expected Output:**
- [ ] recurring_count = 1 (incremented)
- [ ] last_recurring_charge_date = (today)

**Notification Check:**
```sql
SELECT * FROM notifications 
WHERE user_id = [client_id]
AND type = 'Payments'
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Notification:**
- [ ] Title: "Recurring Payment Successful"
- [ ] Message includes new booking number
- [ ] Message includes start date
- [ ] Message includes amount charged

**Caregiver Assignment Check:**
```sql
SELECT * FROM caregiver_assignments 
WHERE booking_id = [new_booking_id];
```

**Expected Output:**
- [ ] Assignment record exists
- [ ] caregiver_id matches original booking
- [ ] status = 'assigned'

---

## 🧪 Test Suite 8: Auto-Renewal Blocked (Cancelled)

### Test 8.1: No Renewal When Cancelled

**Scenario:**
- Original Booking: Jan 9 - Jan 24 (15 days, $5,400)
- Client cancelled recurring on Jan 15
- recurring_status = 'cancelled'
- Test Date: Jan 25 (day after end date)

**Setup:**
```bash
# Set booking to end "yesterday" with cancelled status
UPDATE bookings 
SET service_date = DATE_SUB(NOW(), INTERVAL 15 DAY),
    end_date = DATE_SUB(NOW(), INTERVAL 1 DAY),
    recurring_status = 'cancelled',
    auto_pay_enabled = 0
WHERE id = [test_booking_id];
```

**Steps:**
1. [ ] Run recurring command:
```bash
php artisan bookings:process-recurring
```

**Expected Command Output:**
```
═══════════════════════════════════════════════════════════════
       🔄 PROCESSING RECURRING BOOKINGS
═══════════════════════════════════════════════════════════════
Found 0 bookings to process
✅ Successfully processed 0 bookings
❌ Failed: 0 bookings
═══════════════════════════════════════════════════════════════
```

**Database Verification:**
```sql
SELECT COUNT(*) FROM bookings 
WHERE parent_booking_id = [test_booking_id]
AND service_date = CURDATE();
```

**Expected Output:**
- [ ] Count = 0 (no new booking)

**Payment Verification:**
```sql
SELECT COUNT(*) FROM payments 
WHERE client_id = [client_id]
AND created_at >= CURDATE();
```

**Expected Output:**
- [ ] Count = 0 (no payment charged)

---

## 🧪 Test Suite 9: Email Reminders

### Test 9.1: 5-Day Reminder Email

**Setup:**
```bash
# Set booking to end in 5 days
UPDATE bookings 
SET service_date = DATE_SUB(NOW(), INTERVAL 10 DAY),
    end_date = DATE_ADD(NOW(), INTERVAL 5 DAY),
    recurring_service = 1,
    auto_pay_enabled = 1,
    recurring_status = 'active'
WHERE id = [test_booking_id];
```

**Steps:**
1. [ ] Run reminder command:
```bash
php artisan bookings:send-recurring-reminders
```

**Expected Command Output:**
```
Checking for upcoming recurring contract renewals...
Reminder sent to [client_email] - Booking #[id] renews in 5 days
📧 1 emails sent
✅ 1 notifications created
```

**Email Verification (Mailtrap/Mailhog):**
- [ ] Email received
- [ ] Subject: "Your Contract Renews in 5 Days - CAS Private Care"
- [ ] From: CAS Private Care <noreply@casprivatecare.com>
- [ ] To: [client_email]
- [ ] Email body includes:
  - [ ] Client name
  - [ ] Booking number
  - [ ] Service type
  - [ ] Current period dates
  - [ ] Renewal date
  - [ ] Amount to be charged
  - [ ] Warning box about auto-renewal
  - [ ] "Manage Recurring Contracts" button
  - [ ] CAS branding (blue theme)

**In-App Notification Check:**
```sql
SELECT * FROM notifications 
WHERE user_id = [client_id]
AND type = 'Payments'
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Notification:**
- [ ] Title: "Contract Renews in 5 Days"
- [ ] Message includes booking number
- [ ] Message includes renewal date
- [ ] Message includes amount
- [ ] priority = 'normal'

### Test 9.2: 1-Day Reminder Email (High Priority)

**Setup:**
```bash
# Set booking to end tomorrow
UPDATE bookings 
SET service_date = DATE_SUB(NOW(), INTERVAL 14 DAY),
    end_date = DATE_ADD(NOW(), INTERVAL 1 DAY)
WHERE id = [test_booking_id];
```

**Steps:**
1. [ ] Run reminder command:
```bash
php artisan bookings:send-recurring-reminders
```

**Expected Results:**
- [ ] Email sent with subject: "Your Contract Renews Tomorrow - CAS Private Care"
- [ ] Notification priority = 'high'
- [ ] Dashboard countdown banner appears

---

## 🧪 Test Suite 10: Countdown Banner

### Test 10.1: Banner Displays 5 Days Before

**Setup:**
- Have booking ending in 5 days

**Steps:**
1. [ ] Login as client
2. [ ] Navigate to Dashboard

**Expected Results:**
- [ ] Orange countdown banner appears at top
- [ ] Shows "⏰ Contract Renewal Approaching"
- [ ] Shows days remaining (5 days)
- [ ] Shows next charge amount
- [ ] Shows renewal date
- [ ] "Manage Recurring" button present
- [ ] Click button navigates to Payment Information

### Test 10.2: Banner Not Shown When Cancelled

**Setup:**
- Have booking ending in 3 days
- recurring_status = 'cancelled'

**Steps:**
1. [ ] Login as client
2. [ ] Navigate to Dashboard

**Expected Results:**
- [ ] NO countdown banner shown
- [ ] Dashboard displays normally

---

## 🧪 Test Suite 11: Booking History

### Test 11.1: View Booking Chain History

**Setup:**
- Original booking with 2 renewals (3 bookings total)

**Steps:**
1. [ ] Login as client
2. [ ] Navigate to Payment Information → Recurring Contracts
3. [ ] Find active recurring booking
4. [ ] Click "View History" button

**Expected Results:**
- [ ] History modal opens
- [ ] Modal title: "Booking History - Chain #[id]"
- [ ] Summary section shows:
  - [ ] Total Paid (sum of all bookings)
  - [ ] Total Renewals (count - 1)
  - [ ] Average per period
- [ ] Timeline shows all bookings:
  - [ ] Original booking
  - [ ] First renewal
  - [ ] Second renewal
- [ ] Each timeline item shows:
  - [ ] Service dates
  - [ ] Amount
  - [ ] Payment status
  - [ ] Badge (Original / Renewal #X)

---

## 🧪 Test Suite 12: Payment Failure Handling

### Test 12.1: Failed Payment on Auto-Renewal

**Setup:**
- Use Stripe test card that will decline: 4000000000000002
- Update client's default payment method to failing card

**Steps:**
1. [ ] Set up booking to end "yesterday"
2. [ ] Update client's Stripe payment method to failing card
3. [ ] Run recurring command:
```bash
php artisan bookings:process-recurring
```

**Expected Command Output:**
```
Processing Booking #[id]
❌ Payment failed: Your card was declined
```

**Database Verification - New Booking:**
```sql
SELECT 
    payment_status,
    recurring_status,
    status
FROM bookings 
WHERE parent_booking_id = [original_booking_id]
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Output:**
- [ ] payment_status = 'failed'
- [ ] recurring_status = 'failed'
- [ ] status = 'pending'

**Original Booking Check:**
```sql
SELECT recurring_failed_attempts 
FROM bookings 
WHERE id = [original_booking_id];
```

**Expected Output:**
- [ ] recurring_failed_attempts = 1

**Notification Check:**
```sql
SELECT * FROM notifications 
WHERE user_id = [client_id]
AND type = 'Payments'
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Notification:**
- [ ] Title includes "Payment Failed"
- [ ] Message explains card was declined
- [ ] Instructions to update payment method

---

## 🧪 Test Suite 13: Multiple Bookings

### Test 13.1: Client with Multiple Active Recurring

**Setup:**
- Client has 3 active recurring bookings

**Steps:**
1. [ ] Login as client
2. [ ] Navigate to Payment Information → Recurring Contracts

**Expected Results:**
- [ ] All 3 bookings displayed in separate cards
- [ ] Header shows "3 Active" chip
- [ ] Each card shows correct details
- [ ] Can manage each independently
- [ ] Progress bars work correctly for each

### Test 13.2: Mixed Status Bookings

**Setup:**
- Client has:
  - 2 active recurring bookings
  - 1 paused recurring booking
  - 1 cancelled recurring booking

**Steps:**
1. [ ] Navigate to Recurring Contracts section

**Expected Results:**
- [ ] All 4 bookings visible
- [ ] Active bookings show green badges
- [ ] Paused booking shows yellow badge
- [ ] Cancelled booking shows red badge
- [ ] Appropriate actions for each status

---

## 🧪 Test Suite 14: Edge Cases

### Test 14.1: No Payment Method on File

**Scenario:**
- Client deleted all payment methods
- Recurring booking about to renew

**Steps:**
1. [ ] Delete client's Stripe payment methods
2. [ ] Run recurring command

**Expected Results:**
- [ ] Booking skipped (not processed)
- [ ] Error logged
- [ ] No failed booking created
- [ ] Client notified to add payment method

### Test 14.2: Stripe API Error

**Scenario:**
- Stripe API down or network error

**Steps:**
1. [ ] Temporarily disable Stripe API (wrong key)
2. [ ] Run recurring command

**Expected Results:**
- [ ] Graceful error handling
- [ ] Error logged with details
- [ ] Admin notified
- [ ] Booking marked as failed
- [ ] Client notified

### Test 14.3: Booking Ends on Leap Day

**Scenario:**
- Booking ends Feb 29 (leap year)

**Steps:**
1. [ ] Create booking ending Feb 29, 2024
2. [ ] Process renewal

**Expected Results:**
- [ ] New booking starts Mar 1, 2024
- [ ] Duration calculated correctly
- [ ] No date calculation errors

---

## 📊 Test Results Summary

### Overall Results
- [ ] Total Tests: 60+
- [ ] Passed: ___
- [ ] Failed: ___
- [ ] Blocked: ___
- [ ] Success Rate: ___%

### Critical Path Tests (Must Pass)
- [ ] Auto-enable on payment
- [ ] Cancel recurring (current service protected)
- [ ] Auto-renewal process
- [ ] Payment charged correctly
- [ ] Email reminders sent

### Known Issues
1. _List any bugs found during testing_
2. _..._

### Recommendations
1. _Improvements needed_
2. _..._

---

## 🔍 Manual Verification Checklist

### UI/UX
- [ ] All buttons have loading states
- [ ] Success/error messages clear
- [ ] Modals close properly
- [ ] Progress bars accurate
- [ ] Dates formatted correctly
- [ ] Mobile responsive

### Security
- [ ] Client can only access their bookings
- [ ] Payment info encrypted
- [ ] Stripe API keys secure
- [ ] SQL injection prevented
- [ ] XSS prevented

### Performance
- [ ] Dashboard loads in < 2 seconds
- [ ] Recurring section loads in < 1 second
- [ ] No N+1 queries
- [ ] Pagination working
- [ ] Efficient database queries

---

## 📝 Sign-Off

### Tested By
- Name: ___________________
- Date: ___________________
- Signature: _______________

### Approved By
- Name: ___________________
- Date: ___________________
- Signature: _______________

---

**Document Version**: 1.0  
**Last Updated**: January 10, 2026  
**Status**: Ready for Testing

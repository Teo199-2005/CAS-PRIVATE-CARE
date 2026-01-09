# Recurring Bookings Test Panel - User Guide

## 🧪 Test Panel Location

The **Recurring Booking Test Panel** is now available at the top of your **Client Dashboard** (below the email verification banner).

---

## 🎯 Testing Scenarios

### 1️⃣ **5 Days Remaining Test**

**Purpose:** Test the email reminder system and countdown banner display

**Steps:**
1. Select a paid booking from the dropdown
2. Click **"Set to 5 Days Before Renewal"** button
3. The system will:
   - Set service date so booking ends in 5 days
   - Enable auto-pay and recurring status
   - Make booking ready for reminders

**What to Check:**
- ✅ Click "Send Reminder Emails Now" button
- ✅ Check your email inbox for reminder email
- ✅ Check dashboard for countdown banner showing "5 days"
- ✅ Check notification center for in-app notification

---

### 2️⃣ **1 Day Remaining Test**

**Purpose:** Test urgent reminder display and countdown

**Steps:**
1. Select a paid booking from the dropdown
2. Click **"Set to 1 Day Before Renewal"** button
3. The system will:
   - Set service date so booking ends tomorrow
   - Enable auto-pay and recurring status
   - Show urgent warning

**What to Check:**
- ✅ Countdown banner shows RED alert
- ✅ Message says "Your Contract Renews Tomorrow!"
- ✅ Email reminder has urgent tone
- ✅ All contract details are accurate

---

### 3️⃣ **Fast Renewal Test (1 Minute)** ⚡

**Purpose:** Test the actual auto-renewal and Stripe charging

**⚠️ WARNING:** This will trigger a REAL charge to your Stripe account!

**Steps:**
1. **IMPORTANT:** Make sure you have a payment method saved
2. Select a paid booking from the dropdown
3. Click **"Finish Booking NOW (Renew in 1 min)"** button
4. Confirm the warning dialog
5. The system will:
   - Set service to have ended 1 minute ago
   - Enable auto-pay
   - Make booking ready for immediate renewal

**What to Do Next:**
```bash
# Run this command to process the renewal immediately:
php artisan bookings:process-recurring
```

OR click the **"Process Recurring Bookings Now"** button in the test panel

**What to Check:**
- ✅ Open your [Stripe Dashboard](https://dashboard.stripe.com/test/payments)
- ✅ Look for a new payment charge (within 1-2 minutes)
- ✅ Check your Client Dashboard for a new booking
- ✅ Verify the new booking has:
  - Same service type
  - Same duration (15 days)
  - Same hours per day
  - New service date (starting today/tomorrow)
  - Parent booking ID linked

---

## 🚀 Manual Trigger Buttons

### Send Reminder Emails Now
- Manually runs: `php artisan bookings:send-recurring-reminders`
- Sends emails for ALL bookings within 5 days of renewal
- Shows count of emails sent and notifications created

### Process Recurring Bookings Now
- Manually runs: `php artisan bookings:process-recurring`
- Processes ALL bookings ready for renewal
- **⚠️ WILL CHARGE CARDS!**
- Creates new bookings automatically
- Shows count of processed, created, charged, and failed

---

## 📊 Status Messages

After each test action, you'll see detailed status messages showing:
- ✅ Success/failure status
- 📅 Updated dates (service date, end date, renewal date)
- 💳 Amount that will be charged
- 🔔 Number of emails/notifications sent
- 🔗 Links to Stripe dashboard

---

## 🧪 Complete Test Flow

### **Recommended Test Sequence:**

#### Step 1: Test Email Reminders (Safe)
```
1. Select Booking #5
2. Click "Set to 5 Days Before Renewal"
3. Click "Send Reminder Emails Now"
4. Check email and dashboard banner
```

#### Step 2: Test Countdown Progression (Safe)
```
1. Select same booking
2. Click "Set to 1 Day Before Renewal"
3. Check banner turns RED
4. Check email has urgent tone
```

#### Step 3: Test Actual Renewal (⚠️ CHARGES CARD)
```
1. Select same booking
2. Click "Finish Booking NOW"
3. Confirm warning
4. Click "Process Recurring Bookings Now"
5. Wait 10-30 seconds
6. Check Stripe dashboard for charge
7. Check Client Dashboard for new booking
```

---

## 💳 Monitoring Stripe

### Stripe Dashboard Links:
- **Test Payments:** https://dashboard.stripe.com/test/payments
- **Test Customers:** https://dashboard.stripe.com/test/customers

### What to Look For:
1. **Payment Intent** - Shows the charge being processed
2. **Amount** - Should match booking calculation (hours × days × rate)
3. **Customer** - Should show your client email
4. **Status** - Should be "Succeeded"
5. **Description** - Should reference the booking ID

---

## 📧 Email Testing

### Check Your Inbox For:
- **Subject:** "Reminder: Your Contract Renews in X Days"
- **From:** CAS Private Care
- **Content:**
  - Countdown badge in header
  - Contract details table
  - Amount to be charged
  - "Manage Your Contract" button

### Email Should Include:
- ✅ Booking ID
- ✅ Service type
- ✅ Duration (days)
- ✅ Hours per day
- ✅ Renewal date (formatted)
- ✅ Total amount

---

## 🎨 Dashboard Elements to Verify

### Countdown Banner Should Show:
- 🔔 Icon with pulse animation
- 📅 Days remaining (color-coded)
- 💰 Amount to be charged
- 📋 Booking details
- ⚙️ "Manage" button (goes to Payment Info)
- ℹ️ "Details" button (opens dialog)
- ❌ Close button (dismissible)

### Notification Center Should Show:
- 🔔 New notification
- 📅 Days until renewal
- 💰 Amount
- 🔗 Link to Payment Information

---

## ⚠️ Important Notes

### Before Testing Actual Renewal:
1. ✅ Have a valid payment method saved
2. ✅ Check your Stripe customer ID is set
3. ✅ Use test mode (not live mode)
4. ✅ Be ready to monitor Stripe dashboard
5. ✅ Understand a real charge will be made (in test mode)

### After Testing:
1. Check `storage/logs/laravel.log` for processing logs
2. Check `storage/logs/recurring-bookings.log` for renewal logs
3. Check `storage/logs/recurring-reminders.log` for email logs
4. Verify database changes in `bookings` table

---

## 🐛 Troubleshooting

### No Email Received?
- Check `.env` mail settings
- Check `storage/logs/laravel.log` for email errors
- Verify booking meets criteria (auto_pay_enabled, recurring_status=active)

### No Charge in Stripe?
- Verify user has `stripe_customer_id`
- Check booking `payment_method_id` is set
- Look for errors in `storage/logs/recurring-bookings.log`
- Ensure service date + duration_days < now

### Countdown Banner Not Showing?
- Verify booking is within 5 days of renewal
- Check browser console for errors
- Refresh the page
- Check API response: `/client/recurring/upcoming-renewals`

---

## 🎓 Understanding the Results

### Successful Test Shows:
1. **Email:** Received with correct details and countdown
2. **Banner:** Displayed with correct days remaining
3. **Notification:** Created in notification center
4. **Stripe:** Payment shows in dashboard (for renewal test)
5. **New Booking:** Created automatically with parent_booking_id
6. **Amount:** Correctly calculated (hours × days × rate)

---

## 🔄 Reset Testing

To reset a booking after testing:
1. Go to database → `bookings` table
2. Find the test booking
3. Reset fields:
   - `recurring_service = false`
   - `auto_pay_enabled = false`
   - `recurring_status = null`
   - `service_date = original date`

OR just use a different booking for each test!

---

## 📞 Support

If you encounter issues:
1. Check the status message in the test panel
2. Review Laravel logs
3. Verify Stripe configuration
4. Ensure payment method is saved
5. Check browser console for frontend errors

---

**Happy Testing! 🎉**

Remember: The test panel is for development/testing only. Remove it before going to production!

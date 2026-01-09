# 🔄 Recurring Booking System - Complete User Guide

## 📋 Overview

The CAS Private Care platform features an **automatic recurring booking system** that ensures continuous care services for your clients. Once a booking is approved and paid, it automatically becomes a recurring contract with auto-renewal enabled.

---

## ✅ How It Works

### 1️⃣ **Booking Becomes Recurring Upon Payment**

When a client:
1. Submits a booking request
2. Admin approves the booking
3. Client pays for the booking using a saved payment method

**The booking automatically becomes recurring with these settings:**
```php
✓ recurring_service = true
✓ auto_pay_enabled = true
✓ recurring_status = 'active'
```

**This means:**
- The contract will automatically renew when it ends
- The client's saved payment method will be charged automatically
- A new booking will be created with the same schedule and details

---

### 2️⃣ **Contract Lifecycle**

```
┌─────────────────────────────────────────────────────────────────┐
│ Day 1: Booking Starts                                          │
│ ↓                                                              │
│ Service Period (e.g., 15 days)                                │
│ ↓                                                              │
│ Day -5: Email reminder sent                                   │
│ Day -4: Email reminder sent                                   │
│ Day -3: Email reminder sent                                   │
│ Day -2: Email reminder sent                                   │
│ Day -1: Email reminder sent + Dashboard countdown banner     │
│ ↓                                                              │
│ Last Day: Contract End Date                                   │
│ ↓                                                              │
│ AUTOMATIC RENEWAL TRIGGERED (1:00 AM next day)               │
│ ├─ Client's saved card is charged                           │
│ ├─ New booking is created (continuation)                    │
│ ├─ Payment record is saved                                  │
│ ├─ Caregiver is auto-assigned (same as previous)           │
│ └─ Success notification sent to client                      │
│ ↓                                                              │
│ New Service Period Begins                                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Client Controls

Clients have full control over their recurring contracts from the **Payment Information** page:

### Available Actions:

#### ✅ **Keep Active (Default)**
- Auto-renewal continues
- Contract automatically renews on end date
- Payment is charged automatically

#### ⏸️ **Pause Auto-Renewal**
- Temporarily stops automatic renewals
- Current contract completes as scheduled
- No charge on renewal date
- Can be resumed anytime during current period

#### ▶️ **Resume Auto-Renewal**
- Reactivates automatic renewals
- Next renewal will proceed normally
- Payment will be charged on renewal date

#### ❌ **Cancel Recurring**
- **Permanently disables auto-renewal**
- **Current contract completes as scheduled**
- **No new booking is created after end date**
- **No automatic payment on renewal date**
- Client must manually book new service if needed

---

## 💳 What Happens After Cancellation

When a client cancels recurring:

### ✓ **Current Contract Continues**
```
Example:
- Service Period: Jan 9 - Jan 24, 2026 (15 days)
- Cancel Date: Jan 15, 2026
- Contract Status: Active until Jan 24, 2026
- What Happens: Service continues normally until Jan 24
```

### ❌ **Auto-Renewal Stops**
```
- End Date: Jan 24, 2026 at 11:59 PM
- Expected Renewal: Jan 25, 2026 at 1:00 AM
- Actual Result: No renewal, no charge, contract ends
- Database Update: recurring_status = 'cancelled', auto_pay_enabled = false
```

### 📧 **Notifications**
Client receives:
- In-app notification confirming cancellation
- Clear message: "Your current service period will complete as scheduled, but no new bookings will be created automatically."

---

## 🔔 Reminder System

### Email Reminders
Automated emails sent **daily at 9:00 AM**:

| Days Before Renewal | Subject Line | Content |
|---------------------|--------------|---------|
| 5 days | Contract Renews in 5 Days | Booking details, amount, renewal date |
| 4 days | Contract Renews in 4 Days | Booking details, amount, renewal date |
| 3 days | Contract Renews in 3 Days | Booking details, amount, renewal date |
| 2 days | Contract Renews in 2 Days | Booking details, amount, renewal date |
| 1 day | Contract Renews Tomorrow | Booking details, amount, renewal date, **HIGH PRIORITY** |

### Dashboard Countdown Banner
- Appears 5 days before renewal
- Shows days remaining
- Displays next charge amount
- Click to navigate to Payment Information

---

## 📊 Payment Information Dashboard

### Recurring Contracts Section

Displays all active recurring bookings with:

#### 📈 **Progress Bar**
- Visual indicator of service period completion
- Shows days remaining
- Updates in real-time

#### 📅 **Contract Details**
```
┌─────────────────────────────────────────┐
│ Booking #11                             │
│ Status: Active ✓                        │
│                                         │
│ Service: Caregiver                      │
│ Schedule: 8 hours/day                   │
│ Duration: 15 days                       │
│                                         │
│ Current Period: Jan 9 - Jan 24, 2026   │
│ Next Charge: Jan 25, 2026              │
│ Amount: $5,400                          │
│ Renewals: 0 times                       │
│                                         │
│ Service Progress: [████████░░] 53%     │
│ 8 days remaining                        │
│                                         │
│ [Pause] [Cancel] [View History]        │
└─────────────────────────────────────────┘
```

#### 🔍 **View History**
- Shows all renewals in the booking chain
- Displays total paid across all renewals
- Lists each service period with dates and amounts

---

## 🛠️ Backend Automation

### Scheduled Commands

#### 1. Process Recurring Bookings
```bash
Command: php artisan bookings:process-recurring
Schedule: Daily at 1:00 AM
Log File: storage/logs/recurring-bookings.log
```

**What It Does:**
1. Finds all bookings ending today with `recurring_status = 'active'`
2. For each booking:
   - Creates new booking with same details
   - Charges client's saved payment method
   - Records payment in database
   - Auto-assigns same caregiver(s)
   - Sends success/failure notification
   - Updates recurring count

#### 2. Send Reminder Emails
```bash
Command: php artisan bookings:send-recurring-reminders
Schedule: Daily at 9:00 AM
```

**What It Does:**
1. Finds bookings renewing in 5, 4, 3, 2, or 1 days
2. Sends professional email reminder
3. Creates in-app notification
4. Logs email delivery status

---

## 💡 Example Scenarios

### Scenario 1: Continuous Care (Happy Path)
```
✅ Client books caregiver for Jan 9-24 (15 days, $5,400)
✅ Admin approves booking
✅ Client pays with saved card
   → Recurring auto-enabled

📧 Jan 19: Reminder email "5 days until renewal"
📧 Jan 20: Reminder email "4 days until renewal"
📧 Jan 21: Reminder email "3 days until renewal"
📧 Jan 22: Reminder email "2 days until renewal"
📧 Jan 23: Reminder email "1 day until renewal"

🔄 Jan 25, 1:00 AM: Auto-renewal triggered
   → Card charged $5,400
   → New booking created (Jan 25 - Feb 9)
   → Same caregiver assigned
   → Client notified of successful renewal

✅ Service continues seamlessly
```

### Scenario 2: Client Cancels Recurring
```
✅ Client books caregiver for Jan 9-24 (15 days, $5,400)
✅ Service starts Jan 9
✅ Recurring is active

❌ Jan 15: Client cancels recurring
   → recurring_status = 'cancelled'
   → auto_pay_enabled = false
   → Notification sent

✅ Jan 9-24: Service continues normally
❌ Jan 25: No renewal, no charge, contract ends
📌 Client must manually book new service if needed
```

### Scenario 3: Client Pauses Then Resumes
```
✅ Booking active Jan 9-24

⏸️ Jan 12: Client pauses recurring
   → recurring_status = 'paused'
   → No charge will occur on Jan 25

▶️ Jan 18: Client resumes recurring
   → recurring_status = 'active'
   → Renewal will proceed on Jan 25

🔄 Jan 25: Auto-renewal triggered normally
```

---

## 🔐 Security & Payment

### Payment Method Requirements
- Client must have saved payment method on file
- Payment method stored securely via Stripe
- PCI compliant encryption

### Charge Authorization
- Client authorizes recurring charges upon first payment
- Can revoke authorization anytime by canceling recurring
- Full transparency with reminder emails

### Failure Handling
If payment fails:
- Client receives failure notification
- Booking marked as `payment_status = 'failed'`
- Original booking tracks failed attempts
- No new booking is created
- Client must resolve payment issue manually

---

## 📱 UI Components

### 1. Payment Confirmation Modal
Shows when client clicks "Pay Now":
- Lists saved payment methods
- Displays booking amount
- Shows message: "Auto-renewal will be enabled"
- Purple gradient design
- Lock icon for security

### 2. Payment Success Modal
After successful payment:
- Green checkmark animation
- Message: "Payment successful! Auto-renewal has been enabled for this contract."
- Auto-redirects to dashboard

### 3. Recurring Bookings Manager
Located in Payment Information section:
- Card-based layout
- Status badges (Active, Paused, Cancelled)
- Progress bars for each contract
- Action buttons (Pause/Resume/Cancel)
- History viewer

### 4. Renewal Countdown Banner
Appears 5 days before renewal:
- Orange alert styling
- Days remaining counter
- Next charge amount
- Click to navigate to Payment Information

---

## 🧪 Testing Checklist

### Test Case 1: New Booking Flow
- [ ] Create booking
- [ ] Get admin approval
- [ ] Pay with saved card
- [ ] Verify recurring_service = true
- [ ] Verify auto_pay_enabled = true
- [ ] Verify recurring_status = 'active'
- [ ] Check success message mentions auto-renewal

### Test Case 2: Cancel Recurring
- [ ] Have active recurring booking
- [ ] Click "Cancel Recurring"
- [ ] Confirm cancellation
- [ ] Verify recurring_status = 'cancelled'
- [ ] Verify current service continues
- [ ] Verify no renewal on end date

### Test Case 3: Pause/Resume
- [ ] Have active recurring booking
- [ ] Click "Pause Auto-Renewal"
- [ ] Verify recurring_status = 'paused'
- [ ] Click "Resume Auto-Renewal"
- [ ] Verify recurring_status = 'active'

### Test Case 4: Auto-Renewal
- [ ] Have booking ending tomorrow
- [ ] Run: `php artisan bookings:process-recurring --dry-run`
- [ ] Verify output shows booking will be renewed
- [ ] Run: `php artisan bookings:process-recurring`
- [ ] Verify new booking created
- [ ] Verify payment recorded
- [ ] Verify notification sent

### Test Case 5: Reminder Emails
- [ ] Have booking ending in 5 days
- [ ] Run: `php artisan bookings:send-recurring-reminders`
- [ ] Verify email sent
- [ ] Verify in-app notification created

---

## 📁 Key Files

### Backend
- `app/Console/Commands/ProcessRecurringBookings.php` - Auto-renewal processor
- `app/Console/Commands/SendRecurringReminderEmails.php` - Email reminder sender
- `app/Http/Controllers/RecurringBookingController.php` - API endpoints
- `app/Http/Controllers/ClientPaymentController.php` - Payment processing
- `app/Console/Kernel.php` - Scheduler configuration

### Frontend
- `resources/js/components/RecurringBookingsManager.vue` - Main UI component
- `resources/js/components/RecurringRenewalCountdown.vue` - Countdown banner
- `resources/js/components/ClientPaymentMethods.vue` - Payment methods page
- `resources/js/components/ClientDashboard.vue` - Dashboard integration

### Database
- `bookings` table - Stores booking and recurring info
- `payments` table - Tracks payment records
- `notifications` table - In-app notifications

### Email
- `resources/views/emails/recurring-reminder.blade.php` - Email template

---

## 🎓 Summary

### Key Points

✅ **Auto-Enabled**: Recurring is automatically enabled when booking is paid

✅ **Client Control**: Clients can pause or cancel anytime

✅ **Current Contract Protected**: Cancellation doesn't affect current service period

✅ **No Surprise Charges**: 5 reminder emails sent before renewal

✅ **Seamless Continuity**: Same caregiver, same schedule, automatic renewal

✅ **Transparent**: Full history and progress tracking in dashboard

---

## 🆘 Support

### For Clients
- Navigate to **Payment Information** → **Recurring Contracts**
- View all active recurring bookings
- Manage each contract individually
- Contact support if payment fails

### For Admins
- Monitor recurring bookings in admin dashboard
- Check logs: `storage/logs/recurring-bookings.log`
- Manually run: `php artisan bookings:process-recurring --dry-run`
- Review failed payments in admin panel

---

## 📞 Contact

For questions or issues:
- **Client Portal**: Payment Information section
- **Email**: support@casprivatecare.com
- **Phone**: (555) 123-4567

---

**Last Updated**: January 10, 2026  
**Version**: 1.2.0  
**System**: CAS Private Care - Recurring Booking System

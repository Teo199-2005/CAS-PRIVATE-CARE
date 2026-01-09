# 🎯 Recurring Booking System - Visual Flow Diagram

## Complete System Flow

```
╔═══════════════════════════════════════════════════════════════════════╗
║                    RECURRING BOOKING LIFECYCLE                        ║
╚═══════════════════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 1: BOOKING CREATION                                            │
└─────────────────────────────────────────────────────────────────────┘

    Client Action                  System State
    ─────────────                  ────────────
    📝 Submit Booking      →       status: 'pending'
                                   payment_status: null
                                   recurring_service: false


┌─────────────────────────────────────────────────────────────────────┐
│ STEP 2: ADMIN APPROVAL                                              │
└─────────────────────────────────────────────────────────────────────┘

    Admin Action                   System State
    ────────────                   ────────────
    ✅ Approve Booking     →       status: 'approved'
                                   payment_status: 'unpaid'
                                   recurring_service: false


┌─────────────────────────────────────────────────────────────────────┐
│ STEP 3: CLIENT PAYMENT (CRITICAL MOMENT)                            │
└─────────────────────────────────────────────────────────────────────┘

    Client Action                  System State
    ────────────                   ────────────
    💳 Click "Pay Now"     →      Payment Modal Opens
    💳 Select Saved Card   →      Shows amount
    💳 Confirm Payment     →      Processing...

    ✅ Payment Success     →      ┌─────────────────────────────┐
                                  │ AUTOMATIC UPDATE:           │
                                  │ • payment_status = 'paid'   │
                                  │ • recurring_service = TRUE  │
                                  │ • auto_pay_enabled = TRUE   │
                                  │ • recurring_status = 'active'│
                                  └─────────────────────────────┘

    Response Message:
    "Payment successful! Auto-renewal has been enabled for this contract."


┌─────────────────────────────────────────────────────────────────────┐
│ STEP 4: SERVICE PERIOD (e.g., 15 days)                              │
└─────────────────────────────────────────────────────────────────────┘

    Timeline                       System Actions
    ────────                       ──────────────
    Day 1-10: Service Active      • Caregiver provides care
                                  • Client can manage recurring

    Day 10 (5 days before):       📧 Email Reminder #1
                                  📱 In-app notification
                                  Subject: "Contract Renews in 5 Days"

    Day 11 (4 days before):       📧 Email Reminder #2
                                  Subject: "Contract Renews in 4 Days"

    Day 12 (3 days before):       📧 Email Reminder #3
                                  Subject: "Contract Renews in 3 Days"

    Day 13 (2 days before):       📧 Email Reminder #4
                                  Subject: "Contract Renews in 2 Days"

    Day 14 (1 day before):        📧 Email Reminder #5 (HIGH PRIORITY)
                                  🖥️ Dashboard Countdown Banner
                                  Subject: "Contract Renews Tomorrow"

    Day 15 (Last Day):            • Service completes at 11:59 PM
                                  • Contract ends
                                  • Auto-renewal scheduled for 1:00 AM


┌─────────────────────────────────────────────────────────────────────┐
│ STEP 5: AUTO-RENEWAL (1:00 AM Next Day)                            │
└─────────────────────────────────────────────────────────────────────┘

    System Check:
    ─────────────
    IF recurring_status = 'active' AND auto_pay_enabled = true:

        ┌───────────────────────────────────────────────────┐
        │ AUTOMATIC PROCESS:                                │
        │                                                   │
        │ 1. 💳 Charge saved payment method                │
        │    Amount: $5,400 (same as original)            │
        │                                                   │
        │ 2. 📝 Create new booking                         │
        │    service_date: Day 16 (tomorrow)               │
        │    duration_days: 15 (same as original)          │
        │    hourly_rate: $45 (same as original)           │
        │    duty_type: Same schedule                      │
        │    status: 'approved'                            │
        │    payment_status: 'paid'                        │
        │    recurring_service: true                       │
        │    auto_pay_enabled: true                        │
        │    recurring_status: 'active'                    │
        │    parent_booking_id: [original booking ID]      │
        │                                                   │
        │ 3. 💰 Record payment                             │
        │    Create Payment record in database             │
        │                                                   │
        │ 4. 👤 Auto-assign caregiver                     │
        │    Same caregiver as previous booking            │
        │                                                   │
        │ 5. 📧 Send success notification                  │
        │    Email + In-app notification                   │
        │                                                   │
        │ 6. 📊 Update recurring count                     │
        │    Original booking: recurring_count += 1        │
        │                                                   │
        └───────────────────────────────────────────────────┘

        Result: New service period begins seamlessly!


    ELSE IF recurring_status = 'cancelled' OR 'paused':

        ┌───────────────────────────────────────────────────┐
        │ NO ACTION TAKEN:                                  │
        │                                                   │
        │ ❌ No payment charged                             │
        │ ❌ No new booking created                         │
        │ ✅ Contract ends as scheduled                     │
        │                                                   │
        │ Client notified: "Your contract has ended."      │
        │                                                   │
        └───────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│ STEP 6: CLIENT MANAGEMENT OPTIONS (Anytime During Service)          │
└─────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════╗
║ OPTION A: KEEP ACTIVE (Default - No Action Required)             ║
╚═══════════════════════════════════════════════════════════════════╝

    Client: Does nothing
    Result: Auto-renewal continues as designed
    
    Timeline:
    Day 1-15:  ✅ Service active
    Day 16+:   ✅ Auto-renewal triggered
               ✅ New booking created
               ✅ Payment charged
               ✅ Service continues


╔═══════════════════════════════════════════════════════════════════╗
║ OPTION B: PAUSE AUTO-RENEWAL                                      ║
╚═══════════════════════════════════════════════════════════════════╝

    Client: Clicks "Pause Auto-Renewal" (e.g., on Day 8)
    
    System Update:
    • auto_pay_enabled = false
    • recurring_status = 'paused'
    
    Timeline:
    Day 1-8:   ✅ Service active
    Day 8:     ⏸️ Client pauses recurring
    Day 9-15:  ✅ Service continues
    Day 16:    ❌ No auto-renewal
               ❌ No payment charged
               ✅ Contract ends

    Client can resume anytime:
    • Click "Resume Auto-Renewal"
    • recurring_status = 'active'
    • Next renewal will proceed


╔═══════════════════════════════════════════════════════════════════╗
║ OPTION C: CANCEL RECURRING                                        ║
╚═══════════════════════════════════════════════════════════════════╝

    Client: Clicks "Cancel Recurring" (e.g., on Day 10)
    
    Confirmation Modal:
    ┌─────────────────────────────────────────────────────────────┐
    │ ⚠️ Cancel Recurring Payments?                               │
    │                                                             │
    │ Are you sure you want to cancel automatic renewals?        │
    │                                                             │
    │ ℹ️ Your current service period will complete as scheduled. │
    │   No new bookings will be created automatically.           │
    │                                                             │
    │   [Keep Active]  [Cancel Recurring]                        │
    └─────────────────────────────────────────────────────────────┘

    System Update:
    • recurring_service = false
    • auto_pay_enabled = false
    • recurring_status = 'cancelled'
    
    Notification Sent:
    "You have cancelled recurring payments for booking #11. 
     Your current service period will complete as scheduled, 
     but no new bookings will be created automatically."

    Timeline:
    Day 1-10:  ✅ Service active
    Day 10:    ❌ Client cancels recurring
    Day 11-15: ✅ Service continues (protected)
    Day 16:    ❌ No auto-renewal
               ❌ No payment charged
               ✅ Contract ends permanently

    Important: Cancellation is PERMANENT
    Client must create a new booking manually if they want service to continue


╔═══════════════════════════════════════════════════════════════════╗
║ OPTION D: RESUME AFTER PAUSE                                      ║
╚═══════════════════════════════════════════════════════════════════╝

    Client: Paused on Day 8, Resumes on Day 12
    
    System Update:
    • auto_pay_enabled = true
    • recurring_status = 'active'
    
    Timeline:
    Day 1-8:   ✅ Service active
    Day 8:     ⏸️ Client pauses
    Day 9-12:  ✅ Service continues (paused)
    Day 12:    ▶️ Client resumes
    Day 13-15: ✅ Service continues (active)
    Day 16:    ✅ Auto-renewal triggered
               ✅ Payment charged
               ✅ Service continues


┌─────────────────────────────────────────────────────────────────────┐
│ PAYMENT FAILURE HANDLING                                            │
└─────────────────────────────────────────────────────────────────────┘

    If payment fails during auto-renewal:

    ┌───────────────────────────────────────────────────┐
    │ SYSTEM ACTIONS:                                   │
    │                                                   │
    │ 1. ❌ New booking marked as 'payment_status:     │
    │       failed'                                     │
    │                                                   │
    │ 2. 📊 Original booking: recurring_failed_        │
    │       attempts += 1                               │
    │                                                   │
    │ 3. 📧 Send failure notification to client        │
    │       Subject: "Recurring Payment Failed"        │
    │       Message: "We couldn't charge your card.    │
    │                Please update your payment method."│
    │                                                   │
    │ 4. 📧 Send alert to admin                        │
    │       Admin dashboard shows failed payment       │
    │                                                   │
    │ 5. ❌ No new service begins                      │
    │       Client must resolve payment issue          │
    │                                                   │
    └───────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│ DATABASE TRACKING                                                   │
└─────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════╗
║ Original Booking (ID: 11)                                         ║
╚═══════════════════════════════════════════════════════════════════╝
│ service_date: 2026-01-09                                          │
│ duration_days: 15                                                 │
│ end_date: 2026-01-24                                              │
│ payment_status: 'paid'                                            │
│ recurring_service: true                                           │
│ auto_pay_enabled: true                                            │
│ recurring_status: 'active'                                        │
│ recurring_count: 0 → 1 → 2 → 3 (increments with each renewal)    │
│ last_recurring_charge_date: 2026-01-25                           │
│ parent_booking_id: null (this is the root)                       │
└───────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════╗
║ First Renewal (ID: 25)                                            ║
╚═══════════════════════════════════════════════════════════════════╝
│ service_date: 2026-01-25                                          │
│ duration_days: 15                                                 │
│ end_date: 2026-02-09                                              │
│ payment_status: 'paid'                                            │
│ recurring_service: true                                           │
│ auto_pay_enabled: true                                            │
│ recurring_status: 'active'                                        │
│ parent_booking_id: 11 (links to original)                        │
└───────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════╗
║ Second Renewal (ID: 42)                                           ║
╚═══════════════════════════════════════════════════════════════════╝
│ service_date: 2026-02-10                                          │
│ duration_days: 15                                                 │
│ end_date: 2026-02-25                                              │
│ payment_status: 'paid'                                            │
│ recurring_service: true                                           │
│ auto_pay_enabled: true                                            │
│ recurring_status: 'active'                                        │
│ parent_booking_id: 11 (links to original)                        │
└───────────────────────────────────────────────────────────────────┘

    Booking Chain View (in "View History"):
    ┌─────────────────────────────────────────┐
    │ Total Paid: $16,200                     │
    │ Total Renewals: 2                       │
    │                                         │
    │ 📅 Jan 9-24  → $5,400 (Original)       │
    │ 📅 Jan 25-Feb 9  → $5,400 (Renewal 1)  │
    │ 📅 Feb 10-25 → $5,400 (Renewal 2)      │
    └─────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│ AUTOMATED TASKS (Laravel Scheduler)                                │
└─────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════╗
║ Task 1: Process Recurring Bookings                                ║
╚═══════════════════════════════════════════════════════════════════╝
│ Command: php artisan bookings:process-recurring                   │
│ Schedule: Daily at 1:00 AM                                        │
│ Log: storage/logs/recurring-bookings.log                          │
│                                                                   │
│ What It Does:                                                     │
│ 1. Find all bookings ending today                                │
│ 2. Check if recurring_status = 'active'                          │
│ 3. Charge client's saved payment method                          │
│ 4. Create new booking                                             │
│ 5. Auto-assign caregiver                                          │
│ 6. Send notifications                                             │
└───────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════╗
║ Task 2: Send Reminder Emails                                      ║
╚═══════════════════════════════════════════════════════════════════╝
│ Command: php artisan bookings:send-recurring-reminders           │
│ Schedule: Daily at 9:00 AM                                        │
│                                                                   │
│ What It Does:                                                     │
│ 1. Find bookings renewing in 5, 4, 3, 2, or 1 days              │
│ 2. Send professional email reminder                              │
│ 3. Create in-app notification                                    │
│ 4. Log email delivery status                                     │
└───────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│ SUMMARY: KEY STATES                                                 │
└─────────────────────────────────────────────────────────────────────┘

recurring_status = 'active'
  ✅ Auto-renewal will proceed
  ✅ Payment will be charged
  ✅ New booking will be created

recurring_status = 'paused'
  ⏸️ Auto-renewal temporarily disabled
  ✅ Current service continues
  ❌ No charge on renewal date
  ▶️ Can be resumed anytime

recurring_status = 'cancelled'
  ❌ Auto-renewal permanently disabled
  ✅ Current service continues
  ❌ No charge on renewal date
  ❌ Cannot be resumed (client must create new booking)


┌─────────────────────────────────────────────────────────────────────┐
│ CLIENT EXPERIENCE SUMMARY                                           │
└─────────────────────────────────────────────────────────────────────┘

✅ BENEFITS:
  • Continuous care without interruption
  • No need to rebook every time
  • Same caregiver assigned automatically
  • Transparent with 5 reminders
  • Full control to pause or cancel

⚠️ IMPORTANT:
  • Auto-enabled on first payment (not opt-in)
  • Client must actively cancel if they don't want renewal
  • Cancellation is permanent
  • Current service always protected

🔐 SECURITY:
  • Stripe handles all payment processing
  • PCI compliant
  • Client authorizes recurring on first payment
  • Can revoke authorization anytime


╔═══════════════════════════════════════════════════════════════════╗
║ SYSTEM DESIGNED TO: Ensure continuous care + Client control      ║
╚═══════════════════════════════════════════════════════════════════╝
```

---

**Last Updated**: January 10, 2026  
**Purpose**: Visual reference for recurring booking system flow  
**For**: Development team and stakeholder review

# 🔄 STRIPE INTEGRATION - COMPLETE SYSTEM FLOW

**CAS Private Care LLC - Comprehensive Payment & Commission System**

Last Updated: January 5, 2026

---

## 📋 TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [User Roles & Responsibilities](#user-roles--responsibilities)
3. [Complete Booking & Payment Flow](#complete-booking--payment-flow)
4. [Commission Calculation System](#commission-calculation-system)
5. [Payout Schedules](#payout-schedules)
6. [Button-by-Button Flow](#button-by-button-flow)
7. [Technical Implementation](#technical-implementation)
8. [API Endpoints Reference](#api-endpoints-reference)

---

## 🎯 SYSTEM OVERVIEW

### **Architecture**
```
Client Payments (via Stripe) 
    ↓
Platform Holds Funds
    ↓
Distributes To:
    ├── Caregiver (Hourly Rate - via Stripe Connect)
    ├── Marketing Partner ($1/hour commission - via Stripe Connect)
    ├── Training Center ($0.50/hour commission - via Stripe Connect)
    └── Platform (Remaining balance as service fee)
```

### **Stripe Integration Components**
1. **Stripe Checkout** - Client payment processing
2. **Stripe Connect** - Caregiver/Partner payouts
3. **Stripe Setup Intents** - Save payment methods
4. **Stripe Transfers** - Commission payouts
5. **Stripe Webhooks** - Real-time event handling

---

## 👥 USER ROLES & RESPONSIBILITIES

### **1. CLIENT**
- Books caregiver services
- Adds payment method (credit/debit card)
- Gets charged based on actual hours worked
- Receives receipts and booking confirmations

### **2. CAREGIVER**
- Provides care services
- Tracks time (clock in/out)
- Connects bank account via Stripe Connect
- Receives weekly payouts for hours worked

### **3. MARKETING PARTNER**
- Refers clients using referral codes
- Earns $1/hour commission on referred bookings
- Connects bank account via Stripe Connect
- Receives monthly commission payouts

### **4. TRAINING CENTER**
- Trains caregivers
- Earns $0.50/hour commission per trained caregiver
- Connects bank account via Stripe Connect
- Receives monthly commission payouts

### **5. ADMIN/ADMIN STAFF**
- Approves/rejects bookings
- Assigns caregivers to bookings
- Processes payments manually if needed
- Pays commissions to partners
- Monitors all transactions

---

## 🔄 COMPLETE BOOKING & PAYMENT FLOW

### **PHASE 1: CLIENT BOOKS SERVICE**

#### **Step 1.1: Client Navigates to Booking**
**Location:** Client Dashboard → "Book Now" Button

**Frontend:** `ClientDashboard.vue`
```javascript
const attemptBooking = async () => {
  // Check if client has pending/approved bookings
  if (hasPendingBooking) → Show error, redirect to My Bookings (Pending)
  if (hasApprovedBooking) → Show error, redirect to My Bookings (Approved)
  
  // All clear → Navigate to booking form
  currentSection.value = 'book-form';
}
```

**What Happens:**
- ✅ System checks for existing bookings (limit: 1 active at a time)
- ✅ If clear, shows booking form
- ❌ If blocked, redirects to existing booking

---

#### **Step 1.2: Client Fills Booking Form**
**Location:** Client Dashboard → Book Form Section

**Form Fields:**
- Service Type (Live-in/Live-out)
- Duty Type (12hr/24hr Day/Night)
- Start Date
- Duration (days)
- Service Address
- Borough Selection
- Special Requests
- Referral Code (optional)

**Pricing Calculation:**
```javascript
const calculateBookingPrice = () => {
  const hours = extractHours(dutyType); // 12 or 24
  const days = durationDays;
  const rate = referralCode ? 40 : 45; // $5 discount with referral
  
  const subtotal = hours * days * rate;
  const discount = referralCode ? (subtotal * 0.1) : 0; // Example discount
  const total = subtotal - discount;
  
  return total;
}
```

**Frontend Validation:**
- All required fields must be filled
- Start date must be in the future
- Duration must be at least 1 day
- Address must be in supported boroughs

---

#### **Step 1.3: Client Submits Booking**
**Button:** "Submit Booking Request"

**Backend API:** `POST /api/bookings`

**Controller:** `BookingController@store`

**What Happens:**
1. ✅ **Validate booking data**
2. ✅ **Check client limit** (no pending/approved bookings)
3. ✅ **Validate referral code** (if provided)
4. ✅ **Calculate total price** with discounts
5. ✅ **Create booking record** (status: 'pending')
6. ✅ **Store in database** with all details
7. ✅ **Send notification** to admin for review

**Database Record:**
```php
Booking {
  id: 123,
  client_id: 45,
  service_type: 'live-in',
  duty_type: '12hr-day',
  start_date: '2026-01-10',
  duration_days: 7,
  hourly_rate: 40, // With referral discount
  total_price: 3360, // 12hrs * 7days * $40
  status: 'pending',
  payment_status: 'unpaid',
  referral_code_id: 5,
  created_at: '2026-01-05 10:30:00'
}
```

**Notifications:**
- ✉️ **Client:** "Booking request submitted successfully"
- ✉️ **Admin:** "New booking request from [Client Name]"

---

### **PHASE 2: ADMIN REVIEWS & ASSIGNS**

#### **Step 2.1: Admin Reviews Booking**
**Location:** Admin Dashboard → Bookings Tab → Pending

**What Admin Sees:**
- Client name and contact
- Service details (type, duration, location)
- Requested dates
- Total price
- Payment status: "Unpaid"

**Admin Actions Available:**
- ✅ Approve Booking
- ❌ Reject Booking
- 👁️ View Full Details

---

#### **Step 2.2: Admin Approves Booking**
**Button:** "Approve Booking"

**Backend API:** `POST /api/admin/bookings/{id}/approve`

**What Happens:**
1. ✅ **Update booking status** → 'approved'
2. ✅ **Set approved_at** timestamp
3. ✅ **Create notification** for client
4. ✅ **Update admin dashboard** stats

**Database Update:**
```php
Booking {
  id: 123,
  status: 'approved', // Changed from 'pending'
  approved_at: '2026-01-05 14:00:00',
  approved_by: 1 // Admin ID
}
```

**Notifications:**
- ✉️ **Client:** "Your booking has been approved! We're assigning a caregiver."
- 📊 **Dashboard:** Pending count decreases, Approved count increases

---

#### **Step 2.3: Admin Assigns Caregiver(s)**
**Button:** "Assign Caregiver"

**Location:** Admin Dashboard → Bookings → Booking Details → Assign Caregivers Tab

**What Happens:**
1. ✅ **Admin selects available caregiver(s)**
2. ✅ **Creates assignment record** in `booking_assignments` table
3. ✅ **Links caregiver to booking**
4. ✅ **Captures training center** (if caregiver was trained)
5. ✅ **Sends notifications**

**Backend API:** `POST /api/admin/bookings/{id}/assign-caregiver`

**Database Record:**
```php
BookingAssignment {
  id: 89,
  booking_id: 123,
  caregiver_id: 34,
  status: 'assigned',
  assigned_at: '2026-01-05 14:15:00',
  assigned_by: 1 // Admin ID
}

// Caregiver record has training_center_id
Caregiver {
  id: 34,
  user_id: 78,
  training_center_id: 12, // Will earn commission
  hourly_rate: 32, // Caregiver's rate
  status: 'active'
}
```

**Notifications:**
- ✉️ **Client:** "Caregiver [Name] has been assigned to your booking"
- ✉️ **Caregiver:** "You've been assigned to a new booking starting [Date]"
- ✉️ **Training Center:** "Your trained caregiver has been assigned to a booking"

---

### **PHASE 3: SERVICE DELIVERY & TIME TRACKING**

#### **Step 3.1: Caregiver Clocks In**
**Location:** Caregiver Dashboard → "Clock In" Button

**Frontend:** `CaregiverDashboard.vue`
```javascript
const clockIn = async () => {
  // Call API to record clock-in time
  const response = await fetch('/api/time-tracking/clock-in', {
    method: 'POST',
    body: JSON.stringify({
      booking_id: currentBooking.id,
      client_id: currentBooking.client_id
    })
  });
}
```

**Backend API:** `POST /api/time-tracking/clock-in`

**Controller:** `TimeTrackingController@clockIn`

**What Happens:**
1. ✅ **Create time tracking record**
2. ✅ **Record clock-in timestamp**
3. ✅ **Set status** → 'active'
4. ✅ **Link to booking** and client

**Database Record:**
```php
TimeTracking {
  id: 456,
  caregiver_id: 34,
  booking_id: 123,
  client_id: 45,
  clock_in_time: '2026-01-10 08:00:00',
  clock_out_time: null,
  status: 'active',
  total_hours: null,
  caregiver_earnings: null,
  payment_status: 'pending'
}
```

**UI Updates:**
- 🟢 **Caregiver Dashboard:** Shows active session timer
- 👁️ **Admin Dashboard:** Shows caregiver as "On Duty"
- 📊 **Client Dashboard:** Shows service in progress

---

#### **Step 3.2: Caregiver Clocks Out**
**Location:** Caregiver Dashboard → "Clock Out" Button

**Backend API:** `POST /api/time-tracking/clock-out`

**Controller:** `TimeTrackingController@clockOut`

**What Happens:**
1. ✅ **Record clock-out timestamp**
2. ✅ **Calculate total hours worked**
3. ✅ **Calculate ALL earnings and commissions**
4. ✅ **Update time tracking record**
5. ✅ **Create pending payment entries**

**Calculation Logic:**
```php
// Example: 12-hour shift
$clockIn = '2026-01-10 08:00:00';
$clockOut = '2026-01-10 20:00:00';
$hoursWorked = 12;

// Client is charged their hourly rate
$clientRate = $booking->hourly_rate; // $40 (with referral) or $45
$totalClientCharge = $hoursWorked * $clientRate; // 12 * $40 = $480

// Caregiver earns their hourly rate
$caregiverRate = 32; // Fixed caregiver rate
$caregiverEarnings = $hoursWorked * $caregiverRate; // 12 * $32 = $384

// Marketing partner commission (if booking has referral)
$marketingCommission = 0;
if ($booking->referral_code_id) {
  $marketingPartnerId = $booking->referralCode->user_id;
  $marketingCommission = $hoursWorked * 1.00; // 12 * $1 = $12
}

// Training center commission (if caregiver was trained)
$trainingCommission = 0;
if ($caregiver->training_center_id) {
  $trainingCenterId = $caregiver->training_center_id;
  $trainingCommission = $hoursWorked * 0.50; // 12 * $0.50 = $6
}

// Platform commission (remaining balance)
$agencyCommission = $totalClientCharge 
                   - $caregiverEarnings 
                   - $marketingCommission 
                   - $trainingCommission;
// $480 - $384 - $12 - $6 = $78
```

**Database Update:**
```php
TimeTracking {
  id: 456,
  clock_out_time: '2026-01-10 20:00:00',
  total_hours: 12.0,
  caregiver_earnings: 384.00,
  marketing_partner_id: 56,
  marketing_partner_commission: 12.00,
  training_center_user_id: 12,
  training_center_commission: 6.00,
  agency_commission: 78.00,
  total_client_charge: 480.00,
  status: 'completed',
  payment_status: 'pending'
}
```

**Notifications:**
- ✉️ **Caregiver:** "Shift completed! You earned $384.00"
- ✉️ **Marketing Partner:** "New commission earned: $12.00"
- ✉️ **Training Center:** "New commission earned: $6.00"
- 📊 **Admin:** "Time entry completed - pending payment"

---

### **PHASE 4: CLIENT PAYMENT PROCESSING**

#### **Step 4.1: Client Adds Payment Method (First Time)**
**Location:** Client Dashboard → Payment Methods Section → "Add Payment Method"

**Frontend:** `ClientDashboard.vue`
```javascript
const setupPaymentMethod = async () => {
  // Initialize Stripe
  const stripe = Stripe(stripePublishableKey);
  
  // Create Setup Intent
  const response = await fetch('/api/stripe/create-setup-intent', {
    method: 'POST'
  });
  
  const { client_secret } = await response.json();
  
  // Confirm card setup with Stripe
  const result = await stripe.confirmCardSetup(client_secret, {
    payment_method: {
      card: cardElement,
      billing_details: {
        name: cardholderName
      }
    }
  });
  
  // Save payment method ID to backend
  await fetch('/api/stripe/save-payment-method', {
    method: 'POST',
    body: JSON.stringify({
      payment_method_id: result.setupIntent.payment_method
    })
  });
}
```

**Backend APIs:**
1. `POST /api/stripe/create-setup-intent`
2. `POST /api/stripe/save-payment-method`

**Controller:** `StripeController`

**What Happens:**
1. ✅ **Create Stripe Customer** (if doesn't exist)
2. ✅ **Create Setup Intent** (for saving card)
3. ✅ **Client enters card details** (Stripe.js)
4. ✅ **Stripe validates card**
5. ✅ **Save Payment Method ID** to user record
6. ✅ **Set as default payment method**

**Database Update:**
```php
User {
  id: 45,
  stripe_customer_id: 'cus_abc123xyz',
  stripe_payment_method_id: 'pm_1234567890',
  has_payment_method: true
}
```

**UI Update:**
- 💳 Payment method card shows: "Visa •••• 4242"
- ✅ "Add Payment Method" button → "Update Payment Method"

---

#### **Step 4.2: Admin Processes Payment**
**Location:** Admin Dashboard → Payments Tab → Pending Payments

**Button:** "Process Payment" (for specific time tracking entry)

**What Admin Sees:**
```
Time Entry #456
Client: John Doe
Caregiver: Jane Smith
Date: Jan 10, 2026
Hours: 12.0
Amount: $480.00
Status: Pending
[Process Payment]
```

**Backend API:** `POST /api/stripe/process-payment/{timeTrackingId}`

**Controller:** `StripeController@processPayment`

**What Happens:**

**4.2.1: Charge Client**
```php
// Use saved payment method to charge client
$stripe = new \Stripe\StripeClient(config('stripe.secret_key'));

$paymentIntent = $stripe->paymentIntents->create([
  'amount' => $totalClientCharge * 100, // $480 = 48000 cents
  'currency' => 'usd',
  'customer' => $client->stripe_customer_id,
  'payment_method' => $client->stripe_payment_method_id,
  'off_session' => true,
  'confirm' => true,
  'description' => "Payment for 12 hours of care on Jan 10, 2026",
  'metadata' => [
    'time_tracking_id' => 456,
    'booking_id' => 123,
    'caregiver_id' => 34
  ]
]);
```

**Result:**
- ✅ **Client charged:** $480.00
- ✅ **Payment Intent ID:** `pi_abc123xyz`
- ✅ **Funds held in platform Stripe account**

**4.2.2: Update Time Tracking Record**
```php
TimeTracking {
  id: 456,
  payment_status: 'paid',
  stripe_charge_id: 'pi_abc123xyz',
  client_charged_at: '2026-01-11 10:00:00'
}
```

**4.2.3: Queue Caregiver Payout (Weekly)**
- Caregiver earnings ($384) added to pending weekly payout
- Will be processed on next Friday

**4.2.4: Queue Commission Payouts (Monthly)**
- Marketing commission ($12) added to partner's balance
- Training commission ($6) added to center's balance
- Both paid monthly (1st of next month)

**Notifications:**
- ✉️ **Client:** "Payment of $480.00 processed successfully"
- ✉️ **Caregiver:** "Payment being processed - payout on Friday"
- 📊 **Admin:** "Payment processed successfully"

---

#### **Step 4.3: Batch Payment Processing**
**Location:** Admin Dashboard → Payments Tab → "Process All Pending"

**Button:** "Process All Pending Payments"

**Backend API:** `POST /api/stripe/batch-process`

**What Happens:**
1. ✅ **Get all pending time tracking entries**
2. ✅ **Loop through each entry**
3. ✅ **Charge each client**
4. ✅ **Queue all payouts**
5. ✅ **Return summary report**

**Response:**
```json
{
  "success": true,
  "processed": 45,
  "failed": 2,
  "total_charged": 18500.00,
  "errors": [
    {
      "time_tracking_id": 458,
      "error": "Card declined - insufficient funds"
    },
    {
      "time_tracking_id": 462,
      "error": "Payment method expired"
    }
  ]
}
```

**Admin Action for Failures:**
- Contact clients with failed payments
- Request payment method update
- Manual retry after resolution

---

### **PHASE 5: CAREGIVER PAYOUTS (WEEKLY)**

#### **Step 5.1: Weekly Payout Trigger**
**Schedule:** Every Friday at 5:00 PM

**Automated Command:** `php artisan stripe:process-weekly-payouts`

**Or Manual:** Admin Dashboard → Payouts → "Process Weekly Caregiver Payouts"

**Backend API:** `POST /api/stripe/admin/process-weekly-payouts`

**What Happens:**

**5.1.1: Get All Pending Caregiver Earnings**
```php
$caregiverPayouts = TimeTracking::where('payment_status', 'paid')
  ->whereNotNull('caregiver_earnings')
  ->whereNull('caregiver_paid_at')
  ->groupBy('caregiver_id')
  ->selectRaw('caregiver_id, SUM(caregiver_earnings) as total_earnings')
  ->get();
```

**Example Result:**
```
Caregiver ID 34: $2,688.00 (7 days × 12 hours × $32)
Caregiver ID 45: $1,920.00 (5 days × 12 hours × $32)
Caregiver ID 67: $3,072.00 (8 days × 12 hours × $32)
Total: $7,680.00
```

**5.1.2: Transfer to Each Caregiver via Stripe Connect**
```php
foreach ($caregiverPayouts as $payout) {
  $caregiver = Caregiver::find($payout->caregiver_id);
  
  // Check if bank account connected
  if (empty($caregiver->stripe_connect_id)) {
    // Skip and notify admin
    $failures[] = "Caregiver {$caregiver->user->name} - No bank account";
    continue;
  }
  
  // Create Stripe Transfer
  $transfer = $stripe->transfers->create([
    'amount' => $payout->total_earnings * 100, // Convert to cents
    'currency' => 'usd',
    'destination' => $caregiver->stripe_connect_id,
    'description' => "Weekly payout for {$payout->count} shifts",
    'metadata' => [
      'caregiver_id' => $caregiver->id,
      'week_ending' => now()->format('Y-m-d')
    ]
  ]);
  
  // Mark all entries as paid
  TimeTracking::where('caregiver_id', $caregiver->id)
    ->whereNull('caregiver_paid_at')
    ->update([
      'caregiver_paid_at' => now(),
      'stripe_transfer_id' => $transfer->id
    ]);
  
  // Send notification
  NotificationService::notifyPayoutSent(
    $caregiver->user_id, 
    $payout->total_earnings
  );
}
```

**Result:**
- ✅ **Transfers created:** 3
- ✅ **Total paid:** $7,680.00
- ✅ **Failed:** 0
- ⏰ **Arrival:** 1-3 business days (Standard) or instant (Instant Payouts)

**Notifications:**
- ✉️ **Each Caregiver:** "Weekly payout of $X,XXX.00 sent to your bank"
- 📊 **Admin:** "Weekly payouts processed - 3 caregivers paid"

---

#### **Step 5.2: Caregiver Views Payout**
**Location:** Caregiver Dashboard → Earnings Tab → Payment History

**What Caregiver Sees:**
```
PAYMENT HISTORY
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Date: Jan 12, 2026
Amount: $2,688.00
Status: Paid
Shifts: 7
Transfer ID: tr_abc123xyz
Expected Arrival: Jan 15, 2026
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### **PHASE 6: COMMISSION PAYOUTS (MONTHLY)**

#### **Step 6.1: Marketing Partner Payout**
**Schedule:** 1st of every month OR Manual trigger by admin

**Location:** Admin Dashboard → Marketing Staff → View Commissions → "Pay Commission"

**Button:** "Pay $XXX.XX Commission"

**Backend API:** `POST /api/stripe/admin/pay-marketing-commission/{userId}`

**What Happens:**

**6.1.1: Calculate Total Commission**
```php
$pendingCommission = TimeTracking::where('marketing_partner_id', $userId)
  ->whereNull('marketing_commission_paid_at')
  ->sum('marketing_partner_commission');

// Example: $156.00 (156 hours of referred bookings × $1/hour)
```

**6.1.2: Check Bank Connection**
```php
if (empty($marketingUser->stripe_connect_id)) {
  return error('Bank account not connected');
}
```

**6.1.3: Create Stripe Transfer**
```php
$transfer = $stripe->transfers->create([
  'amount' => 15600, // $156.00 in cents
  'currency' => 'usd',
  'destination' => $marketingUser->stripe_connect_id,
  'description' => "Marketing commission - 156 hours referred",
  'metadata' => [
    'user_id' => $marketingUser->id,
    'commission_type' => 'marketing',
    'month' => 'January 2026'
  ]
]);
```

**6.1.4: Mark Entries as Paid**
```php
TimeTracking::where('marketing_partner_id', $userId)
  ->whereNull('marketing_commission_paid_at')
  ->update([
    'marketing_commission_paid_at' => now(),
    'marketing_commission_stripe_transfer_id' => $transfer->id
  ]);
```

**Notifications:**
- ✉️ **Marketing Partner:** "Commission of $156.00 has been sent to your bank"
- 📊 **Admin:** "Marketing commission paid to [Partner Name]"

---

#### **Step 6.2: Training Center Payout**
**Process identical to Marketing Partner, but:**
- **Rate:** $0.50/hour instead of $1.00/hour
- **API:** `POST /api/stripe/admin/pay-training-commission/{userId}`
- **Tracking:** `training_center_commission` instead of `marketing_partner_commission`

**Example Calculation:**
```
Trained Caregiver worked 312 hours in January
Training Center Commission = 312 × $0.50 = $156.00
```

---

### **PHASE 7: BOOKING COMPLETION**

#### **Step 7.1: Automatic Completion**
**Trigger:** When service end date passes

**Automated Command:** `php artisan bookings:check-status` (runs daily)

**What Happens:**
1. ✅ **Find bookings** where `start_date + duration_days < now()`
2. ✅ **Update status** → 'completed'
3. ✅ **Send notifications** to all parties
4. ✅ **Create payment record** summary
5. ✅ **Enable review system**

**Database Update:**
```php
Booking {
  id: 123,
  status: 'completed',
  completed_at: '2026-01-17 23:59:59'
}
```

**Notifications:**
- ✉️ **Client:** "Your booking has been completed. Please rate your caregiver!"
- ✉️ **Caregiver:** "Service completed. Thank you for your work!"
- 📊 **Admin:** "Booking #123 automatically marked as completed"

---

#### **Step 7.2: Client Rates Caregiver**
**Location:** Client Dashboard → My Bookings → Completed Tab → "Rate Service"

**Button:** "Rate Service"

**What Happens:**
1. ✅ **Show rating dialog**
2. ✅ **Client rates 1-5 stars**
3. ✅ **Client writes review** (optional)
4. ✅ **Submit to backend**
5. ✅ **Update caregiver average rating**
6. ✅ **Display on caregiver profile**

**Backend API:** `POST /api/reviews`

**Database Record:**
```php
Review {
  id: 78,
  booking_id: 123,
  client_id: 45,
  caregiver_id: 34,
  rating: 5,
  review: "Excellent care! Very professional and kind.",
  created_at: '2026-01-18 10:00:00'
}
```

**Notifications:**
- ✉️ **Caregiver:** "You received a 5-star review!"
- 📊 **Admin:** "New review submitted"

---

## 💰 COMMISSION CALCULATION SYSTEM

### **Hourly Rate Breakdown**

**Example: 12-Hour Shift with Referral Code**

```
CLIENT PAYS: $40/hour × 12 hours = $480.00
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DISTRIBUTION:
├─ Caregiver:         $32/hr × 12 = $384.00 (80%)
├─ Marketing Partner:  $1/hr × 12 =  $12.00 (2.5%)
├─ Training Center:  $0.50/hr × 12 =   $6.00 (1.25%)
└─ Platform Fee:        Remaining =  $78.00 (16.25%)
                                    ─────────
                          TOTAL =   $480.00 ✓
```

**Without Referral Code:**
```
CLIENT PAYS: $45/hour × 12 hours = $540.00
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DISTRIBUTION:
├─ Caregiver:         $32/hr × 12 = $384.00 (71.1%)
├─ Marketing Partner:           0 =   $0.00 (0%)
├─ Training Center:  $0.50/hr × 12 =   $6.00 (1.1%)
└─ Platform Fee:        Remaining = $150.00 (27.8%)
                                    ─────────
                          TOTAL =   $540.00 ✓
```

### **Commission Accrual**

**Caregivers (Weekly):**
- Earn on every clocked hour
- Accumulate Monday-Sunday
- Paid every Friday
- Instant or Standard payout

**Marketing Partners (Monthly):**
- Earn $1 per hour on referred bookings
- Accumulate all month
- Paid on 1st of next month
- Standard bank transfer (1-3 days)

**Training Centers (Monthly):**
- Earn $0.50 per hour their caregivers work
- Accumulate all month
- Paid on 1st of next month
- Standard bank transfer (1-3 days)

---

## 📅 PAYOUT SCHEDULES

### **Weekly Payouts (Caregivers)**
```
┌─────────────┬──────────────┬─────────────┐
│   Period    │ Process Date │   Arrival   │
├─────────────┼──────────────┼─────────────┤
│ Jan 6-12    │ Jan 12 (Fri) │ Jan 15 (Mon)│
│ Jan 13-19   │ Jan 19 (Fri) │ Jan 22 (Mon)│
│ Jan 20-26   │ Jan 26 (Fri) │ Jan 29 (Mon)│
│ Jan 27-Feb2 │ Feb 2 (Fri)  │ Feb 5 (Mon) │
└─────────────┴──────────────┴─────────────┘
```

### **Monthly Payouts (Partners & Training)**
```
┌─────────────┬──────────────┬─────────────┐
│   Period    │ Process Date │   Arrival   │
├─────────────┼──────────────┼─────────────┤
│ January     │ Feb 1        │ Feb 4       │
│ February    │ Mar 1        │ Mar 4       │
│ March       │ Apr 1        │ Apr 4       │
└─────────────┴──────────────┴─────────────┘
```

---

## 🔘 BUTTON-BY-BUTTON FLOW

### **CLIENT BUTTONS**

#### **1. "Book Now" Button**
**Location:** Client Dashboard → Main Widget  
**Function:** `attemptBooking()`  
**Flow:**
1. Check for existing pending/approved bookings
2. If blocked → Error message + redirect
3. If clear → Navigate to booking form
4. No API call (client-side check)

---

#### **2. "Submit Booking Request" Button**
**Location:** Client Dashboard → Book Form  
**Function:** `submitBooking()`  
**API:** `POST /api/bookings`  
**Flow:**
1. Validate form fields
2. Calculate total price
3. Send booking data to backend
4. Create booking record (status: 'pending')
5. Show success message
6. Redirect to My Bookings → Pending tab

**Database Changes:**
- ➕ New `Booking` record created
- ✉️ Notification to admin

---

#### **3. "Add Payment Method" Button**
**Location:** Client Dashboard → Payment Methods Section  
**Function:** `setupPaymentMethod()`  
**APIs:**
- `POST /api/stripe/create-setup-intent`
- `POST /api/stripe/save-payment-method`

**Flow:**
1. Call backend to create Setup Intent
2. Initialize Stripe.js with client secret
3. Show Stripe card input form
4. Client enters card details
5. Stripe validates card
6. Save payment method ID to user record
7. Update UI to show saved card

**Database Changes:**
- ✏️ Update `User` → `stripe_customer_id`, `stripe_payment_method_id`

---

#### **4. "Pay Now" Button**
**Location:** Client Dashboard → My Bookings → Action Menu  
**Function:** `goToPayment(booking)`  
**Flow:**
1. Navigate to dedicated payment page
2. URL: `/payment?booking_id=123`
3. Load booking details
4. Show Stripe payment form
5. Process payment (if manual payment flow)

**Note:** In current system, clients don't manually pay - they're auto-charged when admin processes time tracking entries.

---

#### **5. "View Receipt" Button**
**Location:** Client Dashboard → My Bookings → Action Menu  
**Function:** `viewReceipt(bookingId)`  
**API:** `GET /api/receipts/{bookingId}`  
**Flow:**
1. Open new tab with receipt PDF
2. Shows detailed breakdown:
   - Service dates
   - Hours worked
   - Rate per hour
   - Total charged
   - Payment method
   - Caregiver info

---

#### **6. "Rate Service" Button**
**Location:** Client Dashboard → My Bookings → Completed Tab  
**Function:** `rateBooking(bookingId)`  
**API:** `POST /api/reviews`  
**Flow:**
1. Check if client can review (booking completed)
2. Show rating dialog with stars
3. Client selects 1-5 stars
4. Client writes review (optional)
5. Submit to backend
6. Update caregiver average rating
7. Show success message

**Database Changes:**
- ➕ New `Review` record
- ✏️ Update `Caregiver` → `average_rating`

---

### **CAREGIVER BUTTONS**

#### **7. "Connect Bank Account" Button**
**Location:** Caregiver Dashboard → Earnings Tab  
**Function:** `connectBankAccount()`  
**API:** `POST /api/stripe/create-onboarding-link`  
**Flow:**
1. Create Stripe Connect account (if doesn't exist)
2. Generate onboarding link
3. Redirect to Stripe hosted onboarding
4. Caregiver enters bank details
5. Stripe verifies account
6. Redirect back to dashboard
7. Update UI to show "Bank Connected"

**Database Changes:**
- ✏️ Update `Caregiver` → `stripe_connect_id`
- ✏️ Update `User` → `stripe_connect_id`

---

#### **8. "Clock In" Button**
**Location:** Caregiver Dashboard → Main Widget  
**Function:** `clockIn()`  
**API:** `POST /api/time-tracking/clock-in`  
**Flow:**
1. Get current booking assignment
2. Create time tracking record
3. Set clock_in_time to now()
4. Set status to 'active'
5. Start UI timer
6. Enable "Clock Out" button

**Database Changes:**
- ➕ New `TimeTracking` record
  - `clock_in_time` = now()
  - `status` = 'active'

---

#### **9. "Clock Out" Button**
**Location:** Caregiver Dashboard → Main Widget  
**Function:** `clockOut()`  
**API:** `POST /api/time-tracking/clock-out`  
**Flow:**
1. Get active time tracking record
2. Set clock_out_time to now()
3. Calculate total hours worked
4. Calculate caregiver earnings
5. Calculate marketing commission (if referral)
6. Calculate training commission (if trained)
7. Calculate agency commission
8. Update time tracking record
9. Show earnings summary
10. Disable "Clock Out" button

**Database Changes:**
- ✏️ Update `TimeTracking` → ALL commission fields calculated

**Calculations Triggered:**
```php
total_hours = clock_out_time - clock_in_time
caregiver_earnings = total_hours × $32
marketing_commission = total_hours × $1 (if referred)
training_commission = total_hours × $0.50 (if trained)
agency_commission = total_client_charge - all commissions
```

**Notifications:**
- ✉️ Caregiver: "You earned $XXX.XX"
- ✉️ Marketing Partner: "New commission: $XX.XX"
- ✉️ Training Center: "New commission: $XX.XX"

---

### **ADMIN BUTTONS**

#### **10. "Approve Booking" Button**
**Location:** Admin Dashboard → Bookings Tab → Pending  
**Function:** `approveBooking(bookingId)`  
**API:** `POST /api/admin/bookings/{id}/approve`  
**Flow:**
1. Update booking status → 'approved'
2. Set approved_at timestamp
3. Set approved_by (admin user ID)
4. Send notification to client
5. Update dashboard stats
6. Move booking to "Approved" tab

**Database Changes:**
- ✏️ Update `Booking`:
  - `status` = 'approved'
  - `approved_at` = now()
  - `approved_by` = admin.id

**Notifications:**
- ✉️ Client: "Your booking has been approved!"

---

#### **11. "Reject Booking" Button**
**Location:** Admin Dashboard → Bookings Tab → Pending  
**Function:** `rejectBooking(bookingId)`  
**API:** `POST /api/admin/bookings/{id}/reject`  
**Flow:**
1. Update booking status → 'rejected'
2. Set rejected_at timestamp
3. Set rejection reason
4. Send notification to client
5. Update dashboard stats
6. Move booking to "Rejected" tab

**Database Changes:**
- ✏️ Update `Booking`:
  - `status` = 'rejected'
  - `rejected_at` = now()
  - `rejection_reason` = reason

**Notifications:**
- ✉️ Client: "Your booking was declined. Reason: [reason]"

---

#### **12. "Assign Caregiver" Button**
**Location:** Admin Dashboard → Bookings → Booking Details  
**Function:** `assignCaregiver(bookingId, caregiverId)`  
**API:** `POST /api/admin/bookings/{id}/assign-caregiver`  
**Flow:**
1. Validate caregiver availability
2. Create booking assignment record
3. Link caregiver to booking
4. Capture training center ID (if applicable)
5. Send notifications to all parties
6. Update UI to show assigned caregiver

**Database Changes:**
- ➕ New `BookingAssignment` record
- ✏️ Update `Booking` → show assigned caregivers

**Notifications:**
- ✉️ Client: "Caregiver assigned: [Name]"
- ✉️ Caregiver: "New assignment: [Booking details]"

---

#### **13. "Process Payment" Button**
**Location:** Admin Dashboard → Payments Tab → Pending Payments  
**Function:** `processPayment(timeTrackingId)`  
**API:** `POST /api/stripe/process-payment/{timeTrackingId}`  
**Flow:**
1. Get time tracking entry details
2. Get client's saved payment method
3. Create Stripe Payment Intent
4. Charge client the total amount
5. Record payment intent ID
6. Update payment status to 'paid'
7. Queue caregiver payout (weekly)
8. Queue commission payouts (monthly)
9. Show success/error message

**Stripe API Calls:**
```javascript
stripe.paymentIntents.create({
  amount: 48000, // $480 in cents
  customer: 'cus_abc123',
  payment_method: 'pm_xyz789',
  confirm: true
})
```

**Database Changes:**
- ✏️ Update `TimeTracking`:
  - `payment_status` = 'paid'
  - `stripe_charge_id` = payment_intent.id
  - `client_charged_at` = now()

**Notifications:**
- ✉️ Client: "Payment processed: $480.00"
- ✉️ Caregiver: "Payment being processed"

---

#### **14. "Process All Pending" Button**
**Location:** Admin Dashboard → Payments Tab  
**Function:** `batchProcessPayments()`  
**API:** `POST /api/stripe/batch-process`  
**Flow:**
1. Get ALL pending time tracking entries
2. Loop through each entry
3. For each: charge client → queue payouts
4. Collect successes and failures
5. Show summary report

**Response Example:**
```json
{
  "processed": 45,
  "failed": 2,
  "total_charged": 18500.00,
  "errors": [...]
}
```

---

#### **15. "Process Weekly Payouts" Button**
**Location:** Admin Dashboard → Payouts Tab  
**Function:** `processWeeklyPayouts()`  
**API:** `POST /api/stripe/admin/process-weekly-payouts`  
**Flow:**
1. Get all caregivers with pending earnings
2. Group earnings by caregiver
3. For each caregiver:
   - Check bank connection
   - Create Stripe Transfer
   - Mark entries as paid
   - Send notification
4. Show summary report

**Stripe API Calls:**
```javascript
stripe.transfers.create({
  amount: 268800, // $2,688 in cents
  destination: 'acct_caregiver123',
  currency: 'usd'
})
```

**Database Changes:**
- ✏️ Update all `TimeTracking` entries:
  - `caregiver_paid_at` = now()
  - `stripe_transfer_id` = transfer.id

**Notifications:**
- ✉️ Each Caregiver: "Payout sent: $X,XXX.XX"

---

#### **16. "Pay Marketing Commission" Button**
**Location:** Admin Dashboard → Marketing Staff → Commission Details  
**Function:** `payMarketingCommission(userId)`  
**API:** `POST /api/stripe/admin/pay-marketing-commission/{userId}`  
**Flow:**
1. Calculate total pending commission
2. Check bank connection
3. Create Stripe Transfer to marketing partner
4. Mark all entries as paid
5. Send notification
6. Update UI

**Stripe API Calls:**
```javascript
stripe.transfers.create({
  amount: 15600, // $156 in cents
  destination: 'acct_marketing123',
  currency: 'usd'
})
```

**Database Changes:**
- ✏️ Update `TimeTracking` entries:
  - `marketing_commission_paid_at` = now()
  - `marketing_commission_stripe_transfer_id` = transfer.id

**Notifications:**
- ✉️ Marketing Partner: "Commission paid: $156.00"

---

#### **17. "Pay Training Commission" Button**
**Location:** Admin Dashboard → Training Centers → Commission Details  
**Function:** `payTrainingCommission(userId)`  
**API:** `POST /api/stripe/admin/pay-training-commission/{userId}`  
**Flow:**
1. Calculate total pending commission
2. Check bank connection
3. Create Stripe Transfer to training center
4. Mark all entries as paid
5. Send notification
6. Update UI

**Process identical to marketing commission, but uses training commission rates and fields.**

---

### **MARKETING PARTNER BUTTONS**

#### **18. "Connect Bank Account" Button**
**Location:** Marketing Dashboard → Commission Section  
**Function:** `connectMarketingBankAccount()`  
**API:** `POST /api/stripe/connect-bank-account-marketing`  
**Flow:**
1. Show bank account form
2. Collect: account holder, routing, account number, type
3. Create Stripe Connect account
4. Add external bank account
5. Verify micro-deposits (if required)
6. Update user record
7. Show "Bank Connected" status

**Database Changes:**
- ✏️ Update `User`:
  - `stripe_connect_id` = account.id
  - `bank_connected` = true

---

### **TRAINING CENTER BUTTONS**

#### **19. "Connect Bank Account" Button**
**Location:** Training Dashboard → Commission Section  
**Function:** `connectTrainingBankAccount()`  
**API:** `POST /api/stripe/connect-bank-account-training`  
**Flow:** (Same as Marketing Partner)

---

## 🔧 TECHNICAL IMPLEMENTATION

### **Stripe Configuration**

**Environment Variables (.env):**
```env
STRIPE_KEY=pk_test_51xxxxx  # Publishable Key (Frontend)
STRIPE_SECRET=sk_test_51xxxxx  # Secret Key (Backend)
STRIPE_WEBHOOK_SECRET=whsec_xxxxx  # Webhook Signing Secret
```

**Config File (config/stripe.php):**
```php
return [
    'key' => env('STRIPE_KEY'),
    'secret_key' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
```

---

### **Database Schema**

**Key Tables:**

**1. users**
```sql
- stripe_customer_id (for clients)
- stripe_payment_method_id (for clients)
- stripe_connect_id (for caregivers, marketing, training)
- has_payment_method (boolean)
- bank_connected (boolean)
```

**2. bookings**
```sql
- payment_status (unpaid, paid)
- stripe_payment_intent_id
- hourly_rate
- total_price
- referral_code_id (FK)
```

**3. time_trackings**
```sql
- caregiver_earnings
- marketing_partner_commission
- training_center_commission
- agency_commission
- total_client_charge
- payment_status
- stripe_charge_id
- stripe_transfer_id
- caregiver_paid_at
- marketing_commission_paid_at
- training_commission_paid_at
```

**4. caregivers**
```sql
- stripe_connect_id
- training_center_id (FK to users)
- hourly_rate
```

---

### **Service Classes**

**StripePaymentService.php**
```php
Methods:
- createSetupIntent($user)
- createCustomer($user)
- chargeClientForTimeTracking($timeTracking)
- createOnboardingLink($caregiver)
- addBankAccountToConnect($caregiver, $bankData)
- transferToCaregiver($timeTracking)
- transferToMarketing($user, $amount, $metadata)
- transferToTraining($user, $amount, $metadata)
- processWeeklyPayouts()
```

**PaymentService.php**
```php
Methods:
- createPaymentForCompletedBooking($booking)
- calculatePlatformFee($totalAmount, $caregiverAmount)
```

**NotificationService.php**
```php
Methods:
- notifyPaymentReceived($clientId, $amount, $service)
- notifyPayoutSent($caregiverUserId, $amount)
- notifyBookingApproved($clientId, $bookingId)
```

---

## 📡 API ENDPOINTS REFERENCE

### **Client Endpoints**

```
POST   /api/stripe/create-setup-intent
POST   /api/stripe/save-payment-method
GET    /api/stripe/payment-methods
POST   /api/bookings
GET    /api/client/bookings
GET    /api/receipts/{bookingId}
POST   /api/reviews
```

### **Caregiver Endpoints**

```
POST   /api/stripe/create-onboarding-link
POST   /api/stripe/create-account-session
POST   /api/stripe/connect-bank-account
GET    /api/stripe/onboarding-status
POST   /api/time-tracking/clock-in
POST   /api/time-tracking/clock-out
GET    /api/time-tracking/current-session
GET    /api/caregiver/earnings
```

### **Admin Endpoints**

```
POST   /api/admin/bookings/{id}/approve
POST   /api/admin/bookings/{id}/reject
POST   /api/admin/bookings/{id}/assign-caregiver
POST   /api/stripe/process-payment/{timeTrackingId}
POST   /api/stripe/batch-process
GET    /api/stripe/pending-payments
POST   /api/stripe/admin/process-weekly-payouts
POST   /api/stripe/admin/pay-marketing-commission/{userId}
POST   /api/stripe/admin/pay-training-commission/{userId}
GET    /api/admin/marketing-commissions
GET    /api/admin/training-commissions
```

### **Marketing Partner Endpoints**

```
POST   /api/stripe/connect-bank-account-marketing
GET    /api/marketing/referral-code
GET    /api/marketing/commission-summary
GET    /api/marketing/referred-clients
```

### **Training Center Endpoints**

```
POST   /api/stripe/connect-bank-account-training
GET    /api/training/trained-caregivers
GET    /api/training/commission-summary
```

### **Webhook Endpoint**

```
POST   /api/stripe/webhook
```

---

## 🔒 SECURITY CONSIDERATIONS

### **Payment Method Storage**
- ✅ Never store raw card numbers
- ✅ Only store Stripe Payment Method IDs
- ✅ Use Stripe.js for PCI compliance
- ✅ All card data stays with Stripe

### **API Authentication**
- ✅ All endpoints require authentication
- ✅ Role-based access control (admin, client, caregiver, etc.)
- ✅ CSRF token validation
- ✅ Middleware checks user type

### **Stripe Webhooks**
- ✅ Verify webhook signatures
- ✅ Use webhook secrets
- ✅ Validate event authenticity
- ✅ Handle idempotency

### **Connect Accounts**
- ✅ Use separate Stripe Connect accounts
- ✅ Verify bank account ownership
- ✅ Monitor for fraud
- ✅ Handle failed transfers gracefully

---

## 📊 REPORTING & ANALYTICS

### **Admin Dashboard Shows:**

**Financial Overview:**
- Total revenue (this month)
- Pending payments
- Paid out to caregivers
- Paid in commissions
- Platform earnings

**Payment Status:**
- Pending time entries: 23
- Failed payments: 2
- Successful payments: 145
- Payout queue: $12,345.67

**Commission Tracking:**
- Marketing commissions pending: $1,234.00
- Training commissions pending: $567.00
- Caregivers awaiting payout: $8,900.00

---

## 🚨 ERROR HANDLING

### **Common Errors & Solutions**

**1. "Payment method not found"**
- Client needs to add payment method
- Redirect to payment methods section

**2. "Card declined"**
- Notify client of payment failure
- Request alternative payment method
- Retry payment after resolution

**3. "Bank account not connected"**
- Cannot pay caregiver/partner
- Send notification to connect bank
- Skip in batch processing

**4. "Insufficient funds in Stripe account"**
- Check platform Stripe balance
- Ensure clients are being charged
- Contact Stripe support

**5. "Transfer failed"**
- Check Connect account status
- Verify bank account is valid
- Retry transfer or notify recipient

---

## ✅ SUCCESS METRICS

### **What Success Looks Like:**

**For Clients:**
- ✅ Easy, secure payment setup
- ✅ Transparent pricing
- ✅ Auto-charged based on actual hours
- ✅ Digital receipts available

**For Caregivers:**
- ✅ Fast, reliable weekly payouts
- ✅ Easy bank account connection
- ✅ Transparent earnings tracking
- ✅ Direct deposit to bank

**For Partners:**
- ✅ Automated commission tracking
- ✅ Monthly reliable payouts
- ✅ Real-time commission updates
- ✅ Simple bank connection

**For Platform:**
- ✅ Automated payment processing
- ✅ Minimal manual intervention
- ✅ Complete audit trail
- ✅ Scalable to 1000+ users

---

## 📞 SUPPORT & TROUBLESHOOTING

### **For Payment Issues:**

**Client Can't Pay:**
1. Check payment method saved
2. Verify card not expired
3. Check billing address
4. Try alternative card
5. Contact bank if declined

**Caregiver Not Receiving Payout:**
1. Verify bank account connected
2. Check Stripe Connect status
3. Confirm earnings calculated
4. Check payout schedule
5. Review transfer history

**Commission Not Received:**
1. Verify bank account connected
2. Check commission calculation
3. Confirm referral/training link
4. Review payout schedule
5. Contact admin for manual review

---

## 🎓 TRAINING RESOURCES

### **For New Admins:**
1. Review this complete flow document
2. Practice in test mode with test cards
3. Process sample payments
4. Review Stripe dashboard
5. Understand refund process

### **For Caregivers:**
1. How to connect bank account
2. How to clock in/out properly
3. How to view earnings
4. When to expect payouts
5. What to do if payout missing

### **For Partners:**
1. How referral system works
2. How to track commissions
3. How to connect bank account
4. When to expect payouts
5. How to view referred clients

---

## 📝 CONCLUSION

This Stripe integration provides a **complete, automated payment ecosystem** that:

✅ **Charges clients** based on actual hours worked  
✅ **Pays caregivers** weekly via Stripe Connect  
✅ **Pays marketing partners** monthly commission  
✅ **Pays training centers** monthly commission  
✅ **Handles platform fees** automatically  
✅ **Provides complete audit trail**  
✅ **Scales to thousands of users**  
✅ **Requires minimal manual intervention**  

Every button click, API call, and database update is documented above for complete system understanding.

---

**Document Version:** 1.0  
**Last Updated:** January 5, 2026  
**System Status:** ✅ Production Ready  
**Integration Status:** ✅ Fully Functional

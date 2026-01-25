# 🎯 COMPLETE SYSTEM TESTING GUIDE
## CAS Private Care - All Features & Portals

Last Updated: January 5, 2026

---

## 📋 TABLE OF CONTENTS
1. [Quick Start](#quick-start)
2. [Test Accounts](#test-accounts)
3. [Client Portal Features](#client-portal)
4. [Caregiver Portal Features](#caregiver-portal)
5. [Admin Portal Features](#admin-portal)
6. [Marketing Portal Features](#marketing-portal)
7. [Testing Workflows](#testing-workflows)

---

## 🚀 QUICK START

### Start the Application
```bash
# Terminal 1: Start Laravel
php artisan serve

# Terminal 2: Build frontend assets
npm run build
# OR for development:
npm run dev
```

### Access URLs
- **Client Portal**: http://127.0.0.1:8000/client/dashboard
- **Caregiver Portal**: http://127.0.0.1:8000/caregiver/dashboard-vue
- **Admin Portal**: http://127.0.0.1:8000/admin/dashboard
- **Marketing Portal**: http://127.0.0.1:8000/marketing/dashboard

---

## 👥 TEST ACCOUNTS

### Current Active Accounts

#### Client Account
- **Email**: `client@demo.com`
- **Password**: `password`
- **Status**: ✅ Has 1 paid booking (#1)
- **Assigned Caregivers**: 3 caregivers assigned

#### Caregiver Accounts
Check for existing caregivers:
```bash
php artisan tinker --execute="App\Models\User::where('user_type', 'caregiver')->get(['name', 'email'])"
```

#### Admin Account
Check for admin:
```bash
php artisan tinker --execute="App\Models\User::where('is_admin', 1)->first(['name', 'email'])"
```

### Create Test Accounts if Needed

#### Create Admin Account
```bash
php artisan tinker
```
```php
$admin = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@cas.com',
    'password' => bcrypt('password'),
    'user_type' => 'client',
    'is_admin' => true,
    'email_verified_at' => now()
]);
```

#### Create Caregiver Account
```bash
php artisan tinker
```
```php
$user = App\Models\User::create([
    'name' => 'Test Caregiver',
    'email' => 'caregiver@test.com',
    'password' => bcrypt('password'),
    'user_type' => 'caregiver',
    'phone' => '555-1234',
    'email_verified_at' => now(),
    'status' => 'approved'
]);

App\Models\Caregiver::create([
    'user_id' => $user->id,
    'hourly_rate' => 28.00,
    'experience_years' => 5,
    'bio' => 'Experienced caregiver',
    'certifications' => json_encode(['CPR', 'First Aid']),
    'specializations' => json_encode(['Elderly Care']),
    'availability_status' => 'available'
]);
```

---

## 🏠 CLIENT PORTAL

### Features to Test

#### 1. Dashboard Stats
- [ ] Amount Due (should be $0 for paid booking)
- [ ] Contract Status (should show "Ongoing Contract")
- [ ] Total Hours Booked (should show 720 hours)
- [ ] Total Spent (should show $28,800)

#### 2. My Bookings Section
- [ ] View booking details
- [ ] See assigned caregivers (3/3)
- [ ] Check payment status (should show "Paid" badge)
- [ ] View service information

#### 3. Rate Caregivers (After Service)
**Steps:**
1. Go to "My Bookings" section
2. If booking is completed, click "Rate Service"
3. You'll see all assigned caregivers
4. Rate each one (1-5 stars)
5. Add comments
6. Submit review

**To Test This Feature:**
```bash
# Mark booking as completed
php artisan tinker
```
```php
$booking = App\Models\Booking::find(1);
$booking->update(['status' => 'completed']);
```

Now refresh dashboard and you'll see a "Rate Service" button!

#### 4. Payment History
- [ ] Go to "Payment Information" section
- [ ] View all payment records
- [ ] Download receipts

#### 5. Browse Caregivers
- [ ] See all available caregivers
- [ ] View ratings and reviews
- [ ] Filter by specialization

#### 6. Book New Service
- [ ] Click "Book Now"
- [ ] Fill out booking form
- [ ] Submit for admin approval

---

## 🧑‍⚕️ CAREGIVER PORTAL

### How to Access
1. Logout from client account
2. Login with caregiver credentials
3. Go to: http://127.0.0.1:8000/caregiver/dashboard-vue

### Features to Test

#### 1. Dashboard Overview
- [ ] Active assignments count
- [ ] Total earnings (from completed shifts)
- [ ] Hours worked this month
- [ ] Pending payments
- [ ] Average rating from clients

#### 2. Clock In/Out System
**This is the TIME TRACKING you asked about!**

**Steps:**
1. Go to "Time Tracking" section
2. Find your assigned booking
3. Click "Clock In" when you start work
4. System records timestamp
5. Click "Clock Out" when done
6. System calculates:
   - Total hours worked
   - Earnings (hours × hourly_rate)
   - Automatically creates timesheet entry

**View Timesheet:**
```bash
# Check time tracking records
php artisan tinker
```
```php
$caregiver = App\Models\Caregiver::first();
$timesheets = App\Models\TimeTracking::where('caregiver_id', $caregiver->id)->get();
foreach($timesheets as $t) {
    echo "Booking #{$t->booking_id}: {$t->total_hours}hrs × \${$t->hourly_rate} = \${$t->total_earned}\n";
}
```

#### 3. My Assignments
- [ ] View assigned bookings
- [ ] See client information
- [ ] Check service location
- [ ] View schedule

#### 4. Earnings & Payouts
- [ ] View total earnings
- [ ] See paid vs pending amounts
- [ ] Track payment history
- [ ] View payout breakdown:
   - Your earnings (e.g., $28/hr)
   - Platform fee deduction
   - Net amount

#### 5. Ratings & Reviews
- [ ] View ratings from clients
- [ ] Read client feedback
- [ ] See average rating

#### 6. Bank Account Setup
- [ ] Connect Stripe account
- [ ] Add bank details for payouts

---

## 👨‍💼 ADMIN PORTAL

### How to Access
1. Login with admin account
2. Go to: http://127.0.0.1:8000/admin/dashboard

### Features to Test

#### 1. Dashboard Overview
- [ ] Total bookings
- [ ] Active caregivers
- [ ] Revenue statistics
- [ ] Pending approvals

#### 2. Manage Bookings
**This is where you see EVERYTHING!**

- [ ] View all bookings
- [ ] See booking status
- [ ] Approve/reject bookings
- [ ] Assign caregivers
- [ ] View payment status
- [ ] Track booking progress

**Table Columns:**
- Booking ID
- Client name
- Service type
- Date & Time
- Duration
- Price
- Payment status (Paid/Unpaid)
- Assigned caregivers
- Status

#### 3. Caregiver Management
- [ ] View all caregivers
- [ ] Approve new applications
- [ ] View caregiver profiles
- [ ] Check ratings
- [ ] Manage availability

#### 4. Time Tracking & Payouts
**THIS IS WHAT YOU WANT TO SEE!**

**View Caregiver Timesheets:**
1. Go to "Caregiver Payouts" section
2. See list of all caregivers
3. For each caregiver:
   - Total hours worked
   - Earnings breakdown
   - Clock in/out times
   - Pending payments
   - Paid amounts

**Example View:**
```
Caregiver: John Smith
├─ Total Hours: 160 hrs
├─ Hourly Rate: $28/hr
├─ Gross Earnings: $4,480
├─ Paid: $3,200
└─ Pending: $1,280

Recent Timesheets:
├─ Booking #1: Jan 5, 8:00 AM - 4:00 PM (8 hrs) = $224
├─ Booking #2: Jan 6, 9:00 AM - 5:00 PM (8 hrs) = $224
└─ Booking #3: Jan 7, 7:00 AM - 3:00 PM (8 hrs) = $224
```

**Process Payments:**
- [ ] Review pending payouts
- [ ] Approve payments
- [ ] Send to caregiver's bank via Stripe
- [ ] Mark as paid

#### 5. Client Management
- [ ] View all clients
- [ ] See booking history
- [ ] Track spending
- [ ] Manage accounts

#### 6. Reviews & Ratings System
**See all ratings given by clients!**

1. Go to "Reviews" or "Ratings" section
2. View all reviews:
   - Client name
   - Caregiver name
   - Booking ID
   - Rating (1-5 stars)
   - Comments
   - Date submitted

**Or Check via Database:**
```bash
php artisan tinker
```
```php
// See all reviews
$reviews = App\Models\Review::with(['client', 'caregiver.user', 'booking'])->get();
foreach($reviews as $r) {
    echo "{$r->client->name} rated {$r->caregiver->user->name}: {$r->rating}/5 stars\n";
    echo "Comment: {$r->comment}\n\n";
}
```

#### 7. Financial Reports
- [ ] Revenue by month
- [ ] Payment breakdown
- [ ] Platform fees collected
- [ ] Caregiver payouts

---

## 📊 MARKETING PORTAL

### How to Access
1. Create marketing staff account (see below)
2. Login and go to: http://127.0.0.1:8000/marketing/dashboard

### Create Marketing Account
```bash
php artisan tinker
```
```php
$marketing = App\Models\User::create([
    'name' => 'Marketing Staff',
    'email' => 'marketing@cas.com',
    'password' => bcrypt('password'),
    'user_type' => 'marketing',
    'email_verified_at' => now()
]);
```

### Features to Test
- [ ] Generate referral codes
- [ ] Track referrals used
- [ ] View commission earned
- [ ] See booking tied to referrals

---

## 🧪 TESTING WORKFLOWS

### Workflow 1: Complete Booking Cycle

#### Step 1: Client Books Service
1. Login as client@demo.com
2. Click "Book Now"
3. Fill form and submit
4. Logout

#### Step 2: Admin Approves & Assigns
1. Login as admin
2. Go to bookings
3. Find new booking
4. Approve it
5. Assign caregiver(s)
6. Logout

#### Step 3: Client Pays
1. Login as client
2. See approved booking
3. Click "Pay Now"
4. Complete Stripe payment
5. Verify dashboard updates

#### Step 4: Caregiver Works
1. Login as caregiver
2. Go to assignments
3. Clock in when service starts
4. Clock out when done
5. System calculates earnings

#### Step 5: Client Rates Service
1. Admin marks booking as "completed"
2. Client logs in
3. Sees "Rate Service" button
4. Rates all assigned caregivers
5. Submits reviews

#### Step 6: Admin Processes Payment
1. Login as admin
2. Go to caregiver payouts
3. Review timesheet
4. Approve payout
5. Caregiver receives payment

---

### Workflow 2: View Time Tracking Data

#### As Admin - See All Timesheets
```bash
php artisan tinker
```
```php
// Get all time tracking entries
$timesheets = App\Models\TimeTracking::with(['caregiver.user', 'booking'])
    ->orderBy('clock_in', 'desc')
    ->get();

foreach($timesheets as $t) {
    $caregiverName = $t->caregiver->user->name ?? 'Unknown';
    $clockIn = $t->clock_in ? $t->clock_in->format('M d, Y H:i') : 'N/A';
    $clockOut = $t->clock_out ? $t->clock_out->format('M d, Y H:i') : 'Not clocked out';
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Caregiver: {$caregiverName}\n";
    echo "Booking: #{$t->booking_id}\n";
    echo "Clock In: {$clockIn}\n";
    echo "Clock Out: {$clockOut}\n";
    echo "Hours: {$t->total_hours} hrs\n";
    echo "Rate: \${$t->hourly_rate}/hr\n";
    echo "Earned: \${$t->total_earned}\n";
    echo "Status: {$t->status}\n";
    echo "Payment Status: {$t->payment_status}\n";
}
```

#### As Admin - See Caregiver Earnings Summary
```bash
php artisan tinker
```
```php
$caregivers = App\Models\Caregiver::with('user')->get();

foreach($caregivers as $cg) {
    $timesheets = App\Models\TimeTracking::where('caregiver_id', $cg->id)->get();
    $totalHours = $timesheets->sum('total_hours');
    $totalEarned = $timesheets->sum('total_earned');
    $paid = $timesheets->where('payment_status', 'paid')->sum('total_earned');
    $pending = $totalEarned - $paid;
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Caregiver: {$cg->user->name}\n";
    echo "Total Hours: {$totalHours} hrs\n";
    echo "Total Earned: \${$totalEarned}\n";
    echo "Paid: \${$paid}\n";
    echo "Pending: \${$pending}\n";
    echo "Timesheet Entries: {$timesheets->count()}\n";
}
```

---

### Workflow 3: View All Ratings

```bash
php artisan tinker
```
```php
$reviews = App\Models\Review::with(['client', 'caregiver.user', 'booking'])->get();

if($reviews->count() === 0) {
    echo "No reviews yet. Complete a booking and rate the caregiver!\n";
} else {
    foreach($reviews as $r) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Client: {$r->client->name}\n";
        echo "Caregiver: {$r->caregiver->user->name}\n";
        echo "Booking: #{$r->booking_id}\n";
        echo "Rating: {$r->rating}/5 ⭐\n";
        echo "Comment: {$r->comment}\n";
        echo "Date: {$r->created_at->format('M d, Y')}\n";
    }
}
```

---

## 📊 DATABASE QUERIES FOR INSIGHTS

### Get Complete System Overview
```bash
php artisan tinker
```
```php
// Summary
echo "═══════════════════════════════════════\n";
echo "    CAS PRIVATE CARE - SYSTEM STATS    \n";
echo "═══════════════════════════════════════\n\n";

echo "👥 Users:\n";
echo "  Clients: " . App\Models\User::where('user_type', 'client')->count() . "\n";
echo "  Caregivers: " . App\Models\User::where('user_type', 'caregiver')->count() . "\n";
echo "  Admins: " . App\Models\User::where('is_admin', 1)->count() . "\n";
echo "  Marketing: " . App\Models\User::where('user_type', 'marketing')->count() . "\n\n";

echo "📅 Bookings:\n";
echo "  Total: " . App\Models\Booking::count() . "\n";
echo "  Pending: " . App\Models\Booking::where('status', 'pending')->count() . "\n";
echo "  Approved: " . App\Models\Booking::where('status', 'approved')->count() . "\n";
echo "  Completed: " . App\Models\Booking::where('status', 'completed')->count() . "\n\n";

echo "💰 Payments:\n";
echo "  Total Payments: " . App\Models\Payment::count() . "\n";
echo "  Completed: " . App\Models\Payment::where('status', 'completed')->count() . "\n";
echo "  Total Amount: $" . App\Models\Payment::where('status', 'completed')->sum('amount') . "\n\n";

echo "⏰ Time Tracking:\n";
echo "  Total Entries: " . App\Models\TimeTracking::count() . "\n";
echo "  Total Hours: " . App\Models\TimeTracking::sum('total_hours') . " hrs\n";
echo "  Total Earned: $" . App\Models\TimeTracking::sum('total_earned') . "\n";
echo "  Paid Out: $" . App\Models\TimeTracking::where('payment_status', 'paid')->sum('total_earned') . "\n\n";

echo "⭐ Reviews:\n";
echo "  Total Reviews: " . App\Models\Review::count() . "\n";
echo "  Average Rating: " . number_format(App\Models\Review::avg('rating'), 2) . "/5\n\n";
```

---

## 🔧 QUICK COMMANDS

### Create Sample Time Tracking Entry
```bash
php artisan tinker
```
```php
$booking = App\Models\Booking::first();
$caregiver = App\Models\Caregiver::first();

App\Models\TimeTracking::create([
    'caregiver_id' => $caregiver->id,
    'booking_id' => $booking->id,
    'clock_in' => now()->subHours(8),
    'clock_out' => now(),
    'total_hours' => 8,
    'hourly_rate' => 28,
    'total_earned' => 224,
    'status' => 'completed',
    'payment_status' => 'pending'
]);

echo "✅ Time tracking entry created!\n";
```

### Create Sample Review
```bash
php artisan tinker
```
```php
$booking = App\Models\Booking::first();
$client = $booking->client;
$caregiver = App\Models\BookingAssignment::where('booking_id', $booking->id)->first()->caregiver;

App\Models\Review::create([
    'client_id' => $client->id,
    'caregiver_id' => $caregiver->id,
    'booking_id' => $booking->id,
    'rating' => 5,
    'comment' => 'Excellent service! Very professional and caring.',
    'punctuality' => 5,
    'professionalism' => 5,
    'communication' => 5
]);

echo "✅ Review created!\n";
```

---

## 🎯 SUGGESTED TESTING ORDER

1. **Start Here (15 min)**
   - [ ] Run system stats query
   - [ ] Check all existing data
   - [ ] Note what's missing

2. **Client Portal (10 min)**
   - [ ] View current booking
   - [ ] Check payment status
   - [ ] Browse caregivers

3. **Admin Portal (20 min)**
   - [ ] View all bookings
   - [ ] Check time tracking (if any)
   - [ ] View payments
   - [ ] Check reviews

4. **Create Sample Data (10 min)**
   - [ ] Create caregiver account
   - [ ] Create time tracking entry
   - [ ] Create review
   - [ ] Re-check admin portal

5. **Caregiver Portal (15 min)**
   - [ ] Login as caregiver
   - [ ] View assignments
   - [ ] Check earnings
   - [ ] Test clock in/out

6. **Complete Workflow (30 min)**
   - [ ] Follow "Workflow 1" above
   - [ ] Test entire booking cycle
   - [ ] Verify all data connections

---

## 📝 NOTES

- All passwords for test accounts: `password`
- Server must be running: `php artisan serve`
- Frontend must be built: `npm run build`
- Check logs: `storage/logs/laravel.log`

---

## 🆘 TROUBLESHOOTING

### Can't see time tracking data?
```bash
# Check if table exists
php artisan db:table time_trackings

# Create sample entry
# (See "Create Sample Time Tracking Entry" above)
```

### Can't see reviews?
```bash
# Check reviews table
php artisan db:table reviews

# Create sample review
# (See "Create Sample Review" above)
```

### Need to reset and start fresh?
```bash
# ⚠️ WARNING: This deletes all data!
php artisan migrate:fresh --seed
```

---

**Ready to explore? Start with the system stats query to see what you have! 🚀**

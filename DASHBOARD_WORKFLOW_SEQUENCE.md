# CAS Private Care - Complete Dashboard Workflow Sequence

## 📊 Overview
This document maps the complete sequence of interactions between dashboards, showing how data flows when bookings are created, approved, assigned, and completed.

---

## 🔄 Complete Booking Workflow Sequence

### **STEP 1: CLIENT CREATES A BOOKING**

**Dashboard:** Client Dashboard → "Book Service" Section

**Action:**
- Client fills out booking form (service type, date, location, care requirements)
- Submits form via `POST /api/bookings`

**Backend Process:**
```php
BookingController::store()
- Creates Booking record with status='pending' (or 'approved' if admin creates it)
- Stores: client_id, service_type, duty_type, borough, city, county, service_date, 
          duration_days, hourly_rate, client_age, mobility_level, medical_conditions, etc.
- Returns booking data
```

**Immediate Updates:**
1. ✅ **Client Dashboard** → "My Bookings" table updates (new pending booking appears)
2. ✅ **Client Dashboard** → Dashboard stats widget shows updated booking count
3. ✅ **Client Dashboard** → Transaction History shows new pending transaction

**Notifications Sent:**
- 🔔 Notification to Admin: "New booking request from [Client Name]"

---

### **STEP 2: ADMIN APPROVES/REJECTS BOOKING**

**Dashboard:** Admin Dashboard → "Client Bookings" Section

**Action:**
- Admin reviews pending booking
- Clicks "Approve" or "Reject" button
- Calls `POST /api/bookings/{id}/approve` or `POST /api/bookings/{id}/reject`

**Backend Process:**
```php
BookingController::approve() or reject()
- Updates Booking.status to 'approved' or 'rejected'
- NotificationService::notifyBookingApproved() or notifyBookingRejected()
- Sends notification to client
```

**Immediate Updates Across Dashboards:**

#### **Admin Dashboard Updates:**
1. ✅ **Dashboard Stats Widget:**
   - Active Bookings count increases (if approved)
   - Recent Activity feed shows "Booking approved for [Client Name]"
   - Analytics charts update

2. ✅ **Client Bookings Table:**
   - Booking status changes from "Pending" → "Approved" (red chip → green chip)
   - Table refreshes automatically

3. ✅ **Analytics Dashboard:**
   - Revenue projections update
   - Booking trends chart updates

#### **Client Dashboard Updates:**
1. ✅ **Dashboard Stats Widget:**
   - Booking status changes
   - Amount Due may update

2. ✅ **My Bookings Table:**
   - Status chip changes: "Pending" (orange) → "Approved" (green) or "Rejected" (red)
   - Table auto-refreshes

3. ✅ **Notifications:**
   - 🔔 New notification: "Your booking has been approved" or "Your booking has been rejected"

4. ✅ **Transaction History:**
   - Transaction status updates to "Approved" or "Rejected"

#### **Caregiver Dashboard Updates:**
1. ✅ **Job Listings:**
   - NEW booking appears in "Available Bookings" section (only if status='approved')
   - Shows: client name, location, dates, pay rate, spots available
   - Filters: County, City, Date ("This Week", "Soon")

2. ✅ **Dashboard Stats:**
   - Available Jobs count increases

---

### **STEP 3: CAREGIVER VIEWS JOB LISTINGS**

**Dashboard:** Caregiver Dashboard → "Job Listings" Section

**Action:**
- Caregiver views available bookings
- Data loaded from `GET /api/available-clients`

**Backend Process:**
```php
CaregiverController::getAvailableClients()
- Filters bookings where: status IN ['approved', 'confirmed']
- Returns: unassigned or partially assigned bookings
- Includes: client info, location, dates, compensation, spots remaining
```

**Display:**
- Grid/List view of available jobs
- Shows: client name, service type, location, dates, pay rate, estimated earnings
- Indicates: "X of Y spots open" (assignment status)

---

### **STEP 4: ADMIN ASSIGNS CAREGIVERS (Manual Assignment)**

**Dashboard:** Admin Dashboard → "Client Bookings" → Select Booking → Assign Caregivers

**Action:**
- Admin selects a booking
- Opens "Assign Caregivers" dialog
- Searches and selects caregivers
- Calls `POST /api/bookings/{id}/assign-caregivers`

**Backend Process:**
```php
BookingController::assignCaregivers()
- Creates BookingAssignment records
- Updates Booking.assignment_status (unassigned → partial → assigned)
- Sends notifications to assigned caregivers
```

**Immediate Updates:**

#### **Admin Dashboard:**
1. ✅ Booking table shows updated assignment status
2. ✅ Assignment progress indicator updates (e.g., "2/6 assigned")

#### **Caregiver Dashboard:**
1. ✅ **Notifications:**
   - 🔔 New notification: "You have been assigned to [Client Name] booking"

2. ✅ **Dashboard Stats:**
   - Active Assignments count increases
   - Current Client updates

3. ✅ **Schedule/Calendar:**
   - New appointment appears for assigned dates

4. ✅ **Job Listings:**
   - Booking may disappear or show fewer spots available
   - Spots remaining decreases (e.g., "4 of 6 spots open")

---

### **STEP 5: CAREGIVER CLOCKS IN/OUT (Time Tracking)**

**Dashboard:** Caregiver Dashboard → Time Tracking Section

**Action:**
- Caregiver clicks "Clock In" button
- Calls `POST /api/time-tracking/clock-in`

**Backend Process:**
```php
TimeTrackingController::clockIn()
- Creates TimeTracking record with clock_in_time
- Updates caregiver status
```

**Real-Time Updates:**
1. ✅ **Caregiver Dashboard:**
   - Status changes: "Not Clocked In" → "Clocked In" (green chip)
   - Clock In button disabled, Clock Out button enabled
   - Today Hours starts counting

2. ✅ **Admin Dashboard (Time Tracking Section):**
   - Caregiver row shows "Clocked In" status (if viewing)
   - Clock In time displayed
   - Real-time updates every 10 seconds

---

### **STEP 6: BOOKING COMPLETES (Automatic)**

**Backend Process (Automated):**
```php
Artisan Command: check-booking-status (runs periodically)
- Checks bookings where: status='approved' AND service_date + duration_days < now()
- Updates status to 'completed'
- PaymentService::createPaymentForCompletedBooking()
- NotificationService::notifyBookingCompleted()
```

**Updates When Booking Completes:**

#### **Client Dashboard:**
1. ✅ **My Bookings:**
   - Status changes: "Approved" → "Completed" (blue chip)

2. ✅ **Transaction History:**
   - New transaction entry with final amount
   - Status: "Completed"

3. ✅ **Dashboard Stats:**
   - Total Spent increases
   - Completed Bookings count increases

4. ✅ **Notifications:**
   - 🔔 "Your booking with [Caregiver] has been completed"

#### **Admin Dashboard:**
1. ✅ **Dashboard Stats:**
   - Active Bookings decreases
   - Total Revenue increases
   - Completed Bookings count increases

2. ✅ **Analytics:**
   - Revenue chart updates
   - Completion rate updates

3. ✅ **Payments Section:**
   - New payment record created
   - Shows: client, caregiver, amount, status

#### **Caregiver Dashboard:**
1. ✅ **Dashboard Stats:**
   - Total Earnings increases
   - Completed Sessions increases

2. ✅ **Earnings Report:**
   - New payment entry
   - Shows: date, amount, booking details

3. ✅ **Schedule:**
   - Booking moves to "Completed" section

---

## 📈 Dashboard Widget Update Sequence

### **When Booking Created (status='pending'):**

```
CLIENT DASHBOARD:
├── Stats Widget
│   ├── Total Bookings: +1
│   └── Pending Bookings: +1
├── My Bookings Table: New row added (status: "Pending")
└── Transaction History: New row (status: "Pending Assignment")

ADMIN DASHBOARD:
├── Stats Widget
│   └── Active Bookings: +1 (pending counts as active)
├── Client Bookings Table: New row added (status: "Pending")
└── Recent Activity: "New booking request from [Client]"
```

### **When Booking Approved:**

```
CLIENT DASHBOARD:
├── My Bookings: Status chip changes (Pending → Approved)
├── Transaction History: Status updates
└── Notification: "Your booking has been approved"

ADMIN DASHBOARD:
├── Client Bookings: Status chip changes
└── Analytics: Booking approval metrics update

CAREGIVER DASHBOARD:
└── Job Listings: NEW booking appears in available jobs list
```

### **When Caregivers Assigned:**

```
ADMIN DASHBOARD:
└── Client Bookings: Assignment progress updates (e.g., "2/6 assigned")

CAREGIVER DASHBOARD (for assigned caregivers):
├── Notification: "You have been assigned to [Client]"
├── Dashboard Stats: Active Assignments +1
└── Schedule: New appointment appears

CAREGIVER DASHBOARD (for all caregivers):
└── Job Listings: Spots remaining decreases
```

### **When Booking Completed:**

```
CLIENT DASHBOARD:
├── Stats Widget
│   ├── Total Spent: +$X
│   └── Completed Bookings: +1
├── My Bookings: Status → "Completed"
└── Transaction History: New completed transaction

ADMIN DASHBOARD:
├── Stats Widget
│   ├── Active Bookings: -1
│   ├── Total Revenue: +$X
│   └── Completed Bookings: +1
├── Payments: New payment record
└── Analytics: Revenue chart updates

CAREGIVER DASHBOARD:
├── Stats Widget
│   ├── Total Earnings: +$X
│   └── Completed Sessions: +1
├── Earnings Report: New payment entry
└── Transaction History: New completed payment
```

---

## 🔗 API Endpoints & Data Flow

### **Booking Creation Flow:**
```
Client Dashboard (Book Service Form)
    ↓
POST /api/bookings
    ↓
BookingController::store()
    ↓
Database: bookings table (INSERT)
    ↓
NotificationService::notifyBookingCreated()
    ↓
Notifications table (INSERT)
    ↓
Response → Client Dashboard (refresh bookings table)
```

### **Booking Approval Flow:**
```
Admin Dashboard (Approve Button)
    ↓
POST /api/bookings/{id}/approve
    ↓
BookingController::approve()
    ↓
Database: bookings.status = 'approved' (UPDATE)
    ↓
NotificationService::notifyBookingApproved()
    ↓
Notifications table (INSERT for client)
    ↓
Response → Admin Dashboard (refresh table)
    ↓
Client Dashboard (polling/refresh) → Shows approved status
    ↓
Caregiver Dashboard (polling/refresh) → Shows in Job Listings
```

### **Stats Update Flow:**
```
Dashboard Component Mounted
    ↓
GET /api/client/stats (or /api/admin/stats, /api/caregiver/{id}/stats)
    ↓
DashboardController::clientStats() / adminStats() / caregiverStats()
    ↓
Database Queries:
    - Booking counts by status
    - Payment totals
    - User counts
    - Recent activity
    ↓
Response with JSON data
    ↓
Vue Component updates reactive refs
    ↓
Widgets re-render with new data
```

---

## 🎯 Real-Time Update Mechanisms

### **Polling (Auto-Refresh):**
- **Admin Dashboard:** Stats refresh every 30 seconds
- **Caregiver Dashboard:** Time tracking refreshes every 10 seconds
- **Client Dashboard:** Bookings table refreshes on section change

### **Event-Driven Updates:**
- **Notifications:** Real-time via NotificationService
- **Status Changes:** Triggered by user actions (approve, assign, complete)

### **Manual Refresh:**
- All dashboards have refresh buttons/icons
- Tables refresh after CRUD operations (create, update, delete)

---

## 📱 Dashboard-Specific Features

### **CLIENT DASHBOARD:**
- **Book Service:** Create new bookings
- **My Bookings:** View own bookings with status tracking
- **Browse Caregivers:** Search and view caregiver profiles
- **Payment Information:** View transaction history and amounts due
- **Analytics:** Spending trends, monthly breakdowns

### **ADMIN DASHBOARD:**
- **User Management:** Create, edit, delete users (clients, caregivers, staff)
- **Client Bookings:** View all bookings, approve/reject, assign caregivers
- **Time Tracking:** Monitor caregiver clock in/out times
- **Payments:** View all payment transactions
- **Analytics:** Revenue, user growth, booking trends
- **Applications:** Approve/reject caregiver applications

### **CAREGIVER DASHBOARD:**
- **Job Listings:** View available bookings (approved, unassigned/partial)
- **Time Tracking:** Clock in/out, track hours
- **Earnings Report:** View payment history and totals
- **Schedule:** View assigned appointments
- **Profile:** Update availability and information

---

## 🔄 Status Transition Diagram

```
BOOKING STATUS FLOW:
─────────────────────────────────────────────────────
pending (client creates)
    ↓
approved (admin approves) ──→ rejected (admin rejects) [END]
    ↓
[Booking appears in Caregiver Job Listings]
    ↓
[Admin assigns caregivers]
    ↓
[Caregivers clock in/out for shifts]
    ↓
completed (automatic when service_date + duration_days < now())
    ↓
[Payment created automatically]
    ↓
[END]
```

---

## 🎨 Widget Update Priority

1. **HIGH PRIORITY (Immediate):**
   - Booking status changes (Pending → Approved → Completed)
   - Assignment status updates
   - Notification badges

2. **MEDIUM PRIORITY (Within 30 seconds):**
   - Dashboard stats (total bookings, revenue, etc.)
   - Table data refreshes
   - Chart data updates

3. **LOW PRIORITY (On page load/navigation):**
   - Historical analytics
   - Detailed reports
   - Profile information

---

## 🔍 Key Integration Points

1. **Bookings Table:** Central data source connecting all dashboards
2. **Notifications Table:** Real-time communication between users
3. **Payments Table:** Financial tracking across dashboards
4. **Time Tracking Table:** Real-time caregiver activity monitoring
5. **Booking Assignments Table:** Links bookings to caregivers

---

## ✅ Current Status: ALL CONNECTED

All dashboards are fully integrated and update in real-time when:
- ✅ Bookings are created
- ✅ Bookings are approved/rejected
- ✅ Caregivers are assigned
- ✅ Time tracking occurs
- ✅ Bookings complete
- ✅ Payments are processed

The system maintains data consistency across all dashboards through shared database tables and API endpoints.







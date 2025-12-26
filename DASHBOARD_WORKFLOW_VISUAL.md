# CAS Private Care - Dashboard Workflow Visual Diagram

## 🔄 Complete Booking Lifecycle Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        BOOKING CREATION & APPROVAL FLOW                      │
└─────────────────────────────────────────────────────────────────────────────┘

CLIENT DASHBOARD                          ADMIN DASHBOARD                      CAREGIVER DASHBOARD
────────────────                          ────────────────                      ───────────────────

[1] CLIENT CREATES BOOKING
    ┌─────────────────────┐
    │ Book Service Form   │
    │ - Fill details      │
    │ - Submit            │
    └──────────┬──────────┘
               │
               │ POST /api/bookings
               │ (status: 'pending')
               ▼
    ┌─────────────────────┐
    │ My Bookings Table   │◄─── Updates immediately
    │ +1 Pending          │
    └─────────────────────┘
               │
               │ Notification sent
               ▼
    ┌─────────────────────┐
    │ Admin Notification  │
    │ "New booking..."    │
    └──────────┬──────────┘
               │
               │ Admin clicks notification
               ▼
    ┌─────────────────────┐
    │ Client Bookings     │
    │ View new booking    │
    │ Status: Pending     │
    └──────────┬──────────┘
               │
               │ Admin clicks "Approve"
               │ POST /api/bookings/{id}/approve
               ▼
    ┌─────────────────────┐
    │ Status Updated      │
    │ Pending → Approved  │
    └──────────┬──────────┘
               │
               │ Notification sent
               │ Stats updated
               ▼
    ┌─────────────────────┐              ┌─────────────────────┐
    │ My Bookings Table   │◄─────────────│ Client Notification │
    │ Status: Approved    │              │ "Booking approved"  │
    └─────────────────────┘              └─────────────────────┘
               │
               │ Booking appears in
               │ available jobs
               ▼
                                    ┌─────────────────────┐
                                    │ Job Listings        │
                                    │ NEW booking shown   │
                                    │ Status: Approved    │
                                    │ Spots: X of Y open  │
                                    └─────────────────────┘


[2] ADMIN ASSIGNS CAREGIVERS
    ┌─────────────────────┐
    │ Client Bookings     │
    │ Select booking      │
    │ Click "Assign"      │
    └──────────┬──────────┘
               │
               │ POST /api/bookings/{id}/assign-caregivers
               │ Creates BookingAssignment records
               ▼
    ┌─────────────────────┐
    │ Assignment Status   │
    │ "2/6 assigned"      │
    └──────────┬──────────┘
               │
               │ Notifications sent
               │ Stats updated
               ▼
    ┌─────────────────────┐              ┌─────────────────────┐
    │ Job Listings        │◄─────────────│ Notification        │
    │ Spots: 4/6 open     │              │ "Assigned to..."    │
    │ (updated count)     │              └─────────────────────┘
    └─────────────────────┘
                                    ┌─────────────────────┐
                                    │ Schedule            │
                                    │ NEW appointment     │
                                    │ appears             │
                                    └─────────────────────┘


[3] CAREGIVER CLOCKS IN
    ┌─────────────────────┐
    │ Time Tracking       │
    │ Click "Clock In"    │
    └──────────┬──────────┘
               │
               │ POST /api/time-tracking/clock-in
               ▼
    ┌─────────────────────┐
    │ Status Updated      │
    │ "Clocked In"        │
    │ Hours start         │
    └──────────┬──────────┘
               │
               │ Real-time update
               │ (every 10 seconds)
               ▼
    ┌─────────────────────┐
    │ Time Tracking       │
    │ Shows "Clocked In"  │
    │ Today Hours: Xh Ym  │
    └─────────────────────┘


[4] BOOKING COMPLETES (AUTOMATIC)
    ┌─────────────────────┐
    │ Background Job      │
    │ Checks dates        │
    │ (service_date +     │
    │  duration_days)     │
    └──────────┬──────────┘
               │
               │ Status: 'completed'
               │ Payment created
               │ Notifications sent
               ▼
    ┌─────────────────────┐              ┌─────────────────────┐
    │ My Bookings         │              │ Payments Table      │
    │ Status: Completed   │              │ NEW payment record  │
    │ Total Spent: +$X    │              └─────────────────────┘
    └─────────────────────┘
               │
               │ Stats updated
               ▼
    ┌─────────────────────┐              ┌─────────────────────┐
    │ Dashboard Stats     │              │ Earnings Report     │
    │ Completed: +1       │              │ NEW payment entry   │
    │ Revenue: +$X        │              │ Earnings: +$X       │
    └─────────────────────┘              └─────────────────────┘
```

---

## 📊 Widget Update Sequence

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    WIDGET UPDATE TIMELINE                                    │
└─────────────────────────────────────────────────────────────────────────────┘

TIME    EVENT                      CLIENT DASHBOARD        ADMIN DASHBOARD         CAREGIVER DASHBOARD
─────────────────────────────────────────────────────────────────────────────────────────────────────────

T+0     Booking Created
        └─► My Bookings: +1        Stats: Total Bookings   Stats: Active Bookings
                                    +1                       +1
                                    Status: Pending         Recent Activity:
                                                            "New booking..."

T+1     Booking Approved
        └─► Status Change          My Bookings:            Client Bookings:       Job Listings:
                                    Status → Approved       Status → Approved      NEW booking appears
                                    Notification:                                   
                                    "Approved"                                     

T+2     Caregivers Assigned
        └─► Assignment Created                             Client Bookings:       Job Listings:
                                                            "2/6 assigned"         Spots: 4/6 open
                                                                                   Notification:
                                                                                   "Assigned to..."
                                                                                   Schedule:
                                                                                   NEW appointment

T+3     Clock In
        └─► Time Tracking                                  Time Tracking:         Time Tracking:
                                                            Shows "Clocked In"     Status: "Clocked In"
                                                                                   Hours counting

T+4     Booking Completes
        └─► Auto Completion         My Bookings:           Payments:              Earnings Report:
                                    Status → Completed      NEW payment            NEW payment entry
                                    Stats: Total Spent      Stats: Revenue: +$X   Stats: Earnings: +$X
                                    +$X                     Completed: +1          Completed: +1
                                    Completed: +1           Active: -1

```

---

## 🔗 API Call Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          API ENDPOINT FLOW                                   │
└─────────────────────────────────────────────────────────────────────────────┘

CLIENT SIDE                              SERVER SIDE                           DATABASE
─────────────                           ───────────                           ─────────

[CREATE BOOKING]
POST /api/bookings                       BookingController::store()            INSERT INTO bookings
{                                        ├─ Create Booking record              (status='pending')
  service_type,                          ├─ NotificationService                INSERT INTO notifications
  service_date,                          │  ::notifyBookingCreated()           (for admin)
  duration_days,                         └─ Return booking data
  ...
}                                        
                                        ──────────────────────────────────────
                                        
                                        [GET STATS]
GET /api/client/stats                    DashboardController::clientStats()    SELECT FROM bookings
                                        ├─ Count bookings by status            WHERE client_id = X
                                        ├─ Calculate total spent               SELECT FROM payments
                                        ├─ Get recent transactions             WHERE client_id = X
                                        └─ Return JSON stats
                                        
[APPROVE BOOKING]
POST /api/bookings/{id}/approve          BookingController::approve()          UPDATE bookings
                                        ├─ Update status='approved'            SET status='approved'
                                        ├─ NotificationService                 INSERT INTO notifications
                                        │  ::notifyBookingApproved()           (for client)
                                        └─ Return success
                                        
[GET AVAILABLE JOBS]
GET /api/available-clients               CaregiverController::                 SELECT FROM bookings
                                        │  getAvailableClients()               WHERE status IN
                                        ├─ Filter approved bookings            ('approved','confirmed')
                                        ├─ Filter unassigned/partial           AND assignments < needed
                                        └─ Return job listings
                                        
[ASSIGN CAREGIVERS]
POST /api/bookings/{id}/                 BookingController::                   INSERT INTO
  assign-caregivers                      │  assignCaregivers()                 booking_assignments
{                                        ├─ Create assignments                 UPDATE bookings
  caregiver_ids: [...]                   ├─ Update assignment_status           (assignment_status)
}                                        ├─ NotificationService                INSERT INTO notifications
                                        │  ::notifyCaregiverOfAssignment()     (for caregivers)
                                        └─ Return success
                                        
[CLOCK IN]
POST /api/time-tracking/clock-in         TimeTrackingController::clockIn()     INSERT INTO time_trackings
                                        ├─ Create time tracking record         (clock_in_time)
                                        └─ Return success
                                        
[GET TIME TRACKING]
GET /api/time-tracking                   TimeTrackingController::index()       SELECT FROM time_trackings
                                        └─ Return current sessions             WHERE caregiver_id = X
```

---

## 🎯 Data Dependencies

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         DATA FLOW MAP                                        │
└─────────────────────────────────────────────────────────────────────────────┘

                    ┌──────────────┐
                    │   bookings   │◄─────── Central Data Source
                    │    table     │
                    └──────┬───────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│booking_      │  │time_         │  │payments      │
│assignments   │  │trackings     │  │table         │
│table         │  │table         │  │              │
└──────┬───────┘  └──────┬───────┘  └──────┬───────┘
       │                 │                  │
       │                 │                  │
       ▼                 ▼                  ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ CAREGIVER    │  │ CAREGIVER    │  │ ADMIN        │
│ Dashboard    │  │ Dashboard    │  │ Dashboard    │
│ Job Listings │  │ Time Track   │  │ Payments     │
└──────────────┘  └──────────────┘  └──────────────┘

        ┌──────────────┐
        │notifications │◄─────── Real-time Communication
        │table         │
        └──────┬───────┘
               │
        ┌──────┼──────┐
        │      │      │
        ▼      ▼      ▼
    ┌─────┐ ┌─────┐ ┌─────┐
    │CLIENT│ │ADMIN│ │CAREG│
    │DASH  │ │DASH │ │DASH │
    └─────┘ └─────┘ └─────┘
```

---

## ✅ Dashboard Connection Matrix

| Action | Client Dashboard | Admin Dashboard | Caregiver Dashboard |
|--------|-----------------|-----------------|---------------------|
| **Booking Created** | ✅ Shows in My Bookings<br>✅ Stats update | ✅ Shows in Client Bookings<br>✅ Activity feed | ❌ Not visible yet |
| **Booking Approved** | ✅ Status updates<br>✅ Notification | ✅ Status updates<br>✅ Stats update | ✅ Appears in Job Listings |
| **Caregiver Assigned** | ✅ Shows assigned caregiver<br>✅ Notification | ✅ Assignment progress | ✅ Notification<br>✅ Schedule update<br>✅ Job spots decrease |
| **Clock In/Out** | ❌ Not visible | ✅ Time Tracking table | ✅ Status updates<br>✅ Hours tracked |
| **Booking Completed** | ✅ Status updates<br>✅ Total Spent increases<br>✅ Transaction added | ✅ Revenue increases<br>✅ Payment created<br>✅ Stats update | ✅ Earnings increase<br>✅ Payment in report |

---

## 🔔 Notification Flow

```
BOOKING CREATED
    │
    ├─► Admin receives: "New booking request from [Client]"
    │
    └─► (No client notification - booking is their own)

BOOKING APPROVED
    │
    ├─► Client receives: "Your booking has been approved"
    │
    └─► Caregivers: (No notification - booking appears in Job Listings)

CAREGIVER ASSIGNED
    │
    ├─► Caregiver receives: "You have been assigned to [Client] booking"
    │
    └─► Client receives: "[Caregiver] has been assigned to your booking"

BOOKING COMPLETED
    │
    ├─► Client receives: "Your service has been completed"
    │
    └─► Caregiver receives: "Service for booking #X completed"
```

---

## 📱 Real-Time Update Frequency

| Component | Update Method | Frequency |
|-----------|--------------|-----------|
| **Stats Widgets** | Polling | On page load + every 30 seconds |
| **Time Tracking** | Polling | Every 10 seconds |
| **Booking Tables** | Event-driven | On action (create/update/delete) |
| **Notifications** | Event-driven | Immediately when created |
| **Job Listings** | Polling | On page load + manual refresh |

---

## 🎨 Visual Status Indicators

### **Status Chips Colors:**
- 🔴 **Pending** - Orange/Red chip
- 🟢 **Approved** - Green chip
- 🔵 **Completed** - Blue chip
- ⚫ **Rejected** - Red chip

### **Assignment Status:**
- **Unassigned** - Yellow/Warning chip
- **Partial** - Info/Blue chip  
- **Assigned** - Success/Green chip

---

## 🔍 Key Integration Points Summary

1. **Bookings Table** - Central hub connecting all dashboards
2. **Notifications** - Real-time communication between users
3. **API Endpoints** - RESTful endpoints for data exchange
4. **Status Fields** - Unified status tracking (booking status, assignment status)
5. **Statistics Calculations** - Aggregated from bookings, payments, time tracking
6. **Real-time Polling** - Auto-refresh for time-sensitive data
7. **Event Notifications** - Immediate updates via NotificationService

---

**✅ ALL DASHBOARDS ARE FULLY INTEGRATED AND REAL-TIME CONNECTED!**







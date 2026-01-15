# Client Bookings - Admin Dashboard Documentation

## Overview
The Client Bookings section in the Admin Dashboard provides comprehensive booking management with detailed views, actions, and status tracking.

## Location
**Navigation:** Dashboard → BOOKINGS → Client Bookings

---

## Main Features

### 1. **Bookings Table**

#### Table Columns:
| Column | Description |
|--------|-------------|
| **Client** | Name of the client who made the booking |
| **Service** | Type of service (e.g., Caregiver, Nurse) |
| **Date** | Service start date |
| **Time** | Starting time of the service |
| **Hours/Day** | Hours per day (8, 12, or 24) |
| **Duration** | Total service duration in days |
| **Location** | City/Borough where service is provided |
| **Price** | Total cost (with strikethrough if discount applied) |
| **Referral Discount** | Discount amount per hour if referral code used |
| **Assigned** | Progress bar showing caregivers assigned (e.g., 0/1, 1/2) |
| **Status** | Current booking status with colored chip |
| **Actions** | Action buttons for managing the booking |

#### Filter & Search Options:
- **Search:** Text search across bookings
- **Status Filter:** All, Pending, Approved, Rejected
- **Date Filter:** All Time, Today, This Week, This Month
- **Bulk Actions:** Select multiple bookings for bulk deletion

---

## 2. **Booking Actions**

### Action Buttons (Right side of each row):

#### A. **Approve** (✓ Green Check Icon)
- **Visible when:** Status = "pending"
- **Function:** Approves the booking request
- **Action:** Changes status from "pending" to "approved"

#### B. **Reject** (✗ Red X Icon)
- **Visible when:** Status = "pending"
- **Function:** Rejects the booking request
- **Action:** Changes status to "rejected"

#### C. **View Details** (👁 Eye Icon)
- **Always visible**
- **Function:** Opens detailed booking modal
- **Shows:** Complete booking information

#### D. **View Assigned Caregivers** (👥 Group Icon)
- **Visible when:** Status = "approved" OR "confirmed"
- **Function:** Shows list of caregivers assigned to this booking
- **Details:** Names, contact info, assignment status

#### E. **Assign Caregiver** (👤+ Plus Icon)
- **Visible when:** Status = "approved" OR "confirmed"
- **Function:** Opens dialog to assign/add caregivers to booking
- **Action:** Select caregivers to fulfill the booking

---

## 3. **View Booking Details Modal**

### Modal Structure:

#### **Header:**
- Title: "Booking Details"
- Close button (X)
- Status chip (color-coded)

#### **Content Sections:**

### A. **Service Information**
- 🩺 Service Type
- ⏰ Hours per Day
- 📅 Service Date
- 🕐 Starting Time
- ⏱ Duration (days)
- 👥 Caregivers Assigned (progress chip)

### B. **Location**
- 🏙 City/Borough
- 🛣 Street Address
- 🏢 Apartment/Unit

### C. **Client Information**
- 👤 Client Name
- 🎂 Client Age
- 🚶 Mobility Level
- 💊 Medical Conditions

### D. **Service Summary** (Pricing)
- 💼 Duty Type
- ⏰ Hours per Day
- 📅 Duration
- 💵 Rate per Hour
- 🧮 Calculation Formula
- 💰 **Order Total** (large, bold, green)

### E. **Voucher/Referral Code** (if applied)
- 🎫 Referral Code
- 💚 Discount Applied ($/hour)
- 👤 Referred By (name)
- 📧 Email of referrer

### F. **Booking Timeline**
Visual timeline showing:
- ✅ **Booking Created** - Initial submission date/time
- 👥 **Caregivers Assigned** - Number of caregivers assigned
- ✅ **Fully Assigned** - All positions filled
- ⚠️ **Service In Progress** - Currently active
- ✅ **Service Completed** - Successfully finished

---

## 4. **Status System**

### Status Types & Colors:

| Status | Color | Icon | Meaning |
|--------|-------|------|---------|
| **pending** | Orange | 🕐 | Awaiting admin approval |
| **approved** | Blue | ✓ | Approved, needs caregiver assignment |
| **confirmed** | Green | ✓✓ | Fully assigned, ready to start |
| **In Progress** | Yellow | ⏳ | Service currently active |
| **Completed** | Green | ✓ | Service finished |
| **Rejected** | Red | ✗ | Booking declined |

---

## 5. **Assignment Status**

### Caregiver Assignment Progress:

The "Assigned" column shows a progress indicator:
- **Format:** `X / Y` where:
  - X = Currently assigned caregivers
  - Y = Required caregivers
  
- **Progress Bar Colors:**
  - 🔴 **Red:** No caregivers assigned (0/X)
  - 🟡 **Yellow:** Partially assigned (1/2, 2/3, etc.)
  - 🟢 **Green:** Fully assigned (X/X)

### How Many Caregivers Needed?
Based on hours per day:
- **8 hours:** 1 caregiver
- **12 hours:** 2 caregivers
- **24 hours:** 3 caregivers

---

## 6. **Price Display**

### Pricing Information:

#### Regular Price:
```
$5,400
```

#### With Referral Discount:
```
$48,600    ← Strikethrough (original price)
$4,800     ← Discounted price (bold)
```

### Price Calculation:
```
Hours per Day × Duration (days) × Rate per Hour = Total
8 hours × 15 days × $45 = $5,400
```

### With Discount:
```
Hours per Day × Duration × (Rate - Discount) = Total
8 hours × 15 days × ($45 - $5) = 8 × 15 × $40 = $4,800
```

---

## 7. **Workflow Examples**

### Example 1: New Booking Approval
1. Client submits booking → Status: **pending**
2. Admin clicks **View** (eye icon) → Reviews details
3. Admin clicks **Approve** (✓) → Status: **approved**
4. Admin clicks **Assign Caregiver** (👤+) → Assigns caregivers
5. All caregivers assigned → Status: **confirmed**
6. Service starts → Status: **In Progress**
7. Service ends → Status: **Completed**

### Example 2: Checking Assignment Status
1. Navigate to Client Bookings
2. Look at "Assigned" column
3. See **0 / 1** with red progress bar
4. Click **Assign Caregiver** button
5. Select available caregiver
6. Progress updates to **1 / 1** with green bar

---

## 8. **Key Features Summary**

✅ **Search & Filter** - Find bookings quickly  
✅ **Bulk Actions** - Delete multiple bookings at once  
✅ **Status Management** - Approve/reject pending bookings  
✅ **Caregiver Assignment** - Assign caregivers to bookings  
✅ **Detailed View** - See complete booking information  
✅ **Timeline Tracking** - View booking history  
✅ **Referral Tracking** - See applied vouchers & referrers  
✅ **Price Calculation** - Transparent pricing breakdown  
✅ **Real-time Updates** - Auto-refresh every 10 seconds  

---

## 9. **Technical Details**

### Data Structure:
```javascript
{
  id: 123,
  client: "John Doe",
  service: "Caregiver",
  date: "Jan 4, 2026",
  time: "10:00 PM",
  hoursPerDay: 8,
  durationDays: 15,
  location: "New York",
  formattedPrice: "$5,400",
  referralDiscountApplied: 5,
  assignedCount: 0,
  caregiversNeeded: 1,
  status: "pending"
}
```

### API Endpoints:
- `GET /api/admin/bookings` - Fetch all bookings
- `POST /api/admin/bookings/{id}/approve` - Approve booking
- `POST /api/admin/bookings/{id}/reject` - Reject booking
- `POST /api/admin/bookings/{id}/assign` - Assign caregiver
- `DELETE /api/admin/bookings/{id}` - Delete booking

---

## 10. **Common Actions**

### To Approve a Booking:
1. Find booking with "pending" status
2. Click green ✓ button
3. Confirm approval
4. Status changes to "approved"

### To Assign Caregivers:
1. Find approved booking
2. Click blue 👤+ button
3. Select caregiver(s) from list
4. Confirm assignment
5. Progress bar updates

### To View Full Details:
1. Click eye 👁 icon on any booking
2. Modal opens with complete information
3. Review all sections
4. Click "Close" when done

---

**Version:** v1.5.0  
**Last Updated:** January 14, 2026  
**Feature:** Client Bookings Management

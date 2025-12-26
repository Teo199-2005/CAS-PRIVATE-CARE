# Booking Status Restructuring

## Overview
The booking status system has been restructured to separate booking approval status from caregiver assignment status. This provides clearer tracking of both the booking approval workflow and the caregiver assignment progress.

## Changes Made

### 1. Database Schema Changes

#### Migration: `2025_01_02_120000_update_booking_status_structure.php`

**Changes to `bookings` table:**
- **status** column: Changed from `['pending', 'confirmed', 'completed', 'cancelled']` to `['pending', 'approved', 'rejected']`
- **assignment_status** column: New column added with values `['unassigned', 'partial', 'assigned']`

**Status Definitions:**

**Booking Status (status):**
- `pending` - Booking is awaiting admin approval
- `approved` - Booking has been approved by admin
- `rejected` - Booking has been rejected by admin

**Assignment Status (assignment_status):**
- `unassigned` - No caregivers have been assigned yet
- `partial` - Some but not all required caregivers have been assigned
- `assigned` - All required caregivers have been assigned

### 2. Model Updates

#### Booking Model (`app/Models/Booking.php`)

**Added Fields:**
- `assignment_status` to fillable array

**New Methods:**
- `updateAssignmentStatus()` - Automatically updates assignment status based on current assignments
- `getAssignmentProgressAttribute()` - Returns assignment progress as a fraction string (e.g., "1 / 6")

**Logic:**
```php
// Assignment status is calculated based on:
$totalRequired = $booking->duration_days; // 1 caregiver per day
$assignedCount = $booking->assignments()->where('status', 'assigned')->count();

if ($assignedCount == 0) {
    $assignment_status = 'unassigned';
} elseif ($assignedCount < $totalRequired) {
    $assignment_status = 'partial';
} else {
    $assignment_status = 'assigned';
}
```

#### BookingAssignment Model (`app/Models/BookingAssignment.php`)

**Added Model Events:**
- Automatically calls `updateAssignmentStatus()` on the parent booking when:
  - An assignment is created
  - An assignment is updated
  - An assignment is deleted

### 3. Frontend Updates

#### AdminDashboard.vue

**Updated Functions:**
- `getBookingStatusColor()` - Now handles only booking status colors (pending/approved/rejected)
- `getAssignmentStatusColor()` - New function for assignment status colors (unassigned/partial/assigned)
- `loadClientBookings()` - Separates booking status from assignment status
- `confirmAssignCaregivers()` - Updates assignment status instead of booking status
- `approveBooking()` - Sets status to 'approved'
- `rejectBooking()` - Sets status to 'rejected'
- `unassignCaregiver()` - Updates assignment status when caregivers are removed

**Updated Table:**
- Added new "Assignment" column to display assignment status
- Status column now only shows booking approval status
- Filter dropdown updated to show only: All, Pending, Approved, Rejected

**Action Buttons:**
- Approve/Reject buttons only show for bookings with status = 'pending'
- Assign Caregiver button only shows for bookings with status = 'approved'

### 4. Data Migration

#### UpdateBookingStatusSeeder

**Purpose:** Updates existing booking data to use the new status structure

**Conversion Logic:**
```php
Old Status -> New Status
'confirmed' -> 'approved'
'completed' -> 'approved'
'cancelled' -> 'rejected'
'pending'   -> 'pending'
```

**Assignment Status Calculation:**
- Counts existing assignments
- Compares to required caregivers (duration_days / 15)
- Sets appropriate assignment_status

## How to Apply Changes

### Step 1: Run the Migration
```bash
php artisan migrate
```

This will:
- Update the `status` enum in the `bookings` table
- Add the `assignment_status` column

### Step 2: Update Existing Data
```bash
php artisan db:seed --class=UpdateBookingStatusSeeder
```

This will:
- Convert all existing booking statuses to the new format
- Calculate and set assignment_status for all bookings

### Step 3: Clear Cache (if needed)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Usage Examples

### Admin Workflow

1. **New Booking Created**
   - status: `pending`
   - assignment_status: `unassigned`

2. **Admin Approves Booking**
   - status: `approved`
   - assignment_status: `unassigned`
   - Admin can now assign caregivers

3. **Admin Assigns Some Caregivers**
   - status: `approved`
   - assignment_status: `partial` (if not all caregivers assigned)

4. **All Caregivers Assigned**
   - status: `approved`
   - assignment_status: `assigned`

### Rejected Booking

1. **Admin Rejects Booking**
   - status: `rejected`
   - assignment_status: `unassigned` (or whatever it was)
   - No further caregiver assignments allowed

## Benefits

1. **Clear Separation of Concerns**
   - Booking approval is separate from caregiver assignment
   - Easier to track workflow progress

2. **Better Status Tracking**
   - Can see at a glance which bookings need approval
   - Can see which approved bookings need caregiver assignment
   - Can see assignment progress (partial vs fully assigned)

3. **Improved Admin Experience**
   - Clearer action buttons based on status
   - Better filtering options
   - More intuitive workflow

4. **Automatic Status Updates**
   - Assignment status updates automatically when caregivers are assigned/unassigned
   - No manual status management needed

## API Endpoints Affected

- `GET /api/bookings-with-assignments` - Returns bookings with both status fields
- `PUT /api/bookings/{id}` - Can update booking status (pending/approved/rejected)
- `POST /api/bookings/{id}/assign` - Automatically updates assignment_status

## Testing Checklist

- [ ] Create new booking - should have status='pending', assignment_status='unassigned'
- [ ] Approve booking - status should change to 'approved'
- [ ] Reject booking - status should change to 'rejected'
- [ ] Assign partial caregivers - assignment_status should be 'partial'
- [ ] Assign all caregivers - assignment_status should be 'assigned'
- [ ] Unassign caregivers - assignment_status should update accordingly
- [ ] Filter by status - should show only pending/approved/rejected options
- [ ] View booking details - should show both status columns
- [ ] Existing bookings - should be converted to new status structure

## Rollback Instructions

If you need to rollback these changes:

```bash
php artisan migrate:rollback
```

This will:
- Remove the `assignment_status` column
- Revert the `status` enum to the original values

Note: You may need to manually restore booking status values if you rollback after running the seeder.

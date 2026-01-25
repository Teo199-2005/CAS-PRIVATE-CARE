# Assignment Database Structure

## Overview
The caregiver assignment system uses two main tables to track which caregivers are assigned to which bookings.

---

## Tables

### 1. `bookings` Table
Stores all client booking requests with assignment tracking.

**Key Assignment Fields:**
- `status` - Booking approval status: `pending`, `approved`, `rejected`, `completed`
- `assignment_status` - Caregiver assignment status: `unassigned`, `partial`, `assigned`

**Assignment Status Logic:**
- `unassigned` - No caregivers assigned (0 assignments)
- `partial` - Some caregivers assigned but not enough (1+ but < required)
- `assigned` - All required caregivers assigned (matches duration_days)

**Other Important Fields:**
- `id` - Primary key
- `client_id` - Foreign key to users table
- `service_type` - Type of service requested
- `service_date` - Start date of service
- `duration_days` - Number of days (determines how many caregivers needed)
- `borough` - Service location
- `street_address` - Service address
- `special_instructions` - Additional notes

---

### 2. `booking_assignments` Table
Stores the actual caregiver-to-booking assignments (junction table).

**Schema:**
```sql
CREATE TABLE booking_assignments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    booking_id BIGINT NOT NULL,              -- FK to bookings.id
    caregiver_id BIGINT NOT NULL,            -- FK to caregivers.id
    assigned_at DATETIME NOT NULL,           -- When assignment was made
    start_time DATETIME NULL,                -- Actual shift start time
    end_time DATETIME NULL,                  -- Actual shift end time
    status ENUM('assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'assigned',
    notes TEXT NULL,                         -- Assignment notes
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Status Values:**
- `assigned` - Caregiver is assigned but hasn't started
- `in_progress` - Caregiver is currently working
- `completed` - Shift completed
- `cancelled` - Assignment was cancelled

---

## Relationships

### Booking → Assignments (One-to-Many)
```php
// In Booking model
public function assignments()
{
    return $this->hasMany(BookingAssignment::class);
}
```

### Booking → Assigned Caregiver (One-to-One through)
```php
// In Booking model
public function assignedCaregiver()
{
    return $this->hasOneThrough(
        Caregiver::class,
        BookingAssignment::class,
        'booking_id',
        'id',
        'id',
        'caregiver_id'
    )->where('booking_assignments.status', '!=', 'cancelled');
}
```

### BookingAssignment → Booking (Many-to-One)
```php
// In BookingAssignment model
public function booking()
{
    return $this->belongsTo(Booking::class);
}
```

### BookingAssignment → Caregiver (Many-to-One)
```php
// In BookingAssignment model
public function caregiver()
{
    return $this->belongsTo(Caregiver::class);
}
```

---

## How Assignments Work

### 1. Creating an Assignment
When a caregiver is assigned to a booking:
```php
BookingAssignment::create([
    'booking_id' => $bookingId,
    'caregiver_id' => $caregiverId,
    'assigned_at' => now(),
    'status' => 'assigned'
]);
```

### 2. Automatic Status Update
The `BookingAssignment` model has event listeners that automatically update the booking's `assignment_status`:
- When an assignment is **created** → Updates booking assignment_status
- When an assignment is **updated** → Updates booking assignment_status
- When an assignment is **deleted** → Updates booking assignment_status

### 3. Assignment Status Calculation
```php
// In Booking model
public function updateAssignmentStatus()
{
    $totalRequired = $this->duration_days;
    $assignedCount = $this->assignments()->where('status', 'assigned')->count();
    
    if ($assignedCount == 0) {
        $this->assignment_status = 'unassigned';
    } elseif ($assignedCount < $totalRequired) {
        $this->assignment_status = 'partial';
    } else {
        $this->assignment_status = 'assigned';
    }
    
    $this->save();
}
```

---

## Query Examples

### Get All Assigned Caregivers for a Booking
```php
$booking = Booking::find($bookingId);
$assignedCaregivers = $booking->assignments()
    ->with('caregiver')
    ->where('status', '!=', 'cancelled')
    ->get();
```

### Get Assignment Progress
```php
$booking = Booking::find($bookingId);
$progress = $booking->assignment_progress; // Returns "1 / 6" format
```

### Get Bookings with Assignment Status
```php
// Get all unassigned bookings
$unassigned = Booking::where('assignment_status', 'unassigned')->get();

// Get all partially assigned bookings
$partial = Booking::where('assignment_status', 'partial')->get();

// Get all fully assigned bookings
$assigned = Booking::where('assignment_status', 'assigned')->get();
```

### Get Caregiver's Assigned Bookings
```php
$caregiverId = 1;
$assignments = BookingAssignment::where('caregiver_id', $caregiverId)
    ->with('booking')
    ->where('status', '!=', 'cancelled')
    ->get();
```

---

## Database Location
- **SQLite Database:** `database/database.sqlite`
- **Models:** 
  - `app/Models/Booking.php`
  - `app/Models/BookingAssignment.php`
  - `app/Models/Caregiver.php`
- **Migrations:**
  - `database/migrations/2025_12_17_161244_create_bookings_table.php`
  - `database/migrations/2025_12_17_161609_create_booking_assignments_table.php`
  - `database/migrations/2025_01_02_120000_update_booking_status_structure.php`

---

## Summary
- **Assignments are stored in:** `booking_assignments` table
- **Assignment status is tracked in:** `bookings.assignment_status` field
- **Each booking can have multiple assignments** (one per day/shift)
- **Assignment status automatically updates** when assignments are created/updated/deleted
- **Query assignments using:** `Booking::with('assignments.caregiver')` or `BookingAssignment::with('booking', 'caregiver')`

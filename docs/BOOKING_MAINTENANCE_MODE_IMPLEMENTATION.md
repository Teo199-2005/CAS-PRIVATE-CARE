# Booking Maintenance Mode Implementation

## Overview

This feature allows administrators to enable/disable the booking system for maintenance purposes. When enabled:

1. **New bookings are blocked** - Clients attempting to book will see a maintenance modal
2. **Existing bookings are NOT affected** - All current pending, approved, and active bookings continue normally
3. **Visual indicators** - Red pulsing dots appear on booking buttons to indicate maintenance mode is active

## Components Modified

### Backend

#### `app/Http/Controllers/AdminController.php`
Added two new methods:
- `getBookingMaintenanceStatus()` - Returns current maintenance status (public API)
- `toggleBookingMaintenance()` - Toggles maintenance mode on/off (admin only)

#### `routes/api.php`
Added two new API endpoints:
- `GET /api/booking-maintenance-status` - Public endpoint to check maintenance status
- `POST /api/admin/booking-maintenance/toggle` - Admin endpoint to toggle maintenance

### Frontend

#### `resources/js/components/AdminDashboard.vue`
Added:
- **Booking System Status Widget** - Located in the dashboard section after Quick Actions
- Toggle button to enable/disable booking maintenance
- Customizable maintenance message field
- Visual status indicator (Active/Maintenance Mode)

#### `resources/js/components/ClientDashboard.vue`
Added:
- `bookingMaintenanceEnabled` and `bookingMaintenanceMessage` state variables
- `loadBookingMaintenanceStatus()` function - Loads on mount
- `showMaintenanceModal` - Modal displayed when booking is disabled
- Modified `attemptBooking()` to check maintenance status first
- Red pulsing indicator dot on Book Now button when maintenance is active
- Professional maintenance modal with branded styling

#### `resources/views/landing.blade.php`
Added:
- Maintenance modal overlay
- JavaScript to check maintenance status on page load
- `handleBookingClick()` function to intercept booking button clicks
- Red indicator dots on all "Book Now" buttons in services section
- CSS styles for modal and indicator animations

#### `resources/views/services.blade.php`
Added:
- Same maintenance modal and JavaScript as landing.blade.php
- Red indicator dots on all service card booking buttons
- All booking buttons wrapped with maintenance click handler

## Database

Uses existing `app_settings` table with two new keys:
- `booking.maintenance_enabled` - 'true' or 'false' string
- `booking.maintenance_message` - Custom message to show clients

## Usage

### Enabling Maintenance Mode

1. Login as Admin
2. Go to Dashboard
3. Find "Booking System Status" widget (below Quick Actions)
4. Optionally customize the maintenance message
5. Click "Disable Booking" button

### Disabling Maintenance Mode

1. Login as Admin
2. Go to Dashboard
3. Find "Booking System Status" widget
4. Click "Enable Booking" button

## Visual Indicators

### Admin Dashboard
- Green chip = "Active" (booking enabled)
- Orange/Yellow chip = "Maintenance Mode" (booking disabled)
- Toggle button changes color based on current state

### Client Dashboard & Public Pages
- Red pulsing dot on booking buttons when maintenance is active
- Professional modal with:
  - Orange/yellow warning header
  - Custom maintenance message
  - Info about existing bookings not being affected
  - Close button

## API Responses

### GET /api/booking-maintenance-status
```json
{
  "success": true,
  "maintenance_enabled": false,
  "maintenance_message": "Our booking system is currently under maintenance. Please try again later."
}
```

### POST /api/admin/booking-maintenance/toggle
Request:
```json
{
  "enabled": true,
  "message": "Custom maintenance message"
}
```

Response:
```json
{
  "success": true,
  "maintenance_enabled": true,
  "maintenance_message": "Custom maintenance message",
  "message": "Booking maintenance mode enabled successfully"
}
```

## Notes

- The maintenance status is checked on page load
- In client dashboard, it's also loaded as part of the initial data fetch
- Existing bookings (pending, approved, active, completed) are never affected
- Only new booking attempts are blocked
- The feature is admin-only for toggling, but status check is public

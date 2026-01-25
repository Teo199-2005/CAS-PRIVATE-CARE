# Admin Staff Page Permissions System

## Overview

This implementation allows the Master Admin to assign specific page access permissions to Admin Staff users. Admin Staff members will only be able to access pages that have been enabled for them, with disabled pages showing a lock icon and access restriction notification.

## Features

1. **Page Permission Assignment**: Master Admin can check/uncheck specific pages for each Admin Staff user
2. **Visual Indicators**: Disabled pages show with a lock icon in the sidebar
3. **Access Notifications**: When Admin Staff clicks on a restricted page, they see a friendly warning message
4. **17 Configurable Pages**: All sidebar pages are configurable

## Available Pages for Permission Control

### General
- Dashboard
- Notifications
- Profile

### User Management
- Users
- Caregivers
- Housekeepers
- Clients
- Admin Staff
- Marketing Partner
- Training Centers

### Applications
- Contractors Application
- Password Resets

### Bookings
- Client Bookings
- Time Tracking

### Other
- Reviews & Ratings
- Announcements
- Payments
- Analytics

## Implementation Details

### Database Migration
A new migration adds the `page_permissions` JSON column to the `users` table:
```
database/migrations/2026_01_13_000001_add_page_permissions_to_users_table.php
```

**Run the migration:**
```bash
php artisan migrate
```

### Backend Changes

#### User Model (`app/Models/User.php`)
- Added `page_permissions` to the `$fillable` array

#### AdminController (`app/Http/Controllers/AdminController.php`)
New/updated methods:
- `getAdminStaff()` - Returns page_permissions with each admin staff record
- `storeAdminStaff()` - Accepts page_permissions when creating admin staff
- `updateAdminStaff()` - Accepts page_permissions when updating admin staff
- `getAdminStaffPermissions()` - Returns the logged-in admin staff's permissions
- `getDefaultAdminStaffPermissions()` - Helper that returns default permissions (all enabled)

#### Routes (`routes/web.php`)
Added new route:
```php
Route::get('/admin/admin-staff/permissions', [AdminController::class, 'getAdminStaffPermissions']);
```

### Frontend Changes

#### Master Admin Dashboard (`AdminDashboard.vue`)
- Updated Add/Edit Admin Staff dialog to include page permission checkboxes
- Added "Check All" and "Uncheck All" buttons
- Permissions grouped by category for easy selection
- Added helper functions: `getDefaultPagePermissions()`, `selectAllPermissions()`, `deselectAllPermissions()`

#### Admin Staff Dashboard (`AdminStaffDashboard.vue`)
- Added `pagePermissions` ref to store loaded permissions
- Added `loadPagePermissions()` function to fetch permissions from API
- Added `updateNavItemsWithPermissions()` to apply disabled state to nav items
- Added `handleSectionChangeWithPermission()` to check permissions before navigation
- Added `showAccessDeniedNotification()` to show friendly warning for restricted pages
- Added Payments and Analytics pages to navItems

#### Dashboard Template (`DashboardTemplate.vue`)
- Updated nav items to handle `disabled` property
- Added lock icon for disabled items
- Added `handleNavItemClick()` function to emit `disabled-click` event for restricted pages
- Added CSS styles for `.nav-item-disabled` class
- Added new emit: `disabled-click`

## How It Works

1. **Master Admin assigns permissions**: When creating/editing an Admin Staff user, the Master Admin checks/unchecks pages
2. **Permissions are saved**: The permissions are stored as JSON in the `page_permissions` column
3. **Admin Staff logs in**: Their dashboard loads permissions from the API
4. **Nav items are updated**: The sidebar shows disabled/enabled states based on permissions
5. **Access control**: Clicking a disabled page shows a warning notification instead of navigating

## Default Permissions

By default, all new Admin Staff users have ALL pages enabled. The Master Admin can then customize access as needed.

## UI/UX Details

### Disabled Page Indicators
- Lock icon (mdi-lock) appears on disabled pages
- Text appears slightly grayed out and italicized
- Cursor remains clickable (to show the notification)

### Access Denied Notification
When clicking a restricted page, Admin Staff sees:
- Title: "Access Restricted"
- Message: "You do not have permission to access the "[Page Name]" page. Please contact your administrator if you need access."
- Type: Warning (yellow/orange)

## Testing

1. Create a new Admin Staff user with all permissions
2. Edit the Admin Staff and uncheck some pages (e.g., Payments, Analytics)
3. Login as that Admin Staff user
4. Verify that unchecked pages show with lock icons
5. Click on a disabled page and verify the warning notification appears
6. Verify that enabled pages still work normally

## Files Modified

1. `database/migrations/2026_01_13_000001_add_page_permissions_to_users_table.php` (NEW)
2. `app/Models/User.php` - Added page_permissions to fillable
3. `app/Http/Controllers/AdminController.php` - Updated CRUD methods
4. `routes/web.php` - Added new permissions route
5. `resources/js/components/AdminDashboard.vue` - Added permission checkboxes
6. `resources/js/components/AdminStaffDashboard.vue` - Added permission checking
7. `resources/js/components/DashboardTemplate.vue` - Added disabled item handling

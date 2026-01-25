# Admin Role Display Fix

## Issue
The Admin Staff Dashboard was displaying hardcoded role information ("Admin Staff", "Limited Admin Access") instead of showing the actual role from the database.

## Database Structure
The `users` table has the following admin-related fields:
- `user_type`: 'admin' (identifies user as admin)
- `role`: Specific admin role (e.g., "Super Admin", "Admin Staff")
- `department`: Department name (e.g., "System Administration")

## Current Admin Roles in Database
1. **Super Admin** - Master Admin (admin@demo.com)
   - Full platform access
   - System Administration department

2. **Admin Staff** - Admin Staff (staff@demo.com)
   - Limited admin access
   - System Administration department

## Changes Made

### 1. Updated `routes/api.php`
Added `role` and `department` fields to the profile API response:
```php
'role' => $user->role,
'department' => $user->department,
```

### 2. Updated `AdminStaffDashboard.vue`

#### Profile Data Structure
Added role and department to the profile refs:
```javascript
const profile = ref({
  firstName: 'Admin',
  lastName: 'Staff',
  email: 'staff@demo.com',
  role: 'Admin Staff',
  department: 'System Administration'
});
```

#### Load Profile Function
Updated to fetch and store role and department:
```javascript
profile.value = {
  firstName: data.user.name?.split(' ')[0] || 'Admin',
  lastName: data.user.name?.split(' ')[1] || 'Staff',
  email: data.user.email,
  role: data.user.role || 'Admin Staff',
  department: data.user.department || 'System Administration'
};
```

#### Profile Display Section
- Role display now shows actual role from database: `{{ profile.role || 'Admin Staff' }}`
- Access level dynamically shows based on role:
  ```vue
  <span>{{ profile.role === 'Super Admin' ? 'Super Admin Access' : 'Limited Admin Access' }}</span>
  ```

#### Personal Information Form
- Added read-only "Admin Role" field showing the user's role
- Made "Department" field read-only
- Both fields now display actual database values

## Result
The admin dashboard now correctly displays:
- **Super Admin** users see "Super Admin" role and "Super Admin Access"
- **Admin Staff** users see "Admin Staff" role and "Limited Admin Access"
- Both roles display their actual department from the database
- Role information is fetched dynamically from the API

## Testing
To verify the changes:
1. Visit the admin dashboard as different admin users
2. Check the Profile section - role should display correctly
3. Verify the sidebar avatar shows correct role information
4. Confirm that Super Admin and Admin Staff roles display different access levels

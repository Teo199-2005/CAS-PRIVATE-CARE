# Admin Staff Reviews Access - Fixed

## Issue
Admin Staff users were receiving "Unauthorized - Admin access required" error when trying to view the Reviews & Ratings section.

## Root Cause
The `ReviewController::index()` method was checking:
```php
if (!$user || $user->role !== 'admin') {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized - Admin access required'
    ], 403);
}
```

This was incorrect because:
1. The `role` field contains "Super Admin" or "Admin Staff", not "admin"
2. The `user_type` field is what contains "admin" for both admin roles

## Solution
Changed the authentication check to use `user_type` instead of `role`:

### Before:
```php
if (!$user || $user->role !== 'admin') {
```

### After:
```php
if (!$user || $user->user_type !== 'admin') {
```

## Database Structure Reminder

```
users table:
├── user_type: 'admin' (for both Super Admin and Admin Staff)
└── role: 'Super Admin' or 'Admin Staff' (specific role within admin type)
```

### Example Users:
```
Master Admin:
- user_type: 'admin'
- role: 'Super Admin'

Admin Staff:
- user_type: 'admin'
- role: 'Admin Staff'
```

## File Modified
- `app/Http/Controllers/ReviewController.php` (line 345)

## Testing
Now both admin roles can access the Reviews & Ratings section:
- ✅ Super Admin (admin@demo.com)
- ✅ Admin Staff (staff@casprivatecare.com)
- ✅ Admin Staff (staff@demo.com)

## Result
Admin Staff users can now successfully view and manage the Reviews & Ratings section without authentication errors.

## Related Files to Check
If similar authentication issues occur, check these patterns in other controllers:
- ❌ `$user->role === 'admin'` (WRONG)
- ✅ `$user->user_type === 'admin'` (CORRECT)
- ✅ `$user->role === 'Super Admin'` or `$user->role === 'Admin Staff'` (CORRECT for role-specific checks)

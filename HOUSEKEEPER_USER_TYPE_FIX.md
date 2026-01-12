# Housekeeper User Type Fix

## Issue
Housekeeper accounts were being created with `user_type='caregiver'` instead of `user_type='housekeeper'`, causing them to be redirected to `/caregiver/dashboard-vue` instead of `/housekeeper/dashboard-vue`.

## Root Cause
In `AuthController.php` register method, the partner type mapping was incorrectly mapping:
- `'housekeeper' => 'caregiver'` ❌
- `'housekeeping' => 'caregiver'` ❌

This caused all housekeeper registrations to be saved as caregiver user type.

## Files Modified

### `app/Http/Controllers/AuthController.php`

**1. Line 35 - Added 'housekeeper' to partner types check (login method)**
```php
$partnerTypes = ['caregiver', 'housekeeper', 'marketing', 'training_center'];
```

**2. Line 80 - Added 'housekeeper' to allowed user_type validation**
```php
'user_type' => 'required|in:client,caregiver,housekeeper,marketing,training_center',
```

**3. Lines 94-96 - Fixed partner type to user type mapping**
```php
'housekeeper' => 'housekeeper',  // Was: 'caregiver'
'housekeeping' => 'housekeeper',  // Was: 'caregiver'
```

**4. Line 115 - Added 'housekeeper' to partner types for status setting**
```php
$partnerTypes = ['caregiver', 'housekeeper', 'marketing', 'training_center'];
```

## How to Fix Existing Accounts

### Option 1: Run the Fix Script
```bash
php fix-housekeeper-account.php
```

This will:
1. Find users with the affected email (harrypogi007@gmail.com)
2. Update their user_type from 'caregiver' to 'housekeeper'
3. Create a housekeeper record if needed
4. Show confirmation message

### Option 2: Manual Database Update
```sql
-- Update the user type
UPDATE users 
SET user_type = 'housekeeper' 
WHERE email = 'harrypogi007@gmail.com';

-- Check if housekeeper record exists
SELECT * FROM housekeepers WHERE user_id = (SELECT id FROM users WHERE email = 'harrypogi007@gmail.com');

-- If not, create one (replace USER_ID with actual id)
INSERT INTO housekeepers (user_id, gender, availability_status, years_experience, created_at, updated_at)
VALUES (USER_ID, 'female', 'available', 0, NOW(), NOW());
```

### Option 3: Delete and Re-register
1. Delete the incorrectly created account
2. Register again with the same email
3. New account will be created with correct user_type

## Testing After Fix

1. **Logout** from the current session
2. **Login** again with: harrypogi007@gmail.com
3. Should redirect to: `/housekeeper/dashboard-vue` ✅
4. Should see "Housekeeper Portal" (not "Caregiver Portal") ✅

## New Registration Flow (Now Fixed)

1. User clicks "Become a Housekeeper" on landing page
2. Redirects to `/register?partner=housekeeper`
3. Fills out registration form
4. Submits with `partner_type='housekeeper'`
5. Mapped to `user_type='housekeeper'` ✅
6. User record created with `user_type='housekeeper'`
7. Housekeeper record created
8. Login redirects to `/housekeeper/dashboard-vue` ✅

## All Partner Type Mappings (Corrected)

| Partner Type        | User Type      | Dashboard Route           |
|---------------------|----------------|---------------------------|
| caregiver           | caregiver      | /caregiver/dashboard-vue  |
| housekeeper         | housekeeper    | /housekeeper/dashboard-vue |
| housekeeping        | housekeeper    | /housekeeper/dashboard-vue |
| personal_assistant  | caregiver      | /caregiver/dashboard-vue  |
| marketing_partner   | marketing      | /marketing/dashboard-vue  |
| training_center     | training_center| /training/dashboard-vue   |

## Date Fixed
January 12, 2026

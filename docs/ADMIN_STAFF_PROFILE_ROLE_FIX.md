# Admin Staff Profile - Role Display Fix

## Issue
The Admin Staff Dashboard was showing "Super Admin" in the profile instead of "Admin Staff", even though the database had the correct role.

## Root Causes

### 1. Hardcoded Initial Values
The `profileData` ref was initialized with hardcoded "Super Admin":
```javascript
const profileData = ref({
  role: 'Super Admin',  // ❌ Wrong
});
```

### 2. Missing Role Loading
The `loadProfile()` function was not fetching the `role` and `department` fields from the API response.

## Solutions Applied

### 1. Updated Initial Profile Data
**Before:**
```javascript
const profileData = ref({
  firstName: 'Admin',
  lastName: 'User',
  email: 'admin@casprivatecare.com',
  phone: '(646) 282-8282',
  department: 'System Administration',
  role: 'Super Admin',  // ❌ Hardcoded
});

const profile = ref({
  firstName: '',
  lastName: ''  // ❌ Missing role and department
});
```

**After:**
```javascript
const profileData = ref({
  firstName: 'Admin',
  lastName: 'Staff',
  email: 'staff@casprivatecare.com',
  phone: '(646) 555-0102',
  department: 'System Administration',
  role: 'Admin Staff',  // ✅ Correct default
});

const profile = ref({
  firstName: '',
  lastName: '',
  role: 'Admin Staff',  // ✅ Added
  department: 'System Administration'  // ✅ Added
});
```

### 2. Updated loadProfile() Function
**Before:**
```javascript
const loadProfile = async () => {
  try {
    const response = await fetch('/api/profile?user_type=admin');
    if (response.ok) {
      const result = await response.json();
      const data = result.user || result;
      profile.value.firstName = data.first_name || data.name?.split(' ')[0] || 'Admin';
      profile.value.lastName = data.last_name || data.name?.split(' ').slice(1).join(' ') || 'User';
      // ❌ Missing role and department
      
      profileData.value.firstName = profile.value.firstName;
      profileData.value.lastName = profile.value.lastName;
      profileData.value.email = data.email || 'admin@casprivatecare.com';
      profileData.value.phone = data.phone || '(646) 282-8282';
      // ❌ Missing role and department assignment
    }
  } catch (error) {
    console.error('Failed to load profile:', error);
  }
};
```

**After:**
```javascript
const loadProfile = async () => {
  try {
    const response = await fetch('/api/profile?user_type=admin');
    if (response.ok) {
      const result = await response.json();
      const data = result.user || result;
      profile.value.firstName = data.first_name || data.name?.split(' ')[0] || 'Admin';
      profile.value.lastName = data.last_name || data.name?.split(' ').slice(1).join(' ') || 'Staff';
      profile.value.role = data.role || 'Admin Staff';  // ✅ Added
      profile.value.department = data.department || 'System Administration';  // ✅ Added
      
      profileData.value.firstName = profile.value.firstName;
      profileData.value.lastName = profile.value.lastName;
      profileData.value.email = data.email || 'staff@casprivatecare.com';
      profileData.value.phone = data.phone || '(646) 555-0102';
      profileData.value.role = profile.value.role;  // ✅ Added
      profileData.value.department = profile.value.department;  // ✅ Added
      
      console.log('Admin profile loaded, user ID:', adminUserId.value, 'Role:', profile.value.role);
    }
  } catch (error) {
    console.error('Failed to load profile:', error);
  }
};
```

## Database Verification

Current admin roles in database:
```
ID: 1  - Master Admin (admin@demo.com) - Role: Super Admin ✅
ID: 13 - Admin Staff (staff@demo.com) - Role: Admin Staff ✅
ID: 15 - Admin Staff (staff@casprivatecare.com) - Role: Admin Staff ✅
```

## Result

Now when Admin Staff logs in:
- ✅ **Admin Role field** shows "Admin Staff" (not "Super Admin")
- ✅ **Department field** shows "System Administration"
- ✅ **Access badge** shows "Limited Admin Access"
- ✅ **Profile loads** actual role from database
- ✅ **Fields are read-only** (cannot change own role)

## File Modified
- `resources/js/components/AdminStaffDashboard.vue`
  - Lines 4205-4219: Updated initial profile refs
  - Lines 4254-4280: Updated loadProfile() function

## Testing
1. Login as Admin Staff: `staff@casprivatecare.com`
2. Navigate to Profile section
3. Verify "Admin Role" field shows "Admin Staff"
4. Verify access badge shows "Limited Admin Access"
5. Verify fields are read-only

✅ Admin Staff profile now correctly displays their role!

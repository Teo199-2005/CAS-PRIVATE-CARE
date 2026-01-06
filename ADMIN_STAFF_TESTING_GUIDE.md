# Admin Staff Management - Testing Guide

## ✅ Implementation Complete!

The Admin Staff management feature has been successfully implemented and built. Here's how to test it:

## 1. Access the Admin Dashboard

Navigate to: `http://your-domain.com/admin/dashboard`

Login with Super Admin credentials:
- **Email:** `admin@demo.com`
- **Password:** `Password123!` (or your admin password)

## 2. Navigate to Admin Staff Section

1. In the sidebar, click on **"Users"**
2. The dropdown will expand showing:
   - Caregivers
   - Clients
   - **Admin Staff** ← NEW!
   - Marketing Partner
   - Training Centers
3. Click on **"Admin Staff"**

## 3. Test Features

### A. View Existing Admin Staff
- You should see the table with existing admin staff members
- The default admin staff (`staff@demo.com`) should be listed if it exists
- Columns displayed: Name, Email, Phone, Email Verified, Last Login, Joined, Status, Actions

### B. Search and Filter
- Use the search box to search by name or email
- Use the status dropdown to filter by Active/Inactive/All

### C. View Details
- Click the **eye icon** (👁️) on any admin staff row
- A modal will open showing:
  - Full details of the admin staff member
  - Email verification status
  - Last login information
  - **Access Permissions** section showing what they can and cannot access

### D. Create New Admin Staff
1. Click the **"Add Admin Staff"** button
2. Fill in the form:
   - **Name:** Test Admin Staff
   - **Email:** teststaff@demo.com
   - **Phone:** (optional) +1234567890
   - **Password:** TestPassword123!
   - **Status:** Active
3. Click **"Create"**
4. You should see a success message
5. The new admin staff should appear in the table

### E. Edit Admin Staff
1. Click the **pencil icon** (✏️) on any admin staff row
2. Modify the information (e.g., change phone number)
3. Leave password blank to keep existing password
4. Click **"Update"**
5. Changes should be reflected in the table

### F. Delete Admin Staff
1. Click the **pencil icon** then the modal's delete option, OR
2. Select multiple staff using checkboxes
3. Click **"Delete Selected (X)"** button
4. Confirm the deletion in the dialog
5. Staff member(s) should be removed from the table

## 4. Verify Backend

### Check Database
Run this SQL query to see admin staff users:
```sql
SELECT id, name, email, user_type, role, status, email_verified_at, created_at 
FROM users 
WHERE user_type = 'admin' AND role = 'Admin Staff';
```

### Test API Endpoints
You can test the API endpoints directly:

```bash
# Get all admin staff
curl -X GET http://your-domain.com/api/admin/admin-staff

# Create admin staff
curl -X POST http://your-domain.com/api/admin/admin-staff \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Staff",
    "email": "newstaff@demo.com",
    "password": "Password123!",
    "status": "Active"
  }'

# Update admin staff (replace {id} with actual ID)
curl -X PUT http://your-domain.com/api/admin/admin-staff/{id} \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "status": "Inactive"
  }'

# Delete admin staff (replace {id} with actual ID)
curl -X DELETE http://your-domain.com/api/admin/admin-staff/{id}
```

## 5. Expected Behavior

### ✅ What Should Work:
- Creating new admin staff with all validations
- Editing existing admin staff
- Viewing detailed information
- Deleting single or multiple admin staff
- Search by name or email
- Filter by status
- Email verification status display
- Password field security (hidden, optional on edit)
- Success/error notifications

### ⚠️ Validations:
- Name and Email are required
- Email must be unique
- Password must be at least 8 characters (for new staff)
- Status must be Active or Inactive
- Cannot delete users that are not Admin Staff role

## 6. Admin Staff Permissions

When you log in as an Admin Staff user (`staff@demo.com` / `Password123!`), you should:

**✅ Have Access To:**
- View Users (Read-Only)
- Contractors Application
- Password Resets
- Client Bookings
- Time Tracking
- Reviews & Ratings
- Announcements
- Profile

**❌ No Access To:**
- Dashboard (redirected to limited view)
- Full User Management (Create/Edit/Delete users)
- Admin Staff Management
- Payments
- Full Analytics

## 7. Troubleshooting

### Issue: "No data available" in table
**Solution:** 
- Check if admin staff exists in database
- Open browser console (F12) and check for API errors
- Verify the API endpoint `/api/admin/admin-staff` is accessible

### Issue: Cannot create admin staff
**Solution:**
- Check validation messages
- Ensure all required fields are filled
- Verify password meets minimum length (8 chars)
- Check browser console for errors

### Issue: Changes not reflecting
**Solution:**
- Clear browser cache (Ctrl + F5)
- Rebuild assets: `npm run build`
- Restart PHP server if using local development

## 8. Security Notes

🔒 **Important Security Features:**
- Passwords are hashed using Laravel's Hash facade
- Email verification is auto-enabled for admin-created staff
- Only Super Admins can manage Admin Staff
- Admin Staff cannot access this management section
- Deletion requires confirmation
- SQL injection protection via Eloquent ORM
- CSRF token validation on all requests

## 9. Default Credentials

Test with the existing admin staff:
- **Email:** `staff@demo.com`
- **Password:** `Password123!`

This user was created via the `create-admin-staff.php` script.

## 10. Success Indicators

You'll know it's working when:
- ✅ "Admin Staff" appears in the Users menu
- ✅ Table loads with existing admin staff
- ✅ You can create new admin staff successfully
- ✅ Search and filter work correctly
- ✅ Edit and delete operations complete successfully
- ✅ Success toasts appear for all operations
- ✅ No errors in browser console
- ✅ No errors in Laravel logs

---

**Last Updated:** January 3, 2026  
**Status:** ✅ Ready for Testing  
**Build:** Successfully compiled with Vite

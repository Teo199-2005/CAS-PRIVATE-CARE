# Admin Staff Dashboard Update Plan

## Issue
Current AdminStaffDashboard has:
- Empty tables (not loading data)
- No dropdown for user types
- No view buttons
- Dates showing "DEC 2025" only
- Features not working

## Solution
Create a LIMITED but FUNCTIONAL version that:

### ✅ CAN DO (Read & Limited Actions):
1. **Users Section** - Dropdown with categories:
   - Caregivers (view only with VIEW button)
   - Clients (view only with VIEW button)  
   - Marketing Partners (view only with VIEW button)
   - Training Centers (view only with VIEW button)
   - NO CREATE/EDIT/DELETE buttons

2. **Applications** - Full functionality:
   - View pending applications
   - APPROVE/REJECT buttons
   - Proper date formatting

3. **Password Resets** - View only:
   - See reset requests
   - NO manual reset ability

4. **Client Bookings** - View with actions:
   - See all bookings
   - VIEW button for details
   - Proper dates

5. **Time Tracking** - View only:
   - See caregiver hours
   - VIEW button for details

6. **Reviews & Ratings** - View only:
   - See all reviews
   - Star ratings display
   - VIEW button for full review

7. **Announcements** - Full functionality:
   - CREATE new announcements
   - EDIT existing
   - DELETE announcements

8. **Profile** - Own profile only:
   - Update own info
   - Change password
   - Upload avatar

### ❌ CANNOT DO:
- Create/Edit/Delete users
- Access payments
- Access analytics/reports dashboard
- Change system settings
- View financial data

## Implementation

Will create proper data loading from:
- `/api/admin/users`
- `/api/admin/applications`  
- `/api/admin/bookings`
- `/api/admin/time-tracking`
- `/api/reviews`
- `/api/announcements`

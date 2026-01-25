# 🧹 HOUSEKEEPER DASHBOARD - TESTING & VALIDATION GUIDE

**Created:** January 11, 2026  
**Status:** Ready for Testing  
**Implementation:** Complete (Phases 1-9)

---

## 📋 EXECUTIVE SUMMARY

This guide provides comprehensive test cases and validation steps for the Housekeeper Dashboard implementation. All 9 implementation phases are complete, and the system is ready for end-to-end testing.

---

## ✅ PRE-TESTING CHECKLIST

### **1. Environment Setup**
- [ ] Clear browser cache and cookies
- [ ] Verify database migrations are up to date (`php artisan migrate:status`)
- [ ] Rebuild assets after clearing disk space (`npm run build`)
- [ ] Ensure `.env` file has correct database and Stripe credentials
- [ ] Verify server is running (`php artisan serve`)

### **2. Database Verification**
```sql
-- Verify housekeepers table exists
SHOW TABLES LIKE 'housekeepers';

-- Check table structure
DESCRIBE housekeepers;

-- Verify user_type supports 'housekeeper'
SELECT DISTINCT user_type FROM users;

-- Check if booking_assignments has housekeeper support
DESCRIBE booking_assignments;

-- Verify time_trackings has housekeeper support
DESCRIBE time_trackings;
```

### **3. API Endpoint Check**
Test all housekeeper API endpoints are accessible:
```bash
# Check API routes exist
php artisan route:list | grep housekeeper
```

Expected routes:
- GET `/housekeeper/dashboard-vue`
- GET `/connect-bank-account-housekeeper`
- GET `/api/housekeeper/{id}/stats`
- GET `/api/housekeeper/available-clients`
- POST `/api/housekeeper/apply-client/{id}`
- GET `/api/housekeeper/{id}/earnings`
- GET `/api/admin/housekeepers`
- GET `/api/housekeepers`

---

## 🧪 TEST CASES

### **PHASE 1: HOUSEKEEPER REGISTRATION**

#### **Test Case 1.1: Register as Housekeeper**
**Objective:** Verify new users can register as housekeepers

**Steps:**
1. Navigate to `/register`
2. Click "I want to be a partner"
3. Verify modal shows 4 options: Caregiver, **Housekeeper**, Marketing Partner, Training Center
4. Click "Housekeeper" option
5. Fill out registration form:
   - First Name: Test
   - Last Name: Housekeeper
   - Email: test.housekeeper@example.com
   - Password: TestPass123!
   - Phone: (555) 123-4567
   - Address: 123 Test St
   - ZIP: 10001
6. Accept terms and submit

**Expected Results:**
- ✅ Housekeeper option is visible in partner modal
- ✅ Registration form accepts input
- ✅ User is created with `user_type = 'housekeeper'`
- ✅ Housekeeper record is created in `housekeepers` table
- ✅ Status is set to 'pending' (awaiting admin approval)
- ✅ Success message displayed

**Database Verification:**
```sql
SELECT u.id, u.name, u.email, u.user_type, u.status, h.id as housekeeper_id 
FROM users u 
LEFT JOIN housekeepers h ON u.id = h.user_id 
WHERE u.email = 'test.housekeeper@example.com';
```

---

#### **Test Case 1.2: Housekeeper Cannot Access Dashboard (Pending Status)**
**Objective:** Verify pending housekeepers cannot access dashboard

**Steps:**
1. Login with pending housekeeper account
2. Attempt to navigate to `/housekeeper/dashboard-vue`

**Expected Results:**
- ✅ Redirected to pending/waiting page
- ✅ Dashboard not accessible
- ✅ Message: "Your application is being reviewed"

---

### **PHASE 2: ADMIN APPROVAL**

#### **Test Case 2.1: Admin Views Housekeeper Applications**
**Objective:** Verify admin can see pending housekeeper applications

**Steps:**
1. Login as admin
2. Navigate to admin dashboard
3. Go to "Contractors Application" section
4. Verify housekeeper application appears

**Expected Results:**
- ✅ Pending housekeeper listed in applications
- ✅ All details visible (name, email, type: housekeeper)
- ✅ Approve/Reject buttons available

---

#### **Test Case 2.2: Admin Approves Housekeeper**
**Objective:** Verify admin can approve housekeeper application

**Steps:**
1. As admin, find pending housekeeper application
2. Click "Approve" button
3. Verify confirmation

**Expected Results:**
- ✅ Status changed from 'pending' to 'active'
- ✅ Success notification shown
- ✅ Housekeeper can now login and access dashboard

**Database Verification:**
```sql
SELECT status FROM users WHERE email = 'test.housekeeper@example.com';
-- Should return: active
```

---

### **PHASE 3: HOUSEKEEPER DASHBOARD ACCESS**

#### **Test Case 3.1: Login as Approved Housekeeper**
**Objective:** Verify approved housekeepers can login and access dashboard

**Steps:**
1. Login with approved housekeeper credentials
2. Submit login form

**Expected Results:**
- ✅ Authentication successful
- ✅ Automatically redirected to `/housekeeper/dashboard-vue`
- ✅ Dashboard loads without errors
- ✅ Welcome message shows housekeeper name

**Console Check:**
- No JavaScript errors
- No 404 errors
- Vue components mount successfully

---

#### **Test Case 3.2: Dashboard UI Components**
**Objective:** Verify all dashboard UI elements render correctly

**Steps:**
1. View housekeeper dashboard
2. Inspect all sections

**Expected Results:**
- ✅ Header shows "Housekeeper Portal"
- ✅ Navigation menu displays all sections
- ✅ Account Balance card visible
- ✅ Statistics cards show: Active Assignments, Earnings, Rating, Transactions
- ✅ Time Tracking card present
- ✅ Available Clients section visible
- ✅ Profile tab accessible

---

#### **Test Case 3.3: Dashboard Statistics**
**Objective:** Verify statistics API returns correct data

**Steps:**
1. As logged-in housekeeper, open browser DevTools
2. Go to Network tab
3. Refresh dashboard
4. Find API call to `/api/housekeeper/{id}/stats`

**Expected Results:**
- ✅ API returns 200 OK
- ✅ Response contains:
  - `activeAssignments` (number)
  - `totalEarnings` (float)
  - `rating` (float 0-5)
  - `totalTransactions` (number)
- ✅ Stats display on dashboard cards

**API Response Example:**
```json
{
  "activeAssignments": 3,
  "totalEarnings": 1250.00,
  "rating": 4.8,
  "totalTransactions": 15
}
```

---

### **PHASE 4: CLIENT BOOKING SYSTEM**

#### **Test Case 4.1: Client Books Housekeeping Service**
**Objective:** Verify clients can book housekeeping services

**Steps:**
1. Login as client
2. Go to "Book Service" or "New Booking"
3. Check service type dropdown
4. Select "Housekeeping"
5. Fill booking details:
   - Hours per Day: 8 Hours
   - Service Date: Tomorrow
   - Duration: 15 days
   - Location: Manhattan
6. Submit booking

**Expected Results:**
- ✅ "Housekeeping" appears in service type dropdown
- ✅ Form accepts housekeeping selection
- ✅ Booking created with `service_type = 'Housekeeping'`
- ✅ Booking status: 'pending' or 'approved'
- ✅ Confirmation message shown

**Database Verification:**
```sql
SELECT id, client_id, service_type, status, service_date 
FROM bookings 
WHERE service_type = 'Housekeeping' 
ORDER BY id DESC LIMIT 1;
```

---

#### **Test Case 4.2: Housekeeper Sees Available Bookings**
**Objective:** Verify housekeepers can see housekeeping bookings

**Steps:**
1. Login as housekeeper
2. Go to "Available Clients" section
3. Verify housekeeping bookings appear

**Expected Results:**
- ✅ Only housekeeping bookings shown (service_type = 'Housekeeping')
- ✅ Bookings with status 'pending' or 'approved'
- ✅ Details visible: client name, location, hours, rate
- ✅ "Apply" button available for each booking

---

#### **Test Case 4.3: Housekeeper Applies for Booking**
**Objective:** Verify housekeepers can apply for assignments

**Steps:**
1. As housekeeper, view available clients
2. Click "Apply" on a housekeeping booking
3. Confirm application

**Expected Results:**
- ✅ Application submitted successfully
- ✅ Booking assignment created with:
  - `booking_id`
  - `housekeeper_id`
  - `provider_type = 'housekeeper'`
  - `status = 'pending'`
- ✅ Success notification shown
- ✅ Booking removed from "Available Clients" list

**Database Verification:**
```sql
SELECT ba.id, ba.booking_id, ba.housekeeper_id, ba.provider_type, ba.status
FROM booking_assignments ba
WHERE ba.housekeeper_id IS NOT NULL
ORDER BY ba.id DESC LIMIT 1;
```

---

### **PHASE 5: ADMIN DASHBOARD - HOUSEKEEPERS TAB**

#### **Test Case 5.1: Admin Views Housekeepers List**
**Objective:** Verify admin can view all housekeepers

**Steps:**
1. Login as admin
2. Navigate to Users > Housekeepers
3. View housekeepers table

**Expected Results:**
- ✅ "Housekeepers" option visible in Users menu
- ✅ Table displays all housekeepers
- ✅ Columns shown: Name, Email, Phone, ZIP Code, Location, Status, Experience, Rating
- ✅ Search functionality works
- ✅ Location filter works
- ✅ Status filter works

---

#### **Test Case 5.2: Admin Views Housekeeper Details**
**Objective:** Verify admin can see individual housekeeper details

**Steps:**
1. As admin, go to Housekeepers tab
2. Click "View" (eye icon) on any housekeeper
3. Review details modal

**Expected Results:**
- ✅ Modal opens with housekeeper information
- ✅ Shows: name, email, phone, location, status, experience, rating
- ✅ Close button works

---

#### **Test Case 5.3: Admin Creates Booking for Housekeeper**
**Objective:** Verify admin can create housekeeping bookings

**Steps:**
1. As admin, go to "Client Bookings" tab
2. Click "Add Booking"
3. Select service type dropdown
4. Verify "Housekeeping" is an option
5. Create booking with Housekeeping service type

**Expected Results:**
- ✅ "Housekeeping" appears in service type dropdown
- ✅ Booking created successfully
- ✅ Available to housekeepers for application

---

### **PHASE 6: TIME TRACKING & EARNINGS**

#### **Test Case 6.1: Housekeeper Clocks In**
**Objective:** Verify time tracking works for housekeepers

**Steps:**
1. Login as housekeeper with active assignment
2. Go to Time Tracking section
3. Click "Clock In"
4. Verify time starts tracking

**Expected Results:**
- ✅ Clock-in successful
- ✅ Time tracking record created with:
  - `housekeeper_id`
  - `provider_type = 'housekeeper'`
  - `clock_in` timestamp
  - `status = 'in_progress'`
- ✅ Timer visible on dashboard

**Database Verification:**
```sql
SELECT * FROM time_trackings 
WHERE housekeeper_id IS NOT NULL 
AND provider_type = 'housekeeper'
ORDER BY id DESC LIMIT 1;
```

---

#### **Test Case 6.2: Housekeeper Clocks Out**
**Objective:** Verify clock-out and earnings calculation

**Steps:**
1. While clocked in, click "Clock Out"
2. Confirm clock-out

**Expected Results:**
- ✅ Clock-out successful
- ✅ `clock_out` timestamp recorded
- ✅ `total_hours` calculated
- ✅ `amount_earned` calculated (hours × hourly_rate)
- ✅ `status = 'completed'`

---

#### **Test Case 6.3: View Earnings Report**
**Objective:** Verify housekeepers can view earnings

**Steps:**
1. Login as housekeeper
2. Go to Earnings/Transactions section
3. View earnings report

**Expected Results:**
- ✅ All time tracking entries shown
- ✅ Each entry shows: date, hours, rate, total earned
- ✅ Total earnings calculated correctly
- ✅ Can filter by date range

---

### **PHASE 7: PAYMENT INTEGRATION**

#### **Test Case 7.1: Connect Bank Account**
**Objective:** Verify housekeepers can connect Stripe bank account

**Steps:**
1. Login as housekeeper
2. Navigate to `/connect-bank-account-housekeeper`
3. Complete Stripe Connect onboarding

**Expected Results:**
- ✅ Page loads successfully
- ✅ Stripe Connect form renders
- ✅ Bank account can be connected
- ✅ Success redirect after completion

---

#### **Test Case 7.2: Receive Payment**
**Objective:** Verify housekeepers receive payments

**Steps:**
1. Complete time tracking entries
2. Admin processes payroll
3. Verify payment received

**Expected Results:**
- ✅ Payment processed via Stripe
- ✅ Funds transferred to housekeeper account
- ✅ Payment record created in database
- ✅ Transaction visible in housekeeper dashboard

---

### **PHASE 8: PROFILE MANAGEMENT**

#### **Test Case 8.1: Update Housekeeper Profile**
**Objective:** Verify housekeepers can update their profiles

**Steps:**
1. Login as housekeeper
2. Go to Profile section
3. Update fields:
   - Bio
   - Skills
   - Years of experience
   - Hourly rate
4. Save changes

**Expected Results:**
- ✅ Form pre-populated with current data
- ✅ All fields editable
- ✅ Changes saved to database
- ✅ Success notification shown

---

### **PHASE 9: SECURITY & AUTHORIZATION**

#### **Test Case 9.1: Role-Based Access Control**
**Objective:** Verify proper access restrictions

**Test Matrix:**

| User Type | Can Access Housekeeper Dashboard | Can Apply for Jobs | Can View Admin Panel |
|-----------|----------------------------------|-------------------|---------------------|
| Client | ❌ No | ❌ No | ❌ No |
| Caregiver | ❌ No | ❌ No | ❌ No |
| Housekeeper | ✅ Yes | ✅ Yes | ❌ No |
| Admin | ✅ Yes (view only) | ❌ No | ✅ Yes |

**Steps:**
1. Test each user type attempting to access `/housekeeper/dashboard-vue`
2. Verify appropriate redirects

**Expected Results:**
- ✅ Only housekeepers and admins can access
- ✅ Clients redirected to client dashboard
- ✅ Caregivers redirected to caregiver dashboard
- ✅ Unauthenticated users redirected to login

---

#### **Test Case 9.2: API Authorization**
**Objective:** Verify API endpoints are protected

**Steps:**
1. Attempt to call `/api/housekeeper/{id}/stats` without authentication
2. Attempt with wrong user type
3. Attempt with correct housekeeper authentication

**Expected Results:**
- ✅ Unauthenticated request: 401 Unauthorized
- ✅ Wrong user type: 403 Forbidden
- ✅ Correct user: 200 OK with data

---

### **PHASE 10: CROSS-BROWSER TESTING**

#### **Test Case 10.1: Browser Compatibility**
**Objective:** Verify system works across browsers

**Browsers to Test:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Chrome (iOS/Android)
- [ ] Mobile Safari (iOS)

**Test Points:**
- ✅ Dashboard loads correctly
- ✅ Vue components render
- ✅ Forms submit successfully
- ✅ No console errors
- ✅ Responsive design works

---

### **PHASE 11: PERFORMANCE TESTING**

#### **Test Case 11.1: Page Load Time**
**Objective:** Verify acceptable performance

**Metrics:**
- Dashboard initial load: < 3 seconds
- API response time: < 500ms
- Component rendering: < 1 second

**Tools:**
- Chrome DevTools Performance tab
- Network tab for API timing

---

#### **Test Case 11.2: Database Query Performance**
**Objective:** Verify queries are optimized

**Steps:**
1. Enable query logging in Laravel
2. Load housekeeper dashboard
3. Review query count and execution time

**Expected Results:**
- ✅ No N+1 query issues
- ✅ Indexes being used
- ✅ Total queries < 50 for dashboard load
- ✅ Total query time < 500ms

---

## 🐛 KNOWN ISSUES & RESOLUTIONS

### **Issue 1: Disk Space for Build**
**Status:** ⚠️ Active  
**Description:** `npm run build` fails with ENOSPC error  
**Resolution:** Clear disk space and rebuild before deployment  
**Impact:** Vue components need compilation for production

### **Issue 2: Null Bytes in DashboardController**
**Status:** ✅ Resolved  
**Description:** Null byte inserted causing syntax errors  
**Resolution:** Used PowerShell to remove null bytes  
**Prevention:** Use `replace_string_in_file` tool instead of echo commands

---

## 📊 TEST COVERAGE SUMMARY

| Component | Test Cases | Status |
|-----------|------------|--------|
| Registration | 2 | ✅ Ready |
| Admin Approval | 2 | ✅ Ready |
| Dashboard Access | 3 | ✅ Ready |
| Client Booking | 3 | ✅ Ready |
| Admin Management | 3 | ✅ Ready |
| Time Tracking | 3 | ✅ Ready |
| Payments | 2 | ✅ Ready |
| Profile Management | 1 | ✅ Ready |
| Security | 2 | ✅ Ready |
| Cross-Browser | 1 | ✅ Ready |
| Performance | 2 | ✅ Ready |
| **TOTAL** | **24** | **✅ Ready** |

---

## 🔍 REGRESSION TESTING

### **Caregiver System (Verify Not Broken)**
- [ ] Caregiver registration still works
- [ ] Caregiver dashboard accessible
- [ ] Caregiver can apply for caregiving jobs (not housekeeping jobs)
- [ ] Time tracking works for caregivers
- [ ] Payments work for caregivers

### **Client System (Verify Not Broken)**
- [ ] Client registration still works
- [ ] Client can book caregiver services
- [ ] Client can book housekeeping services
- [ ] Client dashboard shows all bookings
- [ ] Payment methods work

### **Admin System (Verify Not Broken)**
- [ ] Admin can manage all user types
- [ ] Caregiver management still works
- [ ] Client management still works
- [ ] Booking management still works
- [ ] Payment processing still works

---

## 📝 ACCEPTANCE CRITERIA

### **Must Pass Before Production:**
- ✅ All 24 test cases pass
- ✅ No console errors
- ✅ No database errors
- ✅ Assets successfully built (`npm run build`)
- ✅ Responsive on mobile devices
- ✅ Works in all major browsers
- ✅ Security tests pass
- ✅ Performance metrics met
- ✅ Regression tests pass

---

## 🚀 DEPLOYMENT CHECKLIST

### **Pre-Deployment:**
- [ ] Clear disk space
- [ ] Run `npm run build` successfully
- [ ] Run all database migrations
- [ ] Verify `.env` configuration
- [ ] Test on staging environment
- [ ] Complete all test cases

### **Deployment:**
- [ ] Backup database
- [ ] Deploy code to production
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Verify deployed assets
- [ ] Smoke test production

### **Post-Deployment:**
- [ ] Monitor error logs
- [ ] Check API response times
- [ ] Verify user registrations work
- [ ] Test critical user flows
- [ ] Monitor Stripe webhook activity

---

## 📞 SUPPORT & TROUBLESHOOTING

### **Common Issues:**

**1. "Housekeeper Dashboard Not Loading"**
- Check if user is approved (status = 'active')
- Verify assets are built (`npm run build`)
- Check browser console for errors
- Clear browser cache

**2. "Cannot See Housekeeping in Service Types"**
- Verify assets are rebuilt
- Clear browser cache
- Check ClientDashboard.vue has 'Housekeeping' option

**3. "API Returns 404"**
- Run `php artisan route:list | grep housekeeper`
- Verify routes are registered
- Check web server configuration

**4. "Database Errors"**
- Run `php artisan migrate:status`
- Ensure all 5 housekeeper migrations are run
- Check database connection

---

## ✅ SIGN-OFF

### **Development Team:**
- [ ] All features implemented
- [ ] Code reviewed
- [ ] Documentation complete

### **QA Team:**
- [ ] All test cases executed
- [ ] Bugs logged and resolved
- [ ] Performance verified

### **Product Owner:**
- [ ] Acceptance criteria met
- [ ] User stories satisfied
- [ ] Ready for production

---

**Document Version:** 1.0  
**Last Updated:** January 11, 2026  
**Next Review:** After full test execution

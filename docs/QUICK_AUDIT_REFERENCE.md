# 🚀 QUICK AUDIT & TESTING COMMANDS

## Run Comprehensive System Audit
```bash
php comprehensive-system-audit.php
```
**What it checks:**
- Database integrity (all tables and relationships)
- User roles and permissions
- Widget/stats accuracy
- Data consistency
- Error handling and null safety
- Booking workflow
- Notification system
- Time tracking
- Review system

**Expected Result:** 100% pass rate (42/42 tests)

---

## Check User Roles
```bash
php check-user-roles.php
```
**Shows:**
- All users with their roles
- User types
- Caregiver profiles
- Role distribution

---

## Fix User Roles (if needed)
```bash
php fix-user-roles.php
```
**Synchronizes:**
- `role` field with `user_type`
- Ensures all users have correct roles
- Updates role distribution

---

## Fix Booking Assignments
```bash
php fix-booking-assignments.php
```
**Corrects:**
- Missing caregiver assignments
- Assignment order
- Start/end dates
- Active status

---

## Test Account Credentials

### Admin
```
Email: admin@demo.com
Role: admin
Dashboard: /admin
```

### Client
```
Email: client@demo.com
Role: client
Dashboard: /client
```

### Caregiver
```
Email: Caregiver1@gmail.com
Role: caregiver
Dashboard: /caregiver
```

### Marketing Staff
```
Email: marketing@demo.com
Role: marketing_staff
Dashboard: /marketing
```

### Training Center
```
Email: training@demo.com
Role: training_center
Dashboard: /training
```

---

## Quick Health Checks

### Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> "Connected!"
```

### Count Users by Role
```bash
php artisan tinker
>>> DB::table('users')->select('role', DB::raw('count(*) as count'))->groupBy('role')->get();
```

### Check Latest Bookings
```bash
php artisan tinker
>>> DB::table('bookings')->orderBy('created_at', 'desc')->limit(5)->get();
```

### Check Payment Status
```bash
php artisan tinker
>>> DB::table('payments')->select('status', DB::raw('SUM(amount) as total'))->groupBy('status')->get();
```

---

## Common Testing Scenarios

### 1. Test Booking Creation
```
1. Login as client@demo.com
2. Click "Book a Caregiver"
3. Fill form and submit
4. Verify booking appears in dashboard
```

### 2. Test Booking Approval
```
1. Login as admin@demo.com
2. Go to "Manage Bookings"
3. Find pending booking
4. Click "Approve"
5. Verify client receives notification
```

### 3. Test Payment Processing
```
1. Login as client with approved booking
2. Click "Pay Now"
3. Use test card: 4242 4242 4242 4242
4. Verify payment success
5. Check receipt generation
```

### 4. Test Time Tracking
```
1. Login as caregiver with assignment
2. Click "Clock In"
3. Wait 5 minutes
4. Click "Clock Out"
5. Verify hours calculated
```

---

## Monitoring Commands

### Check Error Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Queue Status
```bash
php artisan queue:work --once
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Run Migrations
```bash
php artisan migrate:status
php artisan migrate:fresh --seed  # CAUTION: Resets DB
```

---

## Troubleshooting

### Issue: Audit Script Fails
```bash
# Check PHP version
php -v

# Check Composer dependencies
composer install

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: User Roles Incorrect
```bash
# Run fix script
php fix-user-roles.php

# Verify
php check-user-roles.php
```

### Issue: Missing Assignments
```bash
# Run fix script
php fix-booking-assignments.php

# Check database
php artisan tinker
>>> DB::table('booking_assignments')->count();
```

---

## Success Indicators

### ✅ System is Healthy When:
- Audit shows 100% pass rate
- All test accounts can login
- Bookings can be created and approved
- Payments process successfully
- Time tracking works
- Notifications are sent
- No NULL errors in logs

### ⚠️ Warning Signs:
- Audit shows < 95% pass rate
- Database connection issues
- Payment failures
- NULL value errors
- Missing relationships
- Orphaned records

---

## Daily Monitoring (First Week)

### Morning Check
```bash
php comprehensive-system-audit.php
```

### Midday Check
- Review error logs
- Check active users
- Monitor payment processing

### Evening Check
- Review booking activity
- Check notification delivery
- Verify time tracking records

---

## Quick Stats Query

```bash
php artisan tinker

# Get system overview
>>> echo "Users: " . DB::table('users')->count();
>>> echo "Bookings: " . DB::table('bookings')->count();
>>> echo "Payments: $" . DB::table('payments')->sum('amount');
>>> echo "Active Assignments: " . DB::table('booking_assignments')->where('status', 'assigned')->count();
```

---

## Emergency Commands

### Rollback Last Migration
```bash
php artisan migrate:rollback --step=1
```

### Restore Database from Backup
```bash
mysql -u username -p database_name < backup.sql
```

### Disable Maintenance Mode
```bash
php artisan up
```

### Force Clear Everything
```bash
php artisan optimize:clear
```

---

## Contact & Support

**Technical Issues:** Check logs first, then audit  
**Database Issues:** Run audit script for diagnostics  
**User Issues:** Check role configuration  
**Payment Issues:** Review Stripe dashboard

---

**Last Updated:** January 6, 2026  
**System Status:** 🟢 Production Ready

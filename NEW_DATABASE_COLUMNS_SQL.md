# New Database Columns Added - SQL Commands

## Summary of Changes

We added **4 new features** to the database:
1. **Caregiver Certifications** (HHA, CNA, RN)
2. **Salary Range Preferences** (Min/Max hourly rates)
3. **Assigned Hourly Rate** (For bookings)
4. **Assigned Hourly Rate** (For booking assignments)

---

## 1. Caregiver Certifications (6 columns)

### Migration File
`2026_01_08_201510_add_certifications_to_caregivers_table.php`

### SQL Command

```sql
-- Add certification columns to caregivers table
ALTER TABLE `caregivers`
ADD COLUMN `has_hha` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Home Health Aide certification' AFTER `training_certificate`,
ADD COLUMN `hha_number` VARCHAR(255) NULL COMMENT 'HHA certification number' AFTER `has_hha`,
ADD COLUMN `has_cna` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Certified Nursing Assistant certification' AFTER `hha_number`,
ADD COLUMN `cna_number` VARCHAR(255) NULL COMMENT 'CNA certification number' AFTER `has_cna`,
ADD COLUMN `has_rn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Registered Nurse license' AFTER `cna_number`,
ADD COLUMN `rn_number` VARCHAR(255) NULL COMMENT 'RN license number' AFTER `has_rn`;
```

### Purpose
- Track caregiver professional certifications
- Store certification/license numbers
- Filter caregivers by qualifications
- Match caregivers to client requirements

---

## 2. Salary Range Preferences (2 columns)

### Migration File
`2026_01_08_203815_add_salary_range_to_caregivers_table.php`

### SQL Command

```sql
-- Add salary range columns to caregivers table
ALTER TABLE `caregivers`
ADD COLUMN `preferred_hourly_rate_min` DECIMAL(8,2) NULL DEFAULT 20.00 AFTER `rn_number`,
ADD COLUMN `preferred_hourly_rate_max` DECIMAL(8,2) NULL DEFAULT 50.00 AFTER `preferred_hourly_rate_min`;
```

### Purpose
- Store caregiver's preferred salary range
- Display during caregiver assignment
- Help admins set appropriate rates
- Default range: $20 - $50/hr

---

## 3. Assigned Hourly Rate - Bookings (1 column)

### Migration File
`2026_01_08_211232_add_assigned_hourly_rate_to_bookings_table.php`

### SQL Command

```sql
-- Add assigned hourly rate to bookings table
ALTER TABLE `bookings`
ADD COLUMN `assigned_hourly_rate` DECIMAL(8,2) NULL COMMENT 'Actual hourly rate assigned to caregiver for this booking';
```

### Purpose
- Store the rate assigned for single-caregiver bookings
- Used when only one caregiver is assigned
- Affects earnings calculations
- Displayed in booking details

---

## 4. Assigned Hourly Rate - Booking Assignments (1 column) ⭐ MOST IMPORTANT

### Migration File
`2026_01_08_213454_add_assigned_hourly_rate_to_booking_assignments_table.php`

### SQL Command

```sql
-- Add assigned hourly rate to booking_assignments table
ALTER TABLE `booking_assignments`
ADD COLUMN `assigned_hourly_rate` DECIMAL(8,2) NULL AFTER `caregiver_id`;
```

### Purpose
- **PRIMARY STORAGE** for assigned hourly rates
- Each caregiver can have different rate per assignment
- Used for multi-caregiver bookings
- **This is what we fixed today!**
- Affects all payment calculations

---

## Complete SQL Script (Run All at Once)

```sql
-- ==============================================
-- CAS Private Care - Database Updates
-- Date: January 8-9, 2026
-- ==============================================

USE cas_db;

-- 1. Add Caregiver Certifications
ALTER TABLE `caregivers`
ADD COLUMN `has_hha` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Home Health Aide certification' AFTER `training_certificate`,
ADD COLUMN `hha_number` VARCHAR(255) NULL COMMENT 'HHA certification number' AFTER `has_hha`,
ADD COLUMN `has_cna` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Certified Nursing Assistant certification' AFTER `hha_number`,
ADD COLUMN `cna_number` VARCHAR(255) NULL COMMENT 'CNA certification number' AFTER `has_cna`,
ADD COLUMN `has_rn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Registered Nurse license' AFTER `cna_number`,
ADD COLUMN `rn_number` VARCHAR(255) NULL COMMENT 'RN license number' AFTER `has_rn`;

-- 2. Add Salary Range Preferences
ALTER TABLE `caregivers`
ADD COLUMN `preferred_hourly_rate_min` DECIMAL(8,2) NULL DEFAULT 20.00 AFTER `rn_number`,
ADD COLUMN `preferred_hourly_rate_max` DECIMAL(8,2) NULL DEFAULT 50.00 AFTER `preferred_hourly_rate_min`;

-- 3. Add Assigned Hourly Rate to Bookings
ALTER TABLE `bookings`
ADD COLUMN `assigned_hourly_rate` DECIMAL(8,2) NULL COMMENT 'Actual hourly rate assigned to caregiver for this booking';

-- 4. Add Assigned Hourly Rate to Booking Assignments (CRITICAL)
ALTER TABLE `booking_assignments`
ADD COLUMN `assigned_hourly_rate` DECIMAL(8,2) NULL AFTER `caregiver_id`;

-- Verify changes
SELECT 
    'caregivers' AS table_name,
    COUNT(*) AS column_count
FROM information_schema.columns
WHERE table_schema = 'cas_db'
AND table_name = 'caregivers'
AND column_name IN ('has_hha', 'hha_number', 'has_cna', 'cna_number', 'has_rn', 'rn_number', 'preferred_hourly_rate_min', 'preferred_hourly_rate_max')

UNION ALL

SELECT 
    'bookings' AS table_name,
    COUNT(*) AS column_count
FROM information_schema.columns
WHERE table_schema = 'cas_db'
AND table_name = 'bookings'
AND column_name = 'assigned_hourly_rate'

UNION ALL

SELECT 
    'booking_assignments' AS table_name,
    COUNT(*) AS column_count
FROM information_schema.columns
WHERE table_schema = 'cas_db'
AND table_name = 'booking_assignments'
AND column_name = 'assigned_hourly_rate';

-- Expected results:
-- caregivers: 8 columns
-- bookings: 1 column
-- booking_assignments: 1 column
```

---

## Verification Commands

### Check if columns exist:

```sql
-- Check caregivers table
DESCRIBE caregivers;

-- Check bookings table
DESCRIBE bookings;

-- Check booking_assignments table
DESCRIBE booking_assignments;
```

### Check for data:

```sql
-- Check caregivers with certifications
SELECT id, user_id, has_hha, hha_number, has_cna, cna_number, has_rn, rn_number,
       preferred_hourly_rate_min, preferred_hourly_rate_max
FROM caregivers
WHERE has_hha = 1 OR has_cna = 1 OR has_rn = 1;

-- Check bookings with assigned rates
SELECT id, client_id, service_date, assigned_hourly_rate
FROM bookings
WHERE assigned_hourly_rate IS NOT NULL;

-- Check booking assignments with rates
SELECT ba.id, ba.booking_id, ba.caregiver_id, ba.assigned_hourly_rate, ba.status
FROM booking_assignments ba
WHERE ba.assigned_hourly_rate IS NOT NULL;
```

---

## Rollback Commands (If Needed)

```sql
-- WARNING: This will delete the columns and their data!

-- Remove from caregivers
ALTER TABLE `caregivers`
DROP COLUMN `has_hha`,
DROP COLUMN `hha_number`,
DROP COLUMN `has_cna`,
DROP COLUMN `cna_number`,
DROP COLUMN `has_rn`,
DROP COLUMN `rn_number`,
DROP COLUMN `preferred_hourly_rate_min`,
DROP COLUMN `preferred_hourly_rate_max`;

-- Remove from bookings
ALTER TABLE `bookings`
DROP COLUMN `assigned_hourly_rate`;

-- Remove from booking_assignments
ALTER TABLE `booking_assignments`
DROP COLUMN `assigned_hourly_rate`;
```

---

## Laravel Migration Commands

### If you prefer to use Laravel migrations on Ubuntu server:

```bash
# SSH into your server
ssh ubuntu@your-server-ip

# Go to project directory
cd /var/www/casprivatecare

# Pull latest changes (includes migration files)
git pull origin master

# Run migrations
php artisan migrate

# Check migration status
php artisan migrate:status
```

### Expected Output:

```
Migration name ................................................. Batch / Status
2026_01_08_201510_add_certifications_to_caregivers_table .......... [1] Ran
2026_01_08_203815_add_salary_range_to_caregivers_table ............ [1] Ran
2026_01_08_211232_add_assigned_hourly_rate_to_bookings_table ...... [1] Ran
2026_01_08_213454_add_assigned_hourly_rate_to_booking_assignments_table ... [1] Ran
```

---

## Summary Table

| Table Name            | Column Name                   | Type          | Nullable | Default | Purpose                              |
|-----------------------|-------------------------------|---------------|----------|---------|--------------------------------------|
| `caregivers`          | `has_hha`                     | TINYINT(1)    | No       | 0       | Has HHA certification                |
| `caregivers`          | `hha_number`                  | VARCHAR(255)  | Yes      | NULL    | HHA certificate number               |
| `caregivers`          | `has_cna`                     | TINYINT(1)    | No       | 0       | Has CNA certification                |
| `caregivers`          | `cna_number`                  | VARCHAR(255)  | Yes      | NULL    | CNA certificate number               |
| `caregivers`          | `has_rn`                      | TINYINT(1)    | No       | 0       | Has RN license                       |
| `caregivers`          | `rn_number`                   | VARCHAR(255)  | Yes      | NULL    | RN license number                    |
| `caregivers`          | `preferred_hourly_rate_min`   | DECIMAL(8,2)  | Yes      | 20.00   | Minimum preferred hourly rate        |
| `caregivers`          | `preferred_hourly_rate_max`   | DECIMAL(8,2)  | Yes      | 50.00   | Maximum preferred hourly rate        |
| `bookings`            | `assigned_hourly_rate`        | DECIMAL(8,2)  | Yes      | NULL    | Rate for single-caregiver bookings   |
| `booking_assignments` | `assigned_hourly_rate`        | DECIMAL(8,2)  | Yes      | NULL    | **Rate per caregiver assignment** ⭐ |

**Total:** 10 new columns added across 3 tables

---

## Important Notes

1. **Most Critical Column:** `booking_assignments.assigned_hourly_rate`
   - This is what stores the hourly rate you assign to each caregiver
   - This is what we fixed today to display correctly in the UI
   - This is what affects all payment calculations

2. **Certification Columns:**
   - Allows filtering caregivers by qualifications
   - Can require specific certifications for bookings
   - Displays badges in caregiver profiles

3. **Salary Range Columns:**
   - Shows caregiver's preferred rate during assignment
   - Helps set appropriate assigned rates
   - Default range: $20-$50/hr

4. **All columns are nullable** except boolean flags (has_hha, has_cna, has_rn)
   - Safe to add to existing database
   - Won't break existing records
   - Can be populated gradually

---

## Which Method Should You Use?

### Option 1: Run SQL Directly (Faster)
✅ Use if you have direct database access  
✅ All changes applied immediately  
✅ Good for production if migrations already ran on dev  

### Option 2: Laravel Migrations (Recommended)
✅ Use if migrations are in your codebase  
✅ Tracked in migrations table  
✅ Can rollback if needed  
✅ Better for version control  

**Recommended:** Use Laravel migrations on your Ubuntu server since you already have the migration files in the repo.

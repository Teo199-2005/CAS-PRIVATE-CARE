-- Clean up John Doe test data from cas_db database
-- Run these commands in your MySQL database

USE cas_db;

-- 1. Find and remove John Doe user and related data
DELETE ba FROM booking_assignments ba
INNER JOIN bookings b ON ba.booking_id = b.id
INNER JOIN users u ON b.client_id = u.id
WHERE u.name = 'John Doe';

-- 2. Remove bookings for John Doe
DELETE b FROM bookings b
INNER JOIN users u ON b.client_id = u.id
WHERE u.name = 'John Doe';

-- 3. Remove John Doe user account
DELETE FROM users WHERE name = 'John Doe';

-- 4. Check remaining assignments for caregiver ID 2
SELECT 
    ba.id as assignment_id,
    b.id as booking_id,
    u.name as client_name,
    b.service_type,
    b.status,
    b.service_date
FROM booking_assignments ba
INNER JOIN bookings b ON ba.booking_id = b.id
INNER JOIN users u ON b.client_id = u.id
WHERE ba.caregiver_id = 2
AND b.status IN ('approved', 'confirmed')
AND b.service_date >= CURDATE();
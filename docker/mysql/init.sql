-- CAS Private Care - MySQL Initialization Script
-- This script runs when the MySQL container is first created

-- Create test database
CREATE DATABASE IF NOT EXISTS cas_db_test;

-- Grant permissions to the application user
GRANT ALL PRIVILEGES ON cas_db.* TO 'cas_user'@'%';
GRANT ALL PRIVILEGES ON cas_db_test.* TO 'cas_user'@'%';

FLUSH PRIVILEGES;

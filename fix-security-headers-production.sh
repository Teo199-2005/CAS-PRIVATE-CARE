#!/bin/bash

# This script fixes the SecurityHeaders.php file on production server
# Run this on your production server via SSH

echo "Fixing SecurityHeaders.php on production..."

# Navigate to project directory
cd /var/www/casprivatecare.online || exit 1

# Pull latest changes from Git
echo "Pulling latest changes from Git..."
git pull origin master

# Clear all caches
echo "Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting proper permissions..."
chown -R www-data:www-data /var/www/casprivatecare.online
chmod -R 755 /var/www/casprivatecare.online
chmod -R 775 /var/www/casprivatecare.online/storage
chmod -R 775 /var/www/casprivatecare.online/bootstrap/cache

echo "Done! The SecurityHeaders.php file should now be fixed."
echo "Please test the site: https://casprivatecare.online/login"

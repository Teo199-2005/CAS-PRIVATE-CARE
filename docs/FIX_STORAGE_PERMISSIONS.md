# Fix Laravel Storage Permissions Error

## Problem
```
file_put_contents(/var/www/casprivatecare/storage/framework/views/...): Failed to open stream: Permission denied
```

Laravel can't write compiled views to the storage directory because of incorrect permissions.

## 🚀 Quick Fix - Run on Ubuntu Server

```bash
# Fix all Laravel storage and cache permissions
cd /var/www/casprivatecare && \
sudo chown -R ubuntu:www-data /var/www/casprivatecare && \
sudo chmod -R 775 storage bootstrap/cache && \
sudo find storage -type f -exec chmod 664 {} \; && \
sudo find storage -type d -exec chmod 775 {} \; && \
sudo find bootstrap/cache -type f -exec chmod 664 {} \; && \
sudo find bootstrap/cache -type d -exec chmod 775 {} \; && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan view:clear && \
php artisan route:clear && \
echo "✅ Permissions fixed!"
```

## Step-by-Step Explanation

### 1. Set Correct Ownership
```bash
# Give ownership to ubuntu user and www-data group
sudo chown -R ubuntu:www-data /var/www/casprivatecare
```

### 2. Fix Storage Permissions
```bash
# Make storage and cache writable
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Set File Permissions
```bash
# Storage files: 664 (rw-rw-r--)
sudo find storage -type f -exec chmod 664 {} \;

# Storage directories: 775 (rwxrwxr-x)
sudo find storage -type d -exec chmod 775 {} \;

# Bootstrap cache files: 664
sudo find bootstrap/cache -type f -exec chmod 664 {} \;

# Bootstrap cache directories: 775
sudo find bootstrap/cache -type d -exec chmod 775 {} \;
```

### 4. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 5. Restart Services
```bash
sudo systemctl restart php8.3-fpm nginx
```

## Complete Fix Script

Save this as `fix-permissions.sh`:

```bash
#!/bin/bash

echo "🔧 Fixing Laravel permissions..."

cd /var/www/casprivatecare

# Set ownership
echo "Setting ownership..."
sudo chown -R ubuntu:www-data /var/www/casprivatecare

# Set base permissions
echo "Setting base permissions..."
sudo find /var/www/casprivatecare -type f -exec chmod 644 {} \;
sudo find /var/www/casprivatecare -type d -exec chmod 755 {} \;

# Special permissions for storage and cache
echo "Setting storage permissions..."
sudo chmod -R 775 storage bootstrap/cache
sudo find storage -type f -exec chmod 664 {} \;
sudo find storage -type d -exec chmod 775 {} \;
sudo find bootstrap/cache -type f -exec chmod 664 {} \;
sudo find bootstrap/cache -type d -exec chmod 775 {} \;

# Make sure writable directories exist
echo "Ensuring directories exist..."
sudo mkdir -p storage/framework/sessions
sudo mkdir -p storage/framework/views
sudo mkdir -p storage/framework/cache
sudo mkdir -p storage/logs
sudo mkdir -p bootstrap/cache

# Set permissions again on new directories
sudo chown -R ubuntu:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear all caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Restart services
echo "Restarting services..."
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx

echo "✅ Permissions fixed!"
echo "✅ Services restarted!"
echo ""
echo "Try accessing your site now."
```

Make it executable and run:
```bash
chmod +x fix-permissions.sh
./fix-permissions.sh
```

## Understanding the Permissions

### Directory Structure Permissions:
```
/var/www/casprivatecare/
├── storage/               (775 - ubuntu:www-data)
│   ├── app/               (775)
│   ├── framework/         (775)
│   │   ├── cache/         (775)
│   │   ├── sessions/      (775)
│   │   └── views/         (775) ← Error happened here
│   └── logs/              (775)
├── bootstrap/
│   └── cache/             (775 - ubuntu:www-data)
└── public/
    └── build/             (775 - ubuntu:www-data)
```

### Permission Numbers:
- **775** = `rwxrwxr-x`
  - Owner (ubuntu): read, write, execute
  - Group (www-data): read, write, execute
  - Others: read, execute

- **664** = `rw-rw-r--`
  - Owner (ubuntu): read, write
  - Group (www-data): read, write
  - Others: read

## Why This Happens

1. **Wrong Ownership**: Files owned by root or wrong user
2. **Wrong Group**: Not in www-data group
3. **Wrong Permissions**: Directories not writable by web server
4. **Missing Directories**: Framework directories don't exist

## Verification Commands

```bash
# Check storage ownership
ls -la /var/www/casprivatecare/storage/

# Check framework views directory
ls -la /var/www/casprivatecare/storage/framework/

# Check who runs PHP-FPM
ps aux | grep php-fpm

# Check who runs Nginx
ps aux | grep nginx

# Check current user
whoami
```

## Expected Output:
```bash
$ ls -la storage/
drwxrwxr-x  5 ubuntu www-data   4096 Jan  9 00:00 .
drwxr-xr-x 15 ubuntu www-data   4096 Jan  9 00:00 ..
drwxrwxr-x  3 ubuntu www-data   4096 Jan  9 00:00 app
drwxrwxr-x  6 ubuntu www-data   4096 Jan  9 00:00 framework
drwxrwxr-x  2 ubuntu www-data   4096 Jan  9 00:00 logs
```

## After Running Fix

1. **Refresh your browser** - Error should be gone
2. **Check error logs** if still failing:
   ```bash
   tail -f /var/www/casprivatecare/storage/logs/laravel.log
   ```

3. **Check Nginx error log** if needed:
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

## Common Issues

### Issue: "Permission denied" on logs
```bash
sudo chmod -R 775 storage/logs
sudo chown -R ubuntu:www-data storage/logs
```

### Issue: "Permission denied" on sessions
```bash
sudo chmod -R 775 storage/framework/sessions
sudo chown -R ubuntu:www-data storage/framework/sessions
```

### Issue: Views still not compiling
```bash
# Remove all compiled views
sudo rm -rf storage/framework/views/*
sudo chown -R ubuntu:www-data storage/framework/views
sudo chmod 775 storage/framework/views
php artisan view:clear
```

## Prevention - Add to Deployment Script

```bash
#!/bin/bash
# deploy.sh

cd /var/www/casprivatecare

# Pull changes
git pull origin master

# Install dependencies
composer install --no-dev --optimize-autoloader

# Fix permissions BEFORE building
sudo chown -R ubuntu:www-data /var/www/casprivatecare
sudo chmod -R 775 storage bootstrap/cache public/build

# Build assets
sudo rm -rf public/build/*
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions AFTER building
sudo chown -R ubuntu:www-data /var/www/casprivatecare
sudo chmod -R 775 storage bootstrap/cache public/build

# Restart services
sudo systemctl restart php8.3-fpm nginx

echo "✅ Deployment complete!"
```

## Summary

The error occurs because:
- Web server (Nginx/PHP-FPM) runs as `www-data` user
- Files owned by wrong user (probably root)
- `storage/framework/views` directory not writable

**Solution:** Set correct ownership and permissions for Laravel directories.

**Run this now:**
```bash
cd /var/www/casprivatecare && \
sudo chown -R ubuntu:www-data . && \
sudo chmod -R 775 storage bootstrap/cache && \
php artisan view:clear && \
sudo systemctl restart php8.3-fpm nginx
```

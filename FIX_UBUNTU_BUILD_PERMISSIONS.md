# Fix Ubuntu Build Permissions Error

## Problem
```
error during build:
[vite:prepare-out-dir] EACCES: permission denied, unlink '/var/www/casprivatecare/public/build/assets/app-0V5E1M5y.js'
```

The `npm run build` command fails because the user running the build doesn't have permission to delete files in the `public/build` directory.

## Solution - Run These Commands on Your Ubuntu Server

### Option 1: Fix Permissions (Recommended)

```bash
# Change ownership of the entire project to your ubuntu user
sudo chown -R ubuntu:ubuntu /var/www/casprivatecare

# Make sure build directory is writable
sudo chmod -R 755 /var/www/casprivatecare/public/build

# Now try building again
cd /var/www/casprivatecare
npm run build
```

### Option 2: Clear Build Directory First

```bash
# Remove the old build files with sudo
sudo rm -rf /var/www/casprivatecare/public/build/*

# Fix ownership
sudo chown -R ubuntu:ubuntu /var/www/casprivatecare

# Now build
cd /var/www/casprivatecare
npm run build
```

### Option 3: Build with Sudo (Not Recommended)

```bash
# This will work but can cause permission issues later
sudo npm run build

# If you use this, fix ownership after:
sudo chown -R ubuntu:ubuntu /var/www/casprivatecare
```

## After Successful Build

Once the build completes, restart your services:

```bash
# Restart Laravel queue workers (if using)
sudo supervisorctl restart all

# Or restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart Nginx
sudo systemctl restart nginx
```

## Verify Build

```bash
# Check that files were created
ls -la /var/www/casprivatecare/public/build/assets/

# Should see files like:
# app-XXXXXX.js
# app-XXXXXX.css
# vendor-XXXXXX.js
# manifest.json
```

## Prevent Future Issues

Add this to your deployment script:

```bash
#!/bin/bash
# deploy.sh

cd /var/www/casprivatecare

# Pull latest changes
git pull origin master

# Install dependencies
composer install --no-dev --optimize-autoloader

# Fix permissions before build
sudo chown -R ubuntu:ubuntu /var/www/casprivatecare
sudo chmod -R 755 storage bootstrap/cache public/build

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx

echo "✅ Deployment complete!"
```

## Quick Fix (Copy & Paste)

```bash
# Run this entire block on your Ubuntu server:
cd /var/www/casprivatecare && \
sudo rm -rf public/build/* && \
sudo chown -R ubuntu:ubuntu /var/www/casprivatecare && \
sudo chmod -R 755 public/build && \
npm run build && \
sudo systemctl restart php8.3-fpm nginx && \
echo "✅ Build complete and services restarted!"
```

## Expected Output After Success

```
vite v7.3.0 building client environment for production...
✓ 655 modules transformed.
public/build/manifest.json                          1.63 kB │ gzip:   0.38 kB
public/build/assets/materialdesignicons-webfont-*.woff2    403.22 kB
public/build/assets/materialdesignicons-webfont-*.woff     587.98 kB
public/build/assets/materialdesignicons-webfont-*.ttf    1,307.66 kB
public/build/assets/materialdesignicons-webfont-*.eot    1,307.88 kB
public/build/assets/app-*.css                         38.42 kB │ gzip:   7.30 kB
public/build/assets/app-*.css                      1,027.64 kB │ gzip: 144.69 kB
public/build/assets/vendor-*.js                      237.44 kB │ gzip:  88.25 kB
public/build/assets/app-*.js                       1,448.99 kB │ gzip: 393.47 kB
✓ built in 9.78s
```

## Troubleshooting

### Still Getting Permission Errors?

```bash
# Check who owns the files
ls -la /var/www/casprivatecare/public/build/

# Check current user
whoami

# Should be: ubuntu

# Check web server user
ps aux | grep nginx
ps aux | grep php-fpm

# Usually: www-data
```

### Set Correct Permissions for Web Server

```bash
# Set ubuntu as owner, www-data as group
sudo chown -R ubuntu:www-data /var/www/casprivatecare

# Set proper permissions
sudo find /var/www/casprivatecare -type f -exec chmod 644 {} \;
sudo find /var/www/casprivatecare -type d -exec chmod 755 {} \;

# Storage and cache need special permissions
sudo chmod -R 775 /var/www/casprivatecare/storage
sudo chmod -R 775 /var/www/casprivatecare/bootstrap/cache
```

## Summary

The error happens because:
1. Files in `public/build` are owned by a different user (possibly root or www-data)
2. Your ubuntu user can't delete them
3. Vite needs to clear the directory before building

**Solution:** Fix ownership and permissions, then build.

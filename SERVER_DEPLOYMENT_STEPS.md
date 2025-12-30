# Server Deployment - Pull Latest Changes

## You're Currently Logged In:
```
ubuntu@vps-747ca5b3:~$
Server IP: 15.204.248.209
```

## Step-by-Step Commands:

### 1. Navigate to Your Project Directory
```bash
cd /var/www/cas-private-care
# OR if it's in a different location:
cd /var/www/html
# OR
cd ~/cas-private-care
```

### 2. Check Current Git Status
```bash
git status
git branch
```

### 3. Stash Any Local Changes (if needed)
```bash
# Only if you have uncommitted local changes
git stash
```

### 4. Pull Latest Changes from GitHub
```bash
git pull origin master
```

### 5. Install/Update Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies (if needed)
npm install
```

### 6. Clear Laravel Caches
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Set Proper Permissions
```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/cas-private-care

# Set permissions
sudo chmod -R 755 /var/www/cas-private-care
sudo chmod -R 775 /var/www/cas-private-care/storage
sudo chmod -R 775 /var/www/cas-private-care/bootstrap/cache
```

### 8. Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
# OR
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
```

## Quick One-Liner (if in project directory):
```bash
cd /var/www/cas-private-care && git pull origin master && composer install --optimize-autoloader --no-dev && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && sudo chown -R www-data:www-data . && sudo chmod -R 755 . && sudo chmod -R 775 storage bootstrap/cache && sudo systemctl restart php8.3-fpm && sudo systemctl restart nginx
```

## What Just Got Updated:

### Navigation Changes:
- ✅ All pages now use consistent navigation
- ✅ Removed page-specific nav CSS overrides
- ✅ Standard 11-item menu: Home | Services | 1099 Contractors | Training | About | Blog | Contact Us | FAQ | Login | Register

### Font Consistency:
- ✅ All pages load Sora font weights 300-800
- ✅ Navigation uses font-weight: 500 (medium)
- ✅ No more bold/inconsistent nav text

### Body Styling:
- ✅ All pages use Plus Jakarta Sans
- ✅ Consistent color: #0B4FA2
- ✅ Same font size across all pages

### Mobile Footer:
- ✅ Added mobile-only footer to all pages
- ✅ Displays on screens ≤768px

## Testing After Deployment:

1. **Visit your website**: http://15.204.248.209 or your domain
2. **Test these pages**:
   - Landing page (/)
   - About (/about)
   - Blog (/blog)
   - Contact (/contact)
   - FAQ (/faq)
   - 1099 Contractors (/contractor-partner)

3. **Check for**:
   - ✅ Navigation looks consistent across all pages
   - ✅ All 11 menu items visible
   - ✅ Font weight is medium (500), not bold
   - ✅ Body text is consistent
   - ✅ Mobile footer appears on mobile view
   - ✅ No horizontal scrolling on mobile

## Troubleshooting:

### If changes don't appear:
```bash
# Force clear browser cache on server
php artisan view:clear
php artisan cache:clear
sudo systemctl restart nginx
```

### If you get permission errors:
```bash
sudo chown -R www-data:www-data /var/www/cas-private-care
sudo chmod -R 775 /var/www/cas-private-care/storage
```

### If git pull fails:
```bash
# Check what's blocking
git status

# If you have local changes, stash them
git stash

# Try pull again
git pull origin master

# If you want to discard local changes completely
git reset --hard origin/master
```

### Check PHP/Nginx errors:
```bash
# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM error log
sudo tail -f /var/log/php8.3-fpm.log

# Check Laravel log
tail -f /var/www/cas-private-care/storage/logs/laravel.log
```

## System Restart Notice:
Your server shows: **"System restart required"**

After pulling changes, consider restarting:
```bash
sudo reboot
```

Then SSH back in after 1-2 minutes.

## Summary of Commands to Run:

```bash
# 1. Go to project
cd /var/www/cas-private-care

# 2. Pull changes
git pull origin master

# 3. Clear caches
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# 4. Fix permissions
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 5. Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx

# 6. Verify
curl -I http://localhost
```

## Expected Result:
✅ Your live website will now have:
- Consistent navigation across all pages
- Proper font weights (medium 500, not bold)
- Consistent body text styling
- Mobile-responsive footer
- No horizontal scrolling issues

Good luck with the deployment! 🚀

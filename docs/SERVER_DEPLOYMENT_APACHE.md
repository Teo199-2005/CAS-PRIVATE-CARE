# Server Deployment - Pull Latest Changes (APACHE)

## You're Currently Logged In:
```
ubuntu@vps-747ca5b3:~$
Server IP: 15.204.248.209
Web Server: Apache
```

## 🚀 Quick Deployment Commands for APACHE:

### Step-by-Step Commands:

```bash
# 1. Navigate to your project directory
cd /var/www/cas-private-care

# 2. Pull the latest changes from GitHub
git pull origin master

# 3. Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 4. Rebuild caches
php artisan config:cache
php artisan view:cache
php artisan route:cache

# 5. Set proper permissions
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 6. Restart Apache
sudo systemctl restart apache2

# 7. Check Apache status
sudo systemctl status apache2
```

## 📋 One-Liner for Apache:

```bash
cd /var/www/cas-private-care && git pull origin master && php artisan cache:clear && php artisan config:cache && php artisan view:cache && php artisan route:cache && sudo chown -R www-data:www-data . && sudo chmod -R 775 storage bootstrap/cache && sudo systemctl restart apache2
```

## Apache-Specific Commands:

### Restart Apache:
```bash
sudo systemctl restart apache2
```

### Check Apache Status:
```bash
sudo systemctl status apache2
```

### Reload Apache (graceful restart):
```bash
sudo systemctl reload apache2
```

### Check Apache Configuration:
```bash
sudo apache2ctl configtest
```

### View Apache Error Logs:
```bash
# Error log
sudo tail -f /var/log/apache2/error.log

# Access log
sudo tail -f /var/log/apache2/access.log
```

### Enable/Disable Apache Modules (if needed):
```bash
# Enable mod_rewrite (required for Laravel)
sudo a2enmod rewrite

# Restart Apache after enabling modules
sudo systemctl restart apache2
```

## Troubleshooting Apache:

### If .htaccess not working:
```bash
# Check if mod_rewrite is enabled
sudo apache2ctl -M | grep rewrite

# If not enabled:
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Check Apache Virtual Host Config:
```bash
sudo nano /etc/apache2/sites-available/cas-private-care.conf
```

Your VirtualHost should look like:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/cas-private-care/public

    <Directory /var/www/cas-private-care/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### Reload Apache config:
```bash
sudo systemctl reload apache2
```

## Full Deployment Script (Copy & Paste):

```bash
#!/bin/bash
echo "Starting deployment..."

# Navigate to project
cd /var/www/cas-private-care || exit

# Pull latest changes
echo "Pulling latest changes from GitHub..."
git pull origin master

# Clear all caches
echo "Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Rebuild caches
echo "Rebuilding caches..."
php artisan config:cache
php artisan view:cache
php artisan route:cache

# Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# Restart Apache
echo "Restarting Apache..."
sudo systemctl restart apache2

# Check status
echo "Checking Apache status..."
sudo systemctl status apache2 --no-pager

echo "Deployment complete!"
echo "Visit: http://15.204.248.209"
```

## What Just Got Updated:

### Navigation Changes:
- ✅ All pages now use consistent navigation
- ✅ Removed page-specific nav CSS overrides
- ✅ Standard 11-item menu

### Font Consistency:
- ✅ All pages load Sora font weights 300-800
- ✅ Navigation uses font-weight: 500 (medium)

### Body Styling:
- ✅ All pages use Plus Jakarta Sans
- ✅ Consistent color: #0B4FA2

### Mobile Footer:
- ✅ Added mobile-only footer to all pages

## Testing After Deployment:

```bash
# Test if Apache is serving the site
curl -I http://localhost

# Or with your IP
curl -I http://15.204.248.209
```

Visit your website and test all pages!

## Common Apache Issues:

### 500 Internal Server Error:
```bash
# Check Apache error log
sudo tail -50 /var/log/apache2/error.log

# Check Laravel log
tail -50 /var/www/cas-private-care/storage/logs/laravel.log

# Make sure .htaccess is present
ls -la /var/www/cas-private-care/public/.htaccess
```

### Permission Denied Errors:
```bash
sudo chown -R www-data:www-data /var/www/cas-private-care
sudo chmod -R 755 /var/www/cas-private-care
sudo chmod -R 775 /var/www/cas-private-care/storage
sudo chmod -R 775 /var/www/cas-private-care/bootstrap/cache
```

### Laravel routes not working:
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2

# Check if AllowOverride is set to All in your VirtualHost
sudo nano /etc/apache2/sites-available/000-default.conf
```

## System Restart:
Your server shows: **"System restart required"**

After deployment, consider restarting:
```bash
sudo reboot
```

Then SSH back in after 1-2 minutes.

## Quick Commands Summary:

```bash
# Navigate to project
cd /var/www/cas-private-care

# Pull changes
git pull origin master

# Clear and rebuild caches
php artisan cache:clear && php artisan config:cache && php artisan view:cache

# Fix permissions
sudo chown -R www-data:www-data . && sudo chmod -R 775 storage bootstrap/cache

# Restart Apache
sudo systemctl restart apache2

# Check status
sudo systemctl status apache2
```

## Expected Result:
✅ Your live website will now have all the consistency fixes!

Good luck! 🚀

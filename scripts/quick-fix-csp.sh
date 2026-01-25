#!/bin/bash

# Quick Fix Script for CSP + Vue.js Error on Production
# Run this on your production server: bash quick-fix-csp.sh

echo "🔧 Quick Fix: Disabling CSP and Clearing All Caches..."
echo ""

cd /var/www/casprivatecare.online || exit 1

# Clear all Laravel caches
echo "1️⃣  Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Laravel caches cleared"
echo ""

# Restart PHP-FPM (try common versions)
echo "2️⃣  Restarting PHP-FPM..."
if systemctl list-units --type=service | grep -q "php8.4-fpm"; then
    sudo systemctl restart php8.4-fpm
    echo "✅ PHP 8.4 FPM restarted"
elif systemctl list-units --type=service | grep -q "php8.3-fpm"; then
    sudo systemctl restart php8.3-fpm
    echo "✅ PHP 8.3 FPM restarted"
elif systemctl list-units --type=service | grep -q "php-fpm"; then
    sudo systemctl restart php-fpm
    echo "✅ PHP FPM restarted"
else
    echo "⚠️  Could not find PHP-FPM service"
fi
echo ""

# Restart web server
echo "3️⃣  Restarting web server..."
if systemctl list-units --type=service | grep -q "nginx"; then
    sudo systemctl reload nginx
    echo "✅ Nginx reloaded"
elif systemctl list-units --type=service | grep -q "apache2"; then
    sudo systemctl restart apache2
    echo "✅ Apache restarted"
else
    echo "⚠️  Could not detect web server"
fi
echo ""

# Test the site
echo "4️⃣  Testing site response..."
curl -I https://casprivatecare.online 2>/dev/null | head -n 1
echo ""

echo "═══════════════════════════════════════════════════════════════"
echo "✅ Fix complete!"
echo ""
echo "Please test your site:"
echo "   https://casprivatecare.online/caregiver/dashboard-vue"
echo ""
echo "If the error persists, run the diagnostic:"
echo "   bash diagnose-csp.sh"
echo "═══════════════════════════════════════════════════════════════"

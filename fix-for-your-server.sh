#!/bin/bash

# Corrected Quick Fix for Your Server Setup
# Run from: /var/www/casprivatecare

echo "🔧 Fixing CSP + Vue.js Error..."
echo ""

# Make sure we're in the right directory
cd /var/www/casprivatecare || {
    echo "❌ Error: /var/www/casprivatecare directory not found"
    exit 1
}

echo "📁 Working directory: $(pwd)"
echo ""

# Clear all Laravel caches
echo "1️⃣  Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Find and restart correct PHP-FPM version
echo "2️⃣  Finding and restarting PHP-FPM..."
if systemctl list-units --type=service 2>/dev/null | grep -q "php.*fpm"; then
    PHP_SERVICE=$(systemctl list-units --type=service 2>/dev/null | grep -o "php[0-9.]*-fpm\.service" | head -n 1)
    if [ -n "$PHP_SERVICE" ]; then
        echo "Found: $PHP_SERVICE"
        sudo systemctl restart "$PHP_SERVICE"
        echo "✅ PHP-FPM restarted"
    else
        echo "⚠️  Could not determine PHP-FPM service name"
        echo "Available PHP services:"
        systemctl list-units --type=service 2>/dev/null | grep php || echo "None found"
    fi
else
    echo "⚠️  systemctl not available or no PHP-FPM found"
fi
echo ""

# Restart Apache (not Nginx)
echo "3️⃣  Restarting Apache..."
if systemctl list-units --type=service 2>/dev/null | grep -q "apache2"; then
    sudo systemctl restart apache2
    echo "✅ Apache restarted"
elif command -v apachectl &> /dev/null; then
    sudo apachectl restart
    echo "✅ Apache restarted (via apachectl)"
else
    echo "⚠️  Apache not found"
fi
echo ""

# Check where CSP is being set
echo "4️⃣  Checking for CSP in Apache config..."
if [ -d "/etc/apache2" ]; then
    CSP_FOUND=$(sudo grep -r "Content-Security-Policy" /etc/apache2/ 2>/dev/null || echo "")
    if [ -n "$CSP_FOUND" ]; then
        echo "⚠️  CSP FOUND in Apache config:"
        echo "$CSP_FOUND"
        echo ""
        echo "You need to comment out or remove these lines!"
    else
        echo "✅ No CSP in Apache config"
    fi
else
    echo "⚠️  /etc/apache2 directory not accessible"
fi
echo ""

# Check .htaccess
echo "5️⃣  Checking .htaccess for CSP..."
if [ -f "public/.htaccess" ]; then
    if grep -q "Content-Security-Policy" public/.htaccess; then
        echo "⚠️  CSP FOUND in .htaccess - need to remove it"
    else
        echo "✅ No CSP in .htaccess"
    fi
else
    echo "⚠️  public/.htaccess not found"
fi
echo ""

echo "═══════════════════════════════════════════════════════════════"
echo "✅ Basic fix complete!"
echo ""
echo "If the CSP error persists, the CSP header is being set by Apache."
echo "Check the output above for where it was found."
echo ""
echo "Test your site:"
echo "   https://casprivatecare.online/caregiver/dashboard-vue"
echo "═══════════════════════════════════════════════════════════════"

#!/bin/bash

# CSP Diagnostic Script
# This script helps identify where Content-Security-Policy headers are being set

echo "═══════════════════════════════════════════════════════════════"
echo "   CSP (Content Security Policy) Diagnostic Tool"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check if running as root/sudo
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  WARNING: Not running as root. Some checks may be limited."
    echo "   Run with: sudo bash diagnose-csp.sh"
    echo ""
fi

# 1. Check response headers from the live site
echo "1️⃣  Checking live site response headers..."
echo "─────────────────────────────────────────────────────────────"
curl -I https://casprivatecare.online/caregiver/dashboard-vue 2>/dev/null | grep -i "content-security-policy" || echo "✅ No CSP header found in response"
echo ""

# 2. Check Nginx configuration
echo "2️⃣  Checking Nginx configuration for CSP..."
echo "─────────────────────────────────────────────────────────────"
if [ -d "/etc/nginx" ]; then
    grep -r "Content-Security-Policy" /etc/nginx/ 2>/dev/null || echo "✅ No CSP found in Nginx config"
else
    echo "⚠️  Nginx directory not found or no access"
fi
echo ""

# 3. Check Apache configuration
echo "3️⃣  Checking Apache configuration for CSP..."
echo "─────────────────────────────────────────────────────────────"
if [ -d "/etc/apache2" ]; then
    grep -r "Content-Security-Policy" /etc/apache2/ 2>/dev/null || echo "✅ No CSP found in Apache config"
else
    echo "⚠️  Apache directory not found or no access"
fi
echo ""

# 4. Check .htaccess files
echo "4️⃣  Checking .htaccess files for CSP..."
echo "─────────────────────────────────────────────────────────────"
if [ -d "/var/www/casprivatecare.online" ]; then
    find /var/www/casprivatecare.online -name ".htaccess" -exec grep -H "Content-Security-Policy" {} \; 2>/dev/null || echo "✅ No CSP found in .htaccess files"
else
    echo "⚠️  Project directory not found"
fi
echo ""

# 5. Check Laravel SecurityHeaders middleware
echo "5️⃣  Checking Laravel SecurityHeaders.php..."
echo "─────────────────────────────────────────────────────────────"
if [ -f "/var/www/casprivatecare.online/app/Http/Middleware/SecurityHeaders.php" ]; then
    grep -n "Content-Security-Policy" /var/www/casprivatecare.online/app/Http/Middleware/SecurityHeaders.php || echo "✅ No CSP set in SecurityHeaders.php"
else
    echo "⚠️  SecurityHeaders.php not found"
fi
echo ""

# 6. Check for opcache/cached files
echo "6️⃣  Checking PHP cache status..."
echo "─────────────────────────────────────────────────────────────"
php -v 2>/dev/null || echo "⚠️  PHP not found in PATH"
echo "OPcache status: $(php -r 'echo ini_get("opcache.enable") ? "Enabled" : "Disabled";' 2>/dev/null || echo "Unknown")"
echo ""

# 7. Summary and recommendations
echo "═══════════════════════════════════════════════════════════════"
echo "   RECOMMENDATIONS"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "If CSP was found above, disable it by:"
echo ""
echo "• For Nginx: Comment out 'add_header Content-Security-Policy' lines"
echo "  Then run: sudo systemctl reload nginx"
echo ""
echo "• For Apache: Comment out 'Header set Content-Security-Policy' lines"
echo "  Then run: sudo systemctl restart apache2"
echo ""
echo "• Clear Laravel caches:"
echo "  cd /var/www/casprivatecare.online"
echo "  php artisan cache:clear"
echo "  php artisan config:clear"
echo "  php artisan optimize:clear"
echo ""
echo "• Restart PHP-FPM:"
echo "  sudo systemctl restart php8.4-fpm"
echo ""
echo "═══════════════════════════════════════════════════════════════"

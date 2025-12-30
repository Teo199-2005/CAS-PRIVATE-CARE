#!/bin/bash
# Fix Ubuntu deployment - missing dependencies

cd /var/www/casprivatecare

# 1. Remove the problematic service provider or install the package
# Option A: Install Laravel Pail (recommended for development)
# composer require laravel/pail --dev

# Option B: Remove it from config (better for production)
# We'll do Option B since this is production

# 2. Update composer dependencies properly
composer install --no-dev --optimize-autoloader

# 3. If composer fails, update composer itself first
sudo composer self-update
composer install --no-dev --optimize-autoloader

# 4. Clear config cache to remove the Pail reference
php artisan config:clear
php artisan cache:clear

# 5. Install NPM dependencies
npm install

# 6. Build frontend assets
npm run build

# 7. Run migrations
php artisan migrate --force

# 8. Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Set proper permissions
sudo chown -R www-data:www-data /var/www/casprivatecare
sudo chmod -R 775 storage bootstrap/cache

# 10. Restart Apache
sudo systemctl restart apache2

echo "Deployment complete!"

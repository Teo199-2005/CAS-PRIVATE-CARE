# Production Configuration Guide

This guide helps you configure your CAS Private Care LLC application for production deployment.

## Required Environment Variables

Create a `.env` file in your project root with the following configuration:

### Application Settings
```env
APP_NAME="CAS Private Care LLC"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

**Important:**
- Generate a new `APP_KEY` using: `php artisan key:generate`
- **NEVER** set `APP_DEBUG=true` in production
- **ALWAYS** set `APP_ENV=production` in production

### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_database_password
```

### Email Configuration (Brevo - Recommended)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=your_brevo_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Setup Instructions:**
1. Sign up at https://www.brevo.com
2. Go to Settings → SMTP & API → SMTP
3. Generate an SMTP password
4. Verify your sender email address in Settings → Senders
5. See `BREVO_EMAIL_SETUP.md` for detailed setup guide

### OAuth Configuration (Optional but Recommended)
```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback

FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=${APP_URL}/auth/facebook/callback
```

### Logging Configuration
```env
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null
```

For production, recommended log levels:
- `error` - Only log errors and above
- `warning` - Log warnings and errors
- `info` - Log informational messages (not recommended for production)

### Session Configuration
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

### Cache Configuration (Optional - for better performance)
```env
CACHE_DRIVER=file
# Or use Redis for better performance:
# CACHE_DRIVER=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379
```

### Queue Configuration (Optional - for background jobs)
```env
QUEUE_CONNECTION=sync
# Or use database/d Redis for async processing:
# QUEUE_CONNECTION=database
```

## Pre-Deployment Checklist

### 1. Security Configuration
- [ ] `APP_DEBUG=false` is set
- [ ] `APP_ENV=production` is set
- [ ] `APP_KEY` is generated and secure
- [ ] Database credentials are secure
- [ ] All passwords are strong and unique

### 2. File Permissions
Set appropriate file permissions:
```bash
# Storage and cache directories must be writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Optional: Seed initial data (if needed)
php artisan db:seed --class=DatabaseSeeder
```

### 4. Optimize Application
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 5. Verify Development Routes Are Disabled
Ensure `APP_ENV=production` so development routes are automatically disabled.

Check `routes/web.php` - routes inside `if (app()->environment('local', 'development'))` will not be accessible.

## Post-Deployment Verification

### 1. Test Critical Functionality
- [ ] User registration works
- [ ] Login works
- [ ] Dashboard loads correctly
- [ ] Booking creation works
- [ ] Admin functions work

### 2. Security Checks
- [ ] No error stack traces visible to users
- [ ] CSRF protection works
- [ ] Authentication required for protected routes
- [ ] Role-based access control works
- [ ] No development routes accessible

### 3. Performance Checks
- [ ] Page load times are acceptable
- [ ] Database queries are optimized
- [ ] Assets are loading correctly

### 4. Error Logging
Check that errors are being logged:
```bash
tail -f storage/logs/laravel.log
```

## Security Best Practices

1. **Never commit `.env` file** - It's already in `.gitignore`
2. **Use HTTPS** - Configure SSL certificate
3. **Regular backups** - Set up automated database backups
4. **Keep dependencies updated** - Regularly run `composer update`
5. **Monitor logs** - Regularly check error logs
6. **Update Laravel** - Keep Laravel framework updated

## Troubleshooting

### If you see "500 Internal Server Error"
1. Check `storage/logs/laravel.log` for details
2. Verify file permissions on `storage/` and `bootstrap/cache/`
3. Check database connection
4. Verify `APP_KEY` is set

### If development routes are accessible
1. Verify `APP_ENV=production` in `.env`
2. Clear config cache: `php artisan config:clear`
3. Clear route cache: `php artisan route:clear`

### If emails aren't sending
1. Verify SMTP credentials
2. Check email service provider status
3. Check application logs for email errors

## Support

For additional help, refer to:
- Laravel Documentation: https://laravel.com/docs
- Your hosting provider's documentation
- Application logs: `storage/logs/laravel.log`


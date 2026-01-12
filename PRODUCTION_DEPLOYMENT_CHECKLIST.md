# 🚀 Production Deployment Checklist - CAS Private Care

## Pre-Deployment (Before Going Live)

### ✅ Environment Configuration

- [ ] Copy `.env.production.example` to `.env`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_URL` to production domain (https://yoursite.com)
- [ ] Generate new `APP_KEY` if not set: `php artisan key:generate`
- [ ] Set strong `DB_PASSWORD` (16+ characters)
- [ ] Configure production database credentials
- [ ] Set `SESSION_DRIVER=redis`
- [ ] Set `CACHE_STORE=redis`
- [ ] Set `QUEUE_CONNECTION=redis`
- [ ] Configure Redis connection (host, port, password)
- [ ] Replace test Stripe keys with live keys
- [ ] Verify `STRIPE_WEBHOOK_SECRET` from Stripe dashboard
- [ ] Set up Brevo SMTP credentials
- [ ] Configure Sentry DSN
- [ ] Set `SENTRY_ENVIRONMENT=production`
- [ ] Enable `SESSION_ENCRYPT=true`
- [ ] Enable `SESSION_SECURE_COOKIE=true`

### ✅ Database Setup

- [ ] Run migrations: `php artisan migrate --force`
- [ ] Run performance index migration: `php artisan migrate --path=database/migrations/2026_01_11_000001_add_performance_indexes.php`
- [ ] Verify all tables created successfully
- [ ] Create database backup before seeding
- [ ] Seed initial data if needed: `php artisan db:seed`
- [ ] Test database connection: `php artisan tinker` → `DB::connection()->getPdo();`

### ✅ Security Hardening

- [ ] Register `ContentSecurityPolicy` middleware in `bootstrap/app.php`
- [ ] Register `RateLimitMiddleware` in middleware aliases
- [ ] Apply rate limiting to sensitive routes (auth, payment, API)
- [ ] Verify CSRF protection is active on all POST/PUT/DELETE routes
- [ ] Test CSP nonce generation: check inline scripts have `nonce="{{$cspNonce}}"`
- [ ] Review all routes for authentication requirements
- [ ] Ensure no test/debug routes are accessible
- [ ] Check file upload validation (size, type restrictions)
- [ ] Verify password hashing uses bcrypt with rounds=12
- [ ] Test XSS protection on user input fields

### ✅ Performance Optimization

- [ ] Install Redis: `sudo apt install redis-server` or equivalent
- [ ] Start Redis service: `sudo systemctl start redis`
- [ ] Test Redis connection: `redis-cli ping` → PONG
- [ ] Clear and optimize config: `php artisan optimize`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Optimize Composer autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Build frontend assets: `npm run build`
- [ ] Verify assets are compiled to `public/build/`
- [ ] Enable OPcache in php.ini
- [ ] Configure server-level caching (Nginx/Apache)

### ✅ Asset & Frontend

- [ ] Run `npm install` for production dependencies
- [ ] Build assets with `npm run build`
- [ ] Verify `public/build/manifest.json` exists
- [ ] Test all Vue components render correctly
- [ ] Check responsive design on mobile devices
- [ ] Verify all images are optimized (<500KB)
- [ ] Test booking form submission
- [ ] Test payment flow end-to-end
- [ ] Check all modals and popups work
- [ ] Verify Chart.js dashboards load correctly

### ✅ Stripe Integration

- [ ] Replace test keys with live keys in `.env`
- [ ] Create webhook endpoint in Stripe dashboard
- [ ] Set webhook URL: `https://yoursite.com/api/stripe/webhook`
- [ ] Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`
- [ ] Copy webhook signing secret to `STRIPE_WEBHOOK_SECRET`
- [ ] Test webhook with Stripe CLI: `stripe listen --forward-to localhost:8000/api/stripe/webhook`
- [ ] Verify payment processing works with real card (use $1 test)
- [ ] Test refund flow
- [ ] Verify Stripe customer creation on user registration
- [ ] Check payment receipts are sent via email

### ✅ Email Configuration

- [ ] Verify Brevo account is active
- [ ] Confirm `MAIL_FROM_ADDRESS` is verified in Brevo
- [ ] Test welcome email on user registration
- [ ] Test booking confirmation email
- [ ] Test payment receipt email
- [ ] Test password reset email
- [ ] Verify email templates render correctly
- [ ] Check spam score of emails (mail-tester.com)

### ✅ Testing

- [ ] Run full test suite: `php artisan test`
- [ ] Verify 70+ tests passing (unit + feature + integration)
- [ ] Test user registration flow manually
- [ ] Test user login flow manually
- [ ] Test booking creation flow manually
- [ ] Test payment flow with live $1 transaction
- [ ] Test admin dashboard access
- [ ] Test caregiver portal access
- [ ] Test time tracking functionality
- [ ] Test PDF export feature
- [ ] Verify mobile responsiveness on 3+ devices
- [ ] Test with multiple browsers (Chrome, Firefox, Safari)
- [ ] Load test critical endpoints (booking, payment)

### ✅ Monitoring & Logging

- [ ] Sign up for Sentry account (sentry.io)
- [ ] Create new Sentry project
- [ ] Add `SENTRY_LARAVEL_DSN` to `.env`
- [ ] Test Sentry: `php artisan sentry:test`
- [ ] Verify errors appear in Sentry dashboard
- [ ] Configure log rotation: `/etc/logrotate.d/laravel`
- [ ] Set up Sentry alerts (email/Slack)
- [ ] Configure weekly error digest
- [ ] Set up health check monitoring (UptimeRobot/Pingdom)
- [ ] Add `/api/health` endpoint to monitoring
- [ ] Configure server resource monitoring (CPU, RAM, disk)

### ✅ Server Configuration

- [ ] PHP 8.2+ installed
- [ ] Required PHP extensions enabled (pdo_mysql, mbstring, xml, bcmath, curl, gd, zip)
- [ ] Nginx or Apache configured with proper document root
- [ ] SSL certificate installed (Let's Encrypt recommended)
- [ ] HTTPS redirect configured
- [ ] Firewall rules configured (allow 80, 443)
- [ ] Cron job for Laravel scheduler: `* * * * * cd /path && php artisan schedule:run`
- [ ] Queue worker service configured (systemd)
- [ ] Set proper file permissions: `chmod -R 755 storage bootstrap/cache`
- [ ] Set ownership: `chown -R www-data:www-data /var/www/html`

### ✅ Backup & Recovery

- [ ] Configure automated database backups (daily)
- [ ] Test database restore procedure
- [ ] Configure automated file backups (weekly)
- [ ] Store backups off-site (S3, Dropbox, etc.)
- [ ] Document restore procedures
- [ ] Create rollback plan
- [ ] Keep previous version as backup

### ✅ Documentation

- [ ] Update README.md with production notes
- [ ] Document deployment process
- [ ] Create runbook for common issues
- [ ] Document environment variables
- [ ] Create API documentation (if applicable)
- [ ] Document admin user creation process
- [ ] Create user training materials

## Post-Deployment (After Going Live)

### Immediate (First Hour)

- [ ] Monitor Sentry for any critical errors
- [ ] Check application logs: `tail -f storage/logs/laravel.log`
- [ ] Verify health check endpoint: `curl https://yoursite.com/api/health`
- [ ] Test user registration with real email
- [ ] Test payment with real $1 transaction
- [ ] Monitor server resources (CPU, RAM, disk)
- [ ] Check queue worker is running: `ps aux | grep queue:work`
- [ ] Verify cron jobs are executing: `grep CRON /var/log/syslog`

### First 24 Hours

- [ ] Monitor error rates in Sentry
- [ ] Check database performance (slow query log)
- [ ] Review API response times
- [ ] Monitor Redis memory usage
- [ ] Check email delivery rate
- [ ] Review user registrations
- [ ] Monitor payment success rate
- [ ] Check for any 500 errors in logs

### First Week

- [ ] Review all Sentry issues
- [ ] Analyze performance metrics
- [ ] Check cache hit rates
- [ ] Review queue job failures
- [ ] Monitor database growth
- [ ] Collect user feedback
- [ ] Review booking completion rate
- [ ] Analyze payment patterns

## Emergency Rollback Plan

If critical issues occur:

```bash
# 1. Take site offline
php artisan down --message="Maintenance in progress"

# 2. Restore previous version
git checkout <previous-tag>

# 3. Restore database backup
mysql -u user -p database < backup.sql

# 4. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Bring site back online
php artisan up
```

## Support Contacts

- **Hosting Support**: [Provider contact]
- **Stripe Support**: https://support.stripe.com
- **Sentry Support**: https://sentry.io/support
- **DNS Provider**: [Provider contact]

---

**Checklist Version**: 1.0
**Last Updated**: 2026-01-11
**Deployment Score Target**: 100/100 ✅

# Apache Deployment Guide for CAS Private Care
## Ubuntu Server with Apache2

---

## Prerequisites

- Ubuntu 20.04+ or 22.04+
- Apache 2.4+
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Composer 2.x
- Node.js 18+ and npm
- Certbot (for Let's Encrypt SSL)

---

## Step 1: Install Required Apache Modules

```bash
# Enable required Apache modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod deflate
sudo a2enmod expires
sudo a2enmod ssl
sudo a2enmod http2

# Restart Apache
sudo systemctl restart apache2
```

---

## Step 2: Install PHP 8.2 and Extensions

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and required extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl \
    php8.2-xml php8.2-bcmath php8.2-intl php8.2-readline \
    php8.2-opcache php8.2-redis

# Enable PHP-FPM with Apache
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php8.2-fpm
sudo systemctl restart apache2
```

---

## Step 3: Configure PHP for Production

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
; Security settings
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Performance settings
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300

; OPcache settings (important for Laravel)
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
opcache.save_comments = 1

; Session settings
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Lax
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

## Step 4: Deploy Application

```bash
# Create web directory
sudo mkdir -p /var/www/html
cd /var/www

# Clone repository (or upload files)
sudo git clone https://github.com/Teo199-2005/CAS-PRIVATE-CARE.git html
# Or: sudo unzip your-upload.zip -d html

# Set ownership
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

# Set storage and cache permissions
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R 775 /var/www/html/bootstrap/cache

# Install dependencies
cd /var/www/html
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci
sudo -u www-data npm run build
```

---

## Step 5: Configure Environment

```bash
# Copy environment file
sudo -u www-data cp .env.example .env

# Generate application key
sudo -u www-data php artisan key:generate

# Edit environment file
sudo nano .env
```

**Important `.env` settings for production:**

```env
APP_NAME="CAS Private Care"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://casprivatecare.online

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cas_private_care
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Session (secure settings)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Cache
CACHE_STORE=database

# Queue
QUEUE_CONNECTION=database

# Mail (Brevo)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@casprivatecare.online
MAIL_FROM_NAME="CAS Private Care"
```

---

## Step 6: Run Migrations and Optimize

```bash
cd /var/www/html

# Run migrations
sudo -u www-data php artisan migrate --force

# Clear and cache configurations
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Create storage link
sudo -u www-data php artisan storage:link

# Optimize autoloader
sudo -u www-data composer dump-autoload --optimize
```

---

## Step 7: Configure Apache Virtual Host

Copy the provided vhost configuration:

```bash
sudo cp /var/www/html/apache/vhost.conf /etc/apache2/sites-available/casprivatecare.conf
```

Or create manually at `/etc/apache2/sites-available/casprivatecare.conf`:

```apache
<VirtualHost *:80>
    ServerName casprivatecare.online
    ServerAlias www.casprivatecare.online
    DocumentRoot /var/www/html/public
    
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName casprivatecare.online
    ServerAlias www.casprivatecare.online
    DocumentRoot /var/www/html/public
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/casprivatecare.online/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/casprivatecare.online/privkey.pem
    
    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <IfModule mod_headers.c>
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
        Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
        Header always unset X-Powered-By
    </IfModule>
    
    ErrorLog ${APACHE_LOG_DIR}/casprivatecare-error.log
    CustomLog ${APACHE_LOG_DIR}/casprivatecare-access.log combined
</VirtualHost>
```

Enable the site:

```bash
# Disable default site
sudo a2dissite 000-default.conf

# Enable CAS Private Care site
sudo a2ensite casprivatecare.conf

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

## Step 8: Install SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d casprivatecare.online -d www.casprivatecare.online

# Auto-renewal (usually automatic, but verify)
sudo certbot renew --dry-run

# Restart Apache
sudo systemctl restart apache2
```

---

## Step 9: Set Up Queue Worker (Supervisor)

```bash
# Install Supervisor
sudo apt install -y supervisor

# Create queue worker config
sudo nano /etc/supervisor/conf.d/cas-worker.conf
```

Add this configuration:

```ini
[program:cas-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cas-worker:*
```

---

## Step 10: Set Up Cron for Scheduler

```bash
# Edit crontab for www-data
sudo crontab -u www-data -e
```

Add this line:

```cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 11: Database Backup (Daily)

Create backup script at `/opt/scripts/backup-db.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/mysql"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
DB_NAME="cas_private_care"
DB_USER="your_db_user"
DB_PASS="your_db_password"

mkdir -p $BACKUP_DIR
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/$DB_NAME-$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete
```

Make executable and add to cron:

```bash
sudo chmod +x /opt/scripts/backup-db.sh
sudo crontab -e
# Add: 0 2 * * * /opt/scripts/backup-db.sh
```

---

## Step 12: Security Hardening

### Firewall (UFW)

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Fail2Ban (Brute Force Protection)

```bash
sudo apt install -y fail2ban

# Create Laravel jail
sudo nano /etc/fail2ban/jail.local
```

Add:

```ini
[sshd]
enabled = true
maxretry = 5
bantime = 3600

[apache-auth]
enabled = true
maxretry = 5
bantime = 3600
```

```bash
sudo systemctl restart fail2ban
```

---

## Step 13: Monitoring

### Check Application Health

```bash
# Quick health check
curl -s https://casprivatecare.online/health | jq

# Detailed health check
curl -s https://casprivatecare.online/health/detailed | jq
```

### View Logs

```bash
# Laravel logs
tail -f /var/www/html/storage/logs/laravel.log

# Apache logs
tail -f /var/log/apache2/casprivatecare-error.log
tail -f /var/log/apache2/casprivatecare-access.log

# Queue worker logs
tail -f /var/www/html/storage/logs/worker.log
```

---

## Troubleshooting

### Permission Issues

```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R 775 /var/www/html/bootstrap/cache
```

### Clear All Caches

```bash
cd /var/www/html
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan optimize:clear
```

### Rebuild After Code Update

```bash
cd /var/www/html
git pull origin master
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci && npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo supervisorctl restart cas-worker:*
```

---

## Quick Command Reference

| Task | Command |
|------|---------|
| Restart Apache | `sudo systemctl restart apache2` |
| Restart PHP-FPM | `sudo systemctl restart php8.2-fpm` |
| Restart Queue | `sudo supervisorctl restart cas-worker:*` |
| Clear Caches | `php artisan optimize:clear` |
| View Laravel Log | `tail -f storage/logs/laravel.log` |
| Check Health | `curl https://casprivatecare.online/health` |
| Run Migrations | `php artisan migrate --force` |

---

*Last updated: January 23, 2026*

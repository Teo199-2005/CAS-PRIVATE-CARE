@echo off
echo ============================================
echo CAS Private Care - Production Setup
echo ============================================
echo.
echo This script will configure your system for production
echo with strict rate limits for security.
echo.
echo WARNING: This will enforce strict rate limits!
echo.
pause

REM Check if .env exists
if not exist ".env" (
    echo ERROR: .env file not found!
    pause
    exit /b 1
)

echo Step 1: Backing up .env file...
copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2% >nul
echo ✓ Backup created
echo.

echo Step 2: Updating environment settings...
powershell -Command "(gc .env) -replace 'APP_ENV=testing', 'APP_ENV=production' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'APP_ENV=local', 'APP_ENV=production' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'BYPASS_RATE_LIMIT=true', 'BYPASS_RATE_LIMIT=false' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'APP_DEBUG=true', 'APP_DEBUG=false' | Out-File -encoding ASCII .env"
echo ✓ Set APP_ENV=production
echo ✓ Disabled rate limit bypass
echo ✓ Disabled debug mode
echo.

echo Step 3: Clearing and caching...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
call php artisan view:clear
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo ✓ Optimized for production
echo.

echo ============================================
echo ✓ Production Setup Complete!
echo ============================================
echo.
echo Your rate limits are now:
echo - Login: 10 attempts/minute
echo - Register: 5 attempts/minute
echo - API: 60 requests/minute
echo.
echo Security measures are active.
echo.
pause


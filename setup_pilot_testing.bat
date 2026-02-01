@echo off
echo ============================================
echo CAS Private Care - Pilot Testing Setup
echo ============================================
echo.
echo This script will configure your system for pilot testing
echo with relaxed rate limits.
echo.

REM Check if .env exists
if not exist ".env" (
    echo ERROR: .env file not found!
    echo Please copy .env.example to .env first.
    pause
    exit /b 1
)

echo Step 1: Backing up .env file...
copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2% >nul
echo ✓ Backup created
echo.

echo Step 2: Updating environment settings...
powershell -Command "(gc .env) -replace 'APP_ENV=production', 'APP_ENV=testing' | Out-File -encoding ASCII .env"
echo ✓ Set APP_ENV=testing
echo.

echo Step 3: Clearing caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
call php artisan view:clear
echo ✓ Caches cleared
echo.

echo ============================================
echo ✓ Setup Complete!
echo ============================================
echo.
echo Your rate limits are now:
echo - Login: 50 attempts/minute (was 5)
echo - Register: 30 attempts/minute (was 5)
echo - API: 300 requests/minute (was 60)
echo.
echo You can now freely test switching between accounts!
echo.
echo IMPORTANT: Before going to production, run:
echo   setup_production.bat
echo.
pause


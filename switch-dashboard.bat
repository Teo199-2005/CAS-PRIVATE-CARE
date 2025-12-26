@echo off
echo CAS Website Dashboard Switcher
echo ================================
echo.
echo Current Status:
if exist "resources\js\app.js" (
    findstr /C:"SimpleDashboard" "resources\js\app.js" >nul
    if errorlevel 1 (
        echo [COMPLEX] Full-featured Vue dashboard is active
    ) else (
        echo [SIMPLE] Lightweight dashboard is active
    )
) else (
    echo [ERROR] No app.js found
)
echo.
echo Options:
echo 1. Switch to Simple Dashboard (Fast loading)
echo 2. Switch to Complex Dashboard (Full features)
echo 3. Build assets
echo 4. Exit
echo.
set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" (
    echo Switching to Simple Dashboard...
    copy "resources\js\app-simple.js" "resources\js\app.js" >nul
    copy "resources\css\app-simple.css" "resources\css\app.css" >nul
    echo [SUCCESS] Simple dashboard activated
    echo Run 'npm run build' to compile assets
) else if "%choice%"=="2" (
    echo Switching to Complex Dashboard...
    copy "resources\js\app-complex.js" "resources\js\app.js" >nul
    echo [SUCCESS] Complex dashboard activated
    echo Run 'npm run build' to compile assets
) else if "%choice%"=="3" (
    echo Building assets...
    npm run build
) else if "%choice%"=="4" (
    echo Goodbye!
    exit /b 0
) else (
    echo Invalid choice. Please try again.
)

echo.
pause
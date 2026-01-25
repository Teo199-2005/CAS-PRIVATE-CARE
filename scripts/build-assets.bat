@echo off
echo Building CAS Website Assets...
echo.

cd /d "C:\Users\Cocotantan\Downloads\--CAS WEBSITE--"

echo Installing dependencies...
call npm install

echo.
echo Building assets...
call npm run build

echo.
echo Build complete! You can now access:
echo - http://127.0.0.1:8000/caregiver/dashboard-vue (Original Vue dashboard)
echo - http://127.0.0.1:8000/caregiver/dashboard-simple (Simple working dashboard)
echo - http://127.0.0.1:8000/caregiver/dashboard-debug (Debug version)
echo.
pause
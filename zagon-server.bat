@echo off
cd /d "%~dp0"
title Merila 37.001 - Lokalni streznik

echo.
echo ========================================
echo   Merila 37.001 - Zagon lokalnega streznika
echo ========================================
echo.

php -v >nul 2>&1
if errorlevel 1 (
    echo [NAPAKA] PHP ni najden. Preverite, da je PHP v PATH.
    pause
    exit /b 1
)

echo Streznik se zaganja na http://127.0.0.1:8000
echo Za ustavitev pritisnite Ctrl+C
echo.

php artisan serve
pause

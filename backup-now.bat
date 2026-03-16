@echo off
TITLE POS-RESTO - Manual Backup
COLOR 0B
echo.
echo  =============================================
echo     Manual Backup Database - POS RESTO
echo  =============================================
echo.
cd /d "%~dp0"

set "LARAGON_PATH="
if exist "C:\laragon" set "LARAGON_PATH=C:\laragon"
if exist "D:\laragon" set "LARAGON_PATH=D:\laragon"

for /d %%D in ("%LARAGON_PATH%\bin\php\php-*") do set "PHP_BIN=%%D\php.exe"

"%PHP_BIN%" artisan db:backup

echo.
pause

@echo off
TITLE POS-RESTO - Restore Database
COLOR 0C
cls
echo.
echo  =============================================
echo     RESTORE DATABASE - POS RESTO
echo     PERHATIAN: Data saat ini akan DITIMPA!
echo  =============================================
echo.

cd /d "%~dp0"

:: Detect tools
set "LARAGON_PATH="
if exist "C:\laragon" set "LARAGON_PATH=C:\laragon"
if exist "D:\laragon" set "LARAGON_PATH=D:\laragon"

for /d %%D in ("%LARAGON_PATH%\bin\mysql\mysql-*") do set "MYSQL_BIN=%%D\bin\mysql.exe"

:: List available backups
echo Backup tersedia:
echo ----------------------------------------
for %%F in ("storage\app\backups\backup_*.sql") do (
    echo   %%~nxF
)
echo ----------------------------------------
echo.

set /p BACKUP_FILE="Masukkan nama file: "

if not exist "storage\app\backups\%BACKUP_FILE%" (
    echo.
    echo [ERROR] File tidak ditemukan!
    pause
    exit /b
)

echo.
set /p CONFIRM="YAKIN restore? Semua data sekarang akan ditimpa! (yes/no): "
if /i not "%CONFIRM%"=="yes" (
    echo Dibatalkan.
    pause
    exit /b
)

echo.
echo Restoring database...
"%MYSQL_BIN%" -u root pos_resto < "storage\app\backups\%BACKUP_FILE%"

if %errorlevel% equ 0 (
    echo.
    echo [OK] Database berhasil di-restore dari: %BACKUP_FILE%
) else (
    echo.
    echo [ERROR] Restore gagal!
)
echo.
pause

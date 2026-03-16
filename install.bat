@echo off
TITLE POS-RESTO Installer
COLOR 0A
cls
echo.
echo  =============================================
echo     POS-RESTO - AUTO INSTALLER
echo  =============================================
echo.

:: ---- Check Run as Admin ----
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Jalankan sebagai Administrator!
    echo         Klik kanan file ini, pilih "Run as administrator"
    pause
    exit /b
)

:: ---- Detect Laragon Path ----
set "LARAGON_PATH="
if exist "C:\laragon" set "LARAGON_PATH=C:\laragon"
if exist "D:\laragon" set "LARAGON_PATH=D:\laragon"

if "%LARAGON_PATH%"=="" (
    echo [ERROR] Laragon tidak ditemukan di C:\ atau D:\
    echo         Install Laragon dulu: https://laragon.org/download
    pause
    exit /b
)
echo [OK] Laragon ditemukan: %LARAGON_PATH%

:: ---- Detect PHP ----
set "PHP_BIN="
for /d %%D in ("%LARAGON_PATH%\bin\php\php-*") do set "PHP_BIN=%%D\php.exe"
if "%PHP_BIN%"=="" (
    echo [ERROR] PHP tidak ditemukan di Laragon!
    pause
    exit /b
)
echo [OK] PHP: %PHP_BIN%

:: ---- Detect MySQL ----
for /d %%D in ("%LARAGON_PATH%\bin\mysql\mysql-*") do (
    set "MYSQL_BIN=%%D\bin\mysql.exe"
    set "MYSQLDUMP_BIN=%%D\bin\mysqldump.exe"
)
echo [OK] MySQL: %MYSQL_BIN%

:: ---- Set Project Path ----
set "PROJECT_PATH=%~dp0"
cd /d "%PROJECT_PATH%"
echo [OK] Project: %PROJECT_PATH%
echo.

:: ---- Step 1: Composer Install ----
echo [1/8] Menginstall dependencies PHP...
call composer install --no-dev --optimize-autoloader --no-interaction 2>nul
if %errorlevel% neq 0 (
    "%PHP_BIN%" "%LARAGON_PATH%\bin\composer.phar" install --no-dev --optimize-autoloader --no-interaction 2>nul
)
echo      Selesai.
echo.

:: ---- Step 2: NPM Install ----
where npm >nul 2>&1
if %errorlevel% equ 0 (
    echo [2/8] Menginstall dependencies Node.js...
    call npm install --silent 2>nul
    echo      Selesai.
    echo.
    echo [3/8] Build frontend assets...
    call npm run build 2>nul
    echo      Selesai.
) else (
    echo [2/8] Node.js tidak ditemukan, skip npm install.
    echo [3/8] Skip build assets.
)
echo.

:: ---- Step 3: Setup .env ----
echo [4/8] Menyiapkan environment...
if not exist .env (
    copy .env.example .env >nul
    echo      .env dibuat dari .env.example
) else (
    echo      .env sudah ada, skip.
)
echo.

:: ---- Step 4: Generate Key ----
echo [5/8] Generate application key...
"%PHP_BIN%" artisan key:generate --force --no-interaction 2>nul
echo      Selesai.
echo.

:: ---- Step 5: Create Database ----
echo [6/8] Membuat database...
"%MYSQL_BIN%" -u root -e "CREATE DATABASE IF NOT EXISTS pos_resto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
echo      Selesai.
echo.

:: ---- Step 6: Migrate ----
echo [7/8] Migrasi database...
"%PHP_BIN%" artisan migrate --force --no-interaction
echo      Selesai.
echo.

:: ---- Step 7: Optimize ----
echo [8/8] Optimasi aplikasi...
"%PHP_BIN%" artisan storage:link 2>nul
"%PHP_BIN%" artisan config:cache 2>nul
"%PHP_BIN%" artisan route:cache 2>nul
"%PHP_BIN%" artisan view:cache 2>nul
echo      Selesai.
echo.

:: ---- Create Backup Folder ----
if not exist "%PROJECT_PATH%storage\app\backups" (
    mkdir "%PROJECT_PATH%storage\app\backups"
)

:: ---- Set .env to production ----
"%PHP_BIN%" -r "file_put_contents('.env', preg_replace('/APP_ENV=.*/', 'APP_ENV=production', file_get_contents('.env')));" 2>nul
"%PHP_BIN%" -r "file_put_contents('.env', preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', file_get_contents('.env')));" 2>nul

echo.
echo  =============================================
echo     INSTALASI BERHASIL!
echo.
echo     Langkah selanjutnya:
echo     1. Edit file .env jika perlu
echo     2. Jalankan setup-service.bat
echo        (untuk auto-start + auto backup)
echo     3. Buka http://localhost/POS-RESTO/public
echo  =============================================
echo.
pause

@echo off
TITLE POS-RESTO Service Setup
COLOR 0B
cls
echo.
echo  =============================================
echo     SETUP AUTO-START + AUTO BACKUP
echo     POS-RESTO
echo  =============================================
echo.

:: ---- Check Admin ----
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Jalankan sebagai Administrator!
    echo         Klik kanan file ini, pilih "Run as administrator"
    pause
    exit /b
)

:: ---- Detect Laragon ----
set "LARAGON_PATH="
if exist "C:\laragon" set "LARAGON_PATH=C:\laragon"
if exist "D:\laragon" set "LARAGON_PATH=D:\laragon"

if "%LARAGON_PATH%"=="" (
    echo [ERROR] Laragon tidak ditemukan!
    pause
    exit /b
)
echo [OK] Laragon: %LARAGON_PATH%

set "PROJECT_PATH=%~dp0"

:: Detect PHP
for /d %%D in ("%LARAGON_PATH%\bin\php\php-*") do set "PHP_BIN=%%D\php.exe"
echo [OK] PHP: %PHP_BIN%
echo.

:: ======================================
:: 1. LARAGON AUTO-START saat Windows boot
:: ======================================
echo [1/4] Setup Laragon auto-start saat komputer nyala...

:: Via Registry
reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "Laragon" /t REG_SZ /d "\"%LARAGON_PATH%\laragon.exe\" --minimized" /f >nul 2>&1

:: Via Task Scheduler (lebih reliable)
schtasks /delete /tn "POS-RESTO-Laragon-AutoStart" /f >nul 2>&1
schtasks /create /tn "POS-RESTO-Laragon-AutoStart" ^
    /tr "\"%LARAGON_PATH%\laragon.exe\" --minimized" ^
    /sc onlogon /rl highest /f >nul 2>&1

if %errorlevel% equ 0 (
    echo      [OK] Laragon auto-start saat login
) else (
    echo      [WARN] Task scheduler gagal, fallback ke Registry
)
echo.

:: ======================================
:: 2. LARAGON CONFIG: auto start services
:: ======================================
echo [2/4] Konfigurasi Laragon auto-start Apache + MySQL...

set "LARAGON_INI=%LARAGON_PATH%\usr\laragon.ini"
if exist "%LARAGON_INI%" (
    :: Backup config
    copy "%LARAGON_INI%" "%LARAGON_INI%.bak" >nul 2>&1
    
    :: Set auto-start options
    powershell -Command "(Get-Content '%LARAGON_INI%') -replace 'AutoStartAll=.*', 'AutoStartAll=1' | Set-Content '%LARAGON_INI%'" 2>nul
    powershell -Command "(Get-Content '%LARAGON_INI%') -replace 'MinimizeToTray=.*', 'MinimizeToTray=1' | Set-Content '%LARAGON_INI%'" 2>nul
    powershell -Command "(Get-Content '%LARAGON_INI%') -replace 'StartAllOnBoot=.*', 'StartAllOnBoot=1' | Set-Content '%LARAGON_INI%'" 2>nul
    
    echo      [OK] Laragon auto-start Apache + MySQL enabled
) else (
    echo      [WARN] laragon.ini tidak ditemukan
    echo      Set manual: Laragon ^> Preferences ^> Start All Automatically
)
echo.

:: ======================================
:: 3. LARAVEL SCHEDULER (Auto Backup)
:: ======================================
echo [3/4] Setup auto backup scheduler (silent)...

:: Remove old task
schtasks /delete /tn "POS-RESTO-Scheduler" /f >nul 2>&1

:: Generate VBS script agar scheduler jalan tanpa window muncul
set "VBS_FILE=%PROJECT_PATH%scheduler-run.vbs"
echo Set WshShell = CreateObject("WScript.Shell") > "%VBS_FILE%"
echo WshShell.CurrentDirectory = "%PROJECT_PATH:~0,-1%" >> "%VBS_FILE%"
echo WshShell.Run """%PHP_BIN%"" ""%PROJECT_PATH%artisan"" schedule:run --no-interaction", 0, True >> "%VBS_FILE%"
echo Set WshShell = Nothing >> "%VBS_FILE%"

:: Create task: setiap 1 menit jalankan via wscript (silent, no window)
schtasks /create /tn "POS-RESTO-Scheduler" ^
    /tr "wscript.exe \"%VBS_FILE%\"" ^
    /sc minute /mo 1 /f >nul 2>&1

if %errorlevel% equ 0 (
    echo      [OK] Auto backup scheduler aktif (silent mode)
    echo           - Tidak ada window muncul tiap menit
    echo           - Jadwal backup sesuai Settings
) else (
    echo      [ERROR] Gagal setup scheduler
)
echo.

:: ======================================
:: 4. SHORTCUT di Desktop
:: ======================================
echo [4/4] Membuat shortcut di Desktop...

:: Buat VBS untuk shortcut
echo Set WshShell = WScript.CreateObject("WScript.Shell") > "%TEMP%\pos_shortcut.vbs"
echo Set lnk = WshShell.CreateShortcut("%USERPROFILE%\Desktop\POS-RESTO.lnk") >> "%TEMP%\pos_shortcut.vbs"
echo lnk.TargetPath = "http://localhost/POS-RESTO/public" >> "%TEMP%\pos_shortcut.vbs"
echo lnk.Description = "POS RESTO" >> "%TEMP%\pos_shortcut.vbs"
echo lnk.IconLocation = "C:\Windows\System32\shell32.dll, 14" >> "%TEMP%\pos_shortcut.vbs"
echo lnk.Save >> "%TEMP%\pos_shortcut.vbs"

cscript //nologo "%TEMP%\pos_shortcut.vbs" >nul 2>&1
del "%TEMP%\pos_shortcut.vbs" >nul 2>&1
echo      [OK] Shortcut "POS-RESTO" di Desktop
echo.

:: ======================================
:: Create backup folder
:: ======================================
if not exist "%PROJECT_PATH%storage\app\backups" (
    mkdir "%PROJECT_PATH%storage\app\backups"
)

:: ======================================
:: Test backup
:: ======================================
echo  Test backup pertama...
"%PHP_BIN%" "%PROJECT_PATH%artisan" db:backup
echo.

echo.
echo  =============================================
echo     SETUP SELESAI!
echo.
echo     Yang berjalan otomatis saat komputer nyala:
echo     - Laragon start otomatis (minimized)
echo     - Apache + MySQL auto-start
echo     - Auto backup database (12:00 + 23:55)
echo.
echo     Shortcut POS-RESTO ada di Desktop
echo  =============================================
echo.
pause

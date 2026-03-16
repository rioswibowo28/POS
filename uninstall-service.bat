@echo off
TITLE POS-RESTO - Uninstall Services
COLOR 0C
cls
echo.
echo  =============================================
echo     UNINSTALL SERVICES - POS RESTO
echo  =============================================
echo.

net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Jalankan sebagai Administrator!
    pause
    exit /b
)

echo Menghapus scheduled tasks...
schtasks /delete /tn "POS-RESTO-Laragon-AutoStart" /f >nul 2>&1
echo   [OK] POS-RESTO-Laragon-AutoStart dihapus
schtasks /delete /tn "POS-RESTO-Scheduler" /f >nul 2>&1
echo   [OK] POS-RESTO-Scheduler dihapus

echo.
echo Menghapus registry auto-start...
reg delete "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "Laragon" /f >nul 2>&1
echo   [OK] Laragon auto-start registry dihapus

echo.
echo  =============================================
echo     Semua service POS-RESTO telah dihapus.
echo     Laragon tidak lagi auto-start.
echo     Auto backup tidak lagi aktif.
echo  =============================================
echo.
pause

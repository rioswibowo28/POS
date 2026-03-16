Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = "D:\laragon\www\POS-RESTO"
WshShell.Run "cmd /c ""D:\laragon\bin\php\php-8.4.7-Win32-vs17-x64\php.exe"" ""D:\laragon\www\POS-RESTO\artisan"" schedule:run --no-interaction >> ""D:\laragon\www\POS-RESTO\storage\logs\scheduler.log"" 2>&1", 0, True
Set WshShell = Nothing

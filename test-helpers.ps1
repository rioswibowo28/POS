# Test License Config Helper
Write-Host "=== Testing License Config Helper ===" -ForegroundColor Cyan
Write-Host ""

php test-helpers.php

Write-Host ""
Write-Host "=== Press any key to exit ===" -ForegroundColor Yellow
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

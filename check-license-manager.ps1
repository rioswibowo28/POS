# Check License Manager Connection
# This script helps diagnose License Manager connection issues

Write-Host "=== License Manager Connection Checker ===" -ForegroundColor Cyan
Write-Host ""

# 1. Check License Server URL
Write-Host "1. Reading License Server URL from .env..." -ForegroundColor Yellow
$envPath = ".env"
if (Test-Path $envPath) {
    $licenseServerUrl = (Get-Content $envPath | Where-Object { $_ -match "^LICENSE_SERVER_URL=" }) -replace "^LICENSE_SERVER_URL=", "" -replace '"', ''
    if ($licenseServerUrl) {
        Write-Host "   License Server URL: $licenseServerUrl" -ForegroundColor Green
    } else {
        Write-Host "   ⚠ LICENSE_SERVER_URL not found in .env" -ForegroundColor Red
        $licenseServerUrl = "http://localhost:8000"
        Write-Host "   Using default: $licenseServerUrl" -ForegroundColor Yellow
    }
} else {
    Write-Host "   ⚠ .env file not found!" -ForegroundColor Red
    exit 1
}

Write-Host ""

# 2. Test if License Manager is running
Write-Host "2. Testing connection to License Manager..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$licenseServerUrl" -Method GET -TimeoutSec 5 -ErrorAction Stop
    Write-Host "   ✅ License Manager is running! (HTTP $($response.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Cannot connect to License Manager!" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "   Troubleshooting:" -ForegroundColor Yellow
    Write-Host "   - Make sure License Manager is running" -ForegroundColor Gray
    Write-Host "   - Check if URL is correct: $licenseServerUrl" -ForegroundColor Gray
    Write-Host "   - Try running: cd path/to/LICENSE-MANAGER && php artisan serve" -ForegroundColor Gray
    exit 1
}

Write-Host ""

# 3. Test License API endpoint
Write-Host "3. Testing License API endpoint..." -ForegroundColor Yellow
$apiEndpoint = "$licenseServerUrl/api/license/verify"
Write-Host "   Endpoint: $apiEndpoint" -ForegroundColor Gray

# Prompt for license key
Write-Host ""
$licenseKey = Read-Host "   Enter License Key to test (or press Enter to skip)"

if ($licenseKey) {
    try {
        $body = @{
            license_key = $licenseKey
        } | ConvertTo-Json

        $apiResponse = Invoke-RestMethod -Uri $apiEndpoint -Method POST -Body $body -ContentType "application/json" -ErrorAction Stop
        
        Write-Host ""
        if ($apiResponse.valid -eq $true) {
            Write-Host "   ✅ License is VALID!" -ForegroundColor Green
            Write-Host "   License Type: $($apiResponse.license.license_type)" -ForegroundColor Cyan
            Write-Host "   Status: $($apiResponse.license.status)" -ForegroundColor Cyan
            Write-Host "   Customer: $($apiResponse.license.customer_name)" -ForegroundColor Cyan
        } else {
            Write-Host "   ❌ License verification failed!" -ForegroundColor Red
            Write-Host "   Message: $($apiResponse.message)" -ForegroundColor Yellow
        }
    } catch {
        Write-Host ""
        Write-Host "   ❌ API Error!" -ForegroundColor Red
        Write-Host "   Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Yellow
        
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            $reader.Close()
            
            try {
                $errorData = $responseBody | ConvertFrom-Json
                Write-Host "   Message: $($errorData.message)" -ForegroundColor Yellow
            } catch {
                Write-Host "   Response: $responseBody" -ForegroundColor Yellow
            }
        }
    }
} else {
    Write-Host "   ⏭ Skipped license key test" -ForegroundColor Gray
}

Write-Host ""
Write-Host "=== Check Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Make sure License Manager is running" -ForegroundColor Gray
Write-Host "2. Create a license in License Manager admin panel" -ForegroundColor Gray
Write-Host "3. Copy the license key to POS-RESTO settings" -ForegroundColor Gray
Write-Host "4. Test connection again in Settings page" -ForegroundColor Gray
Write-Host ""

pause

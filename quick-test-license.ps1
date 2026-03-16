# Quick Test License Manager Connection
Write-Host "Testing License Manager Connection..." -ForegroundColor Cyan
Write-Host ""

# Test both common ports
$ports = @(8000, 8001)

foreach ($port in $ports) {
    $url = "http://localhost:$port"
    Write-Host "Testing $url..." -ForegroundColor Yellow
    
    try {
        $response = Invoke-WebRequest -Uri $url -Method GET -TimeoutSec 3 -ErrorAction Stop
        Write-Host "  ✅ SUCCESS - License Manager is running on port $port" -ForegroundColor Green
        Write-Host "  Status Code: $($response.StatusCode)" -ForegroundColor Gray
        Write-Host ""
        
        # Test API endpoint
        Write-Host "Testing API endpoint at $url/api/license/verify..." -ForegroundColor Yellow
        $testBody = @{
            license_key = "3953-DABC-C601-1472"
        } | ConvertTo-Json
        
        try {
            $apiResponse = Invoke-RestMethod -Uri "$url/api/license/verify" -Method POST -Body $testBody -ContentType "application/json" -ErrorAction Stop
            
            if ($apiResponse.valid -eq $true) {
                Write-Host "  ✅ API works! License is VALID" -ForegroundColor Green
                Write-Host "  License Type: $($apiResponse.license.license_type)" -ForegroundColor Cyan
                Write-Host "  Status: $($apiResponse.license.status)" -ForegroundColor Cyan
                Write-Host "  Customer: $($apiResponse.license.customer_name)" -ForegroundColor Cyan
            } else {
                Write-Host "  ⚠ API works but license verification failed" -ForegroundColor Yellow
                Write-Host "  Message: $($apiResponse.message)" -ForegroundColor Gray
            }
        } catch {
            $statusCode = $_.Exception.Response.StatusCode.value__
            Write-Host "  ⚠ API returned error (HTTP $statusCode)" -ForegroundColor Yellow
            
            if ($statusCode -eq 404) {
                Write-Host "  License not found in database" -ForegroundColor Gray
            }
        }
        
        Write-Host ""
        Write-Host "✅ USE THIS URL IN SETTINGS: $url" -ForegroundColor Green
        Write-Host ""
        
    } catch {
        Write-Host "  ❌ FAILED - No service running on port $port" -ForegroundColor Red
        Write-Host ""
    }
}

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. If License Manager is running, use the URL shown above" -ForegroundColor Gray
Write-Host "2. If not running, start it with: php artisan serve --port=8001" -ForegroundColor Gray
Write-Host "3. Make sure the license key exists in License Manager database" -ForegroundColor Gray
Write-Host ""

# Test Root API
Write-Host "Testing Root API Endpoint" -ForegroundColor Cyan
Write-Host ""

$response = Invoke-RestMethod -Uri "http://192.168.40.221:8000/api" -Method GET

Write-Host "API Name: $($response.message)" -ForegroundColor Green
Write-Host "Version: $($response.version)" -ForegroundColor Gray
Write-Host ""

Write-Host "Available Endpoints:" -ForegroundColor Yellow
Write-Host ""

Write-Host "AUTH:" -ForegroundColor Cyan
$response.endpoints.auth.PSObject.Properties | ForEach-Object {
    Write-Host "  $($_.Name) - $($_.Value)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "CATEGORIES:" -ForegroundColor Cyan
$response.endpoints.categories.PSObject.Properties | ForEach-Object {
    Write-Host "  $($_.Name) - $($_.Value)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "PRODUCTS:" -ForegroundColor Cyan
$response.endpoints.products.PSObject.Properties | ForEach-Object {
    Write-Host "  $($_.Name) - $($_.Value)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "TABLES:" -ForegroundColor Cyan
$response.endpoints.tables.PSObject.Properties | ForEach-Object {
    Write-Host "  $($_.Name) - $($_.Value)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "ORDERS:" -ForegroundColor Cyan
$response.endpoints.orders.PSObject.Properties | ForEach-Object {
    Write-Host "  $($_.Name) - $($_.Value)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "PAYMENTS:" -ForegroundColor Cyan
$response.endpoints.payments.PSObject.Properties | ForEach-Object {
    Write-Host "  $($_.Name) - $($_.Value)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Test Endpoints:" -ForegroundColor Yellow
Write-Host "  Login: $($response.test_endpoints.login)" -ForegroundColor Gray
Write-Host "  Categories: $($response.test_endpoints.categories)" -ForegroundColor Gray
Write-Host "  Products: $($response.test_endpoints.products)" -ForegroundColor Gray
Write-Host "  Tables: $($response.test_endpoints.tables)" -ForegroundColor Gray
Write-Host ""
Write-Host "Authentication Type: $($response.authentication.type)" -ForegroundColor Yellow
Write-Host "Header: $($response.authentication.header)" -ForegroundColor Gray

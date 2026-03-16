# Quick API Test
$baseUrl = "http://192.168.40.221:8000/api"

Write-Host "Testing API: $baseUrl" -ForegroundColor Cyan
Write-Host ""

# Login
Write-Host "Login..." -ForegroundColor Yellow
$loginBody = '{"email":"admin@posresto.com","password":"password"}'
$login = Invoke-RestMethod -Uri "$baseUrl/login" -Method POST -Body $loginBody -ContentType "application/json"

if ($login.success) {
    Write-Host "Login OK!" -ForegroundColor Green
    $token = $login.data.access_token
    Write-Host "User: $($login.data.user.name)"
    
    $headers = @{
        "Authorization" = "Bearer $token"
    }
    
    Write-Host ""
    Write-Host "Get Categories..." -ForegroundColor Yellow
    $cat = Invoke-RestMethod -Uri "$baseUrl/categories" -Headers $headers
    Write-Host "Categories: $($cat.data.Count)"
    
    Write-Host ""
    Write-Host "Get Products..." -ForegroundColor Yellow
    $prod = Invoke-RestMethod -Uri "$baseUrl/products" -Headers $headers
    Write-Host "Products: $($prod.data.Count)"
    
    Write-Host ""
    Write-Host "Get Tables..." -ForegroundColor Yellow
    $tables = Invoke-RestMethod -Uri "$baseUrl/tables" -Headers $headers
    Write-Host "Tables: $($tables.data.Count)"
    
    Write-Host ""
    Write-Host "All tests passed!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your token:" -ForegroundColor Yellow
    Write-Host $token
} else {
    Write-Host "Login failed!" -ForegroundColor Red
}

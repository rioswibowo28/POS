# Test POS Resto API - Custom Server
# Server: 192.168.40.221:8000

$baseUrl = "http://192.168.40.221:8000/api"

Write-Host "=== POS RESTO API TEST ===" -ForegroundColor Cyan
Write-Host "Server: $baseUrl" -ForegroundColor Gray
Write-Host ""

# Test 1: Login
Write-Host "1. Testing Login..." -ForegroundColor Yellow
try {
    $loginBody = @{
        email = "admin@posresto.com"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/login" -Method POST -Body $loginBody -ContentType "application/json"
    
    if ($loginResponse.success) {
        Write-Host "Login berhasil!" -ForegroundColor Green
        $token = $loginResponse.data.access_token
        Write-Host "User: $($loginResponse.data.user.name)" -ForegroundColor Gray
        Write-Host "Token: $($token.Substring(0, 50))..." -ForegroundColor Gray
        
        # Setup headers
        $headers = @{
            "Authorization" = "Bearer $token"
            "Accept" = "application/json"
        }
    } else {
        Write-Host "Login gagal!" -ForegroundColor Red
        exit
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response: $responseBody" -ForegroundColor Red
    }
    exit
}

Write-Host ""

# Test 2: Get User Profile
Write-Host "2. Testing Get User Profile..." -ForegroundColor Yellow
try {
    $me = Invoke-RestMethod -Uri "$baseUrl/me" -Method GET -Headers $headers
    Write-Host "✓ User: $($me.data.name) ($($me.data.email))" -ForegroundColor Green
    Write-Host "  Roles: $($me.data.roles.name -join ', ')" -ForegroundColor Gray
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 3: Get Categories
Write-Host "3. Testing Get Categories..." -ForegroundColor Yellow
try {
    $categories = Invoke-RestMethod -Uri "$baseUrl/categories" -Method GET -Headers $headers
    Write-Host "✓ Total Categories: $($categories.data.Count)" -ForegroundColor Green
    $categories.data | ForEach-Object {
        Write-Host "  - $($_.name)" -ForegroundColor Gray
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 4: Get Products
Write-Host "4. Testing Get Products..." -ForegroundColor Yellow
try {
    $products = Invoke-RestMethod -Uri "$baseUrl/products" -Method GET -Headers $headers
    Write-Host "✓ Total Products: $($products.data.Count)" -ForegroundColor Green
    $products.data | Select-Object -First 5 | ForEach-Object {
        Write-Host "  - $($_.name) - Rp $($_.price)" -ForegroundColor Gray
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 5: Get Tables
Write-Host "5. Testing Get Tables..." -ForegroundColor Yellow
try {
    $tables = Invoke-RestMethod -Uri "$baseUrl/tables" -Method GET -Headers $headers
    Write-Host "✓ Total Tables: $($tables.data.Count)" -ForegroundColor Green
    $tables.data | Select-Object -First 5 | ForEach-Object {
        Write-Host "  - Table $($_.number) (Capacity: $($_.capacity)) - Status: $($_.status)" -ForegroundColor Gray
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 6: Get Active Orders
Write-Host "6. Testing Get Active Orders..." -ForegroundColor Yellow
try {
    $orders = Invoke-RestMethod -Uri "$baseUrl/orders" -Method GET -Headers $headers
    Write-Host "✓ Total Active Orders: $($orders.data.Count)" -ForegroundColor Green
    if ($orders.data.Count -gt 0) {
        $orders.data | ForEach-Object {
            Write-Host "  - Order #$($_.order_number) - Rp $($_.total)" -ForegroundColor Gray
        }
    } else {
        Write-Host "  (Tidak ada order aktif)" -ForegroundColor Gray
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== TEST SELESAI ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Simpan token ini untuk test selanjutnya:" -ForegroundColor Yellow
Write-Host $token -ForegroundColor White
Write-Host ""
Write-Host "Contoh penggunaan di Postman atau tool lain:" -ForegroundColor Yellow
Write-Host "Authorization: Bearer [TOKEN]" -ForegroundColor Gray

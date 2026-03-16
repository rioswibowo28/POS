# Test API Endpoints

## 1. Test Login (Public Route)

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@posresto.com\",\"password\":\"password\"}"
```

Atau dengan PowerShell:
```powershell
$body = @{
    email = "admin@posresto.com"
    password = "password"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method POST -Body $body -ContentType "application/json"
```

## 2. Test Register (Public Route)

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"password\",\"password_confirmation\":\"password\"}"
```

PowerShell:
```powershell
$body = @{
    name = "Test User"
    email = "test@example.com"
    password = "password"
    password_confirmation = "password"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/register" -Method POST -Body $body -ContentType "application/json"
```

## 3. Test Get Categories (Protected Route)

Pertama login dulu untuk dapat token, lalu:

```bash
curl -X GET http://localhost:8000/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

PowerShell:
```powershell
$token = "YOUR_TOKEN_HERE"
$headers = @{
    "Authorization" = "Bearer $token"
}

Invoke-RestMethod -Uri "http://localhost:8000/api/categories" -Method GET -Headers $headers
```

## 4. Test Get Products

```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

PowerShell:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/products" -Method GET -Headers $headers
```

## 5. Test Get Tables

```bash
curl -X GET http://localhost:8000/api/tables \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

PowerShell:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/tables" -Method GET -Headers $headers
```

## Complete Test Flow (PowerShell)

```powershell
# 1. Login
$loginBody = @{
    email = "admin@posresto.com"
    password = "password"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method POST -Body $loginBody -ContentType "application/json"
$token = $loginResponse.data.access_token

Write-Host "Token: $token"

# 2. Setup headers with token
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

# 3. Get Categories
Write-Host "`n=== Categories ==="
$categories = Invoke-RestMethod -Uri "http://localhost:8000/api/categories" -Method GET -Headers $headers
$categories | ConvertTo-Json

# 4. Get Products
Write-Host "`n=== Products ==="
$products = Invoke-RestMethod -Uri "http://localhost:8000/api/products" -Method GET -Headers $headers
$products | ConvertTo-Json

# 5. Get Tables
Write-Host "`n=== Tables ==="
$tables = Invoke-RestMethod -Uri "http://localhost:8000/api/tables" -Method GET -Headers $headers
$tables | ConvertTo-Json

# 6. Get Active Orders
Write-Host "`n=== Active Orders ==="
$orders = Invoke-RestMethod -Uri "http://localhost:8000/api/orders" -Method GET -Headers $headers
$orders | ConvertTo-Json
```

## Create Order Example (PowerShell)

```powershell
# Assuming you already have $token and $headers from login above

$orderBody = @{
    table_id = 1
    customer_name = "John Doe"
    customer_phone = "08123456789"
    type = "dine_in"
    tax = 2500
    discount = 0
    notes = "Extra pedas"
    items = @(
        @{
            product_id = 1
            product_variant_id = $null
            name = "Nasi Goreng Spesial"
            price = 25000
            quantity = 2
            modifiers = @(
                @{
                    name = "Extra Telur"
                    price = 5000
                }
            )
            notes = "Tidak pakai lombok"
        },
        @{
            product_id = 2
            name = "Es Teh"
            price = 5000
            quantity = 2
        }
    )
} | ConvertTo-Json -Depth 10

$order = Invoke-RestMethod -Uri "http://localhost:8000/api/orders" -Method POST -Body $orderBody -Headers $headers -ContentType "application/json"
$order | ConvertTo-Json -Depth 10
```

## Process Payment Example (PowerShell)

```powershell
# Assuming order created with id = 1

$paymentBody = @{
    order_id = 1
    method = "cash"
    amount = 55000
    received_amount = 60000
    notes = "Payment via cash"
} | ConvertTo-Json

$payment = Invoke-RestMethod -Uri "http://localhost:8000/api/payments/process" -Method POST -Body $paymentBody -Headers $headers -ContentType "application/json"
$payment | ConvertTo-Json
```

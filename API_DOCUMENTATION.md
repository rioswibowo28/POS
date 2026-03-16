# POS RESTO - Point of Sale for Restaurant

Sistem Point of Sale (POS) untuk restoran dengan fitur lengkap menggunakan Laravel 11, JWT Authentication, dan Spatie Permission.

## Fitur Utama

- 🔐 **Autentikasi JWT** - Login, Register, Logout, Refresh Token
- 👥 **Manajemen User & Role** - Admin, Cashier, Kitchen
- 📦 **Manajemen Kategori** - CRUD kategori produk
- 🍕 **Manajemen Produk** - CRUD produk dengan variant dan modifier
- 🪑 **Manajemen Meja** - Status meja (Available, Occupied, Reserved)
- 🛒 **Manajemen Order** - Dine-in, Takeaway, Delivery
- 💳 **Manajemen Pembayaran** - Cash, Card, E-Wallet, QRIS, Bank Transfer
- 📊 **Inventory Management** - Tracking stok produk
- 📈 **Laporan** - Laporan penjualan harian

## Tech Stack

- **Framework**: Laravel 11
- **Authentication**: JWT (tymon/jwt-auth)
- **Authorization**: Spatie Permission
- **Database**: MySQL
- **Architecture**: Repository Pattern & Service Layer

## Instalasi

1. **Clone atau setup project**
```bash
cd d:\laragon\www\POS-RESTO
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. **Konfigurasi Database**
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_resto
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run Migration & Seeder**
```bash
php artisan migrate:fresh --seed
```

6. **Start Server**
```bash
php artisan serve
```

API akan berjalan di: `http://localhost:8000/api`

## Default Users

Setelah seeding, tersedia user default:

| Email | Password | Role |
|-------|----------|------|
| admin@posresto.com | password | Admin |
| cashier@posresto.com | password | Cashier |
| kitchen@posresto.com | password | Kitchen |

## API Documentation

Base URL: `http://localhost:8000/api`

### Authentication

#### Register
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "admin@posresto.com",
  "password": "password"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer"
  },
  "message": "Login successful."
}
```

#### Get User Profile
```http
GET /me
Authorization: Bearer {token}
```

#### Logout
```http
POST /logout
Authorization: Bearer {token}
```

#### Refresh Token
```http
POST /refresh
Authorization: Bearer {token}
```

---

### Categories

#### Get All Categories
```http
GET /categories
Authorization: Bearer {token}
```

#### Get Active Categories
```http
GET /categories/active
Authorization: Bearer {token}
```

#### Create Category
```http
POST /categories
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "name": "Makanan",
  "description": "Menu makanan utama",
  "image": (file),
  "is_active": true
}
```

#### Update Category
```http
PUT /categories/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "name": "Makanan Updated",
  "is_active": true
}
```

#### Delete Category
```http
DELETE /categories/{id}
Authorization: Bearer {token}
```

---

### Products

#### Get All Products
```http
GET /products
Authorization: Bearer {token}
```

#### Get Available Products
```http
GET /products/available
Authorization: Bearer {token}
```

#### Get Products by Category
```http
GET /products/category/{categoryId}
Authorization: Bearer {token}
```

#### Search Products
```http
POST /products/search
Authorization: Bearer {token}
Content-Type: application/json

{
  "keyword": "nasi"
}
```

#### Create Product
```http
POST /products
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "category_id": 1,
  "name": "Nasi Goreng",
  "description": "Nasi goreng spesial",
  "price": 25000,
  "cost": 15000,
  "sku": "NG001",
  "image": (file),
  "is_available": true,
  "is_active": true
}
```

---

### Tables

#### Get All Tables
```http
GET /tables
Authorization: Bearer {token}
```

#### Get Available Tables
```http
GET /tables/available
Authorization: Bearer {token}
```

#### Get Occupied Tables
```http
GET /tables/occupied
Authorization: Bearer {token}
```

#### Create Table
```http
POST /tables
Authorization: Bearer {token}
Content-Type: application/json

{
  "number": "1",
  "capacity": 4,
  "is_active": true
}
```

#### Update Table Status
```http
PUT /tables/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "occupied"
}
```

---

### Orders

#### Get Active Orders
```http
GET /orders
Authorization: Bearer {token}
```

#### Get Today Orders
```http
GET /orders/today
Authorization: Bearer {token}
```

#### Get Completed Orders
```http
GET /orders/completed?start_date=2026-02-01&end_date=2026-02-11
Authorization: Bearer {token}
```

#### Get Order by Table
```http
GET /orders/table/{tableId}
Authorization: Bearer {token}
```

#### Get Order by Order Number
```http
GET /orders/number/{orderNumber}
Authorization: Bearer {token}
```

#### Create Order
```http
POST /orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "table_id": 1,
  "customer_name": "John Doe",
  "customer_phone": "08123456789",
  "type": "dine_in",
  "tax": 2500,
  "discount": 0,
  "notes": "Extra pedas",
  "items": [
    {
      "product_id": 1,
      "product_variant_id": null,
      "name": "Nasi Goreng Spesial",
      "price": 25000,
      "quantity": 2,
      "modifiers": [
        {
          "name": "Extra Telur",
          "price": 5000
        }
      ],
      "notes": "Tidak pakai lombok"
    }
  ]
}
```

#### Add Item to Order
```http
POST /orders/{orderId}/items
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 2,
  "name": "Es Teh Manis",
  "price": 5000,
  "quantity": 2
}
```

#### Update Order Item
```http
PUT /orders/items/{itemId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 3,
  "notes": "Less sugar"
}
```

#### Remove Order Item
```http
DELETE /orders/items/{itemId}
Authorization: Bearer {token}
```

#### Update Order Status
```http
PUT /orders/{orderId}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "completed"
}
```

#### Cancel Order
```http
POST /orders/{orderId}/cancel
Authorization: Bearer {token}
```

---

### Payments

#### Get Payments by Order
```http
GET /payments/order/{orderId}
Authorization: Bearer {token}
```

#### Get Today Payments
```http
GET /payments/today
Authorization: Bearer {token}
```

#### Process Payment
```http
POST /payments/process
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 1,
  "method": "cash",
  "amount": 55000,
  "received_amount": 60000,
  "notes": "Payment via cash"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_id": 1,
    "payment_number": "PAY-20260211-0001",
    "method": "cash",
    "status": "completed",
    "amount": 55000,
    "received_amount": 60000,
    "change_amount": 5000,
    ...
  },
  "message": "Payment processed successfully."
}
```

#### Void Payment
```http
POST /payments/{paymentId}/void
Authorization: Bearer {token}
```

---

## Enums

### Order Type
- `dine_in` - Makan di tempat
- `takeaway` - Bawa pulang
- `delivery` - Delivery

### Order Status
- `pending` - Menunggu
- `processing` - Diproses
- `completed` - Selesai
- `cancelled` - Dibatalkan

### Payment Method
- `cash` - Tunai
- `card` - Kartu Kredit/Debit
- `e_wallet` - E-Wallet (GoPay, OVO, Dana, dll)
- `qris` - QRIS
- `bank_transfer` - Transfer Bank

### Payment Status
- `pending` - Menunggu
- `completed` - Selesai
- `failed` - Gagal

### Table Status
- `available` - Tersedia
- `occupied` - Terisi
- `reserved` - Dipesan

---

## Database Structure

### Tables
1. **users** - User management
2. **roles & permissions** - Role & permission (Spatie)
3. **categories** - Product categories
4. **products** - Products
5. **product_variants** - Product variants
6. **product_modifiers** - Product modifiers
7. **tables** - Restaurant tables
8. **orders** - Orders
9. **order_items** - Order items
10. **payments** - Payments
11. **inventories** - Stock management

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── AuthController.php
│           ├── CategoryController.php
│           ├── ProductController.php
│           ├── TableController.php
│           ├── OrderController.php
│           └── PaymentController.php
├── Models/
│   ├── Category.php
│   ├── Product.php
│   ├── ProductVariant.php
│   ├── ProductModifier.php
│   ├── Table.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── Inventory.php
│   └── User.php
├── Repositories/
│   ├── BaseRepository.php
│   ├── CategoryRepository.php
│   ├── ProductRepository.php
│   ├── TableRepository.php
│   ├── OrderRepository.php
│   ├── PaymentRepository.php
│   └── InventoryRepository.php
├── Services/
│   ├── CategoryService.php
│   ├── ProductService.php
│   ├── TableService.php
│   ├── OrderService.php
│   └── PaymentService.php
└── Enums/
    ├── OrderType.php
    ├── OrderStatus.php
    ├── PaymentMethod.php
    ├── PaymentStatus.php
    └── TableStatus.php
```

---

## Testing dengan Postman

1. Import collection Postman (jika ada)
2. Set environment variable:
   - `base_url`: `http://localhost:8000/api`
   - `token`: Dapatkan dari response login

3. Flow testing:
   - Login sebagai admin
   - Buat order baru
   - Tambah item ke order
   - Process payment
   - Check order completed

---

## License

MIT License

---

## Author

Developed with ❤️ for Restaurant Management

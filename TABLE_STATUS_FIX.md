# Table Status Fix

## Masalah
Table masih menunjukkan status "in use" (occupied) meskipun order sudah dihapus dari database.

## Penyebab
Aplikasi menggunakan **Soft Delete** untuk model Order. Status table tidak otomatis terupdate ketika:
1. Order dihapus langsung dari database (DELETE atau UPDATE deleted_at)
2. Data dimanipulasi di luar aplikasi

## Solusi

### 1. Manual Fix (Quick Fix)
Jalankan command untuk sync semua table status:
```bash
php artisan tables:sync-status
```

### 2. Otomatis (Sudah Diimplementasikan)
System sekarang otomatis akan update table status ke "available" ketika:
- Order dengan table_id di-delete (soft delete)
- Order status diubah menjadi "completed" 
- Order dibatalkan (cancelled)

### 3. Manual Database Update
Jika perlu update manual via database:
```sql
-- Update single table
UPDATE tables SET status = 'available' WHERE number = 1;

-- Update semua table yang tidak punya active order
UPDATE tables t
LEFT JOIN orders o ON t.id = o.table_id AND o.status IN ('pending', 'processing') AND o.deleted_at IS NULL
SET t.status = 'available'
WHERE o.id IS NULL AND t.status = 'occupied';
```

## Pencegahan Ke Depan

### Best Practice:
1. **Jangan hapus data langsung dari database** - gunakan aplikasi atau soft delete
2. **Gunakan Artisan command** `php artisan tables:sync-status` jika terjadi inkonsistensi
3. **Monitor table status** di dashboard untuk deteksi masalah lebih awal

### Artisan Command Tersedia:
- `php artisan tables:sync-status` - Sync table status berdasarkan active orders

## Status Enum Table
- `available` - Meja tersedia untuk order baru
- `occupied` - Meja sedang digunakan (ada order aktif)
- `reserved` - Meja direservasi
- `cleaning` - Meja sedang dibersihkan

## Order Status yang Dianggap "Active"
- `pending` - Order baru, belum diproses
- `processing` - Order sedang diproses

Order dengan status `completed` atau `cancelled` tidak membuat table occupied.

# License API Settings - User Guide

## 📋 Overview

Menu setting untuk mengatur akses API ke License Manager telah ditambahkan di aplikasi POS-RESTO. Menu ini memungkinkan administrator untuk mengkonfigurasi koneksi ke server License Manager melalui antarmuka web yang mudah digunakan.

## 🎯 Fitur

- **Konfigurasi License Manager API** melalui UI (tanpa perlu edit file .env)
- **Live Test Connection** untuk memverifikasi koneksi ke License Manager
- **Auto-save ke Database** dan .env file
- **Real-time Validation** untuk memastikan data yang diinput benar
- **Fallback System** - prioritas pembacaan: Database Settings → .env → Default Config

## 📍 Lokasi Menu

Menu setting ini tersedia di:

**Admin Panel → Settings → License Manager API Settings**

URL: `http://your-app-url/settings` (scroll ke bagian "License Manager API Settings")

## ⚙️ Cara Menggunakan

### 1. Akses Halaman Settings

Login ke aplikasi dan navigasi ke menu **Settings**:

```
Dashboard → Settings
```

### 2. Scroll ke Section "License Manager API Settings"

Di halaman settings, scroll ke bawah hingga menemukan section:

```
🔑 License Manager API Settings
```

### 3. Konfigurasi Parameter

Isi form dengan parameter berikut:

#### **License Server URL** (Required)
- URL lengkap dari License Manager server Anda
- Contoh: `https://license.yourdomain.com` atau `http://localhost:8000`
- **Penting**: Jangan tambahkan trailing slash (/) di akhir

#### **License Key** (Required)
- License key yang Anda terima dari License Manager
- Format biasanya: `XXXX-XXXX-XXXX-XXXX`
- Copy-paste langsung dari License Manager

#### **License Check Interval**
- Seberapa sering sistem melakukan pengecekan license ke server
- Pilihan:
  - 1 Hour (3600s)
  - 6 Hours (21600s)
  - 12 Hours (43200s)
  - **24 Hours (86400s) - Recommended** ✅
  - 48 Hours (172800s)

#### **Grace Period After Expiry**
- Berapa hari aplikasi masih bisa berjalan setelah license expired
- Range: 0-30 hari
- Default: 7 hari
- Berguna untuk memberikan waktu perpanjangan license

#### **Auto-check License on Every Request**
- ⚠️ **Tidak direkomendasikan** untuk production
- Jika diaktifkan, akan memeriksa license pada setiap request (bisa menurunkan performa)
- Hanya aktifkan untuk strict license enforcement

### 4. Test Connection

Sebelum menyimpan, Anda bisa test koneksi terlebih dahulu:

1. Klik tombol **"Test API"** di bagian bawah form
2. Sistem akan mencoba menghubungi License Manager server
3. Hasil test akan muncul:
   - ✅ **Success**: Koneksi berhasil, license valid
   - ❌ **Failed**: Koneksi gagal atau license invalid

### 5. Save Settings

Klik tombol **"Save Settings"** di bagian bawah halaman untuk menyimpan konfigurasi.

> **Note**: Settings akan disimpan ke:
> - Database (table `settings`)
> - File `.env` (untuk backup dan reference)

## 🔧 Technical Details

### Prioritas Pembacaan Konfigurasi

Sistem membaca konfigurasi license dengan urutan prioritas:

1. **Database Settings** (highest priority)
   - Dibaca dari table `settings`
   - Dapat diupdate via UI Settings
   
2. **.env File** (fallback)
   - Jika tidak ada di database, baca dari .env
   
3. **Default Config** (last resort)
   - Jika tidak ada di .env, gunakan default dari `config/license.php`

### Database Structure

Settings disimpan di table `settings` dengan format:

```sql
| key                      | value                          | type    |
|--------------------------|--------------------------------|---------|
| license_server_url       | https://license.example.com    | string  |
| license_key              | XXXX-XXXX-XXXX-XXXX           | string  |
| license_check_interval   | 86400                          | number  |
| license_grace_period     | 7                              | number  |
| license_auto_check       | 0                              | boolean |
```

### .env Variables

Saat save, settings juga akan update variabel di `.env`:

```env
LICENSE_SERVER_URL="https://license.example.com"
LICENSE_KEY="XXXX-XXXX-XXXX-XXXX"
LICENSE_CHECK_INTERVAL=86400
LICENSE_GRACE_PERIOD=7
LICENSE_AUTO_CHECK=false
```

### API Endpoint untuk Test Connection

Endpoint: `POST /admin/test-license-connection`

Request:
```json
{
  "server_url": "https://license.example.com",
  "license_key": "XXXX-XXXX-XXXX-XXXX"
}
```

Response (Success):
```json
{
  "success": true,
  "message": "Connection successful",
  "license_type": "yearly",
  "status": "active"
}
```

Response (Failed):
```json
{
  "success": false,
  "message": "Cannot connect to License Manager server..."
}
```

## 🚨 Troubleshooting

### Problem: "Cannot connect to License Manager server"

**Solusi:**
1. Pastikan License Manager server sedang running
2. Periksa URL server sudah benar (tidak ada typo)
3. Pastikan aplikasi POS-RESTO bisa akses network ke server tersebut
4. Jika menggunakan HTTPS, pastikan SSL certificate valid

### Problem: "Invalid license key"

**Solusi:**
1. Copy-paste ulang license key dari License Manager
2. Pastikan tidak ada spasi atau karakter tambahan
3. Periksa di License Manager apakah license key masih aktif

### Problem: Settings tersimpan tapi tidak efek

**Solusi:**
1. Clear config cache: `php artisan config:clear`
2. Restart queue worker jika menggunakan queue
3. Periksa log di `storage/logs/laravel.log`

## 📝 Best Practices

1. **Gunakan HTTPS** untuk production (secure connection)
2. **Set check interval 24 hours** untuk balance antara security dan performance
3. **Jangan aktifkan auto-check** kecuali benar-benar diperlukan
4. **Set grace period 7 hari** untuk memberikan buffer time
5. **Selalu test connection** sebelum save settings pertama kali
6. **Backup .env file** sebelum melakukan perubahan besar

## 🔐 Security Notes

- License key disimpan dalam plain text di database dan .env
- Pastikan file `.env` tidak ter-commit ke version control
- Batasi akses ke menu settings hanya untuk admin
- Gunakan HTTPS untuk komunikasi ke License Manager

## 📞 Support

Jika mengalami kendala, hubungi tim support atau periksa dokumentasi lengkap di:
- `LICENSE_INTEGRATION.md`
- `API_DOCUMENTATION.md`

---

**Last Updated:** 2026-02-27  
**Version:** 1.0

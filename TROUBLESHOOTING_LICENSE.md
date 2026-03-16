# Troubleshooting: License Connection Failed

## ❌ Error: "License not found"

Jika Anda mendapatkan error **"License not found"** saat test connection, berikut langkah-langkah troubleshooting:

---

## 🔍 Diagnosis

### Langkah 1: Check License Manager Status

Jalankan script checker:

```powershell
.\check-license-manager.ps1
```

Script ini akan:
- ✅ Cek apakah License Manager running
- ✅ Test koneksi ke API endpoint
- ✅ Verify license key (jika Anda masukkan)

### Langkah 2: Manual Check License Manager

```powershell
# Cek apakah License Manager running
curl http://localhost:8000

# Atau buka di browser
# http://localhost:8000
```

**Jika gagal connect:**
- License Manager tidak running
- URL salah

---

## 🛠 Solusi

### Solusi 1: Start License Manager

Jika License Manager belum running:

```powershell
# Masuk ke folder License Manager
cd D:\path\to\LICENSE-MANAGER

# Jalankan server
php artisan serve

# Atau di port tertentu
php artisan serve --port=8000
```

### Solusi 2: Periksa URL Server

Di POS-RESTO Settings, pastikan URL benar:

| Environment | URL |
|-------------|-----|
| Local Development | `http://localhost:8000` |
| Local (IP) | `http://127.0.0.1:8000` |
| Network | `http://192.168.x.x:8000` |
| Production | `https://license.yourdomain.com` |

**❌ Salah:**
- `http://localhost:8000/` (ada trailing slash)
- `http://localhost` (tanpa port)
- `localhost:8000` (tanpa http://)

**✅ Benar:**
- `http://localhost:8000`

### Solusi 3: Create License di License Manager

License key harus sudah dibuat terlebih dahulu di License Manager!

#### A. Via License Manager Admin Panel

1. Buka License Manager: `http://localhost:8000`
2. Login sebagai admin
3. Go to **Licenses** menu
4. Klik **Create New License**
5. Isi form:
   - **License Type**: trial/monthly/yearly/lifetime
   - **Customer Name**: Nama customer
   - **Customer Email**: Email customer
   - **Status**: Active
6. **Generate** license key
7. **Copy** license key yang di-generate

#### B. Via Artisan Command

```powershell
cd D:\path\to\LICENSE-MANAGER

# Create license via command
php artisan license:create --type=trial --customer="Test Customer"

# Output akan menampilkan license key yang di-generate
```

### Solusi 4: Periksa License Key Format

License key biasanya berbentuk:
- `XXXX-XXXX-XXXX-XXXX` (dengan dash)
- `XXXXXXXXXXXXXXXX` (tanpa dash)

Pastikan:
- ✅ Tidak ada spasi di awal/akhir
- ✅ Copy-paste dengan benar
- ✅ License key case-sensitive (A ≠ a)

### Solusi 5: Periksa Status License

Di License Manager, pastikan:
- Status license: **Active** (bukan Suspended/Expired)
- License belum expired
- License belum di-delete (soft deleted)

---

## 📋 Quick Checklist

Sebelum test connection, pastikan:

- [ ] License Manager sedang running
- [ ] URL server benar (http://localhost:8000)
- [ ] License sudah dibuat di License Manager
- [ ] License key di-copy dengan benar
- [ ] Status license = Active
- [ ] Tidak ada firewall blocking connection

---

## 🧪 Manual Test via CURL

Test langsung ke API License Manager:

```powershell
# Windows PowerShell
$body = @{
    license_key = "YOUR-LICENSE-KEY-HERE"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/license/verify" -Method POST -Body $body -ContentType "application/json"
```

```bash
# Linux/Mac
curl -X POST http://localhost:8000/api/license/verify \
  -H "Content-Type: application/json" \
  -d '{"license_key":"YOUR-LICENSE-KEY-HERE"}'
```

**Expected Response (Valid):**
```json
{
  "valid": true,
  "license": {
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "license_type": "trial",
    "status": "active",
    "customer_name": "Test Customer",
    ...
  }
}
```

**Expected Response (Not Found):**
```json
{
  "valid": false,
  "message": "License not found"
}
```

---

## 🔐 Advanced: Check Database Directly

Jika masih gagal, cek database License Manager:

```sql
-- Connect ke database License Manager
mysql -u root -p license_manager

-- Periksa apakah license ada
SELECT license_key, customer_name, status, license_type, deleted_at 
FROM licenses 
WHERE license_key = 'YOUR-LICENSE-KEY';

-- Jika deleted_at tidak NULL, license sudah di-delete
-- Restore dengan:
UPDATE licenses SET deleted_at = NULL WHERE license_key = 'YOUR-LICENSE-KEY';
```

---

## 📞 Masih Gagal?

Jika semua langkah di atas sudah dicoba tapi masih gagal:

1. **Check Log Files:**
   ```powershell
   # POS-RESTO log
   cat storage/logs/laravel.log | Select-Object -Last 50

   # License Manager log
   cd D:\path\to\LICENSE-MANAGER
   cat storage/logs/laravel.log | Select-Object -Last 50
   ```

2. **Enable Debug Mode:**
   Di `.env` POS-RESTO:
   ```env
   APP_DEBUG=true
   ```

3. **Clear All Cache:**
   ```powershell
   php artisan optimize:clear
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Test with Sample License:**
   Buat license baru khusus untuk testing dengan data minimal.

---

## ✅ Success Scenario

Ketika test connection berhasil, Anda akan melihat:

```
✅ Connection successful! License is valid.
📝 License Type: trial
📊 Status: active
👤 Customer: Test Customer
```

Jika sudah berhasil, Anda bisa **Save Settings** dan license akan tersimpan!

---

**Last Updated:** 2026-02-27  
**Version:** 1.0

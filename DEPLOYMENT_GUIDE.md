# Panduan Lengkap Instalasi & Deployment POS-RESTO di Server Baru (Production / Lokal)

Panduan ini berisi langkah-langkah *step-by-step* untuk memindahkan dan menjalankan aplikasi POS-RESTO ke komputer server restoran baru yang menggunakan sistem operasi Windows (seperti Windows 10/11) dengan Laragon.

---

### TAHAP 1: Persiapan Server Baru
1. Pastikan server baru sudah memiliki akses internet sementara (hanya untuk proses download awal).
2. Download dan install **Laragon Full** dari [laragon.org/download](https://laragon.org/download/).
3. Buka Laragon, klik **Menu (Kanan bawah ikon Laragon) > PHP > Quick Settings**, lalu pastikan ekstensi berikut **tercentang**:
   - `fileinfo`
   - `zip`
   - `curl`
   - `gd`
   - `mbstring`
   - `pdo_mysql`
4. Jalankan Laragon (Klik **Start All**). Pastikan Apache dan MySQL menyala (hijau).

---

### TAHAP 2: Pemindahan File Aplikasi
1. Di komputer sumber (lama), pastikan Anda sudah melakukan `git push` terbaru ke GitHub, **ATAU** Anda bisa meng-copy seluruh folder `D:\laragon\www\POS-RESTO` ke dalam Flashdisk.
2. Pada komputer tujuan (baru), *Paste* atau letakkan seluruh folder (`POS-RESTO`) ke dalam direktori: `C:\laragon\www\` (atau drive tempat Anda install Laragon, misal `D:\laragon\www\`).
   > *Catatan: Jika memakai Git Clone, buka terminal di folder `laragon/www` lalu jalankan:* `git clone https://github.com/rioswibowo28/POS.git POS-RESTO`

---

### TAHAP 3: Konfigurasi Lingkungan (`.env`)
1. Buka folder `POS-RESTO` di server baru.
2. Cari file `install.bat`. 
   *(Skrip batch ini sudah dipersiapkan secara spesifik untuk mempermudah pengaturan awal).*
3. **Klik 2x pada file `install.bat`**. Skrip akan otomatis:
   - Membuat salinan `.env` dari `.env.example`.
   - Menghasilkan *App Key* baru.
   - Menginstall seluruh library / dependensi PHP & Node.js (sehingga file `vendor` dan `node_modules` tercipta).
   - Membangun build Vite (`npm run build`).
4. Buka file `.env` menggunakan Notepad atau Text Editor. Sesuaikan bagian baris berikut:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://pos-resto.test  # (Sesuaikan laragon virtual host)

   # Konfigurasi Database (Sesuaikan dengan kredensial server baru)
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_resto
   DB_USERNAME=root
   DB_PASSWORD=WS2026$
   ```

---

### TAHAP 4: Setup Database & Data Awal
1. Buka Laragon, klik tombol **Database** (akan membuka HeidiSQL / PhpMyAdmin bawaan).
2. Buat database baru dengan nama `pos_resto` (huruf kecil semua, dipisah garis bawah).
3. Buka **Terminal Laragon** (tombol Terminal di aplikasi Laragon), lalu masuk ke folder aplikasi:
   ```bash
   cd C:\laragon\www\POS-RESTO
   ```
4. Jalankan perintah migrasi tabel:
   ```bash
   php artisan migrate --force
   ```
5. *(Opsional)* Jika server ini 100% kosong dan Anda butuh **data master baru / bawaan standar** (User Admin, Hak Akses, Shift), jalankan perintah:
   ```bash
   php artisan db:seed --force
   ```
   *Jika server ini hanya pindahan dari server lama, **JANGAN** lakukan `db:seed`. Sebaiknya, Anda cukup me-restore langsung lewat file `.sql` auto-backup terlama*.

---

### TAHAP 5: Performa Server (Wajib di Production)
Jika aplikasi di server baru ini dipakai untuk kasir harian, mode *cache* Laravel sangat **disarankan** agar akses menu, login, dan transaksi lebih cepat serta stabil.

#### 5.1 Kapan dijalankan?
- Jalankan tahap ini **setelah** konfigurasi `.env` final dan migrasi database selesai.
- Jalankan di mode production (`APP_ENV=production`, `APP_DEBUG=false`).
- Lakukan dari terminal pada folder aplikasi:
   ```bash
   cd C:\laragon\www\POS-RESTO
   ```

#### 5.2 Jalankan cache satu per satu (urutan aman)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Penjelasan singkat:
- `config:cache` → Menggabungkan konfigurasi agar pembacaan config lebih cepat.
- `route:cache` → Menyimpan daftar route ke cache (respon routing lebih cepat).
- `view:cache` → *Pre-compile* blade view supaya render awal lebih ringan.
- `event:cache` → Menyimpan pemetaan event-listener agar boot aplikasi lebih cepat.

#### 5.3 Cara cek berhasil atau tidak
- Jika berhasil, terminal biasanya menampilkan pesan sukses seperti *"Configuration cached successfully"* dan sejenisnya.
- Jalankan cek ringkas berikut:
   ```bash
   php artisan about
   ```
   Pastikan bagian cache/status menunjukkan kondisi aktif (tergantung versi Laravel).

#### 5.4 Jika ada perubahan `.env` atau kode
Jika suatu saat Bapak/Ibu mengubah konfigurasi `.env`, route, event, atau tampilan, **wajib** bersihkan cache dulu lalu bangun ulang.

1. Bersihkan seluruh cache Laravel:
    ```bash
    php artisan optimize:clear
    ```
2. Bangun kembali cache production:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    ```

#### 5.5 Catatan penting operasional
- Setelah `config:cache`, nilai `env()` tidak boleh dipakai langsung di file aplikasi (kecuali di file config).
- Jika perintah `route:cache` gagal, biasanya ada route yang memakai closure. Solusinya: ubah route closure ke controller.
- Untuk server kasir yang stabil, lakukan `optimize:clear` hanya saat maintenance/perubahan konfigurasi.

---

### TAHAP 6: Mengembalikan Windows Task Scheduler (Auto Backup 100% Otomatis)
Jika Bapak/Ibu ingin backup harian terus berjalan *walaupun website/kasir sedang ditutup layarnya*, maka penjadwalan Windows adalah jantungnya.
1. Masih di dalam folder aplikasi (`POS-RESTO`), cari file `setup-service.bat`.
2. Klik kanan file `setup-service.bat`, dan pilih **Run As Administrator**.
3. Sistem akan memunculkan jendela Command Prompt hitam untuk meng-injeksi jadwal backup ke dalam otak Windows (membuat task baru bernama `POS-RESTO-Scheduler`). File ini menjamin backup tereksekusi murni pada level *Operating System*.

---

### TAHAP 7: Setting Agar Website & Laragon Berjalan Otomatis Saat PC Dinyalakan
Agar kasir tidak perlu repot membuka Laragon dan menekan tombol *Start* setiap kali komputer dinyalakan pada pagi hari, lakukan konfigurasi berikut:

1. **Auto-Start Laragon:**
   - Buka aplikasi **Laragon**.
   - Klik ikon **Gear (Pengaturan / Preferences)** di pojok kanan atas.
   - Pada tab **General**, centang opsi:
     - ✅ **Run Laragon when Windows starts** (Nyalakan Laragon saat Windows hidup)
     - ✅ **Run minimized** (Sembunyikan Laragon di pojok bawah)
     - ✅ **Start All automatically** (Otomatis menyalakan Apache & MySQL)
   - Klik **X (Close)** pada jendela pengaturan (pengaturan otomatis tersimpan).

2. **Auto-Buka Website di Chrome:**
   - Tekan tombol `Windows + R` di keyboard kasir.
   - Ketikkan: `shell:startup` lalu tekan Enter (Folder Startup Windows akan terbuka).
   - Klik kanan di area kosong pada folder tersebut > pilih **New** > **Shortcut**.
   - Pada kolom *location/URL*, masukkan:
     ```
     "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk http://pos-resto.test
     ```
     *(Catatan: Sesuaikan arah folder Chrome jika berbeda. Tambahan `--kiosk` opsional jika Bapak/Ibu ingin Chrome langsung terbuka dalam mode Layar Penuh/Fullscreen persis seperti mesin POS ATM asli. Hapus `--kiosk` jika ingin jendela biasa).*
   - Klik **Next**, beri nama shortcut (misalnya "Mulai Kasir POS"), lalu klik **Finish**.

Mulai sekarang, ketika tombol *Power* PC dinyalakan, server akan aktif sendiri, dan Chrome akan langsung terbuka menampilkan halaman Login POS tanpa perlu di-_klik_ satupun oleh kasir.

---

### Testing Terakhir
- Coba buka alamat `http://pos-resto.test` atau alamat IP localhost server baru dari *browser*.
- Coba masuk/Login dengan admin / kasir bawaan.
- Sistem POS siap digunakan di server baru.
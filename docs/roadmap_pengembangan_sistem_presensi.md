# ROADMAP DAN PENJABARAN LENGKAP PENGEMBANGAN SISTEM PRESENSI DIGITAL PKBM PIKAT

**Tanggal Pembaruan:** 14 September 2026  
**Versi Framework:** Laravel 13.31.0 (PHP 8.5.1)  
**Status Proyek:** Fase 1 & 2 (Keamanan, Infrastruktur, Presensi Multi-Moda & Workflow Approval)  

---

## 1. KEAMANAN, INFRASTRUKTUR & PERFORMA (SYSTEM HARDENING)

### 1.1 Keamanan Autentikasi & Akses Berkas
* 🟢 **Upgrade Framework Laravel 13 (v13.31.0):** [SELESAI] Memperbarui `composer.json` ke `"laravel/framework": "^13.0"` dan `"laravel/tinker": "^3.0"`, menyelaraskan dependensi `nunomaduro/collision`, serta meregenerasi autoloader sehingga aplikasi berjalan stabil di atas versi Laravel 13 terbaru (v13.31.0).
* 🟢 **Perbaikan Deprecation Warning PHP 8.5:** [SELESAI] Memperbarui `config/database.php` pada opsi koneksi `mysql` dan `mariadb` menggunakan pengecekan dinamis `(defined('Pdo\Mysql::ATTR_SSL_CA') ? Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA)` untuk menggantikan konstanta `PDO::MYSQL_ATTR_SSL_CA` yang *deprecated* di PHP 8.5+.
* 🟢 **Mekanisme Rate Limiting Login:** [SELESAI] Menambahkan pembatasan percobaan login (maksimal 5 kali per menit per IP/akun) menggunakan Laravel `RateLimiter` dan middleware `throttle:login` di `routes/web.php` & `routes/api.php` untuk mencegah serangan *Brute Force*, dilengkapi respon ramah (pesan peringatan web & HTTP 429 JSON API) serta pembersihan counter saat login berhasil.
* ⚪ **Secure Storage Foto Presensi (Private Storage & Signed URLs):** [PENDING] Pemindahan direktori foto presensi sensitif ke `storage/app/private/` dengan pengaksesan via *Temporary Signed URL* yang mewajibkan autentikasi pengguna dan pembatasan waktu akses.

### 1.2 Manajemen Log & Infrastruktur Server
* 🟢 **Pembersihan & Pengamanan File Error Log:** [SELESAI] Membuang file `error_log` liar bawaan server cPanel/Apache, mengalokasikan direktori log ke `storage/logs/`, dan menambahkan pola `error_log` pada `.gitignore` agar tidak mengekspos credential dan log di repositori Git.
* 🟢 **Environment Worker & Config Tuning:** [SELESAI] Mengatur `PHP_CLI_SERVER_WORKERS=1` dan `APP_URL=http://localhost` pada file `.env` untuk konsistensi server reloader dan pengujian rute HTTP.
* ⚪ **Penerapan Script Deployment Otomatis (CI/CD Pipeline):** [PENDING] Pembuatan script *post-deploy* (misal via GitHub Actions) yang otomatis menjalankan `composer install --no-dev`, `php artisan config:cache`, `php artisan route:cache`, dan `php artisan migrate --force`.

---

## 2. PENGEMBANGAN FITUR INTI PRESENSI (ATTENDANCE CORE ENHANCEMENT)

### 2.1 Storage Abstraction & Management Foto Presensi
* 🟢 **Abstraksi Storage Disk Public & Model Accessor:** [SELESAI] 
  - Mengabstraksi seluruh controller upload foto (`ProfileController`, `KaryawanController`, `PresensiFotoController`, `KaryawanPresensiController`) menggunakan Laravel `Storage::disk('public')->putFileAs()`.
  - Memindahkan seluruh foto legacy dari `public/uploads/` ke `storage/app/public/uploads/` dan menghapus direktori `public/uploads/` sepenuhnya dari web root.
  - Menambahkan Eloquent Accessors (`$user->foto_url`, `$presensi->foto_mulai_url`, `$presensi->foto_selesai_url`) pada model `User`, `Presensi`, dan `PresensiKaryawan` untuk menjamin 100% *backward compatibility*.

### 2.2 Workflow Digital Lupa Lapor (Retroactive Attendance Approval)
* 🟢 **Persetujuan Interaktif Kepala Sekolah:** [SELESAI] 
  - Restrukturisasi dan standardisasi nama model menjadi **`PengajuanLupaLapor`** dengan tabel **`pengajuan_lupa_lapor`** (menggantikan nama legacy `Lapor_Lapor` / `lapor__lapors`).
  - Menambahkan status persetujuan (`pending`, `disetujui`, `ditolak`) dan kolom `catatan_kepsek`.
  - Menyempurnakan alur pengajuan oleh Tutor serta persetujuan interaktif oleh Kepala Sekolah.
  - **Otomatisasi Rekap Presensi**: Ketika Kepala Sekolah mengklik "Setujui", sistem secara otomatis melakukan *upsert* (membuat/memperbarui) data kehadiran pada tabel `presensis` (`status = 'hadir'`).

### 2.3 Validasi Lokasi & Fitur Lanjutan
* 🟢 **Presensi Multi-Moda (Sekolah, Kunjungan Rumah, Online):** [SELESAI] 
  - Menambahkan kolom `moda_pembelajaran` (`sekolah`, `kunjungan_rumah`, `online`) dan `link_daring` pada tabel `presensis`.
  - Mengintegrasikan pemilih moda pembelajaran pada form presensi tutor (`tutor/presensi_foto.blade.php`).
  - **Tatap Muka Sekolah**: Strict Geofencing dari titik sekolah PKBM Pikat.
  - **Kunjungan Rumah (Home Visit)**: Catat titik lokasi GPS kunjungan + foto di rumah murid.
  - **Pembelajaran Online**: Bypass radius lokasi + wajib melampirkan foto layar/link ruang pertemuan (Zoom/GMeet).
  - Menambahkan Eloquent Accessor `$presensi->moda_label` untuk kemudahan pelaporan.
* 🟢 **Kalkulasi Radius Geofencing (Rumus Haversine):** [SELESAI] 
  - Membuat service class `App\Services\GeofencingService` dengan fungsi `calculateDistance()` menggunakan **Rumus Haversine** untuk menghitung jarak antara dua pasang koordinat GPS (latitude/longitude) dalam satuan meter.
  - Menambahkan titik koordinat sekolah PKBM Pikat (`sekolah_lat`, `sekolah_lng`) dan toleransi radius (`radius_meter` = 100m) pada file konfigurasi `config/lokasi.php`.
  - Mengintegrasikan pemeriksaan geofencing pada `PresensiFotoController::store()` untuk moda pembelajaran `sekolah`. Jika jarak GPS tutor dengan titik sekolah PKBM Pikat $> 100$ meter, presensi otomatis ditolak dengan pesan peringatan interaktif yang menampilkan jarak sebenarnya.
  - Pengujian otomatis komprehensif pada `tests/Feature/GeofencingTest.php` (uji presensi di dalam radius, di luar radius, bypass moda online, dan perhitungan matematis Haversine).

* ⚪ **Deteksi Manipulasi GPS (Anti Fake GPS):** [PENDING] Integrasi validasi *accuracy level* lokasi browser/device dan deteksi penggunaan aplikasi *mock location* atau manipulasi koordinat GPS.
* ⚪ **Verifikasi Wajah Otomatis (Face Matching / AI Recognition):** [PENDING] Mengintegrasikan pemrosesan AI (misal: Face-API.js / TensorFlow) untuk membandingkan foto presensi tutor secara real-time dengan foto profil master.
* ⚪ **PWA (Progressive Web App) & Offline Mode:** [PENDING] Pemasangan Web App Manifest, Service Worker, dan penyimpanan lokal `IndexedDB` agar presensi tetap dapat dicatat saat perangkat tidak memiliki sinyal internet dan otomatis melakukan sinkronisasi saat online.
* ⚪ **Pengajuan Izin & Sakit Mandiri oleh Tutor:** [PENDING] Modul pengajuan izin dan sakit digital oleh Tutor lengkap dengan upload surat keterangan/dokumen pendukung serta alur verifikasi approval oleh Admin/Kepala Sekolah.

---

## 3. INTEGRASI MANAJEMEN PENGGAJIAN & HONORARIUM (PAYROLL SYSTEM)

### 3.1 Otomatisasi Perhitungan Honor Mengajar Tutor
* ⚪ **Kalkulasi Honorarium Berbasis Presensi Valid:** [PENDING] Menghitung akumulasi jam mengajar terverifikasi secara otomatis per periode bulan dikalikan dengan besaran tarif honor per jam / per sesi kehadiran.
* ⚪ **Dukungan Multitarif:** [PENDING] Fleksibilitas konfigurasi tarif honorarium yang bervariasi berdasarkan jenjang kelas (PAUD/Kesetaraan), kategori mata pelajaran, dan kualifikasi Tutor.

### 3.2 Slip Gaji Digital & Generasi Laporan Keuangan
* ⚪ **Ekspor Slip Gaji PDF:** [PENDING] Otomatisasi pembentukan dokumen Slip Gaji individual Tutor dalam format PDF (menggunakan `barryvdh/laravel-dompdf`) yang dapat diunduh langsung dari dashboard Tutor.
* ⚪ **Modul Rekapitulasi Anggaran:** [PENDING] Laporan komprehensif pengeluaran anggaran honorarium tutor harian, mingguan, dan bulanan bagi manajemen lembaga.

---

## 4. INTEGRASI INTEROPERABILITAS SISTEM & NOTIFIKASI (INTEGRATIONS)

### 4.1 Single Sign-On (SSO) & Integrasi SIM PKBM Pikat
* ⚪ **Integrasi Akun Terpusat (SSO via Laravel Sanctum / OAuth2):** [PENDING] Menghubungkan autentikasi dan basis data pengguna antara Sistem Presensi Digital dan SIM PKBM Pikat menggunakan API Tokens (Laravel Sanctum) atau OAuth2.

### 4.2 Integrasi WhatsApp Gateway (Notifikasi Real-Time)
* ⚪ **Notifikasi Pengingat Absen (Reminder):** [PENDING] Pengiriman pesan WhatsApp pengingat secara otomatis kepada Tutor yang belum melakukan *Clock-In* atau *Clock-Out* sesuai jadwal mengajar.
* ⚪ **Laporan Ketersediaan Tutor ke Wali Murid:** [PENDING] Pesan notifikasi real-time ke WhatsApp orang tua/wali murid saat Tutor terkonfirmasi hadir dan memulai sesi kegiatan belajar mengajar.

### 4.3 Import & Export Massal Data (Bulk Data Management)
* ⚪ **Import Spreadsheet Excel/CSV:** [PENDING] Fitur pengunggahan massal (*bulk import*) data Tutor, siswa, jadwal mengajar, dan pembagian kelas menggunakan paket `maatwebsite/excel`.

---

## 5. EXECUTIVE DASHBOARD & BUSINESS INTELLIGENCE (ANALYTICS)

### 5.1 Dashboard Analytics Kepala Sekolah
* ⚪ **Heatmap Kehadiran & Tren Kinerja:** [PENDING] Visualisasi grafik interaktif (Chart.js / ApexCharts) untuk memantau tren tingkat kehadiran Tutor per bulan, persebaran keterlambatan, dan keaktifan mengajar.
* ⚪ **Indikator Kinerja Utama (KPI Tutor):** [PENDING] Pemeringkatan kedisiplinan dan akumulasi jam mengajar Tutor sebagai acuan evaluasi kinerja tahunan oleh Kepala Sekolah.

### 5.2 Laporan Standar Akreditasi Pendidikan
* ⚪ **Format Laporan Otomatis Akreditasi BAN PAUD & PNF:** [PENDING] Fitur generasi laporan rekapitulasi presensi dan kegiatan mengajar yang sudah disesuaikan dengan format standar lampiran akreditasi BAN PAUD & PNF.

---

## 6. REFACTORED CODEBASE, STANDARDISASI & TESTING (QUALITY ASSURANCE)

### 6.1 Restrukturisasi & Standardisasi System Codebase
* 🟢 **Standardisasi Penulisan Kode (Laravel Pint):** [SELESAI] Menjalankan `vendor/bin/pint` pada seluruh file controller, model, view, seeder, dan migration.
* 🟢 **Standardisasi Bahasa Indonesia & Lokalisasi System:** [SELESAI] Melakukan standardisasi seluruh teks UI, nama modul, format tanggal Carbon locale `id`, status presensi, dan pesan validasi/flash message.
* 🟢 **Sentralisasi Design System via `resources/css/app.css`:** [SELESAI] Menyatukan seluruh styling CSS komponen ke `resources/css/app.css`, menghapus inline `<style>` dari view, membuang folder CSS statis redundan (`public/css/` & `public/assets/css/`), dan merapikan rujukan layout agar 100% Vite native (`@vite(['resources/css/app.css', 'resources/js/app.js'])`).
* 🟢 **Restrukturisasi & Standardisasi Folder Views (`resources/views/layouts/`):** [SELESAI] 
  - Mengonsolidasikan folder `layout/` (singular) dan `layouts/` (plural) menjadi `resources/views/layouts/`.
  - Memisahkan komponen partial navigasi berbahasa Indonesia di `resources/views/layouts/components/` (`navigasi_atas`, `navigasi_bawah_admin`, `navigasi_bawah_kepsek`, `navigasi_bawah_tutor`).
  - Memperbarui 29 file view Blade ke `@extends('layouts.x')`.
  - Membersihkan file *dead-code* (`welcome.blade.php`, `buttomNav.blade.php`, `navbar.blade.php`, `script.blade.php`).

### 6.2 Pengujian Otomatis (Automated Testing Suite) & Verification
* 🟢 **Automated Testing Suite (PHPUnit) & Build Validation:** [SELESAI] Pembuatan dan eksekusi pengujian otomatis `vendor/bin/phpunit` (11 tests, 41 assertions OK) serta kompilasi produksi Vite `npm run build`.

* 🟡 **Penerapan Pattern DRY (Service & Repository Pattern):** [DALAM PROSES] Mengeluarkan logika berulang ke dalam Service Classes.

---

## 7. MATRIKS PRIORITAS DAN TAHAPAN IMPLEMENTASI (ROADMAP MATRIX)

| Tahap | Fokus Utama | Target Hasil | Estimasi Dampak | Status |
|---|---|---|---|---|
| **Fase 1 (Segera)** | Keamanan, Upgrade Laravel 13, Standardisasi Views, Storage & Workflow Lupa Lapor | Sistem stabil di Laravel 13, persetujuan lupa lapor interaktif, storage terabstraksi | 🔴 Kritis (Keamanan & Stabilitas) | 🟡 Dalam Proses |
| **Fase 2 (Jangka Pendek)** | Presensi Multi-Moda, Geofencing GPS, Secure Storage Foto, & Refactoring | Data presensi terverifikasi valid secara lokasi (sekolah, home visit, online) dan berkas aman | 🟡 Tinggi (Integritas Data) | 🟡 Dalam Proses |
| **Fase 3 (Jangka Menengah)** | PWA / Mobile Mode, WhatsApp Gateway, & Modul Honor | Penggunaan mobile mudah, notifikasi otomatis, & honor terhitung | 🟢 Sedang (Efisiensi Operasional) | ⚪ Pending |
| **Fase 4 (Jangka Panjang)** | Single Sign-On (SIM), Face AI, & Business Intelligence | Ekosistem aplikasi terintegrasi utuh dengan analitik eksekutif | 🔵 Strategis (Skalabilitas Sistem) | ⚪ Pending |

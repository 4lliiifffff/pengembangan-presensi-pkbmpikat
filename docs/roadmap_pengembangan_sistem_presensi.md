# ROADMAP DAN PENJABARAN LENGKAP PENGEMBANGAN SISTEM PRESENSI DIGITAL PKBM PIKAT

---

## 1. KEAMANAN, INFRASTRUKTUR & PERFORMA (SYSTEM HARDENING)

### 1.1 Keamanan Autentikasi & Akses Berkas
* **Mekanisme Rate Limiting Login:** Menambahkan pembatasan percobaan login (misal: 5 kali per menit per IP/akun) menggunakan Laravel `RateLimiter` di `routes/web.php` untuk mencegah serangan *Brute Force*.
* **Secure Storage Foto Presensi (Private Storage & Signed URLs):** Pemindahan direktori foto dari `public/uploads/` ke `storage/app/private/` dengan pengaksesan menggunakan *Temporary Signed URL* yang mewajibkan autentikasi pengguna. Hal ini mencegah foto presensi diakses publik secara bebas melalui URL langsung.
* **Perbaikan Deprecation Warning PHP 8.5:** Mengubah konstanta usang `PDO::MYSQL_ATTR_SSL_CA` di `config/database.php` menjadi `Pdo\Mysql::ATTR_SSL_CA` untuk menjamin stabilitas saat server di-upgrade ke PHP 8.5+.

### 1.2 Manajemen Log & Infrastruktur Server
* **Pembersihan & Pengamanan File Error Log:** Mengalokasikan direktori *error log* ke `storage/logs/` dan menambahkan pola `error_log` pada `.gitignore` agar tidak mengekspos variabel lingkungan, path server, dan credential basis data di repositori publik.
* **Penerapan Script Deployment Otomatis (CI/CD Pipeline):** Pembuatan script *post-deploy* (misal via GitHub Actions) yang otomatis menjalankan `composer install --no-dev`, `php artisan config:cache`, `php artisan route:cache`, dan `php artisan migrate --force` untuk mencegah Fatal Error 500 akibat hilangnya direktori `vendor/` di server produksi.

---

## 2. PENGEMBANGAN FITUR INTI PRESENSI (ATTENDANCE CORE ENHANCEMENT)

### 2.1 Geofencing & Validasi Lokasi Presensial (GPS Lock)
* **Kalkulasi Radius Geofencing (Rumus Haversine):** Membatasi absen masuk (*Clock In*) dan absen pulang (*Clock Out*) hanya jika koordinat GPS tutor berada dalam radius yang diizinkan (misal: $\le 100$ meter dari koordinat lokasi mengajar/sekolah).
* **Deteksi Manipulasi GPS (Anti Fake GPS):** Menambahkan pemeriksaan *accuracy level* dan deteksi *mock location* dari browser/perangkat seluler tutor.

### 2.2 Verifikasi Wajah Otomatis (Face Matching / AI Recognition)
* **Penerapan Face API / AI Verification:** Mengintegrasikan pemrosesan AI (misal: `face-api.js` di browser atau API AI seperti Gemini Vision) untuk membandingkan foto presensi tutor secara real-time dengan foto profil terdaftar guna mencegah pengerjaan absen oleh pihak lain (*joki presensi*).

### 2.3 Workflow Digital Lupa Lapor (Retroactive Attendance Approval)
* **Persetujuan Interaktif Kepala Sekolah:** Pengembangan alur persetujuan penuh di mana Kepala Sekolah dapat mengeklik *Setujui* atau *Tolak* beserta catatan revisi.
* **Auto-Upsert Rekapitulasi:** Jika pengajuan disetujui, sistem secara otomatis memasukkan/memperbarui baris kehadiran di tabel `presensis` sehingga rekap kehadiran bulanan langsung ter-update secara akurat.

### 2.4 PWA (Progressive Web App) & Offline Mode
* **Presensi Tanpa Koneksi Internet (Offline Clock-In):** Menggunakan Service Worker dan `IndexedDB` agar tutor tetap bisa mengambil foto dan mencatat waktu saat berada di daerah tanpa sinyal. Data presensi akan secara otomatis di-synchronize ke server saat koneksi internet kembali pulih.
* **Installable Mobile App:** Memungkinkan aplikasi dipasang langsung di layar utama smartphone tutor tanpa melalui Play Store / App Store.

### 2.5 Pengajuan Izin & Sakit Mandiri oleh Tutor
* **Modul Pengajuan Izin Digital:** Tutor dapat mengajukan izin/sakit mandiri melalui aplikasi dengan mengunggah bukti (surat keterangan dokter/surat tugas), yang kemudian diverifikasi oleh Admin/Kepala Sekolah.

---

## 3. INTEGRASI MANAJEMEN PENGGAJIAN & HONORARIUM (PAYROLL SYSTEM)

### 3.1 Otomatisasi Perhitungan Honor Mengajar Tutor
* **Kalkulasi Honorarium Berbasis Presensi Valid:** Sistem menghitung total jam mengajar terverifikasi secara otomatis per bulan dikalikan dengan tarif honor per jam/per sesi.
* **Dukungan Multitarif:** Pengaturan tarif honorarium yang fleksibel berdasarkan jenjang kelas, kategori mata pelajaran, atau kualifikasi tutor.

### 3.2 Slip Gaji Digital & Generasi Laporan Keuangan
* **Ekspor Slip Gaji PDF:** Otomatisasi pembuatan dokumen Slip Gaji individual dalam format PDF.
* **Modul Rekapitulasi Anggaran:** Laporan total pengeluaran honorarium tutor harian, mingguan, dan bulanan untuk manajemen keuangan lembaga.

---

## 4. INTEGRASI INTEROPERABILITAS SISTEM & NOTIFIKASI (INTEGRATIONS)

### 4.1 Single Sign-On (SSO) & Integrasi SIM PKBM Pikat
* **Integrasi Akun Terpusat (SSO via Laravel Sanctum / OAuth2):** Menghubungkan basis data penguji antara Sistem Presensi dan Sistem Informasi Manajemen (SIM) PKBM Pikat. Satu akun dapat digunakan untuk mengakses seluruh ekosistem aplikasi tanpa perlu pendaftaran ulang.

### 4.2 Integrasi WhatsApp Gateway (Notifikasi Real-Time)
* **Notifikasi Pengingat Absen (Reminder):** Pengiriman pesan WhatsApp otomatis kepada tutor yang belum melakukan *Clock-Out* padahal jam mengajar telah selesai.
* **Laporan Ketersediaan Tutor ke Wali Murid:** Pengiriman notifikasi otomatis kepada orang tua/wali murid saat tutor memulai sesi mengajar siswa.

### 4.3 Import & Export Massal Data (Bulk Data Management)
* **Import Spreadsheet Excel/CSV:** Fitur pengunggahan massal untuk data siswa, tutor, dan pembagian kelas di awal tahun ajaran baru guna menghemat waktu input manual.

---

## 5. EXECUTIVE DASHBOARD & BUSINESS INTELLIGENCE (ANALYTICS)

### 5.1 Dashboard Analytics Kepala Sekolah
* **Heatmap Kehadiran & Tren Kinerja:** Visualisasi grafik interaktif tren kehadiran tutor per bulan, analisis tingkat kedisiplinan, dan distribusi total jam mengajar.
* **Indikator Kinerja Utama (KPI Tutor):** Pemeringkatan tutor berdasarkan kedisiplinan dan jumlah jam mengajar terverifikasi.

### 5.2 Laporan Standar Akreditasi Pendidikan
* **Format Laporan Otomatis Akreditasi BAN PAUD & PNF:** Generasi laporan rekapitulasi kehadiran dan kegiatan pembelajaran yang secara format siap dilampirkan untuk instrumen akreditasi lembaga pendidikan non-formal.

---

## 6. REFACTORED CODEBASE & TESTING SUITE (QUALITY ASSURANCE)

### 6.1 Refactoring Kode & Pembersihan Technical Debt
* **Penerapan Pattern DRY (Service & Repository Pattern):** Mengeluarkan logika berulang seperti `countHadir`, `rekapTutor`, dan fungsi agregasi dari controller ke dalam Service Classes.
* **Eliminasi Schema Compatibility Check Berulang:** Menghapus penggunaan `Schema::hasColumn()` di runtime setelah seluruh migrasi basis data dipastikan konsisten di server produksi.

### 6.2 Pengujian Otomatis (Automated Testing Suite)
* **Unit Testing & Feature Testing (PHPUnit / Pest PHP):** Pembuatan berkas pengujian otomatis untuk menguji rute autentikasi, validasi waktu presensi, alur *Lupa Lapor*, dan kebenaran kalkulasi laporan untuk mencegah regresi kode saat dilakukan pembaruan di masa depan.

---

## 7. MATRIKS PRIORITAS DAN TAHAPAN IMPLEMENTASI (ROADMAP MATRIX)

| Tahap | Fokus Utama | Target Hasil | Estimasi Dampak |
|---|---|---|---|
| **Fase 1 (Segera)** | Keamanan, Deprecation Fix, & Bugfix Lupa Lapor | Sistem stabil, bebas warning PHP 8.5, dan rate limited | 🔴 Kritis (Keamanan & Stabilitas) |
| **Fase 2 (Jangka Pendek)** | Geofencing GPS, Secure Storage Foto, & Refactoring | Data presensi terverifikasi valid secara lokasi dan berkas aman | 🟡 Tinggi (Integritas Data) |
| **Fase 3 (Jangka Menengah)** | PWA / Mobile Mode, WhatsApp Gateway, & Modul Honor | Penggunaan mobile mudah, notifikasi otomatis, & honor terhitung | 🟢 Sedang (Efisiensi Operasional) |
| **Fase 4 (Jangka Panjang)** | Single Sign-On (SIM), Face AI, & Business Intelligence | Ekosistem aplikasi terintegrasi utuh dengan analitik eksekutif | 🔵 Strategis (Skalabilitas Sistem) |

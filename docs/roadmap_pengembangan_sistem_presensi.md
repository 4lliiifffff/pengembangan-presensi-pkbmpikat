# ROADMAP DAN PENJABARAN LENGKAP PENGEMBANGAN SISTEM PRESENSI DIGITAL PKBM PIKAT

**Tanggal Pembaruan:** 17 September 2026  
**Versi Framework:** Laravel 13.31.0 (PHP 8.5.1)  
**Status Proyek:** Fase 1, 2, 3, 4, 5 & 6 (Keamanan, Multi-Moda, Multi-Geofence Radius, Payroll SK Dinamis, Web Push Real-Time, Standardisasi UI/UX, Manajemen Kelas/Paket Relasional, & Penjadwalan Sesi Pengganti / Reschedule Future-Proof)  

---

## 1. KEAMANAN, INFRASTRUKTUR & PERFORMA (SYSTEM HARDENING)

### 1.1 Keamanan Autentikasi & Akses Berkas
* 🟢 **Upgrade Framework Laravel 13 (v13.31.0):** [SELESAI] Memperbarui `composer.json` ke `"laravel/framework": "^13.0"` dan `"laravel/tinker": "^3.0"`, menyelaraskan dependensi `nunomaduro/collision`, serta meregenerasi autoloader sehingga aplikasi berjalan stabil di atas versi Laravel 13 terbaru (v13.31.0).
* 🟢 **Perbaikan Deprecation Warning PHP 8.5:** [SELESAI] Memperbarui `config/database.php` pada opsi koneksi `mysql` dan `mariadb` menggunakan pengecekan dinamis `(defined('Pdo\Mysql::ATTR_SSL_CA') ? Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA)` untuk menggantikan konstanta `PDO::MYSQL_ATTR_SSL_CA` yang *deprecated* di PHP 8.5+.
* 🟢 **Mekanisme Rate Limiting Login:** [SELESAI] Menambahkan pembatasan percobaan login (maksimal 5 kali per menit per IP/akun) menggunakan Laravel `RateLimiter` dan middleware `throttle:login` di `routes/web.php` & `routes/api.php` untuk mencegah serangan *Brute Force*, dilengkapi respon ramah (pesan peringatan web & HTTP 429 JSON API) serta pembersihan counter saat login berhasil.
* 🟢 **Konfigurasi Reverse Proxy & HTTPS Enforcement:** [SELESAI] Menambahkan konfigurasi `$middleware->trustProxies(at: '*')` di `bootstrap/app.php` dan `URL::forceScheme('https')` pada `AppServiceProvider` untuk menjamin keamanan tunneling, PWA, dan Web Push Notification tanpa *Mixed Content Block*.
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
  - **Pembelajaran Online**: Bebas batas radius lokasi + wajib melampirkan foto layar/link ruang pertemuan (Zoom/GMeet).
  - Menambahkan Eloquent Accessor `$presensi->moda_label` untuk kemudahan pelaporan.
* 🟢 **Master Multi-Titik Lokasi Presensi & Pemilihan Tempat Absen Berbasis Radius Dinamis:** [SELESAI]
  - **CRUD Master Titik Lokasi (Admin):** Modul kelola multi-geofence (`admin/lokasi-presensi`) dengan pengaturan nama lokasi, tipe (`pusat`, `cabang`, `mitra`, `lainnya`), koordinat latitude/longitude, dan radius toleransi khusus per titik (`radius_meter`, misal 100m, 150m, 200m).
  - **Pemilih Lokasi Interaktif di Kamera Presensi:** Dropdown dinamis `lokasi_presensi_id` pada halaman presensi Tutor, Karyawan, dan Magang.
  - **Validasi Radius Matematis Haversine:** `GeofencingService::checkSelectedLokasiRadius()` memvalidasi posisi GPS pengguna secara presisi terhadap titik lokasi yang dipilih. Jika di luar radius titik tersebut, presensi otomatis ditolak dengan pesan peringatan jarak riil.
  - **Automated Testing Suite:** 8 skenario pengujian komprehensif di `tests/Feature/LokasiPresensiTest.php`.
* 🟢 **Kalkulasi Radius Lokasi Sekolah (Rumus Haversine), Live Tracking & Interactive Peta Leaflet:** [SELESAI] 
  - Membuat service class `App\Services\GeofencingService` dengan fungsi `calculateDistance()` menggunakan **Rumus Haversine** dan helper `getGoogleMapsDirectionsUrl()`.
  - Menambahkan titik koordinat sekolah PKBM Pikat (`sekolah_lat`, `sekolah_lng`) dan toleransi radius (`radius_meter` = 100m) pada file konfigurasi `config/lokasi.php` dan file environment `.env`.
  - **Live GPS Tracking & Line Track Polyline (`navigator.geolocation.watchPosition` & `L.polyline`)**: Memantau pergerakan pengguna secara realtime saat berjalan/berkendara mendekati sekolah dan menampilkan garis panduan beranimasi putus-putus ke gerbang PKBM Pikat saat berada di luar radius.
  - **Proximity Radar & Auto-Unlock**: Widget jarak interaktif yang menampilkan sisa meter menuju zona absensi, tombol cepat *"Petunjuk Arah (Google Maps)"*, dan auto-unlock status hijau serta getaran haptic begitu masuk radius 100m.
  - **Interactive Leaflet Map Modal di Rekap Presensi**: Menggantikan iframe statis pada tabel laporan Admin (`admin/laporan/index.blade.php`) dengan peta Leaflet interaktif yang memvisualisasikan titik presensi, titik sekolah, radius geofence, dan garis ukur selisih jarak.
* 🟢 **Fitur Kontrol Kamera Lanjutan (Mirror, Switch Camera, Grid 3x3, Flash/Torch):** [SELESAI] 
  - **Mirror Mode (`🪞 Mirror`)**: Pratinjau real-time flip horizontal pada video preview dan penangkapan gambar yang di-flip secara konsisten pada 2D canvas HTML5.
  - **Switch Camera (`🔄 Switch`)**: Beralih secara instan antara Kamera Depan (Selfie) dan Kamera Belakang (Kelas/Siswa).
  - **Grid Komposisi (`📐 Grid 3x3`)**: Overlay garis bantu 3x3 *Rule of Thirds* untuk kerapihan foto presensi.
  - **Deteksi Flash/Torch (`⚡ Flash`)**: Integrasi pengontrol senter perangkat jika didukung oleh browser/kamera.
* 🟢 **Seeder Data Siswa & Kelas (`SiswaSeeder`):** [SELESAI] Menyiapkan data sampel kelas (Paket A, Paket B, Paket C, Vokasi) dan siswa terstruktur terikat pada tutor default.
* 🟢 **Deteksi Manipulasi GPS (Anti Fake GPS) & Validasi Akurasi Sinyal:** [SELESAI] 
  - Menolak presensi jika lokasi terdeteksi dari aplikasi mock location / Fake GPS.
  - Membatasi akurasi sinyal lokasi GPS maksimal 200 meter (`config/lokasi.php`). Jika akurasi buruk ($> 200$m) atau $0$m, presensi otomatis ditolak.
* 🟢 **PWA (Progressive Web App) & Offline Mode:** [SELESAI] Web App Manifest (`manifest.json`), Service Worker (`sw.js`), dan penyimpanan lokal `IndexedDB` (`PikatPresensiOfflineDB`) untuk pencatatan presensi saat offline.
* 🟢 **Pengajuan Izin & Sakit Mandiri oleh Tutor:** [SELESAI] Modul pengajuan izin dan sakit digital oleh Tutor lengkap dengan upload surat keterangan/dokumen pendukung serta alur verifikasi Kepala Sekolah.
* 🟢 **Modul Khusus Role Karyawan Magang (Mahasiswa Magang / Siswa PKL):** [SELESAI] Role khusus `magang` dengan alur absensi Clock-In & Clock-Out berbasis foto selfie dan verifikasi geofence radius 100m PKBM Pikat.
* ⚪ **Verifikasi Wajah Otomatis (Face Matching / AI Recognition):** [PENDING] Pemrosesan AI untuk membandingkan foto presensi tutor secara real-time dengan foto profil master.

---

## 3. INTEGRASI MANAJEMEN PENGGAJIAN & HONORARIUM (PAYROLL SYSTEM)

### 3.1 Reformasi Honorarium Berbasis SK — Master Kategori & Tarif Dinamis
* 🟢 **Master Kategori Tutorial & Tarif SK Kepala PKBM:** [SELESAI]
  - Struktur tabel `kategori_tutorials` lengkap dengan model `KategoriTutorial` dan seeder master SK:
    - 1. Tutorial Komunitas (Durasi 2 Jam) = **Rp 75.000,-** / pertemuan
    - 2. Tutorial Komunitas ABK (Durasi 2 Jam) = **Rp 100.000,-** / pertemuan
    - 3. Tutorial Komunitas (Durasi 3 Jam) = **Rp 100.000,-** / pertemuan
    - 4. Gabungan Komunitas per Rombel = **Rp 50.000,-** / rombel
    - 5. Tutorial Distance Learning / DL (Durasi 1,5 Jam) = **Rp 100.000,-** / pertemuan
    - 6. Tutorial Distance Learning / DL ABK (Durasi 1,5 Jam) = **Rp 130.000,-** / pertemuan
  - Status **Anak Berkebutuhan Khusus (ABK)** dikunci di data master Siswa (`siswas.is_abk`).
  - **Dynamic Resolver & Snapshot Finansial:** `PayrollService::resolveHonorSesi()` otomatis mencocokkan moda, durasi, status ABK, dan status gabungan $\rightarrow$ mengunci nilai `nominal_honor_snapshot` pada `presensis` agar data historis payroll tidak terpengaruh perubahan tarif di masa depan.
  - **Total Refactoring & Drop Kolom Legacy `tarif_per_jam`:** Menghilangkan seluruh jejak kolom manual `tarif_per_jam` pada tabel `siswas` demi standarisasi 100% berbasis SK.

### 3.2 Slip Gaji Digital & Generasi Laporan Keuangan
* 🟢 **Ekspor Slip Gaji PDF:** [SELESAI] Otomatisasi pembentukan dokumen Slip Gaji individual Tutor dalam format PDF (`barryvdh/laravel-dompdf`) yang dapat diunduh langsung dari dashboard Tutor maupun Admin/Kepsek.
* 🟢 **Modul Rekapitulasi Anggaran:** [SELESAI] Laporan komprehensif pengeluaran anggaran honorarium tutor bulanan/tahunan beserta ekspor Laporan Rekapitulasi Anggaran PDF.

---

## 4. PENJADWALAN SESI BELAJAR, SESI PENGGANTI & ARSITEKTUR ROLE SISWA (NEW)

### 4.1 Modul Jadwal Sesi & Sesi Pengganti (*Make-Up Class / Reschedule*)
* 🟢 **Struktur Tabel `jadwal_sesis` & Relasi Eloquent:** [SELESAI]
  - Tabel `jadwal_sesis` mencakup `tutor_id`, `siswa_id`, `kategori_tutorial_id`, `jadwal_kerja_id`, `tanggal_rencana`, `jam_masuk_rencana`, `jam_pulang_rencana`, `durasi_jam`, `jenis_sesi` (`reguler`, `pengganti`, `tambahan`, `ujian`), `status` (`terjadwal`, `berlangsung`, `selesai`, `dibatalkan`), `tanggal_asli`, `alasan_penggantian`, `catatan`, dan `presensi_id`.
  - Relasi lengkap pada `Tutor`, `Siswa`, dan `Presensi`.
* 🟢 **Evaluasi Presensi Jam Fleksibel Sesi Pengganti (`ShiftPresensiService`):** [SELESAI]
  - Pengecekan toleransi keterlambatan dihitung presisi terhadap jam target sesi pengganti (misal jam 14:00) alih-alih dipaksa mengikuti jam shift pagi default (07:30).
* 🟢 **Antarmuka Kalender Bulanan & Modal Jadwal Pengganti Tutor:** [SELESAI]
  - Halaman kalender interaktif bulanan (`tutor/jadwal_sesi/index.blade.php`), modal responsive `+ Buat Jadwal Pengganti`, dan tab switcher di menu jadwal.
  - Kartu sesi aktif terintegrasi langsung pada preview kamera presensi tutor (`tutor/presensi_foto.blade.php`), dengan auto-link & auto-complete sesi saat presensi dikirim.
* 🟢 **Penyelarasan Bottom Navigation Bar & Drawer:** [SELESAI]
  - Item ke-4 Bottom Nav Tutor langsung mengarah ke `route('tutor.jadwal-sesi.index')`, dengan drawer "Lainnya" yang menyediakan pintasan lengkap ke Agenda PKBM, Slip Honor, Pengajuan Izin, Lupa Lapor, dan Profil.

### 4.2 Future-Ready Architecture untuk Role Siswa (Self-Attendance)
* 🟢 **Pondasi Kolom Kehadiran & Akun Siswa:** [SELESAI]
  - Tabel `jadwal_sesis` telah dilengkapi kolom `presensi_siswa_id` dan `status_kehadiran_siswa` (`hadir`, `izin`, `sakit`, `alpa`).
  - Ketika modul login & absensi mandiri siswa diimplementasikan di masa depan, arsitektur database sudah 100% siap tanpa perlu migrasi ulang (*Zero Breaking Changes*).

---

## 5. MASTER DATA RELASIONAL & AKADEMIK (ACADEMIC LIFECYCLE)

### 5.1 Relasi Murni Master Jenjang & Kelas Rombel
* 🟢 **Tabel `jenjang_pakets` & Foreign Key `kelas.jenjang_paket_id`:** [SELESAI]
  - Tabel dinamis `jenjang_pakets` untuk menampung program fleksibel (Paket A, B, C, Vokasi, Kursus, dsb.).
  - Foreign key relasional murni `kelas.jenjang_paket_id` $\rightarrow$ `jenjang_pakets.id` dengan relasi `hasMany` & `belongsTo`.
* 🟢 **Siklus Hidup Siswa (Lifecycle Status) & Anti Data-Loss:** [SELESAI]
  - Kolom `status` (`aktif`, `alumni`, `cuti`, `nonaktif`) dan implementasi `SoftDeletes` (`deleted_at`) pada data Siswa.
  - Proteksi penghapusan jenjang paket aktif dengan opsi migrasi rombel otomatis.
* 🟢 **Deteksi Otomatis Multi-Rombel (Sesi Gabungan Komunitas):** [SELESAI]
  - Backend otomatis mendeteksi jika murid berasal dari $>1$ kelas berbeda dalam jenjang paket yang sama $\rightarrow$ otomatis menerapkan skema Gabungan Komunitas (Rp 50.000,- / rombel).

---

## 6. INTEGRASI INTEROPERABILITAS SISTEM & NOTIFIKASI (INTEGRATIONS)

### 6.1 Web Push Notification Real-Time (PWA & FCM Push Service)
* 🟢 **Infrastruktur Web Push & VAPID Key Management:** [SELESAI] Integrasi paket `minishlink/web-push` dengan generator kunci VAPID otomatis via `php artisan webpush:vapid`.
* 🟢 **Auto-Sync & Client Push Manager:** [SELESAI] Sinkronisasi token browser ke database pengguna login, auto-reconnect, dan pengujian push mandiri di menu Profil.
* 🟢 **Otomatisasi Trigger Push Notifikasi Sistem:** [SELESAI] Konfirmasi presensi masuk/pulang, notifikasi pengajuan izin/lupa lapor, notifikasi persetujuan Kepsek, pengingat jadwal mengajar harian, dan broadcast pengumuman payroll.

### 6.2 Import & Export Massal Data (Bulk Data Management)
* 🟢 **Import & Export Spreadsheet Excel/CSV:** [SELESAI] Fitur pengunggahan massal (*bulk import*) data Tutor, Siswa, Jadwal/Agenda, dan Rekap Presensi Retroaktif, serta ekspor laporan presensi berstandar akreditasi menggunakan `maatwebsite/excel`.

### 6.3 WhatsApp Gateway & Single Sign-On (Jangka Panjang)
* ⚪ **Notifikasi WhatsApp Gateway:** [PENDING] Integrasi WhatsApp Gateway pihak ketiga untuk pengiriman notifikasi langsung ke nomor wali murid.
* ⚪ **Single Sign-On (SSO via Laravel Sanctum / OAuth2):** [PENDING] Integrasi autentikasi terpusat dengan SIM PKBM Pikat.

---

## 7. EXECUTIVE DASHBOARD & BUSINESS INTELLIGENCE (ANALYTICS)

### 7.1 Dashboard Analytics Kepala Sekolah
* 🟢 **Heatmap Kehadiran & Tren Kinerja:** [SELESAI] Visualisasi grafik interaktif (Chart.js) untuk memantau tren tingkat kehadiran Tutor per bulan, total sesi, dan akumulasi jam mengajar selama 6 bulan terakhir (`AnalyticsService.php` & `kepsek/dashboard.blade.php`).
* 🟢 **Indikator Kinerja Utama (KPI Tutor):** [SELESAI] Pemeringkatan kedisiplinan (%), jam mengajar (jam), dan skor composite KPI Tutor (Leaderboard #1 Gold, #2 Silver, #3 Bronze) sebagai acuan evaluasi kinerja tahunan oleh Kepala Sekolah.

### 7.2 Laporan Standar Akreditasi Pendidikan
* ⚪ **Format Laporan Otomatis Akreditasi Pendidikan Kesetaraan (BAN PDM / PNF):** [PENDING] Fitur generasi laporan rekapitulasi presensi dan kegiatan mengajar yang disesuaikan dengan format standar lampiran akreditasi BAN PDM.

---

## 8. STANDARDISASI UI/UX, LAYOUT RESPONSIF & FEEDBACK TERPADU

### 8.1 Standardisasi Navigasi & Spacing Dashboard Antar Role
* 🟢 **Sticky & Clean Top Navigation:** [SELESAI] Navigasi atas yang sticky, bersih, dan konsisten di semua role (`tutor`, `magang`, `admin`, `kepala_sekolah`).
* 🟢 **Penyelarasan Spacing & Layout Dashboard:** [SELESAI] Header section `.sectionTitleRow`, sistem `.cardBox`, grid 2-kolom desktop ($\ge 992$px), dan 1 kolom rapi mobile.

### 8.2 Unified Modal, Toast, & Dialog System
* 🟢 **Sistem Modal Dialog & Toast Modern:** [SELESAI] Modal pop-up dan toast notification terpadu (`app-dialog-overlay`, `app-toast-container`) yang elegan dan mendukung tema gelap/terang.

### 8.3 Desain Halaman Pengajuan Izin & Sakit Tutor
* 🟢 **Redesain Form & Riwayat Izin Tutor:** [SELESAI] Struktur halaman lega (`.pengajuanPage`), formulir bersih (`.izinFormCard`), dan grid tanggal adaptif.

---

## 9. QUALITY ASSURANCE & TESTING (TEST SUITE)

* 🟢 **Standardisasi Penulisan Kode (Laravel Pint):** [SELESAI] `vendor/bin/pint --format agent` lolos 100% di seluruh file controller, model, view, seeder, dan migration.
* 🟢 **Automated Testing Suite (PHPUnit):** [SELESAI] Seluruh **107 Feature & Unit Tests** lulus 100% (**438 assertions**).
* 🟢 **Vite Production Assets:** [SELESAI] Kompilasi CSS & JS (`npm run build`) berjalan bersih tanpa error (2.11s).

---

## 10. MATRIKS PRIORITAS DAN TAHAPAN IMPLEMENTASI (ROADMAP MATRIX)

| Tahap | Fokus Utama | Target Hasil | Estimasi Dampak | Status |
|---|---|---|---|---|
| **Fase 1** | Keamanan, Upgrade Laravel 13, Standardisasi Views, Storage & Workflow Lupa Lapor | Sistem stabil di Laravel 13, persetujuan lupa lapor interaktif, storage terabstraksi | 🔴 Kritis (Keamanan & Stabilitas) | 🟢 Selesai |
| **Fase 2** | Presensi Multi-Moda, Multi-Geofence Radius, Anti Fake GPS, PWA, & Modul Honor/Payroll | Data presensi terverifikasi valid secara lokasi multi-titik, offline PWA, & honor terhitung otomatis | 🟡 Tinggi (Integritas Data) | 🟢 Selesai |
| **Fase 3** | Web Push Notification Real-Time, Bulk Import/Export Excel, & Analytics KPI | Notifikasi push instan di HP, manajemen data massal, & dashboard analitik eksekutif | 🟢 Sedang (Efisiensi Operasional) | 🟢 Selesai |
| **Fase 4** | **Honorarium SK: Master Kategori & Tarif Otomatis Sesi SK** | 6 tipe tarif flat sesi SK, dynamic resolver, snapshot immutability, & penghapusan tarif manual | 🔴 Kritis (Akurasi Finansial & Regulasi) | 🟢 Selesai |
| **Fase 5** | **UI/UX Excellence, Unified Modal/Dialog, & Validasi Rombel Paket** | Sticky topbar, spacing dashboard rapi, pop-up dialog modern, form izin lega, & validasi paket gabungan rombel | 🟡 Tinggi (User Experience & Integritas) | 🟢 Selesai |
| **Fase 6** | **Jadwal Sesi Belajar, Reschedule / Sesi Pengganti & Arsitektur Role Siswa** | Tabel `jadwal_sesis`, kalender bulanan tutor, evaluasi shift jam fleksibel sesi pengganti, auto-complete sesi di kamera presensi, bottom nav sync, & kolom future-ready absensi siswa | 🔴 Kritis (Operasional KBM & Fleksibilitas) | 🟢 Selesai |
| **Fase 7 (Mendatang)** | Single Sign-On (SIM), WhatsApp Gateway, Secure Private Storage, & AI Recognition | Ekosistem terintegrasi utuh dengan SIM lembaga, WA gateway wali murid, & proteksi AI lanjutan | 🔵 Strategis (Skalabilitas Sistem) | ⚪ Pending |

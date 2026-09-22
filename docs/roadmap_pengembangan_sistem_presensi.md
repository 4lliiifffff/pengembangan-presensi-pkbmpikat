# ROADMAP DAN PENJABARAN LENGKAP PENGEMBANGAN SISTEM PRESENSI DIGITAL PKBM PIKAT

**Tanggal Pembaruan:** 22 September 2026  
**Versi Framework:** Laravel 13.31.0 (PHP 8.5.1)  
**Status Proyek:** Fase 1, 2, 3, 4, 5 & 6 (Keamanan, Multi-Moda, Multi-Geofence Radius, Payroll SK Dinamis, Web Push Real-Time, Standardisasi UI/UX, Manajemen Kelas/Paket Relasional, Penjadwalan Sesi Pengganti, & Integrasi Autentikasi Lintas Sistem SIMPIKAT)  

---

## 1. KEAMANAN, INFRASTRUKTUR & PERFORMA (SYSTEM HARDENING)

### 1.1 Keamanan Autentikasi & Akses Berkas
* 🟢 **Integrasi Autentikasi Lintas Sistem (SIMPIKAT SSO & JIT Provisioning):** [SELESAI] Mengimplementasikan koneksi database sekunder `simpikat` (`db_simpikat`) dan service terdedikasi `SimpikatAuthService` untuk memungkinkan akun pengguna dari portal utama SIMPIKAT (`/www/SIMPIKAT`) login langsung ke sistem Presensi Pikat. Dilengkapi verifikasi hash Bcrypt standar Laravel, validasi peran (admin, tutor, peserta didik/siswa), penolakan akses aman bagi peran non-presensi (orang tua & asesor), dan Just-In-Time (JIT) Provisioning profil domain (`tutor`, `siswa`, `admin`) sehingga seluruh relasi database presensi berjalan konsisten tanpa error null reference.
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
* 🟢 **Penanganan Presensi Tanpa Clock-Out Lintas Hari (Fault-Tolerant Check-In & Actionable Warning Banner):** [SELESAI]
  - **Prinsip Fault-Tolerant Non-Blocking:** Absen masuk pada hari berikutnya tetap diizinkan berjalan normal agar kehadiran hari baru tidak hangus/terganggu.
  - **Pendeteksian Sesi Lampau Menggantung (`unclosedPastSession`):** Controller presensi Tutor, Karyawan, dan Magang otomatis mendeteksi jika ada catatan kehadiran sebelum hari ini yang memiliki `jam_mulai` namun `jam_selesai` masih `NULL`.
  - **Banner Peringatan Edukatif & Tombol Aksi 1-Klik:** Menampilkan banner peringatan interaktif di atas form presensi hari ini. Untuk Tutor, tombol *"Ajukan Lupa Lapor Sesi Ini"* langsung membuka form Lupa Lapor dengan data tanggal, siswa, dan jam mulai yang sudah terisi otomatis (*pre-filled*). Untuk Karyawan dan Magang, tersedia tombol konfirmasi cepat ke WhatsApp Admin/Pembina.
  - **Sinkronisasi Siklus Jadwal & Pemulihan Payroll:** Pada `PresensiService::syncApprovedAttendance()`, persetujuan Lupa Lapor oleh Kepala Sekolah otomatis meng-update status `JadwalSesi` terkait dari `'berlangsung'` menjadi `'selesai'` dan mengaitkan `presensi_id`, sehingga `PayrollService` secara otomatis memvalidasi sesi tersebut ke dalam perhitungan honorarium SK.
  - **Automated QA Suite:** 5 skenario pengujian komprehensif di `tests/Feature/UnclosedPresensiHandlingTest.php` lulus 100%.

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
* 🟢 **Modernisasi Infrastruktur Peta Leaflet (Aset Lokal, CSS Dark Mode Filter & Modul JS Bersama):** [SELESAI]
  - **Eliminasi CDN Eksternal:** Menghapus seluruh dependensi CDN `unpkg.com` dan gambar remote GitHub. Menginstall `leaflet` via NPM dan mem-bundle CSS serta JS langsung ke pipeline Vite aplikasi (`resources/js/app.js`).

### 2.4 Mobile App Hardening & Manajemen Izin Perangkat (Capacitor Android APK)
* 🟢 **Penanganan Crash Izin Lokasi & Kamera pada Aplikasi Mobile (Capacitor APK):** [SELESAI]
  - **Identifikasi Akar Masalah:** WebView Capacitor (`BridgeWebChromeClient.java`) hanya memiliki satu instance `ActivityResultLauncher` untuk menangani izin web. Saat kamera (`getUserMedia`) dan geolokasi (`getCurrentPosition`) dipanggil secara bersamaan via `Promise.all([camP, locP])`, terjadi tabrakan listener native Android yang menyebabkan aplikasi keluar sendiri / force-close ketika tombol 'Izinkan' ditekan.
  - **Inisialisasi Native Pre-Permissions (`MainActivity.java`):** Menambahkan `requestInitialPermissions()` pada `MainActivity.onCreate()` untuk meminta izin `CAMERA`, `ACCESS_FINE_LOCATION`, `ACCESS_COARSE_LOCATION`, dan `POST_NOTIFICATIONS` langsung di tingkat native Android Activity saat aplikasi dibuka.
  - **Alur Perizinan Sekuensial & Graceful GPS Fallback (Web Presensi Views):** Merefaktor `checkPermissions()` pada 4 peran pengguna (`tutor`, `karyawan`, `magang`, `siswa`) dari paralel `Promise.all` menjadi sekuensial (Kamera terlebih dahulu hingga tuntas, kemudian Lokasi GPS), serta menyertakan fallback otomatis dari high accuracy (8 detik) ke low accuracy (10 detik) untuk mengatasi sinyal GPS lemah atau Android 12+ approximate location.
  - **Pembaruan Identitas Aplikasi Jadi `PresensiPKBMPikat-v1.0`:** Mengubah nama aplikasi menjadi **`PresensiPKBMPikat-v1.0`** di `capacitor.config.json` dan `android/app/src/main/res/values/strings.xml`, melakukan `npx cap sync android`, serta mengompilasi APK debug siap pakai (`mobile-apk/PresensiPKBMPikat-v1.0.apk`).
  - **Resolusi Path Default Marker Icon & Aset Lokal Fallback:** Mengatasi bug HTTP 500 pada `marker-shadow.png` & `marker-icon-2x.png` saat Leaflet memanggil subpath rute (seperti `/admin/lokasi-presensi/marker-shadow.png`) akibat auto-detection bundler. Menyediakan aset statis di `public/images/leaflet/`, mengkonfigurasi `L.Icon.Default.mergeOptions(...)` dengan Vite image imports, meniadakan prototype `_getIconUrl`, dan menyematkan `createPinIcon('target')` di seluruh view admin master lokasi presensi (`create`, `edit`, `index`).
  - **Tile Dark Mode Mandiri (Bebas API Key & Tanpa Watermark):** Menggunakan OpenStreetMap standar dengan CSS Dark Mode filter (`brightness`, `invert`, `contrast`, `hue-rotate`) pada layer `.leaflet-tile` saat `[data-theme="dark"]` aktif. Menghilangkan ketergantungan pada server CartoDB yang memunculkan watermark *"API KEY REQUIRED"* tanpa memerlukan API key eksternal.
  - **Konsolidasi Modul Bersama (`resources/js/leaflet-presensi.js`):** Menggantikan 100+ baris duplikasi kode Leaflet di 4 view kamera presensi berbeda (`siswa`, `tutor`, `magang`, `karyawan`) dengan satu modul terpadu `window.createPresensiMap`.
  - **Marker Pin SVG Lokal & Retina-Ready:** Menggantikan ikon remote dengan `L.divIcon` inline SVG (Target merah ber-drop shadow, User biru dengan efek animasi pulse ring, dan Alternatif abu-abu).
  - **Dark Mode Map Controls Styling:** Penataan CSS khusus pada `.leaflet-container`, zoom control, attribution bar, dan popups agar harmonis dengan tema gelap.
* 🟢 **Perbaikan Pelacakan Lokasi Presensi Pulang & Robust Geolocation Fallback:** [SELESAI]
  - **Resolusi Bug Koordinat Presensi Pulang:** Memperbaiki deklarasi variabel target lokasi (`currentTargetLng`, `currentTargetRadius`, dsb.) dan menyediakan *fallback* otomatis dari relasi `$activeSesi->lokasiPresensi` ke konfigurasi default sekolah saat dropdown pemilihan lokasi tidak dirender pada form presensi pulang (`karyawan` & `tutor`).
  - **Eliminasi Uncaught LatLng Exception di Leaflet:** Menambahkan safe number coercion dan fallback koordinat di `resources/js/leaflet-presensi.js` serta memastikan class `.d-none` dihapus dari container `#leafletMap` dan `invalidateSize()` dipanggil otomatis sehingga peta langsung ter-render akurat tanpa terjebak di status *"Memuat peta lokasi…"*.
  - **Koreksi Deteksi Moda Bimbingan Tutor (`isSekolahModa`):** Memastikan sesi mengajar daring / kunjungan rumah tidak falsely terdeteksi sebagai `sekolah` saat melakukan absen pulang, mencegah timbulnya peringatan palsu di luar area sekolah.
* 🟢 **Harmonisasi Styling Status Siap Presensi Pulang (Kontras Lembut & Ultra-Responsif Mobile):** [SELESAI]
  - **Konsolidasi Banner Bertumpuk:** Mengeliminasi duplikasi banner oranye (`running`) dan banner biru hardcoded (`text-blue-900`, `bg-blue-50`, `bg-gradient-to-r from-blue-50 to-indigo-50`) menjadi satu kartu ringkas terpadu `.card.border-base.bg-card-alt.rounded-2xl`.
  - **Harmonisasi Kontras Tema (Light & Dark Mode):** Menggunakan token desain semantik `var(--text)`, `var(--muted)`, `.bg-success-light`, `.text-success`, dan `.bg-primary-subtle` yang ramah di mata dan mematuhi standar WCAG AAA tanpa kontras yang menyilaukan.
  - **Optimalisasi Ergonomi Mobile Viewport:** Penataan layout flexbox dengan `flex-wrap` dan `gap-2` yang pas di layar smartphone ($\ge 320\text{px}$) sehingga informasi jam masuk, status siap pulang, dan indikator fleksibilitas tampil rapi tanpa *horizontal overflow*.
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
* 🟢 **Standardisasi Arsitektur Upload & Eliminasi Redundansi Folder Storage:** [SELESAI]
  - **Eliminasi Prefix Redundan `uploads/`:** Menghapus penulisan prefix `'uploads/'` pada pemanggilan `Storage::disk('public')->putFileAs()` di seluruh controller (`PresensiFotoController`, `SiswaPresensiController`, `MagangPresensiController`, `KaryawanPresensiController`, `ProfileController`, `Admin\KaryawanController`, `Admin\MagangController`).
  - **Konsolidasi Direktori Fisik Storage:** Memindahkan seluruh berkas foto dari `storage/app/public/uploads/*` langsung ke root folder modul masing-masing (`storage/app/public/foto_karyawan/`, `presensi/`, `presensi_karyawan/`, `presensi_siswa/`, `profiles/`) dan menghapus direktori `uploads/` yang redundan.
  - **Re-linking Symlink Publik:** Menyambungkan ulang link `public/storage` ke `storage/app/public` secara presisi via `storage:link`.
  - **Standardisasi Eloquent Accessor:** Mengintegrasikan accessor cerdas (`foto_mulai_url`, `foto_selesai_url`, `foto_url`) pada model `User`, `Tutor`, `Presensi`, `PresensiKaryawan`, `PresensiMandiriSiswa`, `Magang`.
  - **Migrasi Pemanggilan di Blade:** Menggantikan seluruh pemanggilan raw `asset($p->foto_mulai)` menjadi method accessor aman di seluruh file Blade antarmuka Tutor, Magang, Kepala Sekolah, dan Admin.
* 🟢 **Standardisasi 5 Persona Akun Demo & Quick Login Pengujian:** [SELESAI]
  - Menyelaraskan nama 5 persona utama di database seeder dan seluruh antarmuka pengujian: **Admin (Kak Tasya)**, **Kepala Sekolah (Bu Dara)**, **Tutor (Kak Tari)**, **Mahasiswa Magang (Alif)**, dan **Murid (Zeldi)**.
  - Memperbarui kartu login demo `auth/login`, floating quick switcher `navigasi_atas`, controller fallback provisioning `AuthWebController`, placeholder create form, dan test assertions.
* 🟢 **Model Hibrida Cerdas Kelayakan Absen Pulang Tutor (Smart Check-Out Eligibility - Opsi 3):** [SELESAI]
  - **Latar Belakang & Eliminasi Batasan Kaku:** Menggantikan aturan warisan (*legacy*) `min(3600 detik)` (1 jam kaku) yang tidak selaras dengan jadwal belajar riil tutor (misal sesi 2 jam bisa pulang di menit ke-60, atau sesi 1 jam dipaksa menunggu hingga menit ke-60).
  - **Formula Aturan Hibrida Cerdas:** Tombol dan form presensi pulang terbuka seketika salah satu dari 2 kondisi terpenuhi (*earlier of the two*):
    - **Kondisi A (Target Jadwal Selesai):** Waktu jam server mencapai `jam_pulang_rencana` dari `jadwal_sesis` dikurangi 10 menit toleransi kepulangan wajar (contoh: jadwal 10:00 - 12:00 WIB dibuka mulai 11:50 WIB).
    - **Kondisi B (Durasi Efektif KBM Terpenuhi):** Tutor telah mengajar minimal 80% dari durasi rencana sesi KBM sejak jam masuk aktual (`jam_mulai`) (contoh: sesi 2 jam = 96 menit; sesi 1.5 jam = 72 menit; sesi 1 jam = 48 menit; dengan floor batas bawah 15 menit).
  - **Sinkronisasi Terpusat (`ShiftPresensiService::calculateCheckOutEligibility`):** Perhitungan terpusat yang mengembalikan status kelayakan `bisa_pulang`, `sisa_detik`, `sisa_menit`, `target_waktu_buka`, `alasan_buka`, dan pesan interaktif. Diadopsi langsung oleh `PresensiFotoController` (render view & validasi backend `store`), `TutorDashboardController` (widget countdown), dan antarmuka Blade.
  - **Perbaikan Bug Format Countdown Modulo 60:** Menggantikan `gmdate('i:s')` yang me-reset ke 0 setiap kelipatan 60 menit menjadi `sprintf('%02d:%02d', floor($sec/60), $sec%60)` sehingga durasi di atas 60 menit (misal 96:00) tampil akurat tanpa glitch.
  - **Automated Feature Test Suite:** Pengujian komprehensif di `tests/Feature/TutorCheckOutEligibilityTest.php` untuk sesi 2 jam, sesi 1 jam, keterlambatan masuk, dan HTTP store gating.
* 🟢 **Bypass Waktu Tunggu Pulang & Pembersihan UI/UX Manajemen (Admin & Kepala Sekolah - Opsi A):** [SELESAI]
  - **Peniadaan Waktu Tunggu Minimal 1 Jam di Backend:** Membebaskan role `admin` dan `kepala_sekolah` dari batasan kaku 3600 detik (1 jam) pada `KaryawanPresensiController@store` mode `selesai`. Pimpinan dan administrator kini dapat melakukan presensi pulang sewaktu-waktu sesuai kebutuhan dinas/rapat mendesak.
  - **Eliminasi Teks Ganda & Countdown Card di View:** Menghapus timer countdown `58:39` dan teks redundan `"minimal 1 jam durasi kerja"` pada `resources/views/karyawan/presensi_foto.blade.php`. Menggantikannya dengan banner penjelas *"Akses Fleksibel Kepulangan Aktif"* serta langsung mengaktifkan formulir foto dan tombol kirim presensi pulang.
  - **Automated Feature Test Suite:** 3 skenario pengujian komprehensif di `tests/Feature/KaryawanCheckOutBypassTest.php` lulus 100%.
* 🟢 **Resolusi ArgumentCountError & Penguatan Injeksi Dependency Presensi Magang:** [SELESAI]
  - **Akar Masalah:** Method `MagangPresensiController@index` memanggil internal `$this->foto()` tanpa argumen, padahal signature method `foto(ShiftPresensiService $shiftService)` mewajibkan 1 parameter yang hanya di-*resolve* otomatis oleh container Laravel saat rute dipanggil via router (`route('magang.presensi.foto')`). Pemanggilan method manual `$this->foto()` pada rute `route('magang.presensi')` memicu `ArgumentCountError` (0 passed, exactly 1 expected).
  - **Solusi Arsitektur:** Mempromosikan injeksi `ShiftPresensiService $shiftService` ke constructor controller (`__construct`), mengubah parameter method `foto(?ShiftPresensiService $shiftService = null)` menjadi opsional dengan fallback `$this->shiftService`, serta menambahkan coverage pengujian otomatis untuk `route('magang.presensi')` di `tests/Feature/MagangRoleAndPresensiTest.php` (7 passed, 31 assertions).
* 🟢 **Eliminasi Data Dummy & Penyelarasan Jadwal Kerja Fleksibel Operasional PKBM:** [SELESAI]
  - **Penghapusan Penalti Keterlambatan Shift Pagi Fiktif:** Menghilangkan fallback shift pagi hardcoded (08:00 WIB / batas toleransi 08:30 WIB) pada `ShiftPresensiService` saat tabel database `jadwal_kerja` belum dikonfigurasi.
  - **Evaluasi Berbasis Sesi Belajar Tutor:** Tutor dievaluasi presisi terhadap sesi belajar terjadwal (`JadwalSesi`), atau dicatat secara dinamis sebagai `Sesi Belajar Mandiri` (fleksibel) tanpa penalti keterlambatan palsu saat mengajar di luar jam shift pagi (misal sore atau malam).
  - **Jam Kerja Fleksibel Manajemen (Admin & Kepala Sekolah):** Peran kepemimpinan yang telah memiliki izin dinas luar / bypass radius dievaluasi sebagai `Jam Kerja Fleksibel (Manajemen)` dengan status tepat waktu otomatis.
  - **Presensi Reguler Karyawan & Magang:** Karyawan dan Mahasiswa Magang yang belum memiliki shift khusus di database dicatat sebagai `Presensi Reguler {Role}` tanpa penalti keterlambatan fiktif.
  - **Penyelarasan Koordinat & Kontak Resmi PKBM Pikat:** Memperbarui default fallback koordinat ke lokasi riil PKBM Pikat Bandung (`-6.9308526492339295, 107.60746330217363`) serta memperbarui nomor resmi WhatsApp Admin ke `6285156452939` (menggantikan dummy `6281234567890`).
  - **Pembaruan Kartu Shift di UI Presensi:** Kartu shift pada halaman presensi Tutor, Karyawan, dan Magang kini mendeteksi `has_shift`. Jika bernilai `false`, kartu menampilkan jam waktu nyata dan label waktu fleksibel alih-alih peringatan target masuk 08:00 WIB.
  - **Pemeliharaan Fitur Quick Login Pengujian:** Fitur Quick Login pengujian tetap dipertahankan secara utuh sesuai arahan pengujian operasional.
* 🟢 **Perbaikan Undefined Variable `$lokasiPresensis` pada Presensi Magang:** [SELESAI]
  - **Akar Masalah:** Method `MagangPresensiController@foto` mengirimkan variable `'lokasiPresensis' => $lokasiPresensis` ke view `magang.presensi_foto`, namun inisialisasi query `$lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();` dan import `use App\Models\LokasiPresensi;` belum ada pada controller, menyebabkan fatal error `ErrorException: Undefined variable $lokasiPresensis` saat mengakses `/magang/presensi`.
  - **Solusi & Verifikasi:** Mengimpor model `LokasiPresensi`, memanggil query master titik lokasi aktif, dan memverifikasi kelancaran alur presensi magang via test suite `MagangRoleAndPresensiTest` (7 passed, 31 assertions) serta `UnclosedPresensiHandlingTest`.
* ⚪ **Verifikasi Wajah Otomatis (Face Matching / AI Recognition):** [PENDING] Pemrosesan AI untuk membandingkan foto presensi tutor secara real-time dengan foto profil master.

---

## 3. INTEGRASI MANAJEMEN PENGGAJIAN & HONORARIUM (PAYROLL SYSTEM)

### 3.1 Reformasi Honorarium Berbasis SK - Master Kategori & Tarif Dinamis
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
  - **Dinamisasi Jenis Layanan & Proteksi Relasi Menyeluruh (Admin Lifecycle):**
    - Mengeliminasi batasan enum/hardcoded kaku pada `jenis_layanan` di form tambah/edit Admin (`admin/kategori-tutorial`). Admin kini bebas mendefinisikan jenis layanan baru (misal: *Homeschooling*, *Kursus Vokasi*, *Bimbingan Intensif*, dll.) dilengkapi fitur auto-saran (*datalist*) dan *Quick Tag Pills*.
    - **Penyelarasan Style & Responsivitas Mobile Form (`create` & `edit`):** Menyelaraskan antarmuka form pembuatan dan pengeditan kategori SK dengan Design System terpadu PKBM Pikat. Mengadopsi label `.filterFieldLabel` yang konsisten, deskripsi `.field-help-text`, pembungkus `.form-field-wrapper`, kartu saklar `.checkbox-toggle-card`, kotak edukasi `.info-callout-box`, serta tombol aksi footer `.form-action-footer` (`.btnOutline` dan `.profileBtnPrimary`) yang bertransformasi menjadi *full-width* vertikal pada smartphone tanpa *horizontal overflow*.
    - **Proteksi Integritas Relasi Berlapis (Dual-Layer Protection):** Penghapusan kategori layanan dilindungi dari kesalahan manual admin. Jika kategori terikat dengan `presensis`, `jadwal_sesis`, atau `jadwal_rutins`, sistem otomatis mengalihkan aksi menjadi **Non-Aktif** (`is_aktif = false`) sehingga arsip historis dan slip gaji masa lampau tidak terhapus, namun kategori langsung hilang dari pilihan pembuatan sesi baru ke depan.
    - **Pewarisan Kategori Presensi:** Sesi terjadwal yang mengikat kategori SK khusus langsung mewariskan ID dan tarif honor ke lembar absensi dan rekap payroll tutor tanpa terhambat pemetaan moda tatap muka/daring.
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
  - Halaman kalender interaktif bulanan (`tutor/jadwal_sesi/index.blade.php`), modal responsive `Buat Jadwal Pengganti`, dan tab switcher di menu jadwal.
  - Kartu sesi aktif terintegrasi langsung pada preview kamera presensi tutor (`tutor/presensi_foto.blade.php`), dengan auto-link & auto-complete sesi saat presensi dikirim.
* 🟢 **Penyelarasan Bottom Navigation Bar & Drawer:** [SELESAI]
  - Item ke-4 Bottom Nav Tutor langsung mengarah ke `route('tutor.jadwal-sesi.index')`, dengan drawer "Lainnya" yang menyediakan pintasan lengkap ke Agenda PKBM, Slip Honor, Pengajuan Izin, Lupa Lapor, dan Profil.
* 🟢 **Integer Sanitization Countdown Timer Absen Pulang (Eliminasi Desimal Microsecond):** [SELESAI]
  - Mengonversi nilai selisih waktu `diffInSeconds` secara eksplisit menjadi integer di PHP dan Blade, serta menggunakan `Math.floor` pada modulo detik di JavaScript browser.
  - Memastikan tampilan waktu tunggu pulang di halaman tutor, magang, karyawan, dan dashboard selalu bersih dan akurat berformat `MM:SS` (mencegah teks desimal panjang `55:24.038773...`).

### 4.2 Arsitektur Penuh Role Siswa & Modul Jadwal Belajar (Self-Attendance & Learning Schedule)
* 🟢 **Pondasi Kolom Kehadiran & Akun Siswa:** [SELESAI]
  - Tabel `jadwal_sesis` telah dilengkapi kolom `presensi_siswa_id` dan `status_kehadiran_siswa` (`hadir`, `izin`, `sakit`, `alpa`).
  - Otomatisasi sinkronisasi kehadiran saat siswa melakukan check-in mandiri.
* 🟢 **Sistem Presensi Mandiri Single Check-In Siswa:** [SELESAI]
  - Alur presensi siswa dirancang khusus 1x check-in masuk harian (selfie kamera + validasi radius geofencing sekolah PKBM tanpa countdown pulang).
  - Tampilan dashboard adaptif 2-state (`belum` vs `selesai`) dengan statistik kehadiran bulanan dan riwayat mandiri.
* 🟢 **Modul Jadwal & Kalender Belajar Siswa (`siswa.jadwal`):** [SELESAI]
  - Kalender bulanan interaktif (`.agendaHeaderCard`, `.agendaMonthGrid`) terstandarisasi dengan penanda event dot gabungan antara agenda kegiatan sekolah dan sesi belajar murid bersama tutor.
  - Kartu rincian sesi tutorial (jam sesi, nama tutor, mapel/kategori, jenis sesi reguler/pengganti, dan status kehadiran siswa).
  - Sinkronisasi Bottom Navigation Bar 5 slot (Absen, Riwayat, Dashboard, Jadwal, Lainnya) serta drawer menu komprehensif.

### 4.3 Master Jadwal Rutin KBM Siswa Tertentu & Smart Presensi Time-Gating
* 🟢 **Struktur Master Template Berulang (`jadwal_rutins`):** [SELESAI]
  - Tabel `jadwal_rutins` mengikat secara eksplisit `siswa_id`, `tutor_id`, `kategori_tutorial_id`, `jadwal_kerja_id`, `hari` (`senin` s.d. `minggu`), `jam_masuk`, `jam_pulang`, `durasi_jam`, `is_active`, `berlaku_mulai`, dan `berlaku_sampai`.
  - Foreign key `jadwal_rutin_id` (nullable) pada tabel `jadwal_sesis` untuk menghubungkan sesi aktual ke master template mingguan.
* 🟢 **Engine Otomatisasi Generator Sesi (`JadwalRutinService` & Artisan Command):** [SELESAI]
  - `JadwalRutinService::generateSesiForPeriod()` otomatis menghasilkan instance sesi kalender per tanggal hingga 4 minggu ke depan.
  - Cek anti-duplikasi dan deteksi otomatis agenda hari libur / cuti sekolah dari tabel `jadwals`.
  - Artisan Command `php artisan jadwal:generate-sesi {--weeks=4}` terpasang pada Laravel Scheduler (`routes/console.php`) berjalan setiap Minggu malam pukul 23:00 WIB.
* 🟢 **Panel Admin Kelola Jadwal Rutin Siswa (`admin/jadwal_rutin`):** [SELESAI]
  - CRUD lengkap master jadwal mingguan per-siswa (`index`, `create`, `edit`, `destroy`, `toggleStatus`).
  - **Standardisasi UI & Mobile Responsive Layout:** Menyelaraskan tata letak halaman `index`, `create`, dan `edit` dengan design system utama: tabel responsif desktop (`.table-responsive-desktop`) yang bertransformasi menjadi kartu data mobile (`.data-mobile-card` & `.mobile-card-list`) pada layar ponsel ($\le 768$px).
  - **Form Grid Responsif & Komponen Anti-Squish:** Menggunakan `.form-card-container`, `.form-grid-responsive`, `.info-callout-box`, dan `.checkbox-toggle-card` pada halaman `create` dan `edit` sehingga input waktu, durasi, dan dropdown tertata rapi tanpa penyempitan di layar kecil.
  - **Badge Hari Belajar Harmonis & Dark Mode:** Menambahkan class `.badge-hari-senin` s.d. `.badge-hari-minggu` dengan warna tematik dan adaptasi mode gelap instan.
  - **Modal Generator Sesi Terstandarisasi:** Modal manual generator berbasis `.app-modal-card` dengan overlay blur dan tata letak form konsisten.
  - Pintasan menu terintegrasi di Drawer Navigasi Bawah Admin.
* 🟢 **Penanganan Kondisi Dinamis & Fleksibilitas Reschedule (Alur Mandiri Tutor & Opsi 2):** [SELESAI]
  - **Desentralisasi Penjadwalan ke Tutor:** Admin tidak wajib membuat jadwal rutin; Tutor memiliki kewenangan penuh menyepakati dan menetapkan jadwal langsung bersama peserta didik bimbingannya via modal terpadu 2-in-1:
    - *Mode Rutin Mingguan:* Menetapkan hari, jam, dan batas akhir semester / bulan selesai (`berlaku_sampai`), atau dibiarkan terbuka terus-menerus yang otomatis di-generate berkala oleh sistem.
    - *Mode Sesi Sekali / Pengganti:* Menjadwalkan pertemuan tanggal tunggal (*ad-hoc* / *make-up class*).
  - **Alur Reschedule Terstandarisasi (Opsi 2):** Saat terjadi reschedule, sesi pada tanggal lama otomatis berstatus `dibatalkan` dengan riwayat alasan tercatat jelas, dan sesi pengganti baru dibuat pada tanggal/jam yang disepakati (`jenis_sesi = 'pengganti'`, `tanggal_asli` terdokumentasi) tanpa merusak pola mingguan berikutnya.
  - **Manajemen Pola Rutin Mandiri Tutor:** Tab *"Pola Rutin Saya"* di portal Tutor untuk memantau, mem-pause/mengaktifkan kembali, dan menghapus master jadwal rutin tanpa intervensi manual admin.
  - **Peleburan Halaman `/tutor/jadwal` ke dalam `/tutor/jadwal-sesi` (Tab 3: `tab=agenda`):**
    - Mengeliminasi redundansi halaman kalender terpisah. Seluruh agenda dan pengumuman resmi PKBM kini terintegrasi langsung sebagai **Tab 3 ("Agenda & Pengumuman PKBM")** di halaman `/tutor/jadwal-sesi`.
    - Rute legacy `route('tutor.jadwal')` secara otomatis dialihkan (*HTTP Redirect*) ke `route('tutor.jadwal-sesi.index', ['tab' => 'agenda'])` sehingga kompatibilitas tautan tetap terjaga.
    - File view lama `resources/views/tutor/jadwal.blade.php` telah dihapus dan drawer menu tutor diperbarui langsung mengarah ke tab agenda terpadu.
  - **Header Action Dinamis & Eliminasi Redundansi Tombol:**
    - Header card di `tutor/jadwal_sesi` beradaptasi otomatis sesuai tab yang dipilih: pada tab *"Pola Rutin Saya"* judul menjadi *"Pola Rutin Mengajar"* dengan tombol header tunggal `+ Tambah Pola Rutin`, pada tab *"Agenda & Pengumuman PKBM"* judul menjadi *"Agenda KBM & Libur Sekolah"*, dan pada tab *"Kalender Sesi Belajar"* judul menjadi *"Jadwal Sesi & Pengganti"* dengan tombol header tunggal `+ Buat Jadwal Belajar`.
    - Menghapus tombol redundan inline *"Tambah Pola Rutin"* di dalam body kartu sehingga halaman rapi, konsisten, dan bebas tombol ganda.
  - **Optimalisasi Responsivitas Mobile Ekstra & Penyelarasan Bottom Section:**
    - Menambahkan styling responsif pada `.calendarNavTabsContainer` dan `.calendarNavTab` untuk layar $\le 640$px dan $\le 480$px agar navigasi tab tetap rapat, touch-friendly, dan bebas overflow.
    - Penyesuaian ukuran titik multi-event (`.agendaDotSesi` & `.agendaDotAgenda`) di dalam sel kalender ponsel.
    - Penyelarasan `.dmc-actions` dan `.dmc-footer` pada kartu mobile sehingga tombol aksi (`.profileBtnPrimary`, `.btn-table-action`, `.smallBtn`) tertata ergonomis dan membungkus penuh secara rapi di layar kecil ($\le 480$px).
    - **Penyelarasan Desain Bottom Section (Banner & Empty State):** Seluruh tab (Tab 1 Kalender, Tab 2 Rutin, Tab 3 Agenda) kini mengadopsi bahasa visual terpadu:
      - Saat ada data: dibungkus dalam container beraksen `.agendaBannerBox` dengan `.agendaBannerHeader` yang menyajikan status badge dinamis, live counter kuantitas sesi/pola/kegiatan, serta keterangan tanggal atau frekuensi pengulangan. Varian tema emerald `.rutin` khusus dihadirkan pada Tab Rutin untuk merefleksikan master jadwal berulang.
      - Saat kosong: menyajikan `.emptyAgendaBox` terstandarisasi dengan ikon tematik melayang berlatar pastel, judul bold elegan, deskripsi kontekstual, dan tombol aksi utama (*primary call-to-action*) yang memudahkan aksi cepat.
  - Standardisasi lebar kontainer (`max-w-4xl`) sehingga transisi antar tab bebas dari lonjakan tata letak (*zero layout shift*).
  - Kalender terpadu multi-dot: menampilkan titik biru untuk Sesi Belajar Murid dan titik amber untuk Agenda/Libur resmi PKBM pada grid kalender kedua halaman.
  - Integrasi detail tanggal: menampilkan banner peringatan agenda sekolah (`.agendaNoticeBox`) pada kalender sesi belajar, serta ringkasan sesi murid (`.sesiSummaryBox`) pada kalender agenda sekolah.
  - Parameter URL `?open_modal=1` otomatis membuka modal buat jadwal belajar saat tutor beralih dari halaman agenda.
  - Tab navigasi terpadu serupa juga diterapkan pada panel Admin antara *Kalender Agenda Sekolah* (`admin.jadwal.index`) dan *Master Jadwal Rutin Siswa* (`admin.jadwal-rutin.index`).
  - **Penyelarasan & Harmonisasi Logika Sesi Durasi Non-SK (4-Role Harmonization):**
    - **Tutor:** Pilihan fleksibel durasi belajar di luar SK (misal 1, 1.25, 4 jam) dengan *Smart SK Binding* pada modal penjadwalan (pilih SK otomatis isi jam pulang). Jika tutor mengubah jam pulang menjadi durasi non-SK, sistem menampilkan *Live Warning Callout* (`#boxWarningNonSk`) yang menginformasikan bahwa durasi tidak ada di SK, honor akan menggunakan tarif flat default SK, dan sesi ditandai secara otomatis untuk verifikasi Admin/Kepsek.
    - **Proteksi Clock-Out Adaptif Tutor:** Mengeliminasi jebakan *lockout* statis 1 jam (3600 detik). Tutor sesi singkat ($\le 60$ menit) kini dapat absen pulang setelah melewati ambang proporsional `min(3600, max(900, round(durasi_detik * 0.7)))` (misal sesi 30 menit sudah bisa pulang di menit ke-21) tanpa kehilangan waktu bimbingan.
    - **Siswa (Toleransi Keterlambatan Proporsional):** Pada sesi berdurasi $< 90$ menit, batas toleransi keterlambatan presensi mandiri disesuaikan secara dinamis `min(defaultTolerance, max(10, round(durasi_menit * 0.3)))` sehingga pada sesi kilat 30 menit siswa yang baru hadir di menit ke-25 tidak keliru tercatat *"Tepat Waktu"*.
    - **Admin (Visibilitas & Curricular Audit):** Otomatis menyematkan penanda transparan `[Jadwal Khusus: Durasi X Jam di luar SK]` pada catatan sesi atau master pola rutin tutor untuk kemudahan verifikasi dan audit kurikulum.
    - **Penyempurnaan Komprehensif Penyimpanan Jadwal Belajar Mandiri Tutor & Feedback Validasi:**
      - **Pre-Validation Input Sanitization:** Menyaring nilai `kategori_tutorial_id = 'custom'` atau string kosong sebelum validasi Laravel dijalankan, mengonversinya secara otomatis menjadi `null` sehingga controller dapat mengeksekusi logika fallback durasi non-SK tanpa tersandung aturan `exists:kategori_tutorials,id`.
      - **Normalisasi Format Jam Fleksibel:** Memotong format waktu peramban yang menyertakan detik (`H:i:s`) menjadi `H:i` (`substr($time, 0, 5)`) agar input dari berbagai vendor peramban/perangkat mobile tidak ditolak aturan ketat `date_format:H:i`.
      - **Error Feedback & Auto-Reopen Modal:** Menambahkan wadah alert notifikasi flash session (`success`, `warning`, `error`, `$errors->any()`) pada layout halaman `tutor/jadwal_sesi/index.blade.php` dan bagian dalam `#modalJadwalBaru`, serta skrip JavaScript yang otomatis membuka kembali modal beserta preservasi nilai input terdahulu (`old()`) saat validasi form gagal, mengeliminasi efek *silent failure*.
      - **Isolasi Form Input Dual-Mode (Disabled Toggle):** Menambahkan atribut `disabled` dinamis pada kumpulan elemen input mode yang sedang tidak aktif (Rutin vs Sekali) via `setScheduleMode(isRecurring)` sehingga payload POST tidak saling tumpang tindih.
      - **Definisi Konstanta `HARI_LABELS` & Resolusi Kategori:** Menambahkan mapping konstanta `HARI_LABELS` pada model `JadwalRutin` dan memperbaiki resolusi nama kategori `$katNama` pada alur notifikasi Web Push ke siswa.
  - **Web Push Notifikasi Instan:** Siswa otomatis menerima notifikasi Web Push setiap kali jadwal baru ditetapkan atau di-reschedule oleh tutornya.
* 🟢 **Smart Presensi Time-Gating Masuk Siswa:** [SELESAI]
  - Form kamera presensi siswa terkunci jika tidak ada sesi KBM yang dijadwalkan hari ini (`gatingReason = 'no_schedule'`).
  - Form presensi masuk hanya terbuka mulai **30 menit sebelum sesi dimulai** (`gatingReason = 'too_early'`).
  - Tampilan UI interaktif dilengkapi kartu informasi sesi, tutor pengampu, dan *live countdown timer* JavaScript yang otomatis me-refresh halaman saat jam buka tiba.
  - Server-side validation pada `SiswaPresensiController::store()` menolak upaya manipulasi check-in di luar jadwal.
* 🟢 **Perhitungan Kehadiran Akumulatif Siswa Anti Double-Counting (Distinct Active Days):** [SELESAI]
  - Perhitungan total hadir bulanan (`$totalHadirBulanIni`) dan riwayat profil (`$totalHariHadir`) menggunakan integrasi tanggal unik antara absensi mandiri siswa (`presensi_mandiri_siswas`) dan sesi KBM bersama tutor (`presensis`).
  - Menghilangkan anomali terhitung ganda (2 kali) saat siswa dan tutor sama-sama melakukan presensi pada hari yang sama.
* 🟢 **Perbaikan Siklus Status Sesi KBM & Eliminasi Lock-Out Presensi Mandiri Siswa:** [SELESAI]
  - Memperbaiki transisi status sesi KBM: saat tutor absen masuk, status sesi menjadi `'berlangsung'`, dan baru menjadi `'selesai'` saat tutor absen pulang.
  - Memperluas query pencarian jadwal di `SiswaPresensiController::store()` dan tombol absen di `siswa.jadwal` menjadi `where('status', '!=', 'dibatalkan')`, sehingga murid tetap dapat melakukan presensi mandiri secara mulus meskipun sesi KBM telah dimulai oleh tutornya.
* 🟢 **Penanda Keterlambatan Presensi Mandiri Siswa (Batas Waktu Toleransi KBM):** [SELESAI]
  - Menambahkan kolom `status_kehadiran` (`tepat_waktu`, `terlambat`, `lebih_awal`) dan `menit_keterlambatan` pada tabel `presensi_mandiri_siswas`.
  - Evaluasi batas toleransi (30 menit setelah jam mulai sesi) pada formulir kamera siswa, dengan chip jadwal KBM, batas toleransi, dan badge status keterlambatan real-time.
  - Kartu kehadiran hari ini di dashboard dan daftar riwayat presensi siswa menampilkan status dan menit keterlambatan (*"Terlambat +XX Mnt"*).

* 🟢 **Modernisasi & Penyelarasan UI Kartu Presensi Mandiri Siswa (`siswa.presensi.foto`):** [SELESAI]
  - Standarisasi kartu pasca-presensi mandiri siswa (`alreadyCheckedIn`) menggunakan token design system terpadu (`.data-mobile-card`, `.statusBanner`, `.dmc-grid`, `.dmc-field`, `.dmc-footer`).
  - Snapshot profil siswa dengan preview foto selfie yang dapat diklik untuk memperbesar gambar via `#buktiPhotoModal`.
  - Penyelarasan tata letak responsif pada kartu kondisi time-gating (`no_schedule` dan `too_early`) serta penambahan kelas tombol sekunder `.profileBtnSecondary` di CSS utama.
* 🟢 **Penyederhanaan Database Seeder Khusus Akun Pengguna (`DatabaseSeeder`):** [SELESAI]
  - Konfigurasi run default `DatabaseSeeder` difokuskan murni pada inisialisasi akun pengguna seluruh peran (`AdminSeeder`, `UserRoleSeeder`, `MagangSeeder`, `SiswaUserSeeder`).
  - Mengeliminasi pembuatan otomatis jadwal operasional, data master, dan generator sesi kalender dummy saat `php artisan db:seed` dijalankan, menjaga database tetap bersih dan ringan.

* 🟢 **Integrasi Multi-Geofence Titik Lokasi Presensi pada Seluruh Penjadwalan & Pelacakan Lintas-Peran (Siswa, Tutor, Admin, Kepsek):** [SELESAI]
  - **Arsitektur Database Terintegrasi (`lokasi_presensi_id`):**
    - Menambahkan kolom foreign key `lokasi_presensi_id` (nullable, `onDelete('set null')`) pada 3 tabel penjadwalan inti: `jadwals` (agenda sekolah), `jadwal_rutins` (master pola mingguan), dan `jadwal_sesis` (sesi KBM aktual) via migrasi `2026_09_20_182127_add_lokasi_presensi_id_to_jadwals_and_kbm_tables.php`.
    - Relasi Eloquent `belongsTo(LokasiPresensi::class)` pada model `Jadwal`, `JadwalRutin`, dan `JadwalSesi`.
    - Format alamat pintar `lokasiRingkasan()` dan tautan peta koordinat akurat `lokasiPetaUrl()` (mengarahkan langsung ke titik Google Maps koordinat GPS resmi).
  - **Propagasi Otomatis & Fleksibilitas Reschedule:**
    - `JadwalRutinService::generateSesiForPeriod()` mewariskan `lokasi_presensi_id` master rutin ke seluruh sesi KBM tergenerate.
    - Sinkronisasi instan saat master pola diperbarui (`syncOnMasterUpdate()`).
    - Dukungan opsi ganti atau pertahankan titik lokasi saat sesi di-reschedule oleh Tutor via modal Opsi 2.
    - Impor agenda spreadsheet (`JadwalImport`) otomatis mencocokkan string lokasi dengan master nama lokasi presensi.
  - **Pelacakan Terpadu & Interaktif Lintas Role:**
    - **Siswa (`/siswa/jadwal` & `/siswa/presensi`):** Melihat titik lokasi bimbingan KBM dan agenda sekolah beserta tombol *"Petunjuk Peta"*; form presensi foto otomatis mengunci (`targetLokasi`) koordinat dan geofence radius ke titik KBM sesi hari itu, dilengkapi banner visual informatif.
    - **Tutor (`/tutor/jadwal-sesi` & `/tutor/presensi`):** Pilihan titik lokasi resmi pada modal jadwal rutin, modal sesi sekali, dan modal reschedule; kartu sesi KBM dan agenda menampilkan rincian titik dan petunjuk Google Maps; presensi mengajar tutor otomatis menyelaraskan geofence check-in ke titik sesi KBM hari itu.
    - **Admin & Kepsek (`/admin/jadwal` & `/admin/jadwal_rutin`):** Form pemilih titik presensi resmi + teks ruang/catatan tambahan pada agenda dan master rutin, tabel desktop & kartu mobile menyajikan badge lokasi dengan tautan langsung ke navigasi peta Google Maps.
  - **Automated QA & Testing Suite:** 5 skenario pengujian komprehensif di `tests/Feature/JadwalLokasiTrackingTest.php` (agenda, propagasi rutin, pembuatan sesi sekali, reschedule ubah titik, dan presensi mandiri siswa auto-targeting) lulus 100%.

* 🟢 **Modernisasi Antarmuka Login Autentikasi (`auth.login`):** [SELESAI]
  - Redesain halaman login dengan arsitektur mobile-first (kartu fluid ringkas di layar ponsel) dan split card 2-kolom elegan di desktop/tablet.
  - Penambahan fitur toggle show/hide password, input visual icon, checkbox "Ingat Saya", tombol switch Dark/Light Mode instan, serta proteksi submit loading state.
  - Integrasi notifikasi single floating popup toast global terpadu (`app-notifications.js`), mengeliminasi alert box redundan di dalam form login.

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

### 6.1 Web Push Notification Real-Time & Unified Scheduler Engine (Multi-Role)
* 🟢 **Infrastruktur Web Push & VAPID Key Management:** [SELESAI] Integrasi paket `minishlink/web-push` dengan generator kunci VAPID otomatis via `php artisan webpush:vapid`.
* 🟢 **Auto-Sync & Client Push Manager:** [SELESAI] Sinkronisasi token browser ke database pengguna login, auto-reconnect, dan pengujian push mandiri di menu Profil seluruh role (Tutor, Siswa, Magang, Admin, Kepsek).
* 🟢 **Otomatisasi Trigger Push Notifikasi Sistem Real-Time:** [SELESAI] Konfirmasi presensi masuk/pulang, notifikasi pembuatan/perubahan jadwal sesi KBM ke siswa, alert siswa hadir ke tutor bimbingan, notifikasi pengajuan izin/lupa lapor ke Kepsek, dan broadcast pengumuman payroll.
* 🟢 **Unified Notification Scheduler & Automated Cron Reminders (`presensi:send-reminder`):** [SELESAI]
  - Pengingat pagi hari (06:30 WIB) untuk jadwal sesi belajar siswa, sesi mengajar tutor, dan shift masuk magang.
  - Pengingat presensi pulang (setiap 30 menit) bagi tutor yang telah mengajar $\ge 1$ jam dan magang yang telah menyelesaikan jam kerja harian.
  - Ringkasan harian (16:00 WIB) permohonan izin & lupa lapor yang menunggu persetujuan Kepala Sekolah.

### 6.2 Import & Export Massal Data (Bulk Data Management)
* 🟢 **Import & Export Spreadsheet Excel/CSV:** [SELESAI] Fitur pengunggahan massal (*bulk import*) data Tutor, Siswa, Jadwal/Agenda, dan Rekap Presensi Retroaktif, serta ekspor laporan presensi berstandar akreditasi menggunakan `maatwebsite/excel`.

### 6.3 WhatsApp Gateway & Single Sign-On (Jangka Panjang)
* ⚪ **Notifikasi WhatsApp Gateway:** [PENDING] Integrasi WhatsApp Gateway pihak ketiga untuk pengiriman notifikasi langsung ke nomor wali murid.
* 🟢 **Single Sign-On (SSO One-Time Signed Token & Shared Session SIMPIKAT):** [SELESAI] Integrasi autentikasi terpusat dua arah dengan portal SIMPIKAT (`pkbmpikat.com`). Mengimplementasikan verifikasi HMAC-SHA256 One-Time Signed Token, auto-login JIT Provisioning ke Presensi (`/sso/simpikat`), alur redirect keluar ke SIMPIKAT (`/sso/ke-simpikat`), serta panduan integrasi modul SIMPIKAT (`docs/panduan_integrasi_sso_simpikat.md`).

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

### 8.4 UI/UX Polish, Kontras Tombol & Navigasi Ikon
* 🟢 **Perbaikan Kontras Tombol & Aksesibilitas Warna:** [SELESAI] Penyelarasan styling tombol `.btnNavMaps` (Petunjuk Arah/Peta), `.btnLiveGpsActive`, dan tombol aksi peta di seluruh view presensi agar teks dan ikon terbaca kontras dan jelas (tidak hanya saat hover).
* 🟢 **Penyelarasan Ikon Navigasi Bawah Admin:** [SELESAI] Penambahan wrapper background `.navIconWrap` pada item menu Master Jadwal & Shift Kerja di `navigasi_bawah_admin.blade.php` agar selaras dan konsisten dengan seluruh item navigasi lainnya.
* 🟢 **Standardisasi & Kelengkapan Varian Warna KPI Card (`.kpi-card`):** [SELESAI] Melengkapi style `resources/css/app.css` untuk varian warna kartu KPI yang sebelumnya belum terdefinisi (`.cyan`, `.purple`, `.rose`, `.orange`, `.teal`, `.slate`), menyelaraskan gradient aksen `::before`, latar ikon `.kpi-icon-wrap`, angka metrik `.kpi-val`, serta optimasi kontras tema gelap (`[data-theme="dark"]`).
* 🟢 **Kelengkapan Style Komponen Tabel, Empty State & Utility (`app.css`):** [SELESAI] Melengkapi definisi kelas state kosong tabel (`.tableEmptyState`, `.table-empty-state`, `.tableEmptyIcon`, `.table-empty-icon`, `.tableEmptyTitle`, `.table-empty-title`, `.tableEmptyDesc`, `.table-empty-desc`) dengan Dark Mode, selaras `.filterActionsGroup`, komponen baris user tabel desktop & kartu mobile (`.table-user-*`, `.dmc-*`), animasi `@keyframes spin` (`.spinIcon`), `.radarInfo`, dan `.activeBody`.
* 🟢 **Standardisasi & Desain Responsif Modal Import Excel:** [SELESAI] Memperbarui dan menyelaraskan antarmuka modal impor berkas spreadsheet Excel/CSV di seluruh modul Admin (`admin/siswa`, `admin/karyawan`, `admin/jadwal`, `admin/laporan`) dengan banner panduan unduh template (`.import-template-banner`), area dropzone interaktif, kartu pratinjau berkas terpilih (`.file-selected-card`) beserta info ukuran/status siap unggah dan tombol hapus, validasi ukuran maks 5MB di sisi klien, penutup klik-luar (*backdrop click-to-close*), dukungan Dark Mode optimal, serta tata letak tombol aksi bertingkat yang 100% responsif di layar mobile tanpa *horizontal scroll*.
* 🟢 **Kebijakan Fleksibilitas Bebas Radius Presensi untuk Admin & Kepala Sekolah:** [SELESAI] Mengecualikan peran `admin` dan `kepala_sekolah` dari batasan radius geofencing sekolah/titik cabang (`GeofencingService::isExemptFromRadius()`). Mengizinkan kehadiran saat ada rapat di dinas/instansi luar atau keperluan mendesak, dengan koordinat GPS tetap tercatat sebagai bukti kehadiran, feedback visual peta responsif (`Bebas Radius Aktif &bull; Tugas Dinas/Rapat`, radar dot `pulse-blue`), banner edukatif peran, serta catatan otomatis pada notifikasi sukses dashboard.
* 🟢 **Penyelarasan Styling Tema Banner Akses Fleksibel & Binding Dark Mode Tailwind v4:** [SELESAI] Memperbaiki anomali warna banner *Akses Fleksibel (Bebas Radius) Aktif* yang tampak bernuansa gelap saat pengguna memilih tema terang akibat Tailwind CSS v4 mendeteksi preferensi OS `@media (prefers-color-scheme: dark)`. Menambahkan direktif `@custom-variant dark (&:where([data-theme=dark], [data-theme=dark] *));` pada `resources/css/app.css` agar varian dark terikat ketat ke atribut `[data-theme="dark"]`, serta menstandarisasi banner ke komponen `.statusBanner.flexible` dan `.badge-flexible` berbasis variabel warna desain PKBM Pikat.
* 🟢 **Optimalisasi Layout & Responsivitas Dashboard Admin Mobile:** [SELESAI] Memperbaiki susunan layout Dashboard Admin (`admin/dashboard.blade.php`) yang sebelumnya terlalu rapat dan terhimpit di layar ponsel akibat tag pembungkus `.laporanHeader` yang belum tertutup rapat (menyebabkan *nested padding* ganda pada grid). Menambahkan grid pintasan cepat operasional Admin (`.adminQuickSection` & `.adminQuickGrid`), memodernisasi visual bar chart mingguan (`.barChart`, `.barCount`, dan penanda hari ini), menyelaraskan kartu ringkasan (`.summaryCard` & `.summarySub`), serta menerapkan tata letak adaptif 2-tier pada baris aktivitas presensi terbaru (`.activityRow`) di mobile sehingga nama tutor tidak terpotong dan tombol aksi nyaman dijangkau ibu jari.
* 🟢 **Pembatasan Kamera Presensi Selfie (Khusus Kamera Depan & Eliminasi Flip Kamera):** [SELESAI] Membatasi sistem pengambilan foto presensi di seluruh modul (Tutor, Karyawan/Admin, Mahasiswa Magang, Siswa) agar secara ketat hanya menggunakan kamera depan (`facingMode: { ideal: 'user' }`). Menghilangkan tombol flip/tukar kamera (`#btnToggleSwitch`) dari antarmuka kamera floating toolbar (`#camControlBar`), mengunci variabel `currentFacingMode = 'user'`, serta memastikan orientasi cermin selfie (`isMirrored = true`) aktif secara alami guna mencegah manipulasi presensi foto via kamera belakang.
* 🟢 **Standarisasi Frame Preview Foto Presensi (Rasio Portrait 3:4 & True-Crop WYSIWYG):** [SELESAI] Menyelaraskan ukuran dan framing preview kamera dengan gambar aktual yang diunggah ke server pada 4 modul presensi (Karyawan/Admin, Tutor, Mahasiswa Magang, Siswa). Mengubah `.photoFrame` dari rasio horizontal 4:3 lama menjadi rasio portrait ideal `3/4` (`max-width: 380px; max-height: 480px; margin: 14px auto`) di `resources/css/app.css`. Memperbarui fungsi `snapPhoto()` di JavaScript untuk melakukan kalkulasi pemotongan simetris (*true-crop* 3:4) sebelum digambar ke kanvas, sehingga foto JPEG yang dikirim ke server memiliki framing yang 100% presisi (*What You See Is What You Get*) dengan apa yang dilihat pengguna di preview, menghilangkan ketidaksesuaian visual antara saat membidik, melihat hasil preview, dan verifikasi foto oleh admin di modal riwayat.
* 🟢 **Fitur Preloader Premium Terpadu (Konsisten & Responsif Mobile):** [SELESAI] Mengimplementasikan sistem preloader elegan di seluruh halaman aplikasi (`admin`, `kepsek`, `presensi`, dan `auth.login`) terinspirasi dari standar arsitektur `suksesmetalabadi`. Menggunakan komponen terpusat `layouts/components/preloader.blade.php`, logo resmi PKBM Pikat, indikator animasi bilah ramping (*thin animated loading bar*), styling CSS terpusat di `resources/css/app.css` dengan dukungan Dark Mode otomatis (`[data-theme="dark"]`), transisi fade-out halus, serta fallback timeout protektif 2 detik dan penanganan bfcache (`pageshow`) agar tampilan selalu mulus dan responsif di smartphone.
* 🟢 **Penyelarasan Tombol Izin & Bantuan Presensi (Integrasi Modul Digital & Izin Perangkat):** [SELESAI] Menyelaraskan tombol permohonan izin pada seluruh halaman presensi foto (`tutor`, `karyawan`, `magang`, `siswa`) agar terintegrasi resmi dengan modul sistem PKBM Pikat. Memisahkan aksi formal permohonan izin/sakit digital (`route('tutor.pengajuan-izin')` bagi tutor dengan verifikasi Kepala Sekolah, kelola izin untuk admin/kepsek) dari tombol bantuan teknis WhatsApp khusus kendala GPS/kamera. Serta menstandarisasi tombol izin akses perangkat pada banner peringatan `#permWarning` menjadi tombol aksi jelas `<ion-icon name="refresh-outline"></ion-icon> Aktifkan Izin` dengan ukuran sentuh ergonomis mobile.
* 🟢 **Penyempurnaan Spacing, Kerapihan Style & Responsivitas Mobile Seluruh Halaman Presensi Foto:** [SELESAI]
  - **Akar Masalah:** Sebelumnya, kelas global `.card` diatur dengan `padding: 0;` sehingga elemen formulir, peta, kamera, dan pemilih siswa pada halaman presensi (`tutor`, `karyawan`, `magang`, `siswa`) menempel terlalu rapat ke tepi garis batas kartu. Selain itu, kontainer `.pagePad` memiliki gutter 14px yang terlalu sempit dan membentang tanpa batas di layar lebar, komponen pencarian dan checkbox siswa (`.siswa-item-row`) tidak memiliki aturan CSS, serta pembatas daftar siswa terkunci kaku di 180px.
  - **Penyelarasan Desain CSS (`resources/css/app.css`):**
    - Mengisolasi padding kartu presensi `.pagePad .card` menjadi 22px 20px (mobile: 18px 15px) dengan sudut membulat 20px dan soft shadow elegan.
    - Menetapkan batas lebar responsif pada `.pagePad` (`max-width: 580px; margin: 0 auto; padding: 18px 16px 120px`) agar proporsional menyerupai mobile-app framing baik di ponsel maupun tablet/desktop.
    - Menata tipografi header kartu `.cardTitle` dengan garis pemisah bawah (*subtle border divider*), font 15.5px semibold/extrabold, dan mengaktifkan ikon Ionicons dengan aksen warna primer.
    - Menata `.siswa-item-row` menjadi card-item modern dengan padding 12px 14px, radius 14px, border interaktif, serta efek highlight saat dipilih. Memperluas area gulir `#listSiswaItems` menjadi 280px dengan custom sleek scrollbar.
    - Menstandarisasi tombol aksi submit `.captureBtn` dengan tinggi ergonomis 52px, ikon pendukung (`checkmark-circle-outline` / `log-out-outline`), font tebal, dan micro-interaction hover.
  - **Penyelarasan View Halaman Presensi:** Memperbarui 4 view (`resources/views/tutor/presensi_foto.blade.php`, `karyawan/presensi_foto.blade.php`, `magang/presensi_foto.blade.php`, `siswa/presensi_foto.blade.php`) dengan struktur semantic, ikon judul kartu, dan penataan margin konsisten.



---

## 9. PORTAL & PRESENSI MANDIRI SISWA (STUDENT SELF-ATTENDANCE)

### 9.1 Autentikasi & Arsitektur Role Siswa
* 🟢 **Normalisasi Identifier & Fleksibilitas Login Siswa:** [SELESAI] `AuthWebController` mendukung login menggunakan variasi format: Nomor Absen (`001`, `01`, `1`), format prefix (`sw001`, `SW001`, `SW0001`), NIK resmi (`SW202601`), maupun Email (`siswa@pkbmpikat.com`, `siswa001@pkbmpikat.com`).
* 🟢 **Desain Kotak Notifikasi Login (`login-alert`):** [SELESAI] Komponen visual `.login-alert`, `.login-alert-danger`, `.login-alert-warning`, dan `.login-alert-success` dengan animasi `fadeInSlide` di `app.css` untuk memastikan setiap pesan kesalahan/peringatan terlihat jelas.
* 🟢 **Fitur Quick Login Pengujian Multi-Role (Dev & Testing Mode):** [SELESAI] Panel pengujian cepat di halaman login (`auth.login`) untuk 5 peran sistem (`admin`, `kepala_sekolah`, `tutor`, `magang`, `siswa`) dengan 2 opsi interaksi (Masuk Langsung 1-klik via POST/GET `/quick-login` dan fitur Salin Kredensial ke form dengan animasi *pulse highlight*). Dilengkapi *Quick Role Switcher* interaktif (ikon ⚡ di topbar) saat login dalam lingkungan pengujian, proteksi environment, serta auto-provisioning fallback user dan profil siswa.

### 9.2 Presensi Mandiri Harian Siswa (Single Check-In / Kedatangan)
* 🟢 **Tabel `presensi_mandiri_siswas` & Model:** [SELESAI] Struktur tabel pencatatan kehadiran mandiri siswa lengkap dengan koordinat GPS, foto masuk/pulang, akurasi sinyal, anti-mocking, dan status kehadiran.
* 🟢 **Kamera Presensi & Geofencing Siswa (Single Check-In):** [SELESAI] Form absensi masuk tunggal per hari dengan peta interaktif Leaflet, radar proximity, verifikasi radius geofence di lokasi PKBM/mitra, kamera selfie (mirror, switch facing, flash torch), proteksi anti-duplikasi, auto-link ke status kehadiran di `jadwal_sesis`, serta eliminasi form pulang & jeda countdown 15 menit (`SiswaPresensiController.php`).
* 🟢 **Dashboard, Riwayat & Profil Siswa:** [SELESAI] Dashboard komprehensif dengan 2 status bersih (Belum Absen / Sudah Hadir), integrasi langsung kartu jadwal sesi KBM tutorial hari ini, agenda & jadwal kegiatan umum PKBM mendatang, filter riwayat kehadiran bulanan, modal preview foto presensi, serta pengaturan profil dan Web Push Notification (`SiswaDashboardController.php` & `dashboard.blade.php`).

### 9.3 Pusat Pengelolaan Akun Pengguna Terintegrasi (Unified User & Account Center)
* 🟢 **Pusat Kelola Seluruh Akun (`admin.karyawan.index`):** [SELESAI] Satu pintu terintegrasi untuk mengelola seluruh akun pengguna sistem (Tutor, Siswa, Magang, Admin, Kepala Sekolah).
* 🟢 **Dropdown Filter Peran Responsif & Live Counter Badges:** [SELESAI] Pemilihan peran/role via dropdown adaptif terpadu dalam kartu filter (Semua Peran, Pendidik/Tutor, Siswa, Mahasiswa Magang, Admin, Kepala Sekolah) dengan jumlah pengguna real-time.
* 🟢 **4 Kartu Metrik Ringkas:** [SELESAI] Ringkasan Total Pengguna (Aktif/Nonaktif), Total Tutor, Total Siswa, dan Total Staf/Magang/Manajemen.
* 🟢 **Fitur Manajemen Cepat:** [SELESAI] Reset Password Default (`password123`) sekali klik dengan konfirmasi interaktif, toggle status akun aktif/nonaktif, edit profil pengguna, dan tautan cerdas ke data modul terkait (Data Siswa, Jadwal & Slip Honor Tutor, Presensi Magang).
* 🟢 **Desain Konsisten & Responsif Mobile:** [SELESAI] Tampilan tabel desktop yang rapi dengan badge peran berkode warna, serta tampilan kartu mobile (*mobile-card-list*) yang nyaman digunakan di smartphone (touch targets $\ge 44\text{px}$).

### 9.5 Penyelarasan Desain & Responsivitas Modul Mahasiswa Magang (PKL)
* 🟢 **Standardisasi Tampilan Daftar Magang (`admin.magang.index`):** [SELESAI] Penyelarasan tampilan header, *account-stats-grid* (Total Magang, Aktif, Selesai/Nonaktif), filter card terpadu (*laporanFilterCard*), tabel data ber-avatar, badge status, dan *mobile-card-list* identik dengan modul *Kelola Akun*.
* 🟢 **Formulir Tambah & Edit Magang Responsif (`admin.magang.create`, `admin.magang.edit`):** [SELESAI] Penyusunan formulir 2-bagian terstruktur (*Identitas Akun Login* & *Informasi Instansi/Periode Magang*), upload foto profil dengan feedback ukuran file, dan aksi footer konsisten.
* 🟢 **Monitoring Presensi Magang (`admin.magang.presensi`):** [SELESAI] Rekap presensi masuk/pulang harian magang dengan metrik ringkas (*Total Log*, *Hadir Lengkap*, *Sedang Berlangsung*), filter tanggal & peserta, foto preview modal interaktif, dan format *mobile-card-list* responsif.

### 9.6 Standardisasi Desain Empty State Tabel & Kartu Mobile (Design System Consistency)
* 🟢 **Harmonisasi Komponen Empty State (`resources/css/app.css`):** [SELESAI] Mengintegrasikan estetika *Soft Badge Pill Container* (`.tableEmptyIconWrap`, ukuran 52px, border-radius 16px, background aksen transparan dengan dukungan tema terang & gelap) untuk menggantikan ikon raksasa polos tanpa wadah (`.tableEmptyIcon` 44px kusam) dan menyelaraskannya dengan `.emptyAgendaIcon`.
* 🟢 **Dukungan Khusus Kartu Kosong Mobile (`.tableEmptyCard`):** [SELESAI] Menghadirkan komponen kartu kosong elegan dengan dashed border (`border: 1.5px dashed var(--border)`), background surface adaptif, dan padding proporsional untuk menggantikan div polos yang mengambang di `.mobile-card-list`.
* 🟢 **Refactoring Seluruh View Kosong (@empty) Lintas 5 Peran:** [SELESAI] Mengeliminasi ikon ad-hoc tanpa class (seperti `icon-2xl opacity-40` di `admin/laporan` & `kepsek/presensi`), menambahkan badge ikon berkode warna pada seluruh tabel yang sebelumnya tanpa ikon (`admin/karyawan`, `admin/siswa`, `admin/jadwal_rutin`, `admin/jenjang_paket`, `admin/kategori_tutorial`, `admin/kelas`, `admin/izin`, `admin/payroll`), serta standarisasi menyeluruh pada tampilan empty state seluruh role: Tutor (`riwayat`, `payroll`, `dashboard`), Siswa (`riwayat`, `dashboard`), Magang (`riwayat`, `dashboard`), dan Kepsek (`presensi`, `laporan`, `pengajuan_izin`, `lupa_lapor`, `dashboard`).

### 9.7 Refactoring Skema Penamaan Tabel Database Bahasa Indonesia Baku (Singular & Eliminasi Double Underscore)
* 🟢 **Penamaan Tabel Singular Baku Bahasa Indonesia:** [SELESAI] Refactoring 15 tabel operasional domain PKBM agar tidak menggunakan akhiran plural `-s` bahasa Inggris (`jadwal_kerjas` $\rightarrow$ `jadwal_kerja`, `siswas` $\rightarrow$ `siswa`, `tutors` $\rightarrow$ `tutor`, `presensis` $\rightarrow$ `presensi`, `presensi_karyawans` $\rightarrow$ `presensi_karyawan`, `presensi_mandiri_siswas` $\rightarrow$ `presensi_mandiri_siswa`, `jadwal_rutins` $\rightarrow$ `jadwal_rutin`, `jadwal_sesis` $\rightarrow$ `jadwal_sesi`, `jadwals` $\rightarrow$ `jadwal`, `jenjang_pakets` $\rightarrow$ `jenjang_paket`, `kategori_tutorials` $\rightarrow$ `kategori_tutorial`, `lokasi_presensis` $\rightarrow$ `lokasi_presensi`, `magangs` $\rightarrow$ `magang`, `admins` $\rightarrow$ `admin`).
* 🟢 **Eliminasi Double Underscore (`kepala__sekolahs`):** [SELESAI] Menghapus ketidakkonsistenan double underscore pada tabel kepala sekolah menjadi `kepala_sekolah`.
* 🟢 **Konsistensi Tabel Autentikasi Laravel (`users`):** [SELESAI] Mempertahankan tabel autentikasi bawaan Laravel tetap bernama `users` sesuai konfirmasi dan standar ekosistem Laravel Auth/Sanctum.
* 🟢 **Harmonisasi Model, Validasi, Guard & Test Suite:** [SELESAI] Pembaruan deklarasi `protected $table` di seluruh Model Eloquent, update rules validasi Form Request / Controller (`exists:` & `unique:`), perbaikan pengecekan dinamis `Schema::has...`, penyesuaian seeder, serta update assertions di 20+ file PHPUnit test suite.

### 9.8 Penyelarasan Spacing & Responsivitas Mobile Halaman Admin Kelas & Form Master Jenjang (`/admin/kelas` & `/admin/jenjang-paket/create`)
* 🟢 **Modernisasi Toolbar Filter & Search Kelas (`admin.kelas.index`):** [SELESAI] Menggantikan tata letak filter dan pencarian yang sebelumnya rapat/terhimpit dengan komponen toolbar modern `.kelas-toolbar-card`, scroll horizontal pill tab (`.kelas-tabs-wrapper`) dengan active state kontras biru gradien, search input terintegrasi dengan tombol cari dan clear (x), serta banner indikator filter aktif yang menyajikan info jumlah kelas dan tombol *Reset Filter* 1-klik.
* 🟢 **Redesain Kartu Kelas Modern & Lapang (`.kelasCardModern`):** [SELESAI] Merombak kartu rombel kelas dengan layout yang lega dan bernapas: avatar inisial bergradien 48x48px, nama kelas tegas, badge jenjang terformat rapi, micro-chips tingkat & total siswa dalam grid 2 kolom, kotak catatan rombel bergaris putus-putus lembut, serta tombol aksi *Edit* dan *Hapus* dengan tinggi sentuh ergonomis (40px) yang ramah layar sentuh ponsel.
* 🟢 **Restrukturisasi Formulir Master Jenjang (`create.blade.php` & `edit.blade.php`):** [SELESAI] Menata ulang formulir pendaftaran jenjang & paket yang sebelumnya bertumpuk tanpa jeda vertikal menjadi grid responsif `.form-grid-responsive` 2-kolom dengan margin lapang (`gap: 20px`, `margin-bottom: 20px`), label khusus `.form-field-label`, helper text edukatif penomoran tingkat, info callout pedoman master jenjang, toggle kartu status aktif elegan, serta `.form-action-footer` terpadu dengan tombol Batal dan Simpan.
* 🟢 **Peningkatan CSS Universal Form Container:** [SELESAI] Mengadaptasi `.form-card-container` dengan padding responsif (`20px 16px` di layar mobile $\le 640$px dan `28px 32px` di desktop) untuk memastikan tidak ada pemotongan visual atau tampilan berdesakan di layar HP.

### 9.9 Standardisasi Style & Dimensi Tombol Action Header Payroll (`admin.payroll.index`)
* 🟢 **Harmonisasi Gaya Tombol Log Presensi KBM:** [SELESAI] Menyelaraskan tombol "Log Presensi KBM" dari `.btnOutline` menjadi `.profileBtnPrimary.btn-action-info.btn-header-action` berlatar solid cyan-blue dengan teks putih kontras dan ikon seragam tanpa override warna, selaras dengan Export Excel (`btn-action-success`), Export PDF (`btn-action-primary`), dan Master Tarif SK (`btn-action-purple`).
* 🟢 **Penyetaraan Dimensi, Ketinggian (42px) & Font Tombol Form Action Umumkan ke Tutor:** [SELESAI] Menghadirkan class utility `.btn-header-action` dan mengunci ketinggian kelima tombol secara mutlak (`height: 42px !important; min-height: 42px !important; max-height: 42px !important; line-height: 1 !important; padding: 0 16px !important;`), menyelaraskan wrapper form (`height: 42px !important; align-items: center;`), serta mengunci ukuran ikon Ionicons (17px) sehingga kelima tombol memiliki tinggi yang 100% presisi sama di desktop maupun saat transisi breakpoint mobile/tablet.

### 9.10 Penyelarasan Presisi Wrapper & Leaflet Map Container (Seamless Edge-to-Edge)
* 🟢 **Eliminasi Gap Ketinggian & Blank Space pada Kamera Presensi (`.mapBox`):** [SELESAI] Menghilangkan selisih tinggi 220px vs 240px/280px pada `.map-camera-box` dan `.leaflet-container` di dalam `.mapBox` dengan menyetel `width: 100% !important; height: 100% !important; min-height: 100% !important; border-radius: inherit !important;` serta menambahkan `isolation: isolate; transform: translateZ(0);` agar tile map Leaflet mengisi wadah secara mulus dan terpotong rapi mengikuti sudut lengkung 18px tanpa celah kosong di bawahnya.
* 🟢 **Harmonisasi Seluruh Wrapper Peta (Modal Verifikasi & Picker Master Lokasi):** [SELESAI] Menyelaraskan kontainer modal verifikasi (`.map-modal-frame-wrapper` & `.map-modal-leaflet`) serta picker lokasi (`.overview-map-frame` & `.pickerMapWrapper`) dengan penyesuaian border-radius (16px), soft shadow, proteksi bleed transform tile map, dan pengisian kontainer 100% penuh.

### 9.11 Redesain Responsif & Ergonomis Tombol Perbarui Lokasi Presensi (`.mapToolbar` / `.btnRefreshLocation`)
* 🟢 **Harmonisasi Tombol Lintas 4 Peran Presensi:** [SELESAI] Menstandarisasi penulisan tombol di 7 titik template presensi kamera (`tutor`, `karyawan`, `magang`, `siswa`) dengan menyematkan class terpadu `.btnRefreshLocation`, ikon seragam `<ion-icon name="locate-outline">` (sebelumnya hilang di magang dan siswa), dan teks label baku *"Perbarui Lokasi"*.
* 🟢 **Peningkatan Layout Responsif & Touch Target Mobile Ergonomis:** [SELESAI] Memperbaiki tata letak `.mapToolbar` di `resources/css/app.css` agar secara otomatis menyesuaikan tampilan ponsel (`@media (max-width: 640px)`): tombol melebar 100% penuh (*full-width*) dengan tinggi sentuh ideal 46px untuk kenyamanan jempol satu tangan, efek hover interaktif dengan rotasi ikon `rotate(45deg)`, elevasi bayangan halus, serta integrasi penuh tema gelap (*dark mode* kontras tinggi `#38bdf8`).

### 9.12 Migrasi Domain & Rilis Pembaruan Aplikasi Mobile APK v1.1 (`kehadiran.pkbmpikat.com`)
* 🟢 **Pembaruan Konfigurasi Capacitor Mobile APK:** [SELESAI] Mengalihkan target URL WebView Android dari `https://absen.pkbmpikat.com` menjadi `https://kehadiran.pkbmpikat.com` pada file `mobile-apk/capacitor.config.json` dan aset native Android `mobile-apk/android/app/src/main/assets/capacitor.config.json`, serta sinkronisasi ulang via `npm run sync` (`npx cap sync android`).
* 🟢 **Peningkatan Versi Aplikasi Menjadi v1.1 (`PresensiPKBMPikat-v1.1`):** [SELESAI] Memperbarui identitas dan versi aplikasi menjadi **`PresensiPKBMPikat-v1.1`** (`package.json` v1.1.0, `capacitor.config.json` appName `PresensiPKBMPikat-v1.1`, `android/app/build.gradle` `versionCode 2` & `versionName "1.1"`, dan `strings.xml`), mengompilasi ulang paket debug Android via Gradle (`gradlew.bat assembleDebug`), dan menghasilkan file distribusi siap pasang [`mobile-apk/PresensiPKBMPikat-v1.1.apk`](file:///c:/laragon/www/pengembangan-presensi-pikat/mobile-apk/PresensiPKBMPikat-v1.1.apk) (~8.2 MB).
* 🟢 **Penyelarasan Environment Laravel & CORS/Sanctum:** [SELESAI] Mengatur konfigurasi `APP_URL=https://kehadiran.pkbmpikat.com` dan `SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,kehadiran.pkbmpikat.com` pada `.env` dan `.env.example` untuk memastikan rute aset, otentikasi sesi cookie stateful, serta push notification selaras dengan domain baru.
* 🟢 **Penyelarasan Dokumentasi & Manifest WebView:** [SELESAI] Memperbarui komentar perizinan di `AndroidManifest.xml`, panduan kompilasi APK di `mobile-apk/README.md`, dan placeholder loader `mobile-apk/public/index.html`.

### 9.13 Sinkronisasi Otomatis Titik Lokasi Presensi Sesi Terjadwal & Pre-Selection Siswa (End-to-End Schedule-to-Attendance Flow)
* 🟢 **Tautan Pintar "Absen Sesi Ini" dengan Binding Jadwal Sesi:** [SELESAI] Memperbarui tombol aksi "Absen Sesi Ini" pada jadwal tutor (`tutor.jadwal-sesi.index`) dan "Absen Masuk Sekarang" pada jadwal siswa (`siswa.jadwal`) untuk meneruskan parameter rute `['jadwal_sesi_id' => $sesi->id]`.
* 🟢 **Resolusi & Propagasi Lokasi Target di Controller (`PresensiFotoController` & `SiswaPresensiController`):** [SELESAI] Controller mendeteksi parameter `jadwal_sesi_id`, melakukan eager-loading `['lokasiPresensi', 'jadwalRutin.lokasiPresensi']`, memprioritaskan sesi aktif berjalan atau sesi yang dipilih melalui parameter URL, serta menyediakan fallback andal ke lokasi pola rutin mingguan (`$todaySesi->jadwalRutin->lokasiPresensi`).
* 🟢 **Auto-Select Titik Lokasi & Re-Centering Leaflet Map Radar:** [SELESAI] Form kamera presensi (`tutor/presensi_foto.blade.php` & `siswa/presensi_foto.blade.php`) secara otomatis memilih opsi lokasi yang sesuai di dropdown `#selectLokasiPresensi`, memposisikan titik radar dan geofence circle Leaflet tepat di koordinat lokasi jadwal, menyematkan input tersembunyi `<input type="hidden" name="jadwal_sesi_id">`, dan menampilkan banner info lokasi sesi KBM terjadwal.
* 🟢 **Pre-Selection Murid Terjadwal & Interaktivitas Sesi:** [SELESAI] Pada form presensi tutor, murid yang terjadwal pada sesi tersebut otomatis tercentang pada checklist murid (`input[name="siswa_id[]"]`). Menghadirkan fungsi JavaScript `pilihSesiTerjadwal(sesiId, siswaId, lokasiId)` pada kartu daftar sesi terjadwal hari ini (`#boxScheduledSessions`) untuk transisi dinamis titik lokasi dan murid saat berpindah sesi dengan 1-klik tanpa reload halaman.
* 🟢 **Penyimpanan Integritas Data Presensi & Relasi Sesi:** [SELESAI] Metode `store()` pada `PresensiFotoController` dan `SiswaPresensiController` memvalidasi `jadwal_sesi_id`, mengunci `lokasi_presensi_id` sesuai jadwal/pilihan, mengaitkan ID sesi ke rekor presensi, dan menandai `status_kehadiran_siswa = 'hadir'`.
* 🟢 **Pengujian Komprehensif (Automated Test Suite):** [SELESAI] Menambahkan skenario pengujian pada `JadwalLokasiTrackingTest` (`test_tutor_presensi_foto_page_auto_selects_scheduled_session_location_and_student` dan `test_siswa_presensi_foto_page_auto_selects_requested_jadwal_sesi_id`), seluruh 7 tests lulus 100% (36 assertions).

### 9.14 Perbaikan Styling Halaman Login & Eliminasi Scroller Ganda (Single Unified Scroller)
* 🟢 **Eliminasi Double Scrollbars pada Halaman Login (`auth/login`):** [SELESAI] Mengidentifikasi dan memperbaiki penyebab munculnya dua scrollbar vertikal pada halaman login. Penyebab utama terjadi karena deklarasi `overflow-x: hidden;` pada `.login-wrapper` yang secara spesifikasi CSS memaksa browser mengkalkulasi `overflow-y: auto;`, ditambah efek ambient glow pseudo-element `::after` dengan posisi `bottom: -100px; left: -100px;` yang memicu ketinggian internal melebihi viewport. Di saat bersamaan, dokumen root `html`/`body` juga memiliki scrollbar viewport sehingga memunculkan dua scroller vertikal berdampingan.
* 🟢 **Restrukturisasi Wadah Ambient Glow & Penguncian Scrollbar Tunggal:** [SELESAI] Mengisolasi efek visual ambient glow ke dalam kontainer berposisi tetap (`.login-bg-glow { position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 0; }`), menyetel `.login-wrapper { overflow: visible; }`, serta menetapkan aturan pengatur scrollbar tunggal alami pada `html.login-html, body.login-page { min-height: 100%; overflow-x: hidden; overflow-y: auto; }`. Seluruh tampilan visual, kartu login split responsif, ambient blur, dan dark mode tetap terjaga sempurna dengan satu scrollbar yang mulus.

### 9.15 Eliminasi Redundansi & Harmonisasi Validasi Multi-Atribut Bahasa Indonesia Baku, Tab State Persistence & Modal Isolation
* 🟢 **Pencegahan Redundansi Pesan Password & Standardisasi Rule `current_password`:** [SELESAI] Menggantikan validasi manual dan pesan rancu (*"current password wajib diisi. kata sandi wajib diisi."*) pada `ProfileController`, `MagangDashboardController`, dan `SiswaDashboardController` dengan validasi terpadu Laravel `['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', Rules\Password::defaults()]]`. Menyelaraskan fallback `old_password` bila form input lawas mengirimkan key tersebut, dan memberikan pesan kesalahan spesifik saat kata sandi saat ini tidak cocok (*"Kata sandi saat ini tidak sesuai."*).
* 🟢 **Kamus Lokalisasi Atribut Baku Bahasa Indonesia (`lang/id/validation.php`):** [SELESAI] Menambahkan pemetaan ~50 atribut validasi ke dalam bahasa Indonesia alami dan jelas. Membedakan `current_password` (*"kata sandi saat ini"*), `password` (*"kata sandi baru"*), `old_password` (*"kata sandi lama"*), `password_confirmation` (*"konfirmasi kata sandi"*), `nama_lengkap`, `no_hp`, `earliest_minutes`, `tolerance_minutes`, `jenjang_paket_id`, `kategori_tutorial_id`, `file_excel`, dan atribut relasional lainnya sehingga tidak ada istilah teknis bahasa Inggris mentah yang muncul pada alert error.
* 🟢 **Penyimpanan State Tab Profil Multi-Role (Tab Persistence Saat Error Validasi):** [SELESAI] Memperbaiki perilaku form pada 5 peran pengguna (`admin`, `kepsek`, `tutor`, `magang`, `siswa`) di mana reload halaman saat validasi gagal mereset tab aktif kembali ke tab data pribadi ("Informasi Pribadi"). Menambahkan listener inisialisasi `@if ($errors->has('current_password') || $errors->has('password') || ...)` yang otomatis mengaktifkan tab "Keamanan Sandi" (`switchProfileTab('keamanan')`) serta menyertakan inline error helper `@error` di bawah masing-masing input password.
* 🟢 **Isolasi Error Form Modal Jadwal Sesi (`tutor/jadwal_sesi/index.blade.php`):** [SELESAI] Memisahkan pemicu modal otomatis saat error validasi. Error `file_excel` kini hanya membuka modal import Excel (`bukaModalImportJadwal()`) dan tidak membuka drawer tambah sesi baru, mencegah tumpang-tindih modal dan menyajikan pesan error tepat sasaran.
* 🟢 **Automated Testing & QA:** [SELESAI] Menambahkan feature test komprehensif `tests/Feature/ProfilePasswordValidationTest.php` mencakup 4 skenario verifikasi validasi password profil (field kosong, kata sandi saat ini salah, konfirmasi password tidak cocok, dan pembaruan password sukses) yang lulus 100%.

---

## 10. QUALITY ASSURANCE & TESTING (TEST SUITE)
 
* 🟢 **Standardisasi Penulisan Kode (Laravel Pint):** [SELESAI] `vendor/bin/pint --format agent` lolos 100% di seluruh file controller, model, view, seeder, dan migration.
* 🟢 **Automated Testing Suite (PHPUnit):** [SELESAI] Seluruh **149 Feature & Unit Tests** lulus 100% (**697 assertions**) mencakup seluruh alur presensi tutor, magang, admin, kepsek, siswa mandiri single check-in, dan modul Quick Login multi-role.
* 🟢 **Vite Production Assets:** [SELESAI] Kompilasi CSS & JS (`npm run build`) berjalan bersih tanpa error.

---

## 11. MATRIKS PRIORITAS DAN TAHAPAN IMPLEMENTASI (ROADMAP MATRIX)

| Tahap | Fokus Utama | Target Hasil | Estimasi Dampak | Status |
|---|---|---|---|---|
| **Fase 1** | Keamanan, Upgrade Laravel 13, Standardisasi Views, Storage & Workflow Lupa Lapor | Sistem stabil di Laravel 13, persetujuan lupa lapor interaktif, storage terabstraksi | 🔴 Kritis (Keamanan & Stabilitas) | 🟢 Selesai |
| **Fase 2** | Presensi Multi-Moda, Multi-Geofence Radius, Anti Fake GPS, PWA, & Modul Honor/Payroll | Data presensi terverifikasi valid secara lokasi multi-titik, offline PWA, & honor terhitung otomatis | 🟡 Tinggi (Integritas Data) | 🟢 Selesai |
| **Fase 3** | Web Push Notification Real-Time, Bulk Import/Export Excel, & Analytics KPI | Notifikasi push instan di HP, manajemen data massal, & dashboard analitik eksekutif | 🟢 Sedang (Efisiensi Operasional) | 🟢 Selesai |
| **Fase 4** | **Honorarium SK: Master Kategori & Tarif Otomatis Sesi SK** | 6 tipe tarif flat sesi SK, dynamic resolver, snapshot immutability, & penghapusan tarif manual | 🔴 Kritis (Akurasi Finansial & Regulasi) | 🟢 Selesai |
| **Fase 5** | **UI/UX Excellence, Unified Modal/Dialog, & Validasi Rombel Paket** | Sticky topbar, spacing dashboard rapi, pop-up dialog modern, form izin lega, & validasi paket gabungan rombel | 🟡 Tinggi (User Experience & Integritas) | 🟢 Selesai |
| **Fase 6** | **Jadwal Sesi Belajar, Reschedule / Sesi Pengganti & Master Lokasi** | Tabel `jadwal_sesis`, kalender bulanan tutor, evaluasi shift jam fleksibel sesi pengganti, auto-complete sesi di kamera presensi, bottom nav sync, & master lokasi multi-geofence | 🔴 Kritis (Operasional KBM & Fleksibilitas) | 🟢 Selesai |
| **Fase 7** | **Portal & Presensi Mandiri Siswa PKBM (Student Self-Attendance)** | Autentikasi siswa, dashboard mandiri, absensi foto selfie + Leaflet geofencing, riwayat kehadiran, dan profil akun siswa | 🔴 Kritis (Aktivitas Siswa & Digitalisasi KBM) | 🟢 Selesai |
| **Fase 8 (Mendatang)** | Single Sign-On (SIM), WhatsApp Gateway, Secure Private Storage, & AI Recognition | Ekosistem terintegrasi utuh dengan SIM lembaga, WA gateway wali murid, & proteksi AI lanjutan | 🔵 Strategis (Skalabilitas Sistem) | ⚪ Pending |

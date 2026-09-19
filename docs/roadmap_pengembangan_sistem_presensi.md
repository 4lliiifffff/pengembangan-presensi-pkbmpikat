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
* 🟢 **Modernisasi Infrastruktur Peta Leaflet (Aset Lokal, CSS Dark Mode Filter & Modul JS Bersama):** [SELESAI]
  - **Eliminasi CDN Eksternal:** Menghapus seluruh dependensi CDN `unpkg.com` dan gambar remote GitHub. Menginstall `leaflet` via NPM dan mem-bundle CSS serta JS langsung ke pipeline Vite aplikasi (`resources/js/app.js`).
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

### 8.4 UI/UX Polish, Kontras Tombol & Navigasi Ikon
* 🟢 **Perbaikan Kontras Tombol & Aksesibilitas Warna:** [SELESAI] Penyelarasan styling tombol `.btnNavMaps` (Petunjuk Arah/Peta), `.btnLiveGpsActive`, dan tombol aksi peta di seluruh view presensi agar teks dan ikon terbaca kontras dan jelas (tidak hanya saat hover).
* 🟢 **Penyelarasan Ikon Navigasi Bawah Admin:** [SELESAI] Penambahan wrapper background `.navIconWrap` pada item menu Master Jadwal & Shift Kerja di `navigasi_bawah_admin.blade.php` agar selaras dan konsisten dengan seluruh item navigasi lainnya.
* 🟢 **Standardisasi & Kelengkapan Varian Warna KPI Card (`.kpi-card`):** [SELESAI] Melengkapi style `resources/css/app.css` untuk varian warna kartu KPI yang sebelumnya belum terdefinisi (`.cyan`, `.purple`, `.rose`, `.orange`, `.teal`, `.slate`), menyelaraskan gradient aksen `::before`, latar ikon `.kpi-icon-wrap`, angka metrik `.kpi-val`, serta optimasi kontras tema gelap (`[data-theme="dark"]`).
* 🟢 **Kelengkapan Style Komponen Tabel, Empty State & Utility (`app.css`):** [SELESAI] Melengkapi definisi kelas state kosong tabel (`.tableEmptyState`, `.table-empty-state`, `.tableEmptyIcon`, `.table-empty-icon`, `.tableEmptyTitle`, `.table-empty-title`, `.tableEmptyDesc`, `.table-empty-desc`) dengan Dark Mode, selaras `.filterActionsGroup`, komponen baris user tabel desktop & kartu mobile (`.table-user-*`, `.dmc-*`), animasi `@keyframes spin` (`.spinIcon`), `.radarInfo`, dan `.activeBody`.
* 🟢 **Standardisasi & Desain Responsif Modal Import Excel:** [SELESAI] Memperbarui dan menyelaraskan antarmuka modal impor berkas spreadsheet Excel/CSV di seluruh modul Admin (`admin/siswa`, `admin/karyawan`, `admin/jadwal`, `admin/laporan`) dengan banner panduan unduh template (`.import-template-banner`), area dropzone interaktif, kartu pratinjau berkas terpilih (`.file-selected-card`) beserta info ukuran/status siap unggah dan tombol hapus, validasi ukuran maks 5MB di sisi klien, penutup klik-luar (*backdrop click-to-close*), dukungan Dark Mode optimal, serta tata letak tombol aksi bertingkat yang 100% responsif di layar mobile tanpa *horizontal scroll*.
* 🟢 **Kebijakan Fleksibilitas Bebas Radius Presensi untuk Admin & Kepala Sekolah:** [SELESAI] Mengecualikan peran `admin` dan `kepala_sekolah` dari batasan radius geofencing sekolah/titik cabang (`GeofencingService::isExemptFromRadius()`). Mengizinkan kehadiran saat ada rapat di dinas/instansi luar atau keperluan mendesak, dengan koordinat GPS tetap tercatat sebagai bukti kehadiran, feedback visual peta responsif (`Bebas Radius Aktif &bull; Tugas Dinas/Rapat`, radar dot `pulse-blue`), banner edukatif peran, serta catatan otomatis pada notifikasi sukses dashboard.



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

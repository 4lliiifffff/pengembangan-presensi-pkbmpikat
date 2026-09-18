# PROGRESS PENGEMBANGAN SISTEM PRESENSI DIGITAL PKBM PIKAT

**Tanggal Pembaruan:** 17 September 2026  
**Versi Framework:** Laravel 13.31.0 (PHP 8.5.1)  
**Status Proyek:** Fase 1, 2, 3, 4, 5 & 6 (Keamanan, Multi-Moda, Multi-Geofence Radius, Payroll SK Dinamis, Web Push Real-Time, Standardisasi UI/UX, Master Kelas/Paket Relasional, & Penjadwalan Sesi Pengganti / Reschedule Future-Proof)  
**Repositori Remote:** `https://github.com/4lliiifffff/pengembangan-presensi-pkbmpikat.git` (Branch: `main`)  

---

## 📊 RINGKASAN PROGRESS KESELURUHAN

```mermaid
pie title Status Fitur & Pengkondisian Sistem
    "Selesai (Completed)" : 44
    "Dalam Proses (In Progress)" : 0
    "Belum Dimulai / Backlog (Pending)" : 6
```

| Kategori | Jumlah Item Roadmap | Selesai (🟢) | Dalam Proses (🟡) | Belum Dimulai (⚪) |
|---|---|---|---|---|
| 1. Keamanan, Infrastruktur & Performa | 7 | 6 | 0 | 1 |
| 2. Core Presensi & Validasi Lokasi | 9 | 8 | 0 | 1 |
| 3. Payroll & Master Honorarium SK | 7 | 7 | 0 | 0 |
| 4. Penjadwalan Sesi Belajar, Reschedule & Jadwal Rutin Siswa (*New*) | 6 | 6 | 0 | 0 |
| 5. Master Data Relasional & Siklus Siswa | 4 | 4 | 0 | 0 |
| 6. Integrasi & Web Push Notifikasi | 5 | 3 | 0 | 2 |
| 7. Executive Dashboard & Analytics | 3 | 2 | 0 | 1 |
| 8. UI/UX Excellence & Responsive Layout | 5 | 5 | 0 | 0 |
| 9. Codebase, Standardisasi & QA | 5 | 5 | 0 | 0 |
| **Tambahan (Infrastruktur Teknis & VCS)** | **3** | **3** | **0** | **0** |
| **TOTAL** | **50** | **44** | **0** | **6** |

---

## 🚀 1. DETAIL PROGRESS BERDASARKAN ROADMAP PENGEMBANGAN

### 1. Keamanan, Infrastruktur & Performa (System Hardening)

#### 1.1 Keamanan Autentikasi & Akses Berkas
- 🟢 **Upgrade Framework Laravel 13 (v13.31.0)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Memperbarui `composer.json` ke `"laravel/framework": "^13.0"` dan `"laravel/tinker": "^3.0"`, menyelaraskan dependensi `nunomaduro/collision`, serta meregenerasi autoloader sehingga aplikasi berjalan stabil di atas versi Laravel 13 terbaru (v13.31.0).
- 🟢 **Perbaikan Deprecation Warning PHP 8.5**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Memperbarui `config/database.php` pada opsi koneksi `mysql` dan `mariadb` menggunakan pengecekan dinamis `(defined('Pdo\Mysql::ATTR_SSL_CA') ? Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA)` untuk menggantikan konstanta `PDO::MYSQL_ATTR_SSL_CA` yang *deprecated* di PHP 8.5+.
- 🟢 **Mekanisme Rate Limiting Login**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Menambahkan pembatasan percobaan login (maksimal 5 kali per menit per IP/akun) menggunakan Laravel `RateLimiter` dan middleware `throttle:login` di `routes/web.php` & `routes/api.php` untuk mencegah serangan *Brute Force*, dilengkapi respon ramah (pesan peringatan web & HTTP 429 JSON API) serta pembersihan counter saat login berhasil.
- 🟢 **Konfigurasi Reverse Proxy & HTTPS Enforcement**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Menambahkan `$middleware->trustProxies(at: '*')` di `bootstrap/app.php` dan `URL::forceScheme('https')` pada `AppServiceProvider` saat request memiliki header SSL/Proxy (`x-forwarded-proto === 'https'`) untuk mencegah terjadinya *Mixed Content Block* pada PWA dan Web Push Notification di reverse proxy/tunnel.
- ⚪ **Secure Storage Foto Presensi (Private Storage & Signed URLs)**
  - **Status:** **PENDING**
  - **Rincian Implementasi:** Pemindahan direktori foto presensi sensitif ke `storage/app/private/` dengan pengaksesan via *Temporary Signed URL* yang mewajibkan autentikasi pengguna dan pembatasan waktu akses.

#### 1.2 Manajemen Log & Infrastruktur Server
- 🟢 **Pembersihan & Pengamanan File Error Log**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Membuang file `error_log` liar bawaan server cPanel/Apache, mengalokasikan direktori log ke `storage/logs/`, dan menambahkan pola `error_log` pada `.gitignore` agar tidak mengekspos credential dan log di repositori Git.
- 🟢 **Environment Worker & Config Tuning**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Mengatur `PHP_CLI_SERVER_WORKERS=1` dan `APP_URL=http://localhost` pada file `.env` untuk konsistensi server reloader dan pengujian rute HTTP.
- ⚪ **Penerapan Script Deployment Otomatis (CI/CD Pipeline)**
  - **Status:** **PENDING**
  - **Rincian Implementasi:** Pembuatan script *post-deploy* (misal via GitHub Actions) yang otomatis menjalankan `composer install --no-dev`, `php artisan config:cache`, `php artisan route:cache`, dan `php artisan migrate --force`.

---

### 2. Pengembangan Fitur Inti Presensi (Attendance Core Enhancement)

#### 2.1 Storage Abstraction & Management Foto Presensi
- 🟢 **Abstraksi Storage Disk Public & Model Accessor**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** 
    - Mengabstraksi seluruh controller upload foto (`ProfileController`, `KaryawanController`, `PresensiFotoController`, `KaryawanPresensiController`) menggunakan Laravel `Storage::disk('public')->putFileAs()`.
    - Memindahkan seluruh foto legacy dari `public/uploads/` ke `storage/app/public/uploads/` dan menghapus direktori `public/uploads/` sepenuhnya dari web root.
    - Menambahkan Eloquent Accessors (`$user->foto_url`, `$presensi->foto_mulai_url`, `$presensi->foto_selesai_url`) pada model `User`, `Presensi`, dan `PresensiKaryawan` untuk menjamin 100% *backward compatibility*.

#### 2.2 Workflow Digital Lupa Lapor (Retroactive Attendance Approval)
- 🟢 **Persetujuan Interaktif Kepala Sekolah**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** 
    - Restrukturisasi dan standardisasi nama model menjadi **`PengajuanLupaLapor`** dengan tabel **`pengajuan_lupa_lapor`** (menggantikan nama legacy `Lapor_Lapor` / `lapor__lapors`).
    - Menambahkan status persetujuan (`pending`, `disetujui`, `ditolak`) dan kolom `catatan_kepsek`.
    - Menyempurnakan alur pengajuan oleh Tutor serta persetujuan interaktif oleh Kepala Sekolah.
    - **Otomatisasi Rekap Presensi**: Ketika Kepala Sekolah mengklik "Setujui", sistem secara otomatis melakukan *upsert* (membuat/memperbarui) data kehadiran pada tabel `presensis` (`status = 'hadir'`).

#### 2.3 Validasi Lokasi & Fitur Lanjutan
- 🟢 **Presensi Multi-Moda (Sekolah, Kunjungan Rumah, Online)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** 
    - Menambahkan kolom `moda_pembelajaran` (`sekolah`, `kunjungan_rumah`, `online`) dan `link_daring` pada tabel `presensis`.
    - Mengintegrasikan pemilih moda pembelajaran pada form presensi tutor (`tutor/presensi_foto.blade.php`).
    - **Tatap Muka Sekolah**: Strict Geofencing dari titik sekolah PKBM Pikat.
    - **Kunjungan Rumah (Home Visit)**: Catat titik lokasi GPS kunjungan + foto di rumah murid.
    - **Pembelajaran Online**: Bebas batas radius lokasi + wajib melampirkan foto layar/link ruang pertemuan (Zoom/GMeet).
    - Menambahkan Eloquent Accessor `$presensi->moda_label` untuk kemudahan pelaporan.
- 🟢 **Master Multi-Titik Lokasi Presensi & Pemilihan Tempat Absen Berbasis Radius Dinamis**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Tabel & Model `LokasiPresensi` (`lokasi_presensis`)**: Kolom `nama_lokasi`, `alamat`, `tipe` (`pusat`, `cabang`, `mitra`, `lainnya`), `latitude`, `longitude`, `radius_meter`, dan `is_active`.
    - **CRUD Master Lokasi Admin (`Admin\LokasiPresensiController`)**: Pengelolaan titik-titik cabang/kampus/mitra PKBM Pikat dengan custom radius per titik, toggle status aktif, dan proteksi integritas data.
    - **Pemilih Lokasi Interaktif di Kamera Presensi**: Dropdown `lokasi_presensi_id` pada halaman presensi Tutor, Karyawan, dan Magang.
    - **Validasi Radius Matematis Haversine (`GeofencingService::checkSelectedLokasiRadius`)**: Memverifikasi posisi koordinat pengguna terhadap titik lokasi terpilih. Jika jarak melebihi `radius_meter` titik lokasi tersebut, presensi ditolak dengan alert selisih meter riil.
    - **Automated Feature Test**: 8 skenario komprehensif pada `tests/Feature/LokasiPresensiTest.php`.
- 🟢 **Pembaruan Infrastruktur Peta Leaflet: Bundling Aset Lokal, Auto Dark Mode Tile CartoDB Dark Matter, & Konsolidasi Modul Bersama (`resources/js/leaflet-presensi.js`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Pembersihan Dependensi CDN Eksternal**: Menghapus seluruh link `<link>` dan `<script>` eksternal `unpkg.com/leaflet@1.9.4` serta asset gambar remote `raw.githubusercontent.com/pointhi/leaflet-color-markers` dari 9 view aplikasi (`siswa`, `tutor`, `magang`, `karyawan`, `admin/lokasi-presensi`, `admin/laporan`, `kepsek/presensi`).
    - **Bundling Mandiri via NPM & Vite**: Menginstall package `leaflet` ke `package.json`, mengimpor style `leaflet/dist/leaflet.css` dan modul `leaflet-presensi.js` langsung ke `resources/js/app.js`, serta mengekspor `window.L = L` agar aplikasi mandiri secara offline.
    - **Resolusi Path Default Marker Icon & Eliminasi HTTP 500 Subpath**: Memperbaiki issue di mana Leaflet default icon mengasumsikan marker asset berada di subpath URL halaman saat ini (`/admin/lokasi-presensi/marker-shadow.png` & `marker-icon-2x.png`). Menaruh salinan fisik aset di `public/images/leaflet/`, mengimpor marker PNG melalui Vite asset resolver dengan `delete L.Icon.Default.prototype._getIconUrl` dan `L.Icon.Default.mergeOptions(...)`, serta memperbarui pemanggilan marker di form Admin Lokasi Presensi (`create`, `edit`, `index`) agar menggunakan `window.createPinIcon('target')`.
    - **Auto Dark Mode Tile Switching**: Mengintegrasikan layer CartoDB Dark Matter (`https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png`) saat tema `[data-theme="dark"]` aktif dan OpenStreetMap standar saat mode terang. Dilengkapi `MutationObserver` pada atribut `data-theme` dokumen HTML root untuk transisi visual instan tanpa refresh halaman.
    - **Konsolidasi Modul Bersama (`resources/js/leaflet-presensi.js`)**: Mengeliminasi 100+ baris kode redundan Leaflet di 4 view kamera presensi berbeda (`siswa/presensi_foto.blade.php`, `tutor/presensi_foto.blade.php`, `magang/presensi_foto.blade.php`, `karyawan/presensi_foto.blade.php`) dengan fungsi terpadu `window.createPresensiMap(options)` yang menangani kalkulasi Haversine, akurasi GPS ring, polyline track rute dinamis, multi-lokasi markers, dan fitBounds secara otomatis.
    - **Marker Pin SVG Lokal & Bebas Risiko Broken Asset**: Menggantikan ikon remote dengan `L.divIcon` inline SVG modern (Pin Merah dengan drop shadow untuk target sekolah/gedung, Pin Biru dengan efek animasi pulse ring untuk posisi user, dan Pin Abu-abu untuk alternatif titik presensi).
    - **Styling Kontrol & Popup Peta Mode Gelap**: Menambahkan CSS khusus pada `resources/css/app.css` untuk `.leaflet-container`, kontrol zoom, attribution bar, dan `.leaflet-popup-content-wrapper` agar selaras dengan skema warna gelap aplikasi.
- 🟢 **Kalkulasi Radius Lokasi Sekolah (Rumus Haversine), Live Tracking & Interactive Peta Leaflet**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Membuat service class `App\Services\GeofencingService` dengan fungsi `calculateDistance()` menggunakan **Rumus Haversine** untuk menghitung jarak antara dua pasang koordinat GPS (latitude/longitude) dalam satuan meter, serta method helper `getGoogleMapsDirectionsUrl()` dan `reverseGeocode()`.
    - Menambahkan titik koordinat sekolah PKBM Pikat (`sekolah_lat`, `sekolah_lng`) dan toleransi radius (`radius_meter` = 100m) pada file konfigurasi `config/lokasi.php` dan file environment `.env`.
    - **Live GPS Tracking & Line Track Polyline (`navigator.geolocation.watchPosition` & `L.polyline`)**: Memantau pergerakan pengguna secara realtime saat berjalan/berkendara mendekati sekolah dan menampilkan garis panduan beranimasi putus-putus ke gerbang PKBM Pikat saat berada di luar radius.
    - **Proximity Radar & Auto-Unlock**: Widget jarak interaktif (`.proximityRadarCard`) yang menampilkan sisa meter menuju zona absensi, tombol cepat *"Petunjuk Arah (Google Maps)"*, dan auto-unlock status hijau serta getaran haptic begitu masuk radius 100m.
    - **Interactive Leaflet Map Modal di Rekap Presensi**: Menggantikan iframe statis pada tabel laporan Admin (`admin/laporan/index.blade.php`) dengan peta Leaflet interaktif yang memvisualisasikan titik presensi, titik sekolah, radius geofence, dan garis ukur selisih jarak.
    - Pengujian otomatis komprehensif pada `tests/Feature/GeofencingTest.php`.
- 🟢 **Fitur Kontrol Kamera Lanjutan (Mirror, Switch Camera, Grid 3x3, Flash/Torch)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Mirror Mode (`🪞 Mirror`)**: Pratinjau real-time flip horizontal pada video preview dan penangkapan gambar yang di-flip secara konsisten pada 2D canvas HTML5.
    - **Switch Camera (`🔄 Switch`)**: Beralih secara instan antara Kamera Depan (Selfie) dan Kamera Belakang (Kelas/Siswa).
    - **Grid Komposisi (`📐 Grid 3x3`)**: Overlay garis bantu 3x3 *Rule of Thirds* untuk kerapihan foto presensi.
    - **Deteksi Flash/Torch (`⚡ Flash`)**: Integrasi pengontrol senter perangkat jika didukung oleh browser/kamera.
- 🟢 **Seeder Data Komprehensif Seluruh Role & Skenario Riwayat**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Suite seeder lengkap (`AdminSeeder`, `UserRoleSeeder`, `MagangSeeder`, `KategoriTutorialSeeder`, `SiswaSeeder`, `JadwalSeeder`, `DummyPresensiSeeder`, `LokasiPresensiSeeder`) menggunakan metode `updateOrCreate` untuk keamanan re-seeding tanpa duplikasi data.
- 🟢 **Deteksi Manipulasi GPS (Anti Fake GPS) & Validasi Akurasi Sinyal**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Kolom `lokasi_akurasi` (satuan meter) dan `is_mocked` (boolean) pada tabel `presensis`.
    - **Validasi Provider Palsu**: Implementasi method `validateGpsIntegrity()` pada `GeofencingService` untuk menolak presensi jika lokasi berasal dari aplikasi mock location / Fake GPS.
    - **Batas Toleransi Akurasi (`max_accuracy_meter` = 200m)**: Jika akurasi buruk ($> 200$m) atau $0$m, presensi otomatis ditolak.
- 🟢 **PWA (Progressive Web App) & Offline Mode**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** 
    - **Web App Manifest (`public/manifest.json`)**: Konfigurasi PWA mandiri (`standalone`, theme color `#0B5ED7`).
    - **Service Worker (`public/sw.js`)**: Caching aset statis & offline fallback Network-First Cache-Fallback.
    - **Penyimpanan Lokal IndexedDB (`resources/js/offline-presensi.js`)**: Database `PikatPresensiOfflineDB` dan antrean sync offline otomatis.
- 🟢 **Pengajuan Izin & Sakit Mandiri oleh Tutor**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Tabel `pengajuan_izin_sakit`, formulir mandiri Tutor dengan uploader bukti (PDF/JPG/PNG max 2MB), dan alur approval Kepala Sekolah yang otomatis menyinkronkan status `'izin'`/`'sakit'` pada tabel `presensis`.
- 🟢 **Modul Presensi Khusus Role Karyawan Magang (Mahasiswa Magang / Siswa PKL)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Tabel `magangs`, alur Clock-In/Clock-Out berbasis foto selfie & geofence 100m, dashboard khusus, monitoring admin, dan ekspor Laporan Rekap Presensi Magang ke PDF.
- ⚪ **Verifikasi Wajah Otomatis (Face Matching / AI Recognition)**
  - **Status:** **PENDING**
  - **Rincian Implementasi:** Mengintegrasikan pemrosesan AI untuk membandingkan foto presensi tutor secara real-time dengan foto profil master.

---

### 3. Integrasi Manajemen Penggajian & Honorarium Berbasis SK (Payroll System)

#### 3.1 Master Kategori Tutorial & Tarif SK Kepala PKBM
- 🟢 **Master Kategori & Skema Tarif SK Kepala PKBM**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Tabel `kategori_tutorials`, model `KategoriTutorial`, dan seeder 6 kategori master SK (Komunitas 2j, 3j, ABK, Gabungan Rombel, Distance Learning 1,5j & DL ABK).
    - Status **ABK** dikunci di data master Siswa (`siswas.is_abk`).
    - Kolom `durasi_pilihan`, `kategori_tutorial_id`, dan `nominal_honor_snapshot` pada tabel `presensis`.

#### 3.2 Dynamic Resolver & Snapshot Immutability
- 🟢 **Engine Honor Dinamis & Snapshot Finansial (`PayrollService`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Method `PayrollService::resolveHonorSesi()` otomatis mencocokkan moda, durasi sesi pilihan tutor, status ABK siswa, dan status gabungan rombel $\rightarrow$ mengunci nilai `nominal_honor_snapshot` saat absensi dibuat.
    - **Snapshot Immutability**: Menjamin nominal honor historis yang sudah tersimpan di masa lalu tidak berubah meskipun tarif master diperbarui.
    - **Drop Kolom Legacy**: Menghapus total kolom redundan `tarif_per_jam` dari tabel `siswas` via migrasi bersih.

#### 3.3 Slip Gaji Digital & Rekapitulasi Anggaran
- 🟢 **Ekspor Slip Gaji PDF & Rekapitulasi Anggaran**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Tampilan slip gaji web dan PDF (`slip_pdf.blade.php`), rekapitulasi anggaran bulanan admin, dan broadcast pengumuman payroll via Web Push Notification.

#### 3.4 Dinamisasi Jenis Layanan & Proteksi Relasi Menyeluruh (Admin Lifecycle)
- 🟢 **Konfigurasi Jenis Layanan Dinamis & Perlindungan Relasi Berlapis**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Validasi Dinamis Tanpa Batas:** Mengganti validasi kaku `in:komunitas,dl,lainnya` di `KategoriTutorialController` menjadi string fleksibel (`max:50`) dengan sanitasi otomatis. Admin bebas mendaftarkan jenis layanan baru (misal: *Homeschooling*, *Kursus Vokasi*, *Bimbingan Intensif*, dsb.).
    - **Penyelarasan Style & Responsivitas Mobile Form (`create.blade.php` & `edit.blade.php`):**
      - Mengadopsi struktur tata letak terstandarisasi: `.form-card-container`, `.form-grid-responsive`, `.form-field-wrapper`, dan `.field-help-text`.
      - Menggunakan label modern `.filterFieldLabel` dengan kontras tinggi di mode terang maupun mode gelap.
      - Antarmuka dinamis dilengkapi `<datalist id="listJenisLayanan">` dan tombol tag rekomendasi cepat (*Quick Tag Pills*).
      - Saklar status aktif, klasifikasi ABK, dan rombel gabungan diselaraskan menggunakan komponen `.checkbox-toggle-card` dengan area klik ramah jempol.
      - Menambahkan banner informatif `.info-callout-box` di halaman edit (menampilkan metrik penggunaan sesi, jadwal sesi, dan jadwal rutin) dan halaman create (edukasi penguncian tarif snapshot finansial).
      - Menyelaraskan tombol aksi footer `.form-action-footer` (`.btnOutline` dan `.profileBtnPrimary`) dengan penataan *full-width* responsif pada breakpoint ponsel ($\le 640$px).
    - **Proteksi Integritas Relasi Berlapis (Dual-Layer Protection):** Penghapusan kategori layanan yang telah memiliki keterikatan dengan `presensis`, `jadwal_sesis`, atau `jadwal_rutins` secara otomatis dicegah dari penghapusan fisik dan dialihkan menjadi status **Non-Aktif** (`is_aktif = false`). Kategori seketika hilang dari formulir pembuatan jadwal baru, namun seluruh arsip riwayat presensi dan slip gaji masa lalu tetap aman 100%. Kategori yang belum pernah dipakai tetap dapat dihapus permanen.
    - **Pewarisan Kategori Langsung ke Presensi:** `PresensiFotoController` memprioritaskan kategori SK yang melekat pada sesi terjadwal (`jadwalSesi->kategori_tutorial_id`) saat tutor absen masuk, sehingga layanan baru apa pun langsung terhitung akurat pada payroll.
    - **Design System Badge:** Penambahan kelas CSS `.badge-layanan-custom` dan accessor model `$kategori->jenis_layanan_badge_class` serta `$kategori->jenis_layanan_label`.
    - **Testing Suite:** Feature test `test_admin_can_create_custom_dynamic_jenis_layanan_and_delete_protection` lulus di `DynamicKategoriTutorialPayrollTest.php`.

---

### 4. Penjadwalan Sesi Belajar & Sesi Pengganti (*Make-Up Class / Reschedule*)

#### 4.1 Database & Relasi Jadwal Sesi
- 🟢 **Tabel `jadwal_sesis` & Model `JadwalSesi`**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Migrasi `2026_09_17_000005_create_jadwal_sesis_table.php` dan Model `JadwalSesi.php`.
    - Kolom lengkap: `tutor_id`, `siswa_id`, `kategori_tutorial_id`, `jadwal_kerja_id`, `tanggal_rencana`, `jam_masuk_rencana`, `jam_pulang_rencana`, `durasi_jam`, `jenis_sesi` (`reguler`, `pengganti`, `tambahan`, `ujian`), `status` (`terjadwal`, `berlangsung`, `selesai`, `dibatalkan`), `tanggal_asli`, `alasan_penggantian`, `catatan`, dan `presensi_id`.
    - Relasi `hasMany(JadwalSesi::class)` pada model `Tutor` dan `Siswa`, serta `belongsTo(JadwalSesi::class)` pada `Presensi`.

#### 4.2 Evaluasi Jam Masuk Fleksibel Sesi Pengganti
- 🟢 **Evaluasi Presensi Terhadap Jam Rencana Sesi (`ShiftPresensiService`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Method `ShiftPresensiService::evaluateCheckIn()` menerima objek `?JadwalSesi $jadwalSesi`.
    - Ketika tutor melakukan presensi untuk sesi pengganti (misal jam 14:00), evaluasi keterlambatan dan toleransi dihitung presisi terhadap jam target sesi tersebut, bukan dipaksa menggunakan jam shift pagi default (07:30).

#### 4.3 Antarmuka Kalender Bulanan & Modal Jadwal Pengganti
- 🟢 **Manajemen Kalender & Sesi Pengganti Tutor (`JadwalSesiController`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Controller `JadwalSesiController.php` dengan rute `tutor.jadwal-sesi.*` (`index`, `store`, `update`, `destroy`).
    - Halaman kalender interaktif bulanan (`tutor/jadwal_sesi/index.blade.php`), kartu sesi mobile responsif, dan modal popup `Buat Jadwal Pengganti`.
    - Tab switcher pada agenda tutor (`tutor/jadwal.blade.php`): Sesi Belajar Murid & Agenda Pengumuman Sekolah.
    - Integrasi kartu sesi aktif pada pratinjau kamera presensi (`tutor/presensi_foto.blade.php`), dengan auto-link & auto-complete sesi saat presensi dikirim.

#### 4.4 Penyelarasan Bottom Navigation Bar & Drawer
- 🟢 **Penyelarasan Navigasi Bawah Tutor & Drawer Tambahan**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Item ke-4 Bottom Nav Tutor (`navigasi_bawah_tutor.blade.php`) diarahkan langsung ke `route('tutor.jadwal-sesi.index')` dengan indikator aktif cerdas untuk seluruh sub-rute `tutor.jadwal*`.
    - Drawer menu "Lainnya" dilengkapi kartu cepat Agenda PKBM, Slip Payroll, Pengajuan Izin, Lupa Lapor, dan Profil Saya.

#### 4.5 Arsitektur Penuh Role Siswa, Single Check-In & Modul Jadwal Belajar
- 🟢 **Sistem Presensi Mandiri Siswa (Single Check-In)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Alur presensi siswa disederhanakan menjadi 1x check-in masuk harian (selfie kamera + validasi radius geofencing sekolah PKBM tanpa countdown 15 menit pulang).
    - Status harian siswa di controller `SiswaDashboardController.php` berbasis 2-state (`belum` vs `selesai`).
    - Otomatis menghubungkan status kehadiran siswa di tabel `jadwal_sesis` (`status_kehadiran_siswa = 'hadir'`) saat presensi mandiri disimpan.
- 🟢 **Modul Jadwal & Agenda Kalender Siswa (`siswa.jadwal`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Method `SiswaDashboardController::jadwal(Request $request)` dan view `resources/views/siswa/jadwal.blade.php`.
    - Kalender bulanan interaktif (`.agendaHeaderCard`, `.agendaMonthGrid`) dengan penanda dot event gabungan (sesi belajar KBM murid bersama tutor + agenda pengumuman resmi PKBM).
    - Daftar card detail sesi tutorial belajar (jam sesi, nama tutor pengajar, mapel/kategori tutorial, shift, dan status kehadiran siswa).
- 🟢 **Penyelarasan Bottom Navigation Bar Siswa & Drawer Cepat**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Slot ke-4 Bottom Nav Siswa (`navigasi_bawah_siswa.blade.php`) diarahkan langsung ke `route('siswa.jadwal')` dengan ikon kalender.
    - Drawer menu "Lainnya" diperbarui dengan 4 kartu menu cepat: Profil Siswa, Jadwal & Agenda, Rekap Kehadiran, dan Presensi Mandiri.
    - Penambahan tautan pintas "Lihat Kalender" dan "Lihat Semua" pada dashboard siswa.

#### 4.6 Master Jadwal Rutin KBM Siswa Tertentu & Smart Presensi Time-Gating
- 🟢 **Master Template Berulang, Generator Otomatis, & Smart Time-Gating Siswa**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Database & Model:** Migrasi `2026_09_18_000001_create_jadwal_rutins_table.php`, Model `app/Models/JadwalRutin.php`, serta relasi `jadwalRutins()` pada model `Siswa` dan `Tutor`. Menambahkan `jadwal_rutin_id` pada tabel `jadwal_sesis`.
    - **Generator Engine:** Service `app/Services/JadwalRutinService.php` untuk auto-generation sesi KBM 1-4 minggu ke depan, deteksi otomatis hari libur/cuti sekolah dari tabel `jadwals`, serta sinkronisasi dinamis perubahan master.
    - **Scheduler Otomatis:** Artisan command `php artisan jadwal:generate-sesi` terpasang di Laravel Console Scheduler (`routes/console.php`) berjalan mingguan setiap Minggu pukul 23:00 WIB.
    - **Admin Management Panel (`admin/jadwal_rutin`):** Controller `Admin\JadwalRutinController.php` dengan halaman `index`, `create`, `edit` di `resources/views/admin/jadwal_rutin/`, modal cepat generator kalender, dan tautan di drawer navigasi admin.
    - **Standardisasi UI/UX & Mobile Responsiveness (`jadwal_rutin`):**
      - **Daftar Master (`index.blade.php`):** Menerapkan arsitektur ganda tabel responsif desktop (`.table-responsive-desktop`) dan kartu informasi mobile (`.mobile-card-list` & `.data-mobile-card`). Di layar ponsel ($\le 768$px), tabel bertransformasi menjadi card informatif yang menyajikan nama siswa, kelas, tutor, hari, jam belajar, durasi, status aktif, dan tombol aksi (pause/aktifkan, edit, hapus) secara ergonomis. Filter pencarian diselaraskan menggunakan `.laporanFilterCard` dan `.laporanFilterGrid`.
      - **Formulir Responsif (`create.blade.php` & `edit.blade.php`):** Menggantikan inline grid 2 & 3 kolom yang sempit di mobile dengan class `.form-card-container` dan `.form-grid-responsive`. Mengadopsi `.info-callout-box` untuk catatan time-gating 30 menit, `.checkbox-toggle-card` untuk switch status/generator, serta `.form-action-footer` untuk tombol aksi yang otomatis full-width dan bertumpuk nyaman di layar kecil.
      - **Badge Hari Belajar Terstandarisasi (`app.css`):** Mengimplementasikan style class `.badge-hari-senin` hingga `.badge-hari-minggu` dengan palet warna harmonis dan dukungan mode gelap (`[data-theme="dark"]`).
      - **Modal Generator Sesi Elegan:** Menggunakan `.app-modal-card` dengan backdrop blur, scrollable height (`max-height: calc(100dvh - 32px)`), dan tombol close responsif.
    - **Desentralisasi Penjadwalan Mandiri oleh Tutor (Admin Tidak Wajib Membuat):**
      - **Alur Kesepakatan Mandiri:** Admin tidak lagi diwajibkan menyusun jadwal rutin KBM. Tutor dan Siswa dapat menyepakati jadwal secara langsung, lalu Tutor menginput jadwalnya sendiri melalui portal Tutor (`tutor/jadwal-sesi`).
      - **Modal Terpadu 2-in-1 (`modalJadwalBaru`):** Menyediakan segmented toggle interaktif:
        1. *Ulangi Setiap Minggu (Jadwal Rutin):* Mengisi hari belajar, jam, dan tanggal batas akhir (`berlaku_sampai`). Sesi di-generate otomatis terus berlanjut hingga tanggal/bulan terakhir yang ditentukan tutor (tidak terbatas 4 minggu). Jika dikosongkan, default ke rolling 4 minggu ke depan.
        2. *Hanya Tanggal Ini (Sesi Sekali / Pengganti):* Hanya membuat 1 sesi tunggal di kalender untuk tanggal tertentu tanpa membuat template mingguan berulang.
      - **Tab Manajemen Pola Rutin Tutor:** Tab navigasi di halaman kalender tutor ("Pola Rutin Saya") menampilkan ringkasan seluruh pola rutin mingguan yang dibuat tutor, lengkap dengan tombol jeda/aktifkan dan hapus pola rutin secara mandiri.
    - **Mekanisme Reschedule Sesi Terstruktur (Opsi 2 - Audit Trail Preserved):**
      - Ketika Tutor berhalangan atau menyepakati jadwal pengganti, sesi lama pada tanggal berhalangan **tidak dihapus/ditimpa**, melainkan diperbarui statusnya menjadi `'dibatalkan'` disertai pencatatan riwayat alasan pembatalan (`alasan_penggantian`).
      - Sesi baru di-generate sebagai sesi pengganti bertipe `'pengganti'` dengan mengisi kolom `tanggal_asli` mengacu ke tanggal sesi yang digantikan serta menyertakan alasan.
      - Perlindungan audit & payroll: Sesi yang sudah berstatus `'selesai'` atau sudah memiliki kaitan presensi (`presensi_id`) **terkunci rapat** dan tidak dapat di-reschedule.
      - Notifikasi otomatis: Sistem mengirimkan Web Push Notification instan ke perangkat siswa saat sesi berhasil dijadwalkan ulang.
    - **Peleburan Halaman `/tutor/jadwal` ke dalam `/tutor/jadwal-sesi` (Tab 3: `tab=agenda`):**
      - **Single Source of Truth:** Seluruh kalender akademik dan pengumuman sekolah resmi PKBM dari tabel `jadwals` telah dilebur menjadi **Tab 3 ("Agenda & Pengumuman PKBM")** di `/tutor/jadwal-sesi`. Tidak ada lagi dua halaman kalender yang membingungkan tutor.
      - **Backward Compatible Redirect:** Rute `route('tutor.jadwal')` pada `TutorDashboardController::jadwal` dialihkan via `RedirectResponse` langsung ke `route('tutor.jadwal-sesi.index', ['tab' => 'agenda'])` dengan mempertahankan parameter tanggal, sehingga tautan lama, riwayat browser, dan bookmark tidak pernah rusak.
      - **Pembersihan View Usang:** File view fisik `resources/views/tutor/jadwal.blade.php` telah dihapus sepenuhnya dari repositori.
      - **Navigasi Bawah Tutor:** Tautan pintas Agenda PKBM pada drawer "Lainnya" (`navigasi_bawah_tutor.blade.php`) diperbarui langsung mengarah ke `route('tutor.jadwal-sesi.index', ['tab' => 'agenda'])`.
    - **Penyelarasan Style & Tab Navigasi Terpadu:**
      - **Tab Navigasi Terpadu (`.calendarNavTabsContainer`):** 3 tab terintegrasi modern:
        1. *Kalender Sesi Belajar* (`tab=kalender`, dengan indikator sesi hari ini).
        2. *Pola Rutin Saya* (`tab=rutin`, dengan badge jumlah pola aktif).
        3. *Agenda & Pengumuman PKBM* (`tab=agenda`, dengan badge jumlah agenda sekolah).
      - **Header Action Dinamis & Eliminasi Redundansi Tombol:**
        - Header card pada `tutor/jadwal_sesi/index.blade.php` menyesuaikan diri secara cerdas berdasarkan tab yang aktif:
          - Saat tab *"Pola Rutin Saya"* aktif: Header menampilkan label *"MASTER POLA BERULANG"*, judul *"Pola Rutin Mengajar"*, deskripsi pola berulang, serta tombol header tunggal `+ Tambah Pola Rutin` (memanggil `bukaModalJadwalBaru(true)`).
          - Saat tab *"Agenda & Pengumuman PKBM"* aktif: Header menampilkan label *"AGENDA RESMI & PENGUMUMAN"*, judul *"Agenda KBM & Libur Sekolah"*, serta tombol header tunggal `+ Buat Jadwal Belajar` (memanggil `bukaModalJadwalBaru(false)`).
          - Saat tab *"Kalender Sesi Belajar"* aktif: Header menampilkan label *"PERENCANAAN KBM & RESCHEDULE"*, judul *"Jadwal Sesi & Pengganti"*, serta tombol header tunggal `+ Buat Jadwal Belajar` (memanggil `bukaModalJadwalBaru(false)`).
        - Tombol redundan inline *"Tambah Pola Rutin"* di dalam body daftar pola rutin dihapus, menyisakan antarmuka yang bersih dan terorganisir.
      - **Optimalisasi Responsivitas Mobile Penuh:**
        - Menambahkan media query pada `resources/css/app.css` untuk `.calendarNavTabsContainer`, `.calendarNavTab`, dan `.calendarNavBadge` pada breakpoint $\le 640$px dan $\le 480$px (padding rapat, scrolling horizontal mulus tanpa scrollbar visual, touch target ergonomis).
        - Skala ukuran titik multi-event kalender (`.agendaDotSesi` & `.agendaDotAgenda`) disesuaikan menjadi 4.5px pada ponsel agar tidak berdesakan di dalam sel hari.
        - Kontainer aksi footer kartu data mobile (`.dmc-actions` & `.dmc-footer`) dioptimalkan untuk layar ponsel $\le 480$px agar tombol aksi (`.profileBtnPrimary`, `.btn-table-action`, `.smallBtn`) membungkus secara proporsional dan tidak terpotong.
      - **Standardisasi Lebar Kontainer (`max-w-4xl`):** Menghilangkan *layout jumping/shift* saat berpindah tab dengan menyeragamkan batas lebar layout desktop menjadi `max-w-4xl px-0 mx-auto pb-6`.
      - **Kalender Multi-Dot Interaktif:** Grid kalender bulanan menampilkan penanda ganda: titik biru (`.agendaDotSesi`) untuk sesi belajar murid dan titik amber (`.agendaDotAgenda`) untuk agenda/libur resmi PKBM. Legenda kalender di bawah grid diperbarui menampilkan kedua jenis penanda tersebut.
      - **Penyelarasan Desain Bottom Section (Banner & Empty State) Lintas 3 Tab:**
        - Seluruh tab (Tab 1 Kalender, Tab 2 Rutin, Tab 3 Agenda) kini mengadopsi bahasa visual dan komponen penataan yang harmonis dan seirama:
          1. **Active Data Container (`.agendaBannerBox`):** Saat terdapat data sesi/pola/kegiatan, data dibungkus dalam wadah bergradien lembut dan border 1px yang menaungi `.agendaBannerHeader`. Header memuat badge status dinamis (misal *Sesi KBM Hari Ini / Terjadwal* untuk Tab 1, *Master Pola Rutin* bertema emerald untuk Tab 2, dan *Agenda Resmi Terjadwal* untuk Tab 3), live counter item, serta keterangan tanggal atau status berulang.
          2. **Empty State Seragam (`.emptyAgendaBox`):** Saat tidak ada data, seluruh tab menampilkan kartu berbingkai *dashed border* 1.5px, wadah ikon melayang 48px dengan aksen pastel sesuai tema tab (`school-outline` untuk kalender, `repeat-outline` untuk rutin, `calendar-clear-outline` untuk agenda), judul bold informatif, deskripsi jelas, dan tombol aksi utama (*primary call-to-action*) yang memudahkan pengguna langsung bertindak.
      - **Integrasi Informasi Detail Tanggal:**
        - Pada kalender *Jadwal-Sesi* (Tab 1): banner peringatan agenda sekolah (`.agendaNoticeBox`) dilengkapi tautan cepat beralih langsung ke Tab 3.
        - Pada kalender *Agenda PKBM* (Tab 3): ringkasan sesi murid (`.sesiSummaryBox`) dilengkapi tautan cepat beralih langsung ke Tab 1.
      - **Penyelarasan Sub-Navigasi Admin:** Menerapkan komponen tab serupa pada panel Admin untuk menghubungkan *Kalender Agenda Sekolah* (`admin.jadwal.index`) dan *Master Jadwal Rutin Siswa* (`admin.jadwal-rutin.index`).
    - **Smart Presensi Time-Gating Siswa:** Proteksi form presensi masuk siswa di `SiswaPresensiController` (kamera & tombol absen terkunci jika bukan hari KBM atau sebelum H-30 menit jam mulai sesi), dilengkapi kartu status dan *live countdown timer* JavaScript pada view `resources/views/siswa/presensi_foto.blade.php`.
    - **Database Seeder Otomatis:** Seeder `JadwalRutinSeeder.php` yang terdaftar pada `DatabaseSeeder.php` untuk menginisialisasi pola master mingguan siswa-tutor dan menghasilkan 30 sesi kalender siap pakai untuk 4 minggu ke depan tanpa menyentuh data presensi.
    - **Testing Suite:** `tests/Feature/JadwalRutinAndPresensiGatingTest.php` (7 test cases) dan `tests/Feature/TutorJadwalMandiriTest.php` (6 test cases, termasuk pengujian tab agenda dan redirect legacy route) lolos 100%. Total 141 tests (602 assertions) PASSED.

#### 4.7 Perbaikan Perhitungan Kehadiran Siswa (Anti Double-Counting Distinct Days)
- 🟢 **Penyelarasan Statistik Kehadiran Siswa (Dashboard & Profil)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Akar Masalah:** Sebelumnya, saat murid melakukan absen mandiri (`presensi_mandiri_siswas`) dan tutor mencatat sesi kelas (`presensis`) di hari yang sama, total hadir dihitung dengan penjumlahan aritmatika sederhana ($1 + 1 = 2$).
    - **Penyelesaian Backend (`SiswaDashboardController`):** Menghitung total hadir bulanan (`$totalHadirBulanIni`) dan total keseluruhan di profil (`$totalHariHadir`) menggunakan penggabungan tanggal unik (`DISTINCT` dates via `pluck('tgl_presensi')->unique()`). Jika pada tanggal yang sama murid absen mandiri dan tutor mengabsen sesi, sistem menghitungnya tepat sebagai 1 Hari Hadir.
    - **Penyelarasan UI (`dashboard.blade.php` & `profil.blade.php`):** Dashboard tetap menampilkan transparansi rincian ("Absen Mandiri", "Sesi Kelas", dan "Total Hadir" berbasis hari aktif unik), sedangkan profil menampilkan rincian "Hari Hadir", "Absen Mandiri", dan "Sesi Kelas".
    - **Automated Testing:** Penambahan feature test `test_dashboard_attendance_counts_unique_days_preventing_double_count` di `tests/Feature/SiswaRoleAndPresensiTest.php`. Total suite lulus 133 tests (554 assertions).

#### 4.8 Perbaikan Countdown Timer Absen Pulang (Eliminasi Desimal Microsecond)
- 🟢 **Standardisasi Integer Sanitization pada Countdown Timer Multi-Role**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Akar Masalah:** Perhitungan selisih detik (`$jamMulaiDt->diffInSeconds($nowDt, false)`) pada Carbon dapat mengembalikan angka bertipe `float` dengan presisi microsecond (misal `3324.038773...`). Saat dioperasikan dengan modulo JavaScript (`totalSec % 60`), JavaScript menghasilkan sisa bagi desimal panjang (`24.03877300000022`), sehingga teks countdown menampilkan `55:24.03877300000022`.
    - **Penyelesaian Backend & Blade:** Melakukan *explicit integer casting* `(int)` pada kalkulasi `$diffDetik` dan `$sisaDetik` di `PresensiFotoController`, `TutorDashboardController`, serta Blade views.
    - **Penyelesaian JavaScript Timer:** Menggunakan `Math.floor(totalSec)` dan `Math.floor(totalSec % 60)` di script browser timer pada halaman presensi Tutor, Magang, Karyawan, serta dashboard Tutor sehingga hitungan mundur selalu bulat dan mulus berformat `MM:SS`.

#### 4.9 Perbaikan Siklus Status Sesi KBM & Eliminasi Lock-Out Presensi Siswa
- 🟢 **Eliminasi Lock-Out Presensi Mandiri Siswa Pasca-Check-In Tutor**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Akar Masalah:** Ketika tutor melakukan presensi masuk (clock in), controller tutor sebelumnya langsung meng-update `jadwal_sesis.status` menjadi `'selesai'` (prematur). Di sisi siswa, `SiswaPresensiController::store()` dan blade `siswa.jadwal` menggunakan filter kaku `where('status', 'terjadwal')`. Akibatnya, saat siswa ingin melakukan presensi mandiri, query gagal menemukan sesi dan memblokir siswa dengan peringatan *"Presensi tidak dapat dilakukan karena Anda tidak memiliki jadwal KBM yang aktif hari ini."*
    - **Perbaikan Transisi Status Sesi Tutor (`Tutor\PresensiFotoController`):**
      - Saat tutor absen masuk (`mode === 'mulai'`), status `jadwal_sesis` di-update menjadi `'berlangsung'`.
      - Saat tutor absen pulang (`mode === 'selesai'`), status `jadwal_sesis` di-update menjadi `'selesai'`.
    - **Perbaikan Validasi Presensi Siswa (`SiswaPresensiController`):**
      - Pengecekan `$todaySesi` pada method `store()` diselaraskan dengan method `foto()`, yaitu mencari sesi dengan `where('status', '!=', 'dibatalkan')` dan memprioritaskan sesi yang belum diabsen mandiri oleh siswa (`presensi_siswa_id IS NULL` atau `status_kehadiran_siswa != 'hadir'`).
      - Auto-link sesi ke `presensi_mandiri_siswas` diperbarui dengan `where('status', '!=', 'dibatalkan')`.
    - **Penyelarasan Tampilan Jadwal Siswa (`resources/views/siswa/jadwal.blade.php`):**
      - Menambahkan badge status `'Sedang Berlangsung'` (badge layanan DL) saat `status === 'berlangsung'`.
      - Tombol *"Absen Masuk Sekarang"* tetap dapat diakses oleh siswa selama sesi hari ini berstatus bukan `'dibatalkan'` dan siswa belum melakukan presensi mandiri.
    - **Pengujian Otomatis:**
      - Feature test baru `test_siswa_can_presensi_mandiri_even_if_tutor_already_clocked_in_session` di `tests/Feature/SiswaRoleAndPresensiTest.php`.
      - Pembaruan skenario transisi dua tahap pada `test_presensi_masuk_auto_links_and_completes_jadwal_sesi` di `tests/Feature/JadwalSesiPenggantiTest.php`.
      - Seluruh suite pengujian aplikasi (134 tests, 562 assertions) lulus 100%.

#### 4.10 Penanda Keterlambatan Presensi Mandiri Siswa (Batas Waktu Toleransi KBM)
- 🟢 **Evaluasi Batas Toleransi, Pencatatan Keterlambatan & Tampilan Status Siswa**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Database Migration:** Migrasi `2026_09_18_000002_add_status_kehadiran_to_presensi_mandiri_siswas_table.php` menambahkan kolom `status_kehadiran` (`tepat_waktu`, `terlambat`, `lebih_awal`) dan `menit_keterlambatan` pada tabel `presensi_mandiri_siswas`.
    - **Model `PresensiMandiriSiswa`:** Menambahkan `$fillable`, casting integer `menit_keterlambatan`, helper `isTerlambat(): bool`, dan accessor `status_kehadiran_label`.
    - **Evaluasi Dinamis di Controller (`SiswaPresensiController`):**
      - Pada `foto()`, sistem mengevaluasi `$todaySesi` terhadap jam saat ini (`$now`) dengan batas toleransi 30 menit (`$todaySesi->jadwalKerja->tolerance_minutes ?? config('presensi_sk.tolerance_minutes', 30)`). Menghasilkan array `$sesiEval` yang dikirim ke view.
      - Pada `store()`, sistem menghitung status keterlambatan aktual saat data disimpan, mencatat `status_kehadiran` dan `menit_keterlambatan`, serta mengirimkan flash message notifikasi dan Web Push spesifik (misal: *"Anda tercatat terlambat 61 menit dari jadwal KBM (09:00 WIB)"*).
    - **Penyelarasan Tampilan UI Siswa:**
      - Di halaman kamera `siswa/presensi_foto.blade.php`: Menampilkan card info jadwal KBM (jam sesi, target masuk, batas toleransi) dengan badge status real-time (`Tepat Waktu` / `Terlambat (+XX mnt)`), teks alert peringatan jika lewat toleransi, serta pembaruan kartu hasil presensi.
      - Di dashboard siswa `siswa/dashboard.blade.php`: Kartu kehadiran hari ini (`.todayCard`) otomatis menampilkan border/ikon warning dan chip keterlambatan `+XX Mnt`.
      - Di riwayat siswa `siswa/riwayat.blade.php`: Menampilkan badge `Terlambat (+XX mnt)` di samping status Hadir.
    - **Pengujian Otomatis:**
      - Feature test `test_siswa_presensi_evaluates_late_when_exceeding_tolerance_limit` di `tests/Feature/SiswaRoleAndPresensiTest.php`.
      - Seluruh test suite (135 tests, 572 assertions) lulus 100%.

#### 4.11 Modernisasi & Penyelarasan Style Halaman Absen Siswa (Design System & Responsivitas Mobile)
- 🟢 **Standardisasi Kartu Pasca-Presensi Mandiri, Time-Gating, & Modal Bukti Foto Siswa**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Akar Kebutuhan:** Tampilan halaman presensi siswa setelah melakukan absen masuk sebelumnya masih menggunakan komponen kaku/tabel sederhana yang kurang konsisten dengan layout modern halaman lain (`.data-mobile-card`, `.statusBanner`, `.dmc-grid`, dsb.) dan kurang optimal di layar perangkat mobile.
    - **Penyelarasan Kartu Bukti Presensi (`resources/views/siswa/presensi_foto.blade.php`):**
      - Menggunakan `.statusBanner.done` (tepat waktu) dan `.statusBanner.running` (terlambat) dengan ikon status selaras.
      - Membungkus detail bukti presensi mandiri ke dalam `.data-mobile-card` lengkap dengan header tanggal, badge kehadiran (`Hadir (Tepat Waktu)` / `Terlambat (+XX mnt)` / `Lebih Awal`), dan card profil snapshot selfie siswa.
      - Menampilkan grid informasi rapi (`.dmc-grid` & `.dmc-field`): Jam Kedatangan, Status Kehadiran, Titik Lokasi Belajar, Integritas GPS, serta detail Sesi KBM Terkait (mata pelajaran & tutor pengampu).
      - Menyelaraskan tombol aksi responsif (`.dmc-footer` & `.dmc-actions`) menuju Dashboard, Jadwal Mingguan, dan Riwayat Presensi.
    - **Penyelarasan Kartu Time-Gated (`no_schedule` & `too_early`):**
      - Merestrukturisasi tampilan saat jadwal belum tiba atau tidak ada KBM ke dalam `.statusBanner` dan `.data-mobile-card` yang rapi di layar ponsel dengan tipografi harmonis dan tombol aksi terstandarisasi.
    - **Modal Pratinjau Foto Bukti Presensi (`#buktiPhotoModal`):**
      - Menambahkan modal preview foto snapshot selfie presensi yang dapat diklik langsung dari foto avatar untuk memperbesar gambar secara jernih dan responsif.
    - **Standarisasi CSS Design System (`resources/css/app.css`):**
      - Menambahkan kelas tombol sekunder terstandarisasi `.profileBtnSecondary` yang adaptif baik pada Light Mode maupun Dark Mode (`[data-theme="dark"]`).
    - **Pengujian & QA:**
      - Seluruh suite pengujian automated feature tests (135 tests, 572 assertions) lulus 100%. Formatter Laravel Pint lolos tanpa error.

#### 4.12 Penyederhanaan Database Seeder Khusus Akun Pengguna (Account-Only Seeding)
- 🟢 **Standardisasi Seeder Default (`DatabaseSeeder`) Terisolasi Khusus Akun Pengguna**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Akar Kebutuhan:** Sebelumnya, eksekusi `php artisan db:seed` secara otomatis men-generate data master, jadwal operasional, dan puluhan sesi dummy kalender (`JadwalSeeder`, `JadwalRutinSeeder`, `KategoriTutorialSeeder`, dll.). Dibutuhkan alur seeding default yang bersih, ringan, dan hanya sebatas inisialisasi akun pengguna untuk seluruh role.
    - **Penyelarasan `DatabaseSeeder` (`database/seeders/DatabaseSeeder.php`):**
      - Membatasi panggilan seeder utama hanya pada 4 seeder akun:
        1. `AdminSeeder::class` (Akun Admin utama).
        2. `UserRoleSeeder::class` (Akun Admin operasional, Tutor pengajar, dan Kepala Sekolah).
        3. `MagangSeeder::class` (Akun Mahasiswa Magang/PKL).
        4. `SiswaUserSeeder::class` (Akun Siswa presensi mandiri).
      - Menghapus pemanggilan seeder jadwal, master tarif, dan generator sesi dari run default `DatabaseSeeder`, namun tetap mempertahankan file-file seeder tersebut secara independen di `database/seeders/` untuk kebutuhan manual testing bila diperlukan.
    - **Penyempurnaan `SiswaUserSeeder`:**
      - Menambahkan fallback mandiri (`self-contained`) untuk memastikan relasi kelas default dan data profil siswa otomatis terbentuk tanpa bergantung pada dummy seeder eksternal.
      - Menyelaraskan query kolom sesuai struktur migrasi aktif (`no_absen` tanpa kolom usang `nis` dan `alamat`).
    - **Verifikasi:**
      - Perintah `php artisan db:seed` sukses mengeksekusi pembuatan seluruh akun pengguna (Admin, Kepsek, Tutor, Magang, Siswa) dengan bersih dan cepat.
      - Seluruh test suite (135 tests, 572 assertions) tetap lulus 100%.

#### 4.13 Modernisasi Antarmuka Login Autentikasi (Responsive Mobile & Design System)
- 🟢 **Redesain Antarmuka Login Autentikasi Multi-Peran**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Akar Kebutuhan:** Tampilan login sebelumnya menggunakan layout ilustrasi lawas yang memakan ruang vertikal terlalu besar pada layar ponsel, tidak memiliki toggle password (show/hide), tidak mendukung preferensi Dark Mode, dan belum mengadopsi komponen design system modern PKBM Pikat.
    - **Arsitektur Responsive (Mobile-First & Desktop Split Card):**
      - **Mobile (< 768px):** Tampilan kartu fluid di tengah dengan header kompak memuat logo resmi sekolah (`Logo.jpeg`), judul sistem presensi, dan form yang ramah jempol (*thumb-friendly*) tanpa scrolling yang melelahkan.
      - **Desktop/Tablet (>= 768px):** Tampilan split card modern (lebar 920px); sisi kiri memuat panel showcase brand bergradien biru signature PKBM Pikat dengan sorotan fitur (Geofencing GPS, Kalender Sesi Real-time, Rekapitulasi Presensi & Payroll), dan sisi kanan memuat formulir kredensial akun.
    - **Fitur Interaktivitas & UX Formulir:**
      - **Toggle Show/Hide Password:** Ikon mata interaktif (`ion-icon name="eye-outline"` / `eye-off-outline`) untuk memudahkan pengecekan pengetikan kata sandi di ponsel.
      - **Input Icons:** Ikon pembantu visual (`person-outline` dan `lock-closed-outline`) dengan efek fokus bercahaya (`glow shadow`).
      - **Opsi Remember Me & Bantuan:** Checkbox modern "Ingat Saya" untuk menjaga sesi login tetap aktif, serta tautan bantuan WhatsApp Admin resmi.
      - **Integrasi Single Popup Toast (Anti-Redundan):** Notifikasi pesan error/warning/success dialihkan 100% ke sistem floating popup toast global (`.app-toast-container` via meta flash session) yang konsisten di seluruh aplikasi, sekaligus mengeliminasi banner alert inline redundan di dalam form login agar antarmuka tetap bersih dan tidak bertumpuk ganda.
      - **State Loading & Anti Double-Click:** Animasi teks "Memverifikasi..." dan penonaktifan tombol submit saat proses autentikasi berlangsung.
      - **Theme Switcher Instant:** Tombol toggle mode gelap/terang di sudut kanan atas yang otomatis tersimpan di `localStorage`.
    - **Penyelarasan CSS (`resources/css/app.css`):**
      - Menggunakan token CSS variabel `--font-sans` (Inter), `--blue`, `--blue2`, `--blue-gradient`, `--radius-xl`, serta ambient glow background blur yang menawan dan ringan.
    - **Verifikasi:**
      - Seluruh test suite (135 tests, 572 assertions) lulus 100%. Formatter Laravel Pint lolos rapi. Production bundle Vite terkompilasi bersih.

#### 4.14 Harmonisasi Logika Sesi Durasi Non-SK (4-Role Harmonization: Tutor, Siswa, Admin, Kepsek)
- 🟢 **Penyelarasan & Proteksi Menyeluruh Sesi KBM di Luar Durasi SK**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - **Latar Belakang Masalah:** SK Kepala PKBM Pikat mengatur durasi resmi tutorial 1.5 jam, 2 jam, dan 3 jam dengan nominal honor flat per pertemuan. Namun di lapangan, tutor kerap membuat jadwal dengan durasi khusus (misal 1 jam, 1.25 jam, atau 4 jam) yang tidak terdapat di master SK. Kondisi ini sebelumnya berpotensi menimbulkan cacat logika: jebakan lockout 1 jam absen pulang tutor pada sesi kilat, ketidaksesuaian toleransi keterlambatan siswa, ketiadaan visibilitas di admin, serta anomali pelaporan payroll kepsek.
    - **Peran Tutor (Fleksibilitas Terbimbing & Smart SK Binding):**
      - Modal Buat Jadwal Belajar (`tutor/jadwal_sesi/index.blade.php`) dilengkapi dropdown kategori SK di atas input waktu.
      - Memilih kategori SK otomatis menghitung dan mengisi `jam_pulang` sesuai durasi resmi.
      - Jika tutor mengubah jam pulang menjadi durasi non-SK (misal 1 jam 15 menit), sistem menampilkan *Live Warning Callout* (`#boxWarningNonSk`) yang menginformasikan bahwa durasi tidak ada di SK, sesi ditandai secara khusus, dan kompensasi menggunakan tarif flat default SK.
    - **Proteksi Clock-Out Adaptif Tutor (`PresensiFotoController`):**
      - Mengganti pembatasan statis 3600 detik (1 jam) dengan ambang batas adaptif: `min(3600, max(900, (int) round($durasiRencanaDetik * 0.7)))`.
      - Pada sesi berdurasi singkat ($\le 60$ menit), tutor dapat melakukan presensi pulang setelah memenuhi 70% durasi belajar (minimal 15 menit). Misalnya, sesi 30 menit dapat clock-out di menit ke-21 tanpa terblokir.
    - **Peran Siswa (Toleransi Keterlambatan Proporsional di `SiswaPresensiController`):**
      - Untuk sesi singkat ($< 90$ menit), batas toleransi keterlambatan presensi mandiri disesuaikan dinamis menjadi: `min(defaultTolerance, max(10, (int) round($durasiMenit * 0.3)))`.
      - Mencegah siswa pada sesi kilat 30 menit yang baru hadir di menit ke-25 keliru tercatat sebagai *"Tepat Waktu"*.
    - **Peran Admin (Transparansi Kurikulum & Audit Trail):**
      - Pada `JadwalSesiController`, jika sesi berdurasi di luar SK, controller otomatis menyematkan tag penanda transparan `[Jadwal Khusus: Durasi X Jam di luar SK]` pada kolom catatan sesi maupun keterangan master pola rutin.
      - Menjaga integritas data tanpa memutus fleksibilitas tutor.
    - **Peran Kepala Sekolah (Transparansi Finansial & Audit Payroll):**
      - Pada `PayrollService`, sesi berdurasi di luar SK diberi label transparan: `Tutorial Non-SK (Aktual X.Xj - Flat Default SK)` dengan snapshot honor default.
      - Pada view rincian payroll (`admin/payroll/show.blade.php`), sesi non-SK ditandai dengan badge oranye `Non-SK` pada tabel desktop dan kartu mobile sehingga Kepala Sekolah memiliki transparansi finansial penuh tanpa keraguan audit.
    - **Testing & Verifikasi:**
      - Feature test baru `test_tutor_can_create_custom_duration_session_with_non_sk_tagging` ditambahkan pada `tests/Feature/TutorJadwalMandiriTest.php`.
      - Seluruh test suite (142 tests, 607 assertions) lulus 100%. Kompilasi Vite asset dan linter Laravel Pint lolos rapi.

---

### 5. Master Data Relasional & Siklus Siswa (Academic Lifecycle)

#### 5.1 Relasi Murni Master Jenjang & Kelas Rombel
- 🟢 **Tabel `jenjang_pakets` & Foreign Key `kelas.jenjang_paket_id`**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Tabel dinamis `jenjang_pakets` (Paket A, B, C, Vokasi, Kursus, dsb.) dan relasi Foreign Key murni `kelas.jenjang_paket_id` $\rightarrow$ `jenjang_pakets.id`.

#### 5.2 Siklus Hidup Siswa (Lifecycle Status) & Anti Data-Loss
- 🟢 **Siklus Hidup Siswa & SoftDeletes**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Status `aktif`, `alumni`, `cuti`, `nonaktif`, `SoftDeletes` (`deleted_at`), dan proteksi hapus jenjang paket aktif dengan fitur migrasi rombel.

#### 5.3 Deteksi Otomatis Multi-Rombel (Sesi Gabungan Komunitas)
- 🟢 **Deteksi Otomatis Sesi Gabungan Komunitas**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Backend otomatis mendeteksi jika murid berasal dari $>1$ kelas berbeda dalam jenjang paket yang sama $\rightarrow$ otomatis menerapkan skema Gabungan Komunitas (Rp 50.000,- / rombel).

---

### 6. Integrasi Interoperabilitas Sistem & Notifikasi (Integrations)

#### 6.1 Web Push Notification Real-Time & Unified Scheduler Engine (Multi-Role)
- 🟢 **Infrastruktur Web Push, VAPID & Guzzle Client**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Paket `minishlink/web-push`, command `webpush:vapid`, migrasi `push_subscriptions`, dan `WebPushService` dengan proteksi SSL bypass Windows/Laragon serta helper pengiriman multi-role (`sendToUser`, `sendToSiswa`, `sendToTutor`, `sendToMagang`, `sendToAdmins`, `sendToKepsek`).
- 🟢 **Auto-Sync Token Browser & Client Push Manager**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Script `resources/js/push-notification.js` auto-sinkronisasi token browser, auto-reconnect, dan pengujian push mandiri di menu Profil seluruh role (Tutor, Siswa, Magang, Admin, Kepsek).
- 🟢 **Otomatisasi Trigger Push Notifikasi Sistem Real-Time & Scheduled Cron**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Notifikasi instan presensi masuk/pulang Tutor, Siswa, dan Magang.
    - Notifikasi otomatis ke Siswa saat Tutor menjadwalkan atau mereschedule sesi belajar KBM.
    - Alert instan ke Tutor saat Siswa bimbingan telah tiba dan melakukan check-in di sekolah.
    - Notifikasi pengajuan izin & lupa lapor ke Kepala Sekolah beserta persetujuannya.
    - Command `presensi:send-reminder` terpadu dengan opsi `--type` (`morning`, `clockout`, `pending-approvals`) dan otomatisasi cron di `routes/console.php`.
    - Broadcast pengumuman slip gaji / honorarium kepada tutor.

#### 6.2 Import & Export Massal Data (Bulk Data Management)
- 🟢 **Import & Export Spreadsheet Excel/CSV**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Ekspor laporan presensi komprehensif 14 kolom + KPI cards (`PresensiExport.php`), ekspor/import massal data Siswa, Tutor/Karyawan, Jadwal/Agenda, dan Presensi Retroaktif via `maatwebsite/excel`.

#### 6.3 Integrasi Eksternal (Jangka Panjang)
- ⚪ **Integrasi Akun Terpusat (SSO SIM PKBM Pikat via Sanctum / OAuth2)**: [PENDING]
- ⚪ **Notifikasi WhatsApp Gateway**: [PENDING]

---

### 7. Executive Dashboard & Business Intelligence (Analytics)

#### 7.1 Dashboard Analytics Kepala Sekolah
- 🟢 **Heatmap Kehadiran, Tren Kinerja & Leaderboard KPI Tutor**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Grafik interaktif Chart.js tren kehadiran 6 bulan terakhir (`AnalyticsService.php`), kalkulasi skor composite KPI Tutor, dan leaderboard peringkat kedisiplinan (#1 Gold, #2 Silver, #3 Bronze).

#### 7.2 Laporan Standar Akreditasi Pendidikan
- ⚪ **Format Laporan Otomatis Akreditasi Pendidikan Kesetaraan (BAN PDM / PNF)**: [PENDING]

---

### 8. Standardisasi UI/UX, Layout Responsif & Feedback Terpadu

#### 8.1 Standardisasi Navigasi & Spacing Dashboard Antar Role
- 🟢 **Sticky Top Navigation & Breathing Room Layout**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:** Topbar navigasi sticky, header section `.sectionTitleRow`, dan grid 2-kolom desktop ($\ge 992$px) seimbang.

#### 8.4 UI/UX Polish, Kontras Tombol, Dialog Modal & Navigasi Ikon
- 🟢 **Penyelarasan Kontras Tombol, Dialog Modal & Navigasi Admin**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Perbaikan kontras warna teks tombol `.btnNavMaps` (Petunjuk Arah/Peta), `.btnLiveGpsActive`, dan tombol aksi peta di seluruh view presensi agar teks dan ikon terbaca kontras dan jelas (tidak hanya saat hover).
    - Penambahan wrapper background `.navIconWrap` pada item menu Master Jadwal & Shift Kerja di `navigasi_bawah_admin.blade.php` agar selaras dan konsisten dengan seluruh item navigasi lainnya.
    - Standardisasi styling dialog konfirmasi `.app-modal-card`, `.app-modal-header`, `.app-modal-footer` pada `resources/css/app.css` dengan dukungan Dark Mode, scrolling viewport adaptif (`max-height: calc(100dvh - 32px)`), dan tata letak responsif pada perangkat mobile.

---

### 9. Portal & Presensi Mandiri Siswa PKBM (*Student Self-Attendance*)

#### 9.1 Autentikasi & Arsitektur Role Siswa
- 🟢 **Role Siswa, Middleware & Relasi User**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Integrasi role `'siswa'` pada `RoleMiddleware`, pengalihan otomatis login di `AuthWebController`, dan pembatasan akses rute `/siswa/*`.
    - Migrasi penambahan kolom `user_id` pada tabel `siswas`, relasi timbal-balik `belongsTo(User::class)` & `hasOne(Siswa::class)`, serta pembuatan `SiswaUserSeeder`.
    - **Sinkronisasi Otomatis Model (`App\Observers\SiswaObserver`)**:
      - **Auto-Provisioning**: Setiap kali Siswa baru dibuat (via Form Admin, Excel Import, atau Seeder), akun `User` dengan role `siswa` otomatis dibuat dan dikaitkan ke `user_id`.
      - **Auto-Update**: Saat nama atau kontak siswa diperbarui, data di tabel `users` otomatis tersinkronisasi.
      - **Auto-Deactivate**: Saat siswa diarsipkan (soft delete) atau status diset nonaktif/alumni, akun `User` otomatis dinonaktifkan (`is_active = 0`).
      - **Admin Reset Password**: Tombol reset password default (`password123`) pada halaman detail Siswa di panel Admin (`admin.siswa.resetPassword`).
    - **Normalisasi Identifier & Fleksibilitas Login Siswa (`AuthWebController`)**:
      - Mendukung berbagai variasi input username: `sw001`, `SW001`, `SW0001`, `001`, `01`, `1`, `siswa@pkbmpikat.com`, `siswa001@pkbmpikat.com`, maupun NIK `SW202601`.
    - **Desain Notifikasi Alert Login (`login.blade.php` & `app.css`)**:
      - Komponen visual alert `.login-alert` dengan warna kontras, border lembut, icon, dan animasi `fadeInSlide` agar pesan peringatan atau kesalahan selalu terlihat jelas oleh pengguna.

#### 9.2 Presensi Mandiri Harian Siswa (Single Check-In / Kedatangan)
- 🟢 **Tabel `presensi_mandiri_siswas` & Engine Absensi Siswa**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Migrasi `create_presensi_mandiri_siswas_table` dan Model `PresensiMandiriSiswa.php`.
    - `SiswaPresensiController.php`: Form absensi masuk tunggal per hari dengan peta interaktif Leaflet, radar proximity, verifikasi radius geofence di lokasi PKBM/mitra, kamera selfie (mirror, switch facing, flash torch), anti fake-GPS, proteksi anti-duplikasi, auto-link ke status kehadiran di `jadwal_sesis`, serta eliminasi form pulang & jeda countdown 15 menit.
    - `SiswaDashboardController.php`: Dashboard ringkasan kehadiran mandiri dan sesi kelas dengan 2 status bersih (Belum Absen / Sudah Hadir), integrasi langsung kartu jadwal sesi KBM tutorial hari ini bersama tutor, agenda & jadwal kegiatan umum PKBM mendatang, filter riwayat kehadiran bulanan, modal preview foto presensi, serta pengaturan profil dan Web Push Notification.
    - Template antarmuka blade responsif: `siswa/dashboard.blade.php`, `siswa/presensi_foto.blade.php`, `siswa/riwayat.blade.php`, `siswa/profil.blade.php`, dan `navigasi_bawah_siswa.blade.php`.

#### 9.3 Pusat Pengelolaan Akun Pengguna Terintegrasi (Unified User & Account Center)
- 🟢 **Halaman Manajemen Akun Terpadu (`resources/views/admin/karyawan/index.blade.php`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - Mengintegrasikan seluruh akun pengguna (Admin, Kepala Sekolah, Tutor/Pendidik, Siswa, dan Mahasiswa Magang) ke dalam satu pusat pengelolaan yang terstruktur.
    - **4 Kartu Metrik Cepat**: Total Pengguna, Tutor & Pengajar, Siswa Terdaftar, dan Magang/Staf Manajemen.
    - **Filter Peran Dropdown Responsif**: Dropdown pemilihan peran/role adaptif dengan live counter badge (`Semua Peran`, `Pendidik / Tutor`, `Peserta Didik / Siswa`, `Mahasiswa Magang`, `Administrator`, `Kepala Sekolah`) yang terintegrasi langsung dalam grid filter responsif.
    - **Fitur Aksi Lengkap**: Reset Password instan (`password123`) dengan dialog konfirmasi, toggle aktif/nonaktif, edit profil akun, dan link cerdas ke detail data modul terkait (Data Siswa, Slip Honor Tutor, Presensi Magang).
    - **Desain Responsif Desktop & Mobile**: Tabel modern pada desktop dan kartu ramah sentuhan (*touch-friendly $\ge 44\text{px}$*) pada layar smartphone.

#### 9.4 Sistem Pagination Responsif & Bahasa Indonesia
- 🟢 **Sentralisasi Komponen Navigasi Halaman (`resources/views/vendor/pagination/`)**
  - **Status:** **SELESAI**
  - **Rincian Implementasi:**
    - `custom.blade.php`, `bootstrap-5.blade.php`, `bootstrap-4.blade.php`, `default.blade.php`: Seluruh pagination di sistem diseragamkan dengan teks **Bahasa Indonesia** (`Sebelumnya`, `Selanjutnya`, `X / Y`, `Menampilkan X - Y dari Z data`).
    - Didaftarkan secara global di [AppServiceProvider.php](file:///c:/laragon/www/pengembangan-presensi-pikat/app/Providers/AppServiceProvider.php) via `Paginator::defaultView('vendor.pagination.custom')` & `Paginator::defaultSimpleView('vendor.pagination.custom')`.
    - **Dual-Mode Ultra-Responsive UI**: Mode desktop ($\gt 768\text{px}$) dengan pil nomor halaman (*page pills*) elegan + shadow aktif, dan mode mobile / tablet ($\le 768\text{px}$) beralih otomatis ke mode *compact touch navigation* berukuran sentuh nyaman ($\ge 42\text{px}$) dengan *zero horizontal overflow* pada seluruh smartphone.

---

### 10. Refactored Codebase, Standardisasi & QA

#### 10.1 Standardisasi System Codebase
- 🟢 **Laravel Pint Code Formatter**: `vendor/bin/pint --format agent` lolos 100% di seluruh file controller, model, view, seeder, dan migration.
- 🟢 **Sentralisasi Design System via `resources/css/app.css`**: Menyatukan seluruh styling CSS komponen, menghapus inline styles, dan merapikan layout Vite native.
- 🟢 **Standardisasi Folder Views (`resources/views/layouts/`)**: Konsolidasi layout, navigasi berbahasa Indonesia, dan pembersihan file *dead-code*.

#### 10.2 Automated Testing Suite
- 🟢 **PHPUnit Test Suite**: Seluruh **141 Feature & Unit Tests** lulus 100% (**597 assertions**).
- 🟢 **Vite Production Assets**: `npm run build` berjalan bersih tanpa error.

---

## 🛠️ 2. PERUBAHAN & PENGKONDISIAN TEKNIS (INFRASTRUKTUR & VCS)

| No | Nama Perubahan / Fitur | Kategori | Deskripsi & Dampak | Status |
|---|---|---|---|---|
| 1 | **Inisialisasi Remote Repositori Git** | Git & VCS | Repositori Git baru, branch `main`, remote `origin` (`https://github.com/4lliiifffff/pengembangan-presensi-pkbmpikat.git`), commit/push. | 🟢 Selesai |
| 2 | **Migrasi Baseline Codebase `presensi-pkbmpikat`** | Core Setup | Memindahkan seluruh kode proyek lama ke dalam repositori pengembangan baru. | 🟢 Selesai |
| 3 | **Penyesuaian Kompatibilitas Dependensi PHP 8.5** | Environment | Konfigurasi `composer.json` & paket `maatwebsite/excel`, `barryvdh/laravel-dompdf` di PHP 8.5. | 🟢 Selesai |
| 4 | **Pemasangan & Konfigurasi Laravel Boost MCP** | AI & Tooling | Instalasi dev `laravel/boost` dan konfigurasi file `AGENTS.md`. | 🟢 Selesai |
| 5 | **Implementasi Web Push Service & VAPID Tooling** | Push Service | Menambahkan `minishlink/web-push`, command `webpush:vapid`, dan integrasi Service Worker PWA v2. | 🟢 Selesai |
| 6 | **Master Kategori & Skema Tarif SK Kepala PKBM** | Payroll SK | Migrasi `kategori_tutorials`, seeder 6 skema SK, dynamic snapshot resolver, dan eliminasi form manual. | 🟢 Selesai |
| 7 | **UI/UX Excellence & Unified Dialog System** | Frontend | Standardisasi layout dashboard, sticky navigation, unified modern dialog/toast, dan form izin responsif. | 🟢 Selesai |
| 8 | **Master Kelas Terstruktur & Deteksi Otomatis Sesi Gabungan** | Core Presensi | Master kelas Paket A (1-6), B (7-9), C (10-12) & auto-detect multi-rombel vs 1 rombel reguler. | 🟢 Selesai |
| 9 | **Total Refactoring `tarif_per_jam` & Skema Terstruktur `kelas`** | Database Refactor | Drop `siswas.tarif_per_jam`, tambah `kelas.tingkat` (varchar 50) untuk modularitas Vokasi/Paket baru. | 🟢 Selesai |
| 10 | **Refactoring Foreign Key Relasional `kelas.jenjang_paket_id`** | Database Integrity | Migrasi Foreign Key murni `kelas.jenjang_paket_id` $\rightarrow$ `jenjang_pakets.id` dengan ON DELETE SET NULL. | 🟢 Selesai |
| 11 | **Penguatan Integrasi Murid > Kelas > Paket & Anti Data-Loss** | Academic Lifecycle | Status siklus siswa (`aktif`, `alumni`, `cuti`, `nonaktif`), SoftDeletes (`deleted_at`), dan migrasi rombel saat hapus jenjang. | 🟢 Selesai |
| 12 | **Penjadwalan Sesi Pengganti (*Make-Up Class*) & Master Lokasi** | Schedule & Attendance | Tabel `jadwal_sesis`, kalender bulanan, toleransi jam fleksibel, auto-complete sesi kamera presensi, dan bottom nav sync. | 🟢 Selesai |
| 13 | **Master Titik Lokasi Presensi Multi-Geofence & Pemilihan Radius** | Geofencing & Location | Tabel `lokasi_presensis`, CRUD Admin lokasi + radius meter per titik, dropdown pemilih titik di kamera presensi, dan validasi Haversine per lokasi. | 🟢 Selesai |
| 14 | **Perbaikan Aksesibilitas Kontras Tombol & Navigasi Ikon** | UI/UX Polish | Penyelarasan styling tombol `.btnNavMaps` agar selalu kontras terbaca dan background icon Jadwal Shift di nav bawah Admin. | 🟢 Selesai |
| 15 | **Modul Portal & Presensi Mandiri Siswa PKBM** | Student Self-Attendance | Autentikasi siswa, tabel `presensi_mandiri_siswas`, dashboard ringkasan, kamera selfie Leaflet geofencing, riwayat, dan profil siswa. | 🟢 Selesai |
| 16 | **Sinkronisasi Otomatis Akun Siswa & Users (Observer & Lifecycle)** | User-Student Sync | `SiswaObserver` auto-provisioning akun `users` saat tambah siswa, auto-sync data nama/kontak, auto-deactivate saat nonaktif/soft-delete, dan reset password admin. | 🟢 Selesai |
| 17 | **Penyelarasan Desain & Responsivitas Modul Mahasiswa Magang (PKL)** | UI/UX & Responsive | Standarisasi seluruh view `admin/magang` (index, create, edit, presensi) mengikuti design system modern `admin/karyawan` dengan *account-stats-grid*, *laporanFilterCard*, *laporanTable*, dan *mobile-card-list*. | 🟢 Selesai |
| 18 | **Standardisasi CRUD Kategori Tutorial & Tarif SK (Full-Page Navigation)** | UI/UX & Flow | Refactoring CRUD Kategori Tutorial dari modal popup menjadi halaman `create.blade.php` dan `edit.blade.php` terpisah yang konsisten dengan standar modul Admin lainnya. | 🟢 Selesai |
| 19 | **Standardisasi Styling Tombol Ekspor & Impor Agenda (`/jadwal`)** | UI/UX Consistency | Memperbarui tombol Export Excel (`btn-action-success`), Import Agenda (`btn-action-info`), Tambah Agenda (`profileBtnPrimary`), serta tombol modal import dan download template agar selaras dengan modul Karyawan dan Siswa. | 🟢 Selesai |
| 20 | **Desentralisasi Jadwal Rutin Tutor & Reschedule Terstruktur (Opsi 2)** | Schedule & Reschedule | Admin tidak wajib buat jadwal; Tutor dapat membuat pola rutin mingguan (fleksibel `berlaku_sampai`) atau sesi sekali lewat modal terpadu 2-in-1, mengelola pola rutin sendiri, serta reschedule sesi via Opsi 2 (sesi lama dibatalkan dengan alasan, sesi baru dibuat bertipe pengganti dengan Web Push ke siswa). | 🟢 Selesai |
| 21 | **Penyelarasan Style & Integrasi Lintas-Modul Halaman Jadwal & Jadwal-Sesi** | UI/UX & Integration | Menyatukan tab navigasi sub-menu (`.calendarNavTabsContainer`), lebar kontainer (`max-w-4xl`), grid kalender multi-dot (sesi biru + agenda amber), banner pengumuman PKBM di jadwal sesi, ringkasan sesi murid di agenda PKBM, dan sub-tab di panel Admin. | 🟢 Selesai |

---

## 📌 3. REKAPITULASI DOKUMEN & ACTION PLAN SELANJUTNYA

### Item Backlog Terencana (Fase Lanjutan):
- **Master Asesmen & Tugas Penunjang**: Tabel `honor_asesmens` & `honor_penunjangs` (soal STS/SAS, periksa, awas, rapor, rapat, outing).
- **Secure Storage Foto Presensi**: Pemindahan direktori foto sensitif ke `storage/app/private/` dengan *Temporary Signed URL*.
- **Integrasi SSO SIM PKBM Pikat & WhatsApp Gateway**: Single Sign-On akun terpusat dan pengiriman notifikasi via WA ke wali murid.
- **Laporan Standar Akreditasi Pendidikan Kesetaraan (BAN PDM / PNF)**: Template laporan otomatis yang disesuaikan dengan instrumen akreditasi pendidikan nonformal.

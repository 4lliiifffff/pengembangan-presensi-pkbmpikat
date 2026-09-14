# ANALISIS LENGKAP PROJECT PRESENSI PKBM PIKAT
## Dokumen Akademis untuk Laporan Praktik Kerja Lapangan (PKL)

> **Tanggal Analisis:** 6 September 2026  
> **Analis:** Antigravity AI (berdasarkan eksplorasi kode sumber project)  
> **Sumber Data:** Seluruh source code, migration, controller, model, middleware, view, route, konfigurasi, dan error log yang ditemukan di workspace `c:\laragon\www\presensi-pkbmpikat`

---

## 1. RINGKASAN PROJECT

**Nama Sistem:** Presensi App (berdasarkan `APP_NAME` di `.env`)  
**Domain Production:** `presensi.pkbmpikat.com` (berdasarkan error log di `routes/error_log`)  
**Nama Database:** `pkbc5947_smart` (berdasarkan `DB_DATABASE` di `.env`)

Sistem ini adalah **Aplikasi Presensi Digital berbasis Web** yang dikembangkan untuk **PKBM Pikat** (Pusat Kegiatan Belajar Masyarakat). Sistem dirancang untuk merekam dan merekap kehadiran tutor (pengajar) saat mengajar siswa, dilengkapi dengan bukti foto sebagai verifikasi kehadiran.

---

## 2. TEKNOLOGI YANG DIGUNAKAN

### Backend
| Teknologi | Versi | Sumber Bukti |
|-----------|-------|--------------|
| PHP | ^8.2 | `composer.json` baris 9 |
| Laravel Framework | ^12.0 | `composer.json` baris 11 |
| Laravel Sanctum | ^4.3 | `composer.json` baris 12 (untuk API token) |
| Laravel Tinker | ^2.10.1 | `composer.json` baris 13 |

### Library Tambahan
| Library | Versi | Fungsi |
|---------|-------|--------|
| `barryvdh/laravel-dompdf` | ^3.1 | Export laporan ke PDF |
| `maatwebsite/excel` | ^3.1 | Export laporan ke Excel/XLSX |
| `fakerphp/faker` | ^1.23 | Data dummy untuk testing |

### Database
| Komponen | Nilai | Sumber Bukti |
|----------|-------|--------------|
| DBMS | MySQL | `.env` baris 27 |
| Host | 127.0.0.1 | `.env` baris 28 |
| Port | 3306 | `.env` baris 29 |

### Frontend
| Teknologi | Keterangan |
|-----------|------------|
| HTML/Blade Template | Semua view menggunakan Blade (`.blade.php`) |
| CSS (Vanilla/Custom) | Berdasarkan struktur resources/css |
| JavaScript | Berdasarkan resources/js |
| Vite | Build tool (`vite.config.js` ada di root) |

### Konfigurasi Server
| Parameter | Nilai | Sumber |
|-----------|-------|--------|
| Timezone | Asia/Jakarta (WIB) | `.env` baris 13, digunakan konsisten di kode |
| Session Driver | File | `.env` baris 34 |
| Cache | File | `.env` baris 47 |
| Queue | Sync (tidak async) | `.env` baris 45 |
| Lokasi Sekolah | `https://maps.app.goo.gl/ahQ61nnpNQ9Z6RcWA` | `.env` baris 8 |

---

## 3. ARSITEKTUR DAN STRUKTUR PROJECT

### Arsitektur Umum
Sistem menggunakan arsitektur **MVC (Model-View-Controller)** bawaan Laravel dengan tambahan:
- **Role-Based Access Control (RBAC)** melalui `RoleMiddleware`
- **Trait Pattern** (`ResolvesTutor`, `ResolvesAdmin`) untuk berbagi logika antar-controller
- **Pola User + Profile** (User untuk login, profil terpisah per role)

### Struktur Direktori Utama
```
presensi-pkbmpikat/
├── app/
│   ├── Exports/              ← Kelas export Excel (PresensiExport)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/        ← Controller untuk role admin
│   │   │   ├── Kepsek/       ← Controller untuk role kepala_sekolah
│   │   │   ├── Tutor/        ← Controller untuk role tutor
│   │   │   └── ...           ← Controller umum (Auth, Dashboard, dll)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php  ← Middleware RBAC
│   ├── Models/               ← Eloquent Models
│   └── Providers/
├── config/
│   └── lokasi.php            ← Konfigurasi lokasi sekolah
├── database/
│   ├── migrations/           ← 24 file migration
│   └── seeders/              ← AdminSeeder (user admin default)
├── resources/
│   └── views/
│       ├── admin/            ← View panel admin
│       ├── kepsek/           ← View panel kepala sekolah
│       ├── tutor/            ← View panel tutor
│       ├── karyawan/         ← View presensi karyawan
│       └── auth/             ← View login
└── routes/
    └── web.php               ← Semua route web (82 baris)
```

---

## 4. AKTOR DAN HAK AKSES

Sistem memiliki **3 peran (role)** yang terdefinisi di tabel `users.role` dan dikontrol oleh `RoleMiddleware`:

| Role | Nilai di DB | Prefix URL | Dashboard |
|------|------------|------------|-----------|
| Admin | `admin` | `/admin/` | `/admin/dashboard` |
| Tutor/Pengajar | `tutor` | `/tutor/` | `/tutor/dashboard` |
| Kepala Sekolah | `kepala_sekolah` | `/kepsek/` | `/kepsek/dashboard` |

### Matriks Hak Akses

| Fitur | Admin | Tutor | Kepala Sekolah |
|-------|-------|-------|----------------|
| Kelola Data Siswa (CRUD) | ✅ | ❌ | ❌ |
| Kelola Data Kelas (CRUD) | ✅ | ❌ | ❌ |
| Kelola Jadwal/Agenda (CRUD) | ✅ | ❌ | ❌ |
| Kelola Data Karyawan/User (CRUD) | ✅ | ❌ | ❌ |
| Input Izin Tutor | ✅ | ❌ | ❌ |
| Laporan Presensi (Filter+Export) | ✅ | ❌ | ❌ |
| Presensi Foto (Clock In/Out) | ❌ | ✅ | ❌ |
| Lihat Riwayat Presensi Sendiri | ❌ | ✅ | ❌ |
| Lihat Jadwal Kegiatan | ❌ | ✅ | ❌ |
| Pengajuan Lupa Lapor | ❌ | ✅ | ❌ |
| Dashboard Monitoring Harian | ✅ | ❌ | ✅ |
| Laporan Rekap per Tutor | ❌ | ❌ | ✅ |
| Lihat Presensi Tutor (Monitor) | ❌ | ❌ | ✅ |
| Kelola Lupa Lapor (Verifikasi) | ❌ | ❌ | ✅ |
| Presensi Karyawan (diri sendiri) | ✅ | ❌ | ✅ |
| Kelola Profil Sendiri | ✅ | ✅ | ✅ |

---

## 5. MODUL DAN FITUR

### 5.1 Modul Autentikasi
**File:** `app/Http/Controllers/AuthWebController.php`
- Login menggunakan **NIK** atau **Email** (dual-field)
- Fitur **Remember Me**
- Redirect otomatis ke dashboard sesuai role setelah login
- Logout aman (invalidasi session + regenerasi CSRF token)
- Pertahanan Session Fixation Attack

### 5.2 Modul Presensi Tutor (Fitur Inti)
**File:** `app/Http/Controllers/Tutor/PresensiFotoController.php`
- **Clock In (Absen Masuk):** Tutor memilih siswa yang diajar, mengambil foto, mencatat jam dan lokasi GPS
- **Clock Out (Absen Pulang):** Upload foto pulang, catat jam selesai
- **Multi-sesi:** Satu tutor dapat mengajar beberapa siswa dalam satu hari
- **Aturan 1 Jam:** Absen pulang minimal 1 jam setelah absen masuk
- **Backup kompatibilitas:** Sistem memeriksa keberadaan kolom `tutor_id` di tabel presensis sebelum filter (backward compatibility)

### 5.3 Modul Dashboard
**Admin** (`app/Http/Controllers/DashboardController.php`):
- Statistik hadir/izin hari ini
- Grafik batang kehadiran mingguan (7 hari)
- Daftar presensi terbaru (5 data)

**Tutor** (`app/Http/Controllers/Tutor/TutorDashboardController.php`):
- Status presensi hari ini (belum_mulai/proses/selesai)
- Countdown timer sisa waktu sebelum bisa absen pulang
- Daftar jadwal mendatang (maks 8 item)

**Kepala Sekolah** (`app/Http/Controllers/Kepsek/KepsekDashboardController.php`):
- Statistik hadir hari ini
- Grafik kehadiran mingguan

### 5.4 Modul Laporan
**Admin** (`app/Http/Controllers/Admin/LaporanController.php`):
- Filter berdasarkan rentang tanggal, tutor, siswa, status
- Export Excel (XLSX) dengan header informatif
- Export PDF (A4 Landscape)
- Chart tren kehadiran harian

**Kepala Sekolah** (`app/Http/Controllers/Kepsek/KepsekDashboardController.php`):
- Rekap kehadiran per tutor (persentase hadir, total jam mengajar)
- Export PDF (A4 Portrait)
- Filter per bulan/tahun

### 5.5 Modul Manajemen Data (Admin)
- **Siswa:** CRUD lengkap, penugasan kelas dan tutor
- **Kelas:** CRUD kelompok belajar
- **Jadwal/Agenda:** Buat pengumuman kegiatan (judul, deskripsi, tanggal, lokasi → link Google Maps)
- **Karyawan:** CRUD akun pengguna (semua role), toggle aktif/nonaktif

### 5.6 Modul Izin
**File:** `app/Http/Controllers/Admin/IzinController.php`
- Admin dapat memberikan status izin kepada tutor untuk siswa tertentu pada tanggal tertentu
- AJAX endpoint untuk mengambil daftar siswa per tutor
- Izin dapat dicabut (delete)

### 5.7 Modul Lupa Lapor
**Tutor** (`app/Http/Controllers/Tutor/LupaLaporController.php`):
- Pengajuan laporan retroaktif (untuk tanggal yang sudah lewat)
- Validasi: tanggal tidak boleh masa depan, jam selesai > jam mulai, alasan minimal 10 karakter
- Tutor hanya bisa hapus pengajuan miliknya sendiri

**Kepala Sekolah** (`app/Http/Controllers/Kepsek/KepsekDashboardController.php`):
- Melihat semua pengajuan lupa lapor
- Filter berdasarkan tanggal dan nama
- Menghapus pengajuan

### 5.8 Modul Presensi Karyawan
**File:** `app/Http/Controllers/KaryawanPresensiController.php`
- Admin dan Kepala Sekolah dapat melakukan presensi untuk diri sendiri
- Mekanisme sama dengan presensi tutor (foto masuk/pulang, minimal 1 jam)

### 5.9 Modul Profil
**File:** `app/Http/Controllers/ProfileController.php`
- Semua role dapat mengubah data profil (nama, NIK, no HP, foto)
- Ubah password (verifikasi password lama)

---

## 6. ALUR SISTEM / PROSES BISNIS

### Alur Login
```
Browser → GET /login → Form Login
→ POST /login (NIK/Email + Password)
→ AuthWebController::process()
  → Coba Auth::attempt() dengan NIK
  → Jika gagal, coba Auth::attempt() dengan Email
  → Jika berhasil → Session::regenerate() → Redirect ke dashboard sesuai role
  → Jika gagal → Back dengan pesan error
```

### Alur Presensi Tutor (Alur Utama)
```
Tutor Login → /tutor/dashboard
→ Status: "Belum Mulai" → Tombol "Mulai Presensi"
→ GET /tutor/presensi → Form presensi foto
  → Pilih siswa yang akan diajar
  → Aktifkan kamera → Ambil foto
  → POST /tutor/presensi (mode=mulai, foto, lokasi)
  → Server: Validasi → Simpan foto ke public/uploads/presensi/{tutor_id}/{tanggal}/
  → Buat record Presensi (jam_mulai = waktu server WIB)
→ Dashboard: Status "Proses" + Countdown 1 jam
→ Setelah ≥1 jam → Ambil foto pulang
  → POST /tutor/presensi (mode=selesai, foto, lokasi)
  → Server: Validasi durasi → Update record (jam_selesai, foto_selesai)
→ Dashboard: Status "Selesai"
```

### Alur Laporan Admin
```
Admin → /admin/laporan → Filter (tanggal, tutor, siswa, status)
→ Lihat statistik + tabel presensi terbaru + chart
→ Export Excel → LaporanController::exportExcel() → PresensiExport → .xlsx
→ Export PDF → LaporanController::exportPdf() → DomPDF → .pdf
```

### Alur Lupa Lapor
```
Tutor → /tutor/lupa-lapor → Form pengajuan
→ Isi: siswa, tanggal (≤ hari ini), jam mulai, jam selesai, alasan
→ POST /tutor/lupa-lapor → Simpan ke tabel lapor__lapors
→ Kepala Sekolah → /kepsek/lupa-lapor → Lihat semua pengajuan
→ Kepala Sekolah dapat menghapus pengajuan yang sudah diverifikasi
```

---

## 7. ANALISIS DATABASE

### Daftar Tabel (berdasarkan migration)

| Nama Tabel | Fungsi | File Migration |
|------------|--------|----------------|
| `users` | Akun login semua pengguna | `0001_01_01_000000_create_users_table.php` |
| `admins` | Profil detail admin | `2026_03_04_140000_create_admins_table.php` |
| `tutors` | Profil detail tutor | `2026_03_04_150000_create_tutors_table.php` |
| `kepala__sekolahs` | Profil detail kepala sekolah | `2026_03_06_083634_create_kepala__sekolahs_table.php` |
| `kelas` | Data kelompok belajar | `2026_03_04_151000_create_kelas_table.php` |
| `siswas` | Data peserta didik | `2026_03_04_151100_create_siswas_table.php` |
| `presensis` | Rekaman presensi tutor | `2026_03_04_154948_create_presensis_table.php` |
| `lapor__lapors` | Pengajuan lupa lapor tutor | `2026_03_16_081150_create_lapor__lapors_table.php` |
| `jadwals` | Agenda/jadwal kegiatan | `2026_03_10_000000_create_jadwals_table.php` |
| `presensi_karyawans` | Presensi admin/kepsek | `2026_05_18_110725_create_presensi_karyawans_table.php` |
| `sessions` | Sesi login pengguna | `2026_04_05_120000_create_sessions_table.php` |
| `cache` | Cache Laravel | `0001_01_01_000001_create_cache_table.php` |
| `jobs` | Antrian pekerjaan (Queue) | `0001_01_01_000002_create_jobs_table.php` |
| `personal_access_tokens` | Token Sanctum (API) | `2026_02_24_061022_create_personal_access_tokens_table.php` |

### Struktur Tabel Utama

#### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto-increment |
| nik | varchar(20) UNIQUE | Nomor Induk Karyawan (untuk login) |
| nama_lengkap | varchar | Nama lengkap pengguna |
| email | varchar NULLABLE UNIQUE | Email (untuk login alternatif) |
| password | varchar | Hash bcrypt |
| role | enum('admin','tutor','kepala_sekolah') | Peran pengguna |
| no_hp | varchar NULLABLE | Nomor HP |
| foto | varchar NULLABLE | Path foto profil |
| is_active | boolean DEFAULT 1 | Status aktif/nonaktif |
| remember_token | varchar NULLABLE | Token Remember Me |

#### Tabel `presensis`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| tutor_id | bigint FK → tutors | |
| siswa_id | bigint FK → siswas | |
| tgl_presensi | date | |
| jam_mulai | time | Jam absen masuk |
| jam_selesai | time | Jam absen pulang |
| foto_mulai | varchar(255) NULLABLE | Path foto masuk |
| foto_selesai | varchar(255) NULLABLE | Path foto pulang |
| lokasi_mulai | varchar(255) NULLABLE | Koordinat/nama lokasi masuk |
| lokasi_selesai | varchar(255) NULLABLE | Koordinat/nama lokasi pulang |
| status | varchar(20) DEFAULT 'pending' | hadir/izin/alpha/pending |

#### Tabel `lapor__lapors`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| tutor_id | bigint FK → tutors | ON DELETE CASCADE |
| siswa_id | bigint FK → siswas | ON DELETE CASCADE |
| tanggal | date | Tanggal mengajar yang dilaporkan |
| jam_mulai | time | |
| jam_selesai | time | |
| alasan | text | Alasan lupa lapor |

#### Tabel `siswas`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| no_absen | varchar(50) UNIQUE | Nomor absen siswa |
| nama_siswa | varchar(120) | |
| no_hp | varchar(30) | |
| nama_wali | varchar(120) | Nama orang tua/wali |
| kelas_id | bigint FK → kelas | |
| tutor_id | bigint FK → tutors NULLABLE | Tutor pembimbing |

### Relasi Antar Tabel

```
users (1) ←→ (1) tutors
users (1) ←→ (1) admins
users (1) ←→ (1) kepala__sekolahs
users (1) ←→ (N) presensi_karyawans

tutors (1) ←→ (N) presensis
tutors (1) ←→ (N) lapor__lapors
tutors (1) ←→ (N) siswas [sebagai tutor pembimbing]

siswas (1) ←→ (N) presensis
siswas (N) ←→ (1) kelas

presensis : tutor_id FK, siswa_id FK
lapor__lapors : tutor_id FK, siswa_id FK
```

### Catatan Database Khusus
- Nama tabel `lapor__lapors` menggunakan **double underscore** — ini hasil dari konversi nama class `Lapor_Lapor` oleh Laravel, dan sudah ditangani dengan `$table = 'lapor__lapors'` di model.
- Nama tabel `kepala__sekolahs` juga menggunakan double underscore karena nama model `Kepala_Sekolah`.
- Tabel `presensis` (bukan `presensi`) adalah bentuk plural Laravel dari `Presensi`.

---

## 8. TEMUAN MASALAH

### 8.1 Masalah Teknis

#### [T1] Deprecation Warning PHP 8.5 — PDO::MYSQL_ATTR_SSL_CA
- **Kondisi saat ini:** Setiap kali artisan dijalankan, muncul pesan `PHP Deprecated: Constant PDO::MYSQL_ATTR_SSL_CA is deprecated since 8.5`
- **Bukti:** Output terminal saat `php artisan key:generate`; lokasi di `config/database.php` baris 61 dan 81
- **Dampak:** Warning mengganggu output; pada versi PHP berikutnya bisa menjadi Fatal Error; jika digunakan di PHP 8.5+, koneksi database bisa gagal
- **Akar masalah:** Template konfigurasi database Laravel menggunakan konstanta lama `PDO::MYSQL_ATTR_SSL_CA` yang sudah deprecated
- **Prioritas:** SEDANG (saat ini warning, bukan error)
- **Solusi:** Ganti dengan `Pdo\Mysql::ATTR_SSL_CA` atau gunakan conditional check versi PHP

#### [T2] Error Log Server Production yang Sangat Panjang
- **Kondisi saat ini:** File `routes/error_log` berisi 429 baris error PHP Fatal Error dari server production `presensi.pkbmpikat.com` sejak April hingga September 2026
- **Bukti:** `routes/error_log` — semua error berkaitan `Class "Illuminate\Support\Facades\Route" not found` dan `Class "Illuminate\Support\Facades\Artisan" not found`
- **Dampak:** Sistem sempat atau masih mengalami kegagalan total di production (error 500) — vendor autoload tidak terpasang dengan benar di server
- **Akar masalah:** Di server production, direktori `vendor/` kemungkinan tidak di-upload atau `composer install` tidak dijalankan setelah deploy. Error `Class not found` untuk Laravel Facade adalah tanda klasik `vendor/` tidak ada
- **Prioritas:** TINGGI (berpotensi sistem tidak berjalan di production)
- **Solusi:** Pastikan `composer install --no-dev` dijalankan di server production setelah setiap deploy

#### [T3] File Error Log Tersimpan dalam Direktori Source Code
- **Kondisi saat ini:** File `error_log` ditemukan di: `routes/`, `app/Models/`, `app/Http/Controllers/`, `app/Http/Controllers/Admin/`, `app/Http/Controllers/Tutor/`, `app/Http/Controllers/Kepsek/`, `app/Exports/`, `database/migrations/`, `database/seeders/`
- **Bukti:** Listing direktori — setiap folder berisi file `error_log`
- **Dampak:** File error log berada di dalam source code; jika di-commit ke Git, informasi sensitif (stack trace, path absolut server, nama database) bisa bocor
- **Akar masalah:** PHP error log di-konfigurasi untuk menulis ke direktori yang sama dengan source code di server production
- **Prioritas:** TINGGI (keamanan informasi)
- **Solusi:** Konfigurasi `error_log` ke direktori `/tmp` atau `storage/logs`; tambahkan `error_log` ke `.gitignore`

#### [T4] Backward Compatibility Check Berulang di Banyak Tempat
- **Kondisi saat ini:** `Schema::hasColumn('presensis', 'tutor_id')`, `Schema::hasColumn('users', 'is_active')`, dll digunakan di banyak controller dan model
- **Bukti:** `PresensiFotoController.php` baris 89, 93, 163; `KaryawanController.php` baris 20, 27; `User.php` baris 121; `LaporanController.php` baris 29-30
- **Dampak:** Overhead performa (multiple Schema::hasColumn() queries per request); code smells — menandakan skema DB tidak konsisten
- **Akar masalah:** Migrasi bertahap yang menambah kolom secara inkremental, namun kode lama tidak di-refactor setelah migrasi selesai
- **Prioritas:** SEDANG
- **Solusi:** Jalankan semua migrasi, pastikan skema lengkap, lalu hapus semua `Schema::hasColumn()` checks di kode production

#### [T5] Status Default Presensi Inkonsisten
- **Kondisi saat ini:** Migration membuat kolom `status` dengan default `'pending'`, tetapi kode controller menggunakan nilai `'hadir'`, `'izin'`, `'alpha'`, `'proses'`
- **Bukti:** `create_presensis_table.php` baris 24: `default('pending')`; `PresensiFotoController.php` baris 206: `$presensi->status = 'hadir'`; `LaporanController.php` baris 107
- **Dampak:** Data lama dengan status `'pending'` bisa tidak terhitung dalam laporan; logika filter status bisa salah
- **Prioritas:** SEDANG
- **Solusi:** Jalankan migrasi untuk mengubah default menjadi `'hadir'` atau tambahkan status `'pending'` ke semua logika filter

#### [T6] Duplikasi Logika countHadir
- **Kondisi saat ini:** Method `countHadir()` yang identik didefinisikan ulang di `DashboardController.php` dan `KepsekDashboardController.php`
- **Bukti:** `DashboardController.php` baris 85-89 dan `KepsekDashboardController.php` baris 337-342
- **Dampak:** Jika logika berubah, harus diubah di dua tempat (violates DRY principle)
- **Prioritas:** RENDAH
- **Solusi:** Pindahkan ke Trait atau Service class

#### [T7] Duplikasi Logika Export PDF Laporan (Rekap Tutor)
- **Kondisi saat ini:** Logika `rekapTutor` ditulis identik di `laporan()` dan `exportPdf()` di `KepsekDashboardController`
- **Bukti:** Baris 99-145 dan 195-230 di `KepsekDashboardController.php`
- **Prioritas:** RENDAH
- **Solusi:** Ekstrak ke method helper private

#### [T8] Static Counter di PresensiExport Berpotensi Bermasalah
- **Kondisi saat ini:** `static $no = 0;` digunakan dalam method `map()` di `PresensiExport.php`
- **Bukti:** `PresensiExport.php` baris 69
- **Dampak:** Jika export digunakan lebih dari sekali dalam request yang sama, nomor urut akan terus bertambah dari angka terakhir (tidak reset ke 1)
- **Prioritas:** RENDAH
- **Solusi:** Gunakan counter di property class atau inject nomor dari luar

### 8.2 Masalah Database

#### [D1] Nama Tabel dengan Double Underscore
- **Kondisi saat ini:** Tabel `lapor__lapors` dan `kepala__sekolahs` memiliki nama yang tidak konvensional
- **Bukti:** Migration file `2026_03_16_081150_create_lapor__lapors_table.php` baris 14; Model `Lapor_Lapor.php` baris 62
- **Dampak:** Membingungkan developer baru; harus selalu mendefinisikan `$table` secara eksplisit
- **Prioritas:** RENDAH (sudah di-handle di kode)
- **Solusi:** Untuk project baru, gunakan nama model tanpa underscore (e.g., `LaporLapor` → tabel `lapor_lapors`)

#### [D2] Tidak Ada Foreign Key dari `siswas` ke `tutors` di Migration Awal
- **Kondisi saat ini:** Kolom `tutor_id` di tabel `siswas` ditambahkan melalui migrasi terpisah
- **Bukti:** `2026_05_18_222811_add_tutor_id_to_siswas_table.php`
- **Prioritas:** INFORMASI

#### [D3] Data Izin Tutor Tidak Ada Log Waktu Pengajuan
- **Kondisi saat ini:** Tabel `lapor__lapors` menyimpan data izin lupa lapor, tapi tidak ada kolom untuk status approval (disetujui/ditolak)
- **Bukti:** Migration `create_lapor__lapors_table.php` — tidak ada kolom `status` atau `approved_at`
- **Dampak:** Kepala Sekolah tidak bisa memberikan tanda persetujuan; data hanya bisa dihapus, tidak bisa di-approve
- **Prioritas:** SEDANG

### 8.3 Masalah Keamanan

#### [K1] APP_DEBUG=true di Environment yang Mungkin Production
- **Kondisi saat ini:** `.env` baris 4: `APP_DEBUG=true`
- **Dampak:** Jika file `.env` ini digunakan di production, stack trace error akan tampil ke pengguna, mengekspos informasi sensitif
- **Catatan:** `.env` saat ini dikonfigurasi dengan `DB_USERNAME=root` dan `DB_PASSWORD=` (kosong) — ciri khas environment lokal
- **Prioritas:** RENDAH untuk lokal, TINGGI jika di-deploy tanpa perubahan

#### [K2] Email Auto-generate dengan Format Terprediksi
- **Kondisi saat ini:** Email dibuat otomatis dengan format `{nik}@local.test`
- **Bukti:** `KaryawanController.php` baris 64
- **Dampak:** Format email predictable; bukan masalah kritis untuk sistem internal, tapi tidak ideal
- **Prioritas:** RENDAH

#### [K3] Tidak Ada Rate Limiting di Login
- **Kondisi saat ini:** Tidak ditemukan ThrottleRequests middleware atau rate limiter di route login
- **Bukti:** `web.php` baris 27-28 — tidak ada `throttle:` middleware
- **Dampak:** Rentan terhadap serangan Brute Force
- **Prioritas:** SEDANG
- **Solusi:** Tambahkan `Route::middleware('throttle:5,1')` di route login

#### [K4] Foto Disimpan di Folder `public/`
- **Kondisi saat ini:** Foto presensi disimpan di `public/uploads/presensi/...`
- **Bukti:** `PresensiFotoController.php` baris 162, 187
- **Dampak:** Foto dapat diakses langsung via URL tanpa autentikasi; siapapun yang tahu URL foto bisa melihatnya
- **Prioritas:** SEDANG
- **Solusi:** Pindahkan ke `storage/app/private/` dan buat endpoint yang terautentikasi untuk mengakses foto

### 8.4 Masalah Fungsional

#### [F1] Lupa Lapor Tidak Terintegrasi ke Presensi Resmi
- **Kondisi saat ini:** Pengajuan lupa lapor tersimpan di tabel `lapor__lapors` yang TERPISAH dari tabel `presensis`
- **Bukti:** Model `Lapor_Lapor.php` baris 43-44 (komentar eksplisit menyebut ini)
- **Dampak:** Data lupa lapor tidak masuk ke rekap presensi resmi; kepala sekolah harus memverifikasi manual; statistik kehadiran tidak akurat
- **Prioritas:** SEDANG

#### [F2] Status Izin Hanya Bisa Diberikan Admin, Bukan Kepala Sekolah
- **Kondisi saat ini:** Route izin hanya ada di grup `middleware(['auth', 'role:admin'])`
- **Bukti:** `web.php` baris 54-58 — `IzinController` hanya di grup admin
- **Dampak:** Kepala Sekolah tidak bisa langsung memberikan izin, harus meminta admin
- **Prioritas:** RENDAH (tergantung kebijakan institusi)

#### [F3] Tidak Ada Validasi Tutor Hanya Bisa Presensi untuk Siswa Miliknya
- **Kondisi saat ini:** Tutor dapat memilih `siswa_id` mana saja saat presensi, meski ada filter `where('tutor_id', $tutor->id)` di tampilan dropdown
- **Bukti:** `PresensiFotoController.php` baris 85 dan baris 150-151 (validasi hanya `integer`, tidak ada `exists` check)
- **Dampak:** Jika seseorang manipulasi request, tutor bisa memasukkan siswa yang bukan miliknya
- **Prioritas:** SEDANG

#### [F4] KaryawanController::destroy Tidak Menghapus Foto
- **Kondisi saat ini:** Method `destroy` hanya memanggil `User::findOrFail($id)->delete()` tanpa menghapus foto yang terupload
- **Bukti:** `KaryawanController.php` baris 158-162
- **Dampak:** File foto karyawan yang dihapus tetap ada di `public/uploads/foto_karyawan/` — akumulasi file orphan
- **Prioritas:** RENDAH

#### [F5] Fitur Admin TutorController Tidak Ditemukan di Route
- **Kondisi saat ini:** Route `resource('karyawan', ...)` menggantikan tutor management, tapi ada file `TutorController` di `app/Http/Controllers/Admin/` yang tidak direferensikan di route
- **Bukti:** `web.php` baris 47; daftar file di `app/Http/Controllers/Admin/`
- **Dampak:** Kemungkinan ada controller yang tidak terpakai (dead code)
- **Prioritas:** RENDAH

### 8.5 Masalah UI/UX

#### [U1] Tidak Dapat Dikonfirmasi dari Kode
Karena file view sangat besar (misalnya `presensi_foto.blade.php` = 49,762 bytes, `dashboard.blade.php` tutor = 20,526 bytes), analisis UI/UX memerlukan pemeriksaan langsung di browser. Tidak dapat dideklarasikan masalah UI/UX secara definitif dari kode saja.

### 8.6 Masalah Performa

#### [P1] Schema::hasColumn() Dipanggil Berkali-kali per Request
- **Kondisi saat ini:** Dalam satu request ke halaman laporan, `Schema::hasColumn()` dipanggil hingga 5-6 kali untuk kolom yang berbeda
- **Bukti:** `LaporanController.php` baris 29-30; `PresensiFotoController.php` baris 89, 163
- **Dampak:** Setiap `Schema::hasColumn()` melakukan query ke database (INFORMATION_SCHEMA); untuk traffic tinggi ini bisa menambah latensi
- **Prioritas:** RENDAH (untuk skala kecil, tidak signifikan)

#### [P2] Tidak Ada Pagination di Beberapa Query
- **Kondisi saat ini:** `riwayatIzin` di `IzinController` di-limit ke 50 tanpa pagination; `LupaLaporController::index()` mengambil semua record tanpa pagination
- **Bukti:** `IzinController.php` baris 26-27; `LupaLaporController.php` baris 24-28
- **Dampak:** Jika data banyak, halaman bisa lambat karena load semua data sekaligus
- **Prioritas:** RENDAH (untuk skala awal)

---

## 9. PRIORITAS MASALAH

| Prioritas | ID | Masalah |
|-----------|----|---------|
| 🔴 TINGGI | T2 | Error log production — vendor tidak terinstall di server |
| 🔴 TINGGI | T3 | File error_log di dalam source code (keamanan informasi) |
| 🟡 SEDANG | T1 | Deprecation PHP 8.5 — PDO::MYSQL_ATTR_SSL_CA |
| 🟡 SEDANG | T4 | Backward compatibility checks berulang |
| 🟡 SEDANG | T5 | Status default 'pending' tidak konsisten |
| 🟡 SEDANG | K3 | Tidak ada rate limiting di login |
| 🟡 SEDANG | K4 | Foto di folder public (akses tanpa auth) |
| 🟡 SEDANG | F1 | Lupa lapor tidak terintegrasi ke presensi resmi |
| 🟡 SEDANG | F3 | Validasi tutor-siswa tidak ketat |
| 🟡 SEDANG | D3 | Tidak ada status approval di lupa lapor |
| 🟢 RENDAH | T6 | Duplikasi countHadir |
| 🟢 RENDAH | T7 | Duplikasi logika PDF kepsek |
| 🟢 RENDAH | T8 | Static counter export |
| 🟢 RENDAH | K1 | APP_DEBUG=true (lokal aman) |
| 🟢 RENDAH | F4 | Delete karyawan tidak hapus foto |
| 🟢 RENDAH | P1 | Schema::hasColumn overhead |

---

## 10. REKOMENDASI PENGEMBANGAN

Berikut rekomendasi yang relevan untuk PKL, berurutan dari yang paling berdampak:

### Rekomendasi 1: Perbaiki Deprecation Warning PDO (Implementabel)
**File terdampak:** `config/database.php`  
**Perubahan:** Ganti `PDO::MYSQL_ATTR_SSL_CA` dengan `Pdo\Mysql::ATTR_SSL_CA` (dengan conditional check versi PHP)  
**Justifikasi:** Berdampak nyata, mudah dibuktikan before/after, perbaikan nyata untuk kompatibilitas PHP masa depan

### Rekomendasi 2: Tambahkan Rate Limiting di Login (Implementabel)
**File terdampak:** `routes/web.php`  
**Perubahan:** Tambahkan middleware `throttle:5,1` pada route POST `/login`  
**Justifikasi:** Meningkatkan keamanan, satu baris kode, dapat dibuktikan

### Rekomendasi 3: Tambahkan Validasi Kepemilikan Siswa saat Presensi (Implementabel)
**File terdampak:** `app/Http/Controllers/Tutor/PresensiFotoController.php`  
**Perubahan:** Tambahkan validasi `Rule::in()` agar siswa_id yang dikirim harus milik tutor yang login  
**Justifikasi:** Menutup celah keamanan fungsional

### Rekomendasi 4: Tambahkan Status Approval di Lupa Lapor (Implementabel)
**File terdampak:** Migration baru + `LupaLaporController` + `KepsekDashboardController` + view  
**Perubahan:** Tambah kolom `status` (pending/disetujui/ditolak) dan `catatan_kepsek` di tabel `lapor__lapors`  
**Justifikasi:** Meningkatkan fungsionalitas bisnis yang nyata, ada before/after yang jelas

### Rekomendasi 5: Hapus/Pindahkan File Error Log dari Source Code
**Tindakan:** Tambahkan `error_log` ke `.gitignore` dan konfigurasi PHP error log ke direktori yang tepat  
**Justifikasi:** Keamanan informasi

---

## 11. PERUBAHAN YANG DILAKUKAN

> **CATATAN:** Berdasarkan instruksi analisis, perubahan hanya dilakukan setelah mendapatkan persetujuan. Bagian ini akan diisi setelah diskusi dengan pembimbing PKL mengenai perbaikan mana yang akan diimplementasikan.

Perubahan yang **telah** dilakukan selama sesi analisis ini:
- **Tidak ada perubahan kode** — analisis bersifat read-only untuk memastikan tidak ada fungsi yang terganggu

---

## 12. HASIL SETELAH PERBAIKAN

> Akan diisi setelah implementasi perbaikan.

---

## 13. LATAR BELAKANG LAPORAN PKL

### A. Kondisi dan Konteks Permasalahan

PKBM Pikat merupakan lembaga pendidikan non-formal yang menyelenggarakan program pembelajaran dengan melibatkan tutor (pengajar) dan siswa. Dalam operasional sehari-hari, lembaga membutuhkan mekanisme pencatatan kehadiran tutor yang akurat sebagai dasar evaluasi kinerja dan pelaporan kepada pemangku kepentingan.

### B. Kondisi Sistem Saat Ini

Berdasarkan analisis kode sumber, diketahui bahwa PKBM Pikat telah mengembangkan dan men-deploy sistem presensi digital berbasis web dengan domain `presensi.pkbmpikat.com`. Sistem dibangun menggunakan framework Laravel 12 dengan PHP 8.2+, dilengkapi fitur presensi berbasis foto, manajemen data siswa dan tutor, serta pelaporan yang dapat diekspor ke Excel dan PDF.

### C. Kebutuhan Organisasi/Pengguna

Berdasarkan fitur yang diimplementasikan dalam sistem, teridentifikasi kebutuhan sebagai berikut:
1. **Pencatatan kehadiran tutor** dengan bukti foto dan lokasi GPS
2. **Multi-peran pengguna:** Admin (pengelola data), Tutor (pelaksana presensi), Kepala Sekolah (monitor dan laporan)
3. **Rekap dan laporan** kehadiran yang dapat dicetak dan diekspor
4. **Mekanisme koreksi** untuk tutor yang lupa melakukan presensi digital

### D. Permasalahan / Kesenjangan (Gap)

Berdasarkan analisis kode dan error log yang ditemukan, teridentifikasi beberapa kesenjangan:

1. **Kesenjangan Operasional:** Error log production (`routes/error_log`) mencatat ratusan PHP Fatal Error selama periode April–September 2026, mengindikasikan ketidakstabilan sistem di lingkungan production akibat pengelolaan deployment yang belum terstandarisasi.

2. **Kesenjangan Fungsional:** Fitur Lupa Lapor yang sudah ada tidak terintegrasi ke rekap presensi resmi, sehingga data kehadiran tutor yang diajukan melalui mekanisme lupa lapor tidak terefleksikan dalam laporan kehadiran.

3. **Kesenjangan Keamanan:** Tidak adanya pembatasan percobaan login (rate limiting) berpotensi membuka kerentanan brute force; foto presensi disimpan di direktori yang dapat diakses publik tanpa autentikasi.

4. **Kesenjangan Teknis:** Penggunaan konstanta PHP yang sudah deprecated (`PDO::MYSQL_ATTR_SSL_CA`) menimbulkan warning yang dapat berevolusi menjadi error pada versi PHP yang akan datang.

5. **Kesenjangan Kode:** Adanya pengecekan kompatibilitas skema database (`Schema::hasColumn()`) yang tersebar di banyak controller menandakan bahwa proses migrasi database belum terkonsolidasi, menyebabkan kode yang sulit dipelihara.

### E. Dampak dari Permasalahan

- **Operasional:** Instabilitas di server production dapat menyebabkan tutor tidak bisa melakukan presensi, yang berpengaruh pada akurasi data kehadiran.
- **Data:** Data lupa lapor yang tidak terintegrasi menyebabkan laporan kehadiran tidak mencerminkan kondisi nyata.
- **Keamanan:** Potensi akses tidak sah melalui brute force atau akses langsung ke file foto.
- **Maintainability:** Kode dengan banyak pengecekan backward compatibility menyulitkan pengembangan dan pemeliharaan jangka panjang.

### F. Alasan Diperlukan Pengembangan/Perbaikan

Mengingat sistem sudah di-deploy dan aktif digunakan (`presensi.pkbmpikat.com`), perbaikan diperlukan untuk:
1. Memastikan keandalan sistem di lingkungan production
2. Meningkatkan keamanan autentikasi dan penyimpanan data
3. Memperbaiki integritas data laporan kehadiran
4. Menyelaraskan kode dengan standar PHP terkini

### G. Arah Solusi yang Dilakukan Selama PKL

Selama masa PKL, pekerjaan yang dilakukan mencakup:
1. **Analisis komprehensif** sistem yang sudah ada
2. **Identifikasi masalah** teknis, keamanan, dan fungsional
3. **Implementasi perbaikan** yang terukur dan dapat dibuktikan
4. **Dokumentasi** untuk mendukung keberlangsungan pengembangan sistem

---

## 14. IDENTIFIKASI MASALAH

Berdasarkan analisis mendalam terhadap kode sumber, konfigurasi, dan log sistem, ditemukan masalah-masalah berikut:

1. Sistem menggunakan konstanta PHP yang sudah deprecated (`PDO::MYSQL_ATTR_SSL_CA`) yang dapat menyebabkan kegagalan koneksi database pada versi PHP 8.5 ke atas.
2. File error log server production tersimpan di dalam direktori source code, berpotensi mengekspos informasi sensitif.
3. Tidak terdapat mekanisme pembatasan percobaan login (rate limiting), sehingga sistem rentan terhadap serangan brute force.
4. Foto presensi sebagai bukti kehadiran disimpan di direktori publik dan dapat diakses tanpa autentikasi.
5. Fitur Lupa Lapor tidak terintegrasi ke rekap presensi resmi, menyebabkan data kehadiran tidak akurat.
6. Terdapat duplikasi logika di beberapa controller yang menyulitkan pemeliharaan kode.
7. Error log production menunjukkan adanya masalah deployment yang menyebabkan sistem sempat tidak dapat diakses.

---

## 15. BATASAN MASALAH

Mengingat keterbatasan waktu dan scope PKL, perbaikan difokuskan pada:

1. **Perbaikan keamanan autentikasi:** Implementasi rate limiting pada fitur login.
2. **Perbaikan kompatibilitas PHP:** Pembaruan konstanta database yang sudah deprecated.
3. **Perbaikan validasi data:** Penambahan validasi kepemilikan siswa pada presensi tutor.
4. **Perbaikan fungsional Lupa Lapor:** Penambahan mekanisme status persetujuan.

Hal-hal yang **berada di luar batasan** pekerjaan PKL ini:
- Migrasi infrastruktur server production
- Perubahan arsitektur sistem secara menyeluruh
- Implementasi fitur baru yang tidak terkait dengan masalah yang ditemukan
- Pengujian performa dan load testing

---

## 16. RUMUSAN MASALAH

Berdasarkan latar belakang dan identifikasi masalah di atas, rumusan masalah dalam laporan PKL ini adalah:

1. Bagaimana cara memperbaiki konfigurasi database agar kompatibel dengan PHP versi terbaru (8.5+)?
2. Bagaimana cara mengimplementasikan mekanisme keamanan login untuk mencegah serangan brute force?
3. Bagaimana cara memperbaiki validasi data presensi agar tutor hanya dapat melakukan presensi untuk siswa yang ditugaskan kepadanya?
4. Bagaimana cara meningkatkan fungsi Lupa Lapor agar kepala sekolah dapat memberikan status persetujuan secara digital?

---

## 17. TUJUAN

Tujuan dari kegiatan PKL dan pengembangan/perbaikan sistem ini adalah:

1. Memperbaiki konfigurasi `config/database.php` untuk menghilangkan deprecation warning dan memastikan kompatibilitas dengan PHP 8.5+.
2. Mengimplementasikan rate limiting pada endpoint login untuk meningkatkan keamanan sistem autentikasi.
3. Menambahkan validasi kepemilikan siswa pada proses presensi tutor untuk mencegah manipulasi data.
4. Menambahkan kolom `status` dan `catatan_kepsek` pada tabel `lapor__lapors` beserta alur verifikasi oleh kepala sekolah, sehingga proses Lupa Lapor memiliki mekanisme approval yang terstruktur.

---

## 18. MANFAAT

### Manfaat Teoritis
1. Memberikan pemahaman praktis tentang implementasi Laravel 12 dalam konteks sistem informasi pendidikan non-formal.
2. Menambah referensi tentang best practice keamanan web application dalam lingkungan production.
3. Menjadi dokumentasi pengembangan sistem yang dapat dijadikan acuan pengembangan selanjutnya.

### Manfaat Praktis
**Bagi PKBM Pikat:**
1. Meningkatnya keandalan sistem presensi digital yang sudah berjalan.
2. Meningkatnya keamanan sistem dari potensi serangan brute force.
3. Data presensi yang lebih akurat dengan adanya mekanisme approval lupa lapor.
4. Kode yang lebih mudah dipelihara oleh developer selanjutnya.

**Bagi Mahasiswa PKL:**
1. Pengalaman langsung menganalisis dan memperbaiki sistem produksi nyata.
2. Pemahaman mendalam tentang keamanan aplikasi web berbasis Laravel.
3. Kemampuan membaca dan memahami kode yang ditulis oleh developer lain.

**Bagi Universitas:**
1. Terjalinnya kerja sama yang baik antara institusi akademik dan institusi pendidikan masyarakat.
2. Tersedianya studi kasus nyata yang dapat digunakan dalam pembelajaran.

---

## 19. INFORMASI YANG MASIH PERLU DIKONFIRMASI

Berikut informasi yang **tidak dapat dipastikan** dari analisis kode saja dan memerlukan konfirmasi dari pembimbing lapangan atau pihak PKBM Pikat:

| No | Informasi yang Diperlukan | Mengapa Diperlukan |
|----|--------------------------|-------------------|
| 1 | Jumlah total tutor, siswa, dan pengguna aktif | Untuk menilai skala sistem dan relevansi perbaikan performa |
| 2 | Apakah sistem saat ini berjalan di production (`presensi.pkbmpikat.com`)? | Error log menunjukkan masalah deployment — perlu konfirmasi status terkini |
| 3 | Versi PHP di server production | Menentukan urgensi perbaikan deprecation warning |
| 4 | Apakah ada SLA atau jam operasional sistem? | Untuk menilai dampak downtime |
| 5 | Apakah ada fitur yang sedang dalam pengembangan oleh developer lain? | Untuk menghindari konflik saat melakukan perbaikan |
| 6 | Kebijakan kepemilikan siswa — apakah satu siswa bisa diajar lebih dari satu tutor? | Untuk menentukan logika validasi yang tepat |
| 7 | Apakah kepala sekolah berwenang memberikan izin, atau hanya admin? | Untuk menentukan apakah perlu perubahan hak akses |
| 8 | Apakah data lupa lapor seharusnya masuk ke rekap presensi resmi setelah disetujui? | Menentukan scope perbaikan fitur Lupa Lapor |
| 9 | Nama resmi dan singkatan PKBM Pikat untuk keperluan laporan formal | Untuk akurasi penulisan laporan akademis |
| 10 | Sejarah pengembangan sistem (dikembangkan oleh siapa, kapan mulai) | Untuk kelengkapan latar belakang laporan |

---

## 20. KESIMPULAN

### Ringkasan Temuan

Sistem Presensi PKBM Pikat adalah aplikasi web berbasis **Laravel 12 / PHP 8.2+** yang sudah cukup lengkap secara fungsional. Sistem berhasil mengimplementasikan:

✅ Autentikasi multi-credential (NIK/Email)  
✅ Role-Based Access Control dengan 3 peran (Admin, Tutor, Kepala Sekolah)  
✅ Presensi berbasis foto dengan verifikasi durasi minimal (1 jam)  
✅ Dashboard real-time dengan statistik dan grafik kehadiran  
✅ Laporan yang dapat diekspor (Excel dan PDF)  
✅ Fitur Lupa Lapor untuk koreksi presensi retroaktif  
✅ Manajemen data siswa, kelas, jadwal, dan karyawan  
✅ Presensi terpisah untuk karyawan administrasi  

### Masalah Kritis yang Ditemukan

Namun, analisis menemukan beberapa masalah yang perlu segera ditangani:

⚠️ **Keamanan:** Tidak ada rate limiting login; foto publik tanpa auth  
⚠️ **Teknis:** Deprecation warning PHP 8.5; file error log di source code  
⚠️ **Produksi:** Error log menunjukkan ratusan fatal error di server production  
⚠️ **Fungsional:** Lupa lapor tidak terintegrasi ke presensi resmi  

### Rekomendasi Prioritas untuk PKL

Untuk konteks PKL, perbaikan yang paling relevan dan dapat dibuktikan adalah:

1. **Perbaikan deprecation warning** (1 baris kode, dampak nyata)
2. **Rate limiting login** (keamanan, 1 baris route middleware)  
3. **Validasi kepemilikan siswa** (peningkatan integritas data)
4. **Status approval lupa lapor** (peningkatan fungsional yang terukur)

Keempat perbaikan tersebut memenuhi kriteria PKL: memiliki manfaat nyata, dapat dibuktikan sebelum-sesudah, dan tidak berisiko merusak fitur yang sudah berjalan.

---

*Dokumen ini disusun berdasarkan eksplorasi kode sumber project presensi-pkbmpikat yang tersedia di workspace `c:\laragon\www\presensi-pkbmpikat`. Seluruh temuan berdasarkan bukti kode, konfigurasi, dan log yang tersedia. Klaim yang memerlukan konfirmasi eksternal sudah ditandai secara eksplisit.*

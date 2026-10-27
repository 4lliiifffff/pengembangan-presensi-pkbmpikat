# SOFTWARE REQUIREMENTS SPECIFICATION
## SISTEM INFORMASI PRESENSI DIGITAL DAN MANAJEMEN PEMBELAJARAN BERBASIS WEB DAN MOBILE (PRESENSI PKBM PIKAT)
### Version 1.1.0 approved
### Penyusun: Tim Pengembang Sistem Presensi PKBM Pikat / Mahasiswa Praktik Kerja Lapangan (PKL)
### Institusi: Pusat Kegiatan Belajar Masyarakat (PKBM) Pikat, Kabupaten Tuban, Jawa Timur
### Tanggal Pengesahan: 26 Oktober 2026

---

## Daftar Isi
1. [Pendahuluan](#1-pendahuluan)
   - 1.1 [Tujuan Penulisan Dokumen](#11-tujuan-penulisan-dokumen)
   - 1.2 [Konvensi Dokumen](#12-konvensi-dokumen)
   - 1.3 [Audiens yang Dituju dan Saran Pembacaan](#13-audiens-yang-dituju-dan-saran-pembacaan)
   - 1.4 [Batasan dan Ruang Lingkup Produk](#14-batasan-dan-ruang-lingkup-produk)
   - 1.5 [Definisi, Singkatan, dan Istilah](#15-definisi-singkatan-dan-istilah)
   - 1.6 [Dokumen Rujukan](#16-dokumen-rujukan)
2. [Gambaran Umum Sistem](#2-gambaran-umum-sistem)
   - 2.1 [Perspektif Produk](#21-perspektif-produk)
   - 2.2 [Fungsi Utama Produk](#22-fungsi-utama-produk)
   - 2.3 [Karakteristik dan Kelas Pengguna](#23-karakteristik-dan-kelas-pengguna)
   - 2.4 [Lingkungan Operasional](#24-lingkungan-operasional)
   - 2.5 [Batasan Desain dan Implementasi](#25-batasan-desain-dan-implementasi)
   - 2.6 [Dokumentasi Pengguna](#26-dokumentasi-pengguna)
3. [Spesifikasi Kebutuhan Antarmuka Eksternal](#3-spesifikasi-kebutuhan-antarmuka-eksternal)
   - 3.1 [Antarmuka Pengguna (User Interfaces)](#31-antarmuka-pengguna-user-interfaces)
   - 3.2 [Antarmuka Perangkat Keras (Hardware Interfaces)](#32-antarmuka-perangkat-keras-hardware-interfaces)
   - 3.3 [Antarmuka Perangkat Lunak (Software Interfaces)](#33-antarmuka-perangkat-lunak-software-interfaces)
   - 3.4 [Antarmuka Komunikasi (Communications Interfaces)](#34-antarmuka-komunikasi-communications-interfaces)
4. [Fitur dan Kebutuhan Fungsional Sistem](#4-fitur-dan-kebutuhan-fungsional-sistem)
   - 4.1 [Autentikasi Pengguna, Keamanan Sesi, Quick Login, dan Single Sign-On (SSO) SIMPIKAT](#41-autentikasi-pengguna-keamanan-sesi-quick-login-dan-single-sign-on-sso-simpikat)
   - 4.2 [Presensi Multi-Moda dan Validasi Geofencing Lanjutan bagi Tutor](#42-presensi-multi-moda-dan-validasi-geofencing-lanjutan-bagi-tutor)
   - 4.3 [Presensi Mandiri Murid (Peserta Didik), Smart Time-Gating, dan Evaluasi Keterlambatan](#43-presensi-mandiri-murid-peserta-didik-smart-time-gating-dan-evaluasi-keterlambatan)
   - 4.4 [Presensi Karyawan, Pimpinan Lembaga, dan Mahasiswa Magang](#44-presensi-karyawan-pimpinan-lembaga-dan-mahasiswa-magang)
   - 4.5 [Pengelolaan Penjadwalan Sesi Belajar Mengajar, Pola Rutin, Desentralisasi Tutor, dan Reschedule Terstruktur](#45-pengelolaan-penjadwalan-sesi-belajar-mengajar-pola-rutin-desentralisasi-tutor-dan-reschedule-terstruktur)
   - 4.6 [Pengajuan dan Verifikasi Lupa Lapor Kehadiran Retroaktif](#46-pengajuan-dan-verifikasi-lupa-lapor-kehadiran-retroaktif)
   - 4.7 [Pengajuan dan Persetujuan Dispensasi Izin serta Sakit Mandiri Berlampiran Dokumen Resmi](#47-pengajuan-dan-persetujuan-dispensasi-izin-serta-sakit-mandiri-berlampiran-dokumen-resmi)
   - 4.8 [Pengelolaan Kategori Tutorial, Tarif Surat Keputusan (SK), dan Otomasi Penggajian (Payroll) dengan Immutability Snapshot](#48-pengelolaan-kategori-tutorial-tarif-surat-keputusan-sk-dan-otomasi-penggajian-payroll-dengan-immutability-snapshot)
   - 4.9 [Pengelolaan Data Induk Akademik, Jenjang Paket Kesetaraan, Rombel Kelas, Siklus Hidup Siswa, dan Multi-Titik Lokasi Presensi](#49-pengelolaan-data-induk-akademik-jenjang-paket-kesetaraan-rombel-kelas-siklus-hidup-siswa-dan-multi-titik-lokasi-presensi)
   - 4.10 [Dasbor Analitik Eksekutif, Pemantauan Presensi Harian Real-Time, dan Leaderboard Kedisiplinan](#410-dasbor-analitik-eksekutif-pemantauan-presensi-harian-real-time-dan-leaderboard-kedisiplinan)
   - 4.11 [Layanan Notifikasi Web Push Real-Time Standar VAPID dan Pengingat Otomatis Presensi/Sesi](#411-layanan-notifikasi-web-push-real-time-standar-vapid-dan-pengingat-otomatis-presensisesi)
   - 4.12 [Pelaporan Presensi Terpadu, Ekspor Rekapitulasi Excel 14 Kolom, Cetak PDF Resmi, dan Jejak Audit (Audit Trail)](#412-pelaporan-presensi-terpadu-ekspor-rekapitulasi-excel-14-kolom-cetak-pdf-resmi-dan-jejak-audit-audit-trail)
5. [Kebutuhan Non-Fungsional Lainnya](#5-kebutuhan-non-fungsional-lainnya)
   - 5.1 [Kebutuhan Kinerja (Performance Requirements)](#51-kebutuhan-kinerja-performance-requirements)
   - 5.2 [Kebutuhan Keselamatan dan Toleransi Kesalahan (Safety Requirements)](#52-kebutuhan-keselamatan-dan-toleransi-kesalahan-safety-requirements)
   - 5.3 [Kebutuhan Keamanan dan Privasi Data (Security Requirements)](#53-kebutuhan-keamanan-dan-privasi-data-security-requirements)
   - 5.4 [Atribut Kualitas Perangkat Lunak (Software Quality Attributes)](#54-atribut-kualitas-perangkat-lunak-software-quality-attributes)
   - 5.5 [Aturan Bisnis (Business Rules)](#55-aturan-bisnis-business-rules)
6. [Kebutuhan Tambahan dan Legalitas](#6-kebutuhan-tambahan-dan-legalitas)
   - 6.1 [Persyaratan Basis Data (Database Requirements)](#61-persyaratan-basis-data-database-requirements)
   - 6.2 [Persyaratan Hukum dan Legalitas (Legal Requirements)](#62-persyaratan-hukum-dan-legalitas-legal-requirements)
   - 6.3 [Integritas Data dan Jejak Audit (Data Integrity)](#63-integritas-data-dan-jejak-audit-data-integrity)
Lampiran A: Glosarium Istilah
Lampiran B: Model Analisis Sistem
Lampiran C: Daftar Keputusan dan Resolusi Teknis (To Be Determined List / TBD)

---

## Riwayat Revisi Dokumen (Revision History)

| Nama Penyusun | Tanggal | Alasan Perubahan (Reason for Changes) | Versi |
|---|---|---|---|
| Tim Pengembang Sistem Presensi PKBM Pikat | 06/09/2026 | Inisiasi awal Software Requirements Specification untuk modul presensi foto dan pencatatan kehadiran tutor. | 1.0.0 |
| Tim Pengembang Sistem Presensi PKBM Pikat | 26/10/2026 | Menyesuaikan struktur dan isi Software Requirements Specification (SRS) dengan standar IEEE 830, mengadopsi format formal sesuai Contoh SRS dan materi kuliah, menyematkan matriks 5 peran pengguna, User Stories & Acceptance Criteria pada setiap fitur fungsional (REQ-001 s/d REQ-054), atribut kualitas non-fungsional lengkap (Availability, Reliability, Ergonomy, Portability, Response Time, Safety, Security), aturan bisnis SK Honorarium, serta model analisis sistem (BPMN, ERD, Use Case, State Transition). | 1.1.0 |

---

## 1. Pendahuluan

### 1.1 Tujuan Penulisan Dokumen
Dokumen Software Requirements Specification (SRS) ini disusun untuk memberikan gambaran yang jelas, terstruktur, komprehensif, dan mengikat mengenai spesifikasi kebutuhan perangkat lunak Sistem Informasi Presensi Digital dan Manajemen Pembelajaran PKBM Pikat. Dokumen ini menjadi acuan teknis dan legal utama bagi tim pengembang dalam melaksanakan siklus rekayasa perangkat lunak—meliputi tahap analisis kebutuhan, perancangan arsitektur, implementasi kode sumber, penjaminan mutu melalui pengujian otomatis, hingga pemeliharaan sistem. Dengan adanya SRS ini, seluruh pemangku kepentingan memiliki pemahaman yang selaras mengenai perilaku dan batasan sistem, meminimalisasi risiko miskomunikasi, serta memastikan bahwa sistem yang dibangun benar-benar menyelesaikan permasalahan operasional nyata di PKBM Pikat, khususnya dalam hal transparansi kehadiran, akuntabilitas honorarium pendidik, dan kedisiplinan belajar mengajar.

### 1.2 Konvensi Dokumen
Dalam penyusunan dokumen SRS ini digunakan sejumlah konvensi tipografi dan struktur penulisan formal: Huruf tebal (bold) digunakan untuk judul bab, sub-bab, nama peran pengguna, dan istilah kunci yang memerlukan penekanan khusus. Huruf miring (italic) digunakan untuk istilah teknis asing, nama fungsi, nama kelas, metode algoritma, atau protokol jaringan, seperti Single Sign-On, Haversine, Geofencing, SoftDeletes, dan Laravel. HURUF KAPITAL digunakan untuk akronim resmi atau singkatan baku seperti SRS, PKBM, SIMPIKAT, KBM, RBAC, API, NIK, dan VAPID. Setiap butir kebutuhan fungsional diidentifikasi secara unik menggunakan kode alfanumerik terindeks berformat REQ-XXX (mulai dari REQ-001 hingga REQ-054) guna mempermudah keterlacakan (traceability) antara kebutuhan bisnis, baris kode program, dan skenario pengujian otomatis. Bahasa utama dokumen adalah Bahasa Indonesia formal baku yang deskriptif dan lugas.

### 1.3 Audiens yang Dituju dan Saran Pembacaan
Dokumen ini ditujukan bagi berbagai pihak berkepentingan, antara lain: (1) Tim Pengembang Perangkat Lunak (Software Engineers) sebagai panduan implementasi arsitektur kode, struktur basis data, dan logika bisnis; (2) Tim Penjamin Mutu (QA Engineers) sebagai tolok ukur penyusunan skenario pengujian unit dan fitur otomatis berbasis Acceptance Criteria; (3) Kepala Sekolah dan Pimpinan Lembaga untuk memastikan kesesuaian sistem dengan tata tertib kerja dan regulasi Surat Keputusan honorarium; (4) Tutor dan Staf Akademik untuk memahami hak akses, alur presensi multi-moda, penjadwalan, dan pengajuan dispensasi; serta (5) Instansi Pembina Pendidikan (Dinas Pendidikan) sebagai bahan audit akuntabilitas operasional dan tata kelola digital lembaga non-formal. Pembaca disarankan membaca Bab 2 terlebih dahulu untuk memahami konteks umum arsitektur, kemudian mendalami Bab 3 dan Bab 4 untuk rincian fungsional, serta Bab 5 untuk parameter kualitas dan aturan bisnis.

### 1.4 Batasan dan Ruang Lingkup Produk
Sistem Informasi Presensi Digital PKBM Pikat adalah ekosistem perangkat lunak client-server berbasis web dan mobile yang dikembangkan khusus untuk melayani operasional Pusat Kegiatan Belajar Masyarakat (PKBM) Pikat di Kabupaten Tuban, Jawa Timur. Sistem ini mendigitalisasi proses presensi kamera dan koordinat geografis bagi seluruh civitas akademika, yang terdiri dari 5 peran utama: Administrator, Kepala Sekolah, Tutor, Murid kesetaraan (Paket A, B, C) serta vokasi, dan Mahasiswa Magang. Ruang lingkup sistem mencakup presensi mengajar 3 moda (Tatap Muka di Sekolah, Kunjungan ke Rumah Siswa, dan Jarak Jauh/Daring), presensi mandiri murid dengan Smart Time-Gating H-30 menit, presensi kerja pimpinan dan magang, penjadwalan KBM terdesentralisasi (master jadwal rutin mingguan dan alur reschedule terstruktur), alur verifikasi lupa lapor retroaktif dan izin berlampiran dokumen oleh Kepala Sekolah, otomasi penggajian tutor berbasis 6 skema tarif SK Kepala PKBM dengan kekekalan data historis (immutability snapshot), dasbor analitik eksekutif, Web Push Notification real-time, integrasi SSO SIMPIKAT, serta ekspor rekapitulasi Excel 14 kolom dan cetak PDF resmi.

### 1.5 Definisi, Singkatan, dan Istilah
- **SRS:** Software Requirements Specification, dokumen spesifikasi kebutuhan perangkat lunak formal yang mendeskripsikan kapabilitas fungsional dan non-fungsional sistem.
- **PKBM Pikat:** Pusat Kegiatan Belajar Masyarakat Pikat, satuan pendidikan non-formal di Kabupaten Tuban, Jawa Timur yang menyelenggarakan program kesetaraan (Paket A, B, C) dan pemberdayaan vokasi.
- **SIMPIKAT:** Sistem Informasi Manajemen PKBM Pikat, portal akademik induk terintegrasi yang menjadi sumber master data dan otentikasi sentral.
- **SSO:** Single Sign-On, mekanisme otentikasi terpadu yang memungkinkan satu akun digunakan untuk mengakses berbagai subsistem di lingkungan PKBM Pikat.
- **RBAC:** Role-Based Access Control, mekanisme kontrol keamanan yang membatasi hak akses pengguna berdasarkan peran operasionalnya.
- **Haversine:** Persamaan trigonometri lingkaran besar untuk menghitung jarak lengkung bola antara dua koordinat geografis (latitude, longitude) di permukaan bumi.
- **Geofencing:** Pembatasan perimeter virtual berbasis koordinat pusat dan radius toleransi (meter) untuk memvalidasi lokasi kehadiran pengguna.
- **KBM:** Kegiatan Belajar Mengajar, aktivitas bimbingan pembelajaran antara tutor dan murid dalam berbagai moda layanan.
- **Surat Keputusan (SK):** Surat Keputusan Kepala PKBM Pikat Nomor 014/SK/PKBM-PIKAT/IX/2026 tentang Penetapan Skema Honorarium Tutor dan Standar Waktu KBM.
- **Immutability Snapshot:** Prinsip kekekalan data historis di mana nominal tarif honorarium dan durasi mengajar dikunci permanen pada saat transaksi presensi selesai.
- **Smart Time-Gating:** Mekanisme penguncian dan pembukaan jendela presensi secara otomatis berdasarkan jadwal KBM yang ditentukan.
- **VAPID:** Voluntary Application Server Identification (RFC 8292), standar kriptografi pengiriman notifikasi Web Push aman ke peramban pengguna.

### 1.6 Dokumen Rujukan
1. IEEE Std 830-1998: IEEE Recommended Practice for Software Requirements Specifications.
2. ISO/IEC/IEEE 29148:2018: Systems and software engineering — Life cycle processes — Requirements engineering.
3. Undang-Undang Republik Indonesia Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional (Ketentuan Pendidikan Non-Formal).
4. Surat Keputusan Kepala PKBM Pikat Nomor 014/SK/PKBM-PIKAT/IX/2026 tentang Penetapan Skema Honorarium Tutor dan Standar Waktu Kegiatan Belajar Mengajar.
5. Materi Kuliah: Software Requirement Specification (SRS), Nurfitria Khoirunnisa.
6. Template dan Dokumen Acuan: Contoh SRS.docx (VeriCult, 2026).
7. Roadmap dan Laporan Analisis Pembaruan Sistem Presensi Digital PKBM Pikat versi 2026.

---

## 2. Gambaran Umum Sistem

### 2.1 Perspektif Produk
Sistem Informasi Presensi Digital PKBM Pikat merupakan platform perangkat lunak mandiri yang terintegrasi dalam ekosistem teknologi informasi PKBM Pikat. Sistem ini menggantikan proses pencatatan presensi manual berbasis kertas dan menyempurnakan sistem terdahulu (legacy system) yang memiliki berbagai kelemahan arsitektur, seperti perhitungan gaji statis per jam pada tabel siswa yang tidak sesuai regulasi SK, ketiadaan modul presensi mandiri siswa, kerentanan terhadap manipulasi GPS palsu, dan ketergantungan pada jaringan distribusi konten (CDN) eksternal. Sistem ini terhubung langsung dengan portal induk SIMPIKAT melalui mekanisme Single Sign-On (SSO) dan Just-In-Time Provisioning. Arsitektur backend dibangun menggunakan Framework Laravel 13 berbasis PHP 8.5 dengan pola Model-View-Controller yang diperkuat oleh Dedicated Service Layer (PayrollService, GeofencingService, JadwalRutinService, WebPushService). Pada sisi klien, sistem dapat diakses secara responsif melalui peramban web modern (PWA) maupun aplikasi mobile Android siap pasang berbasis Capacitor.

### 2.2 Fungsi Utama Produk
Fungsi utama produk terstruktur dalam tujuh pilar operasional: (1) Autentikasi dan Manajemen Akses Pengguna: Mendukung login email/NIK, proteksi brute force rate limiting, fasilitas Quick Login pengujian, pembaruan profil mandiri, dan integrasi SSO SIMPIKAT. (2) Presensi Multi-Moda & Geofencing Lanjutan: Perekaman kehadiran tutor dalam moda Sekolah (geofence ketat), Kunjungan Rumah (catat koordinat murid), dan Pembelajaran Daring (unggah link rapat & tangkapan layar) yang dilengkapi deteksi anti-mock GPS dan kontrol kamera lengkap. (3) Presensi Mandiri Murid: Single check-in harian kedatangan siswa berakurasi tinggi dengan Smart Time-Gating penguncian kamera sebelum H-30 menit dan penandaan otomatis status keterlambatan (>30 menit KBM). (4) Presensi Karyawan, Pimpinan, dan Magang: Presensi kerja Clock-In/Clock-Out harian, fasilitas dispensasi bebas radius bagi pimpinan yang dinas luar, dan presensi mahasiswa magang radius 100 meter. (5) Penjadwalan KBM Terpadu: Master jadwal rutin mingguan, generator sesi 4 pekan ke depan, otonomi penjadwalan mandiri oleh tutor, alur reschedule terstruktur ber-alasan, dan kalender agenda. (6) Alur Persetujuan & Dispensasi: Pengajuan lupa lapor retroaktif dan izin/sakit berlampiran dokumen dengan kewenangan approval mutlak Kepala Sekolah. (7) Otomasi Payroll & Pelaporan Eksekutif: Perhitungan honorarium otomatis berbasis SK 6 kategori layanan, deteksi multi-rombel, immutability snapshot nilai honor, dasbor analitik tren kehadiran, notifikasi Web Push VAPID, ekspor Excel 14 kolom, dan cetak PDF kop resmi.

### 2.3 Karakteristik dan Kelas Pengguna
Sistem presensi PKBM Pikat melayani 5 kelas pengguna dengan pemisahan tugas, hak akses, dan karakteristik keahlian operasional sebagai berikut:

| Kategori Pengguna | Tugas Utama | Hak Akses (Berdasarkan Fungsi) | Kemampuan / Karakteristik |
|---|---|---|---|
| **Super Admin / Administrator** | Mengelola seluruh data master organisasi, jenjang paket pendidikan, rombel kelas, data induk siswa, akun pengguna, multi-titik lokasi presensi, master tarif honorarium SK, jadwal kerja, serta pemantauan log audit sistem. | Memiliki hak akses menyeluruh (Full Access / CRUD) ke seluruh modul konfigurasi sistem, manajemen pengguna, bypass radius dinas luar, pengaturan ulang kata sandi instan, serta ekspor-impor data massal. | Memiliki keahlian teknis tingkat lanjut dalam administrasi basis data, pengelolaan web server, pemahaman integritas data relasional, dan tata kelola keamanan sistem informasi. |
| **Kepala Sekolah / Pimpinan Lembaga** | Memegang otoritas pengawasan eksekutif, mengevaluasi kedisiplinan civitas akademika, meninjau dan menetapkan keputusan (Setujui/Tolak) permohonan lupa lapor retroaktif dan permohonan izin/sakit, serta memverifikasi laporan penggajian bulanan. | Akses dasbor analitik eksekutif, panel persetujuan lupa lapor, panel persetujuan izin, modul presensi pimpinan dengan dispensasi bebas radius dinas luar, serta unduh rekapitulasi penggajian dan presensi resmi. | Memiliki mobilitas tinggi, menggunakan perangkat telepon pintar, tablet, atau laptop, serta memiliki wewenang hukum tertinggi dalam pengesahan operasional lembaga. |
| **Tutor / Tenaga Pendidik** | Menyelenggarakan kegiatan belajar mengajar sesuai jadwal, melakukan presensi masuk dan pulang multi-moda (Sekolah, Kunjungan Rumah, Jarak Jauh), mengatur jadwal sesi belajar mandiri dengan murid, melakukan reschedule jika terkendala, serta mengajukan permohonan lupa lapor atau izin secara tertib. | Akses dasbor tutor, modul presensi kamera multi-moda, modul penjadwalan sesi belajar (termasuk jadwal rutin dan kalender agenda), pengajuan lupa lapor retroaktif, pengajuan izin berlampiran dokumen, dan rincian slip honorarium individual. | Memiliki kemampuan teknologi dasar hingga menengah dalam pengoperasian aplikasi pada ponsel pintar di lapangan, memahami etika pengajaran, dan tertib administrasi bimbingan. |
| **Murid / Peserta Didik** | Melakukan presensi mandiri satu kali saat kedatangan di sekolah untuk mengikuti KBM, memantau jadwal bimbingan belajar harian, dan melihat rekapitulasi catatan kehadiran pribadi. | Akses portal murid responsif, modul presensi swafoto kedatangan sekolah dalam jendela waktu H-30 menit, kalender jadwal bimbingan, riwayat kehadiran, serta pembaruan profil kata sandi mandiri. | Memiliki kemampuan teknologi bervariasi dengan rentang usia beragam (program kesetaraan Paket A, B, C dan vokasi), memerlukan antarmuka visual yang sangat ringkas, jelas, dan intuitif. |
| **Mahasiswa Magang / Karyawan** | Melaksanakan tugas kedinasan harian atau program Praktik Kerja Lapangan, mencatat kehadiran kerja masuk (Clock-In) dan pulang (Clock-Out) harian di lingkungan PKBM Pikat, serta mengunduh rekapitulasi kehadiran magang berkala. | Akses portal presensi kerja harian berbasis foto dan validasi geofence sekolah 100 meter, dasbor statistik jam kerja, riwayat presensi harian, dan fasilitas ekspor rekapitulasi presensi PDF bertanda tangan resmi. | Memiliki pemahaman pengoperasian gawai ponsel pintar yang baik, memahami tata tertib jam kerja institusi, dan berkepentingan terhadap validitas bukti presensi untuk institusi asalnya. |

### 2.4 Lingkungan Operasional
Sistem dirancang untuk berjalan pada arsitektur client-server terdistribusi dengan rincian lingkungan operasional sebagai berikut:
- Lingkungan Klien (Client-Side): Dapat diakses menggunakan perangkat komputer meja, laptop, tablet, maupun telepon pintar (smartphone) berbasis Windows, macOS, Linux, Android (versi 8.0 Oreo hingga Android 14+), dan iOS. Peramban web yang didukung meliputi Google Chrome, Mozilla Firefox, Microsoft Edge, dan Apple Safari versi mutakhir yang telah mendukung HTML5, CSS3, JavaScript ES6, Geolocation API, MediaDevices API, dan Service Worker. Perangkat klien wajib memiliki kamera yang berfungsi dan sensor GPS berakurasi tinggi.
- Lingkungan Server (Server-Side): Berjalan di atas Virtual Private Server (VPS) atau Dedicated Server bersistem operasi Linux Ubuntu Server 22.04 LTS / 24.04 LTS. Web server menggunakan Nginx atau Apache yang terkonfigurasi dengan sertifikat SSL/TLS valid. Runtime backend menggunakan PHP versi 8.2 hingga versi 8.5 dengan ekstensi pdo_mysql, mbstring, openssl, gd, zip, intl, dan bcmath. Sistem Manajemen Basis Data (DBMS) menggunakan MySQL 8.0 atau MariaDB 10.6 dengan storage engine InnoDB.
- Jaringan: Server memerlukan koneksi internet stabil dengan IP publik statis. Klien memerlukan koneksi internet seluler minimal 4G LTE atau Wi-Fi untuk sinkronisasi data presensi dan pengunggahan berkas foto.

### 2.5 Batasan Desain dan Implementasi
Dalam pengembangan sistem, berlaku batasan desain dan implementasi sebagai berikut:
1. Kerangka Kerja Backend: Wajib menggunakan Framework Laravel 13 berbasis PHP 8.5 dengan arsitektur Clean Code, mematuhi standar PSR-12 melalui validasi berkala Laravel Pint, dan memisahkan logika bisnis kompleks ke dalam Dedicated Service Layer guna mencegah terjadinya Fat Controller.
2. Integritas Basis Data: Wajib menggunakan basis data relasional MySQL/MariaDB yang ternormalisasi hingga 3NF. Menghilangkan redundansi data, menerapkan Foreign Key Constraints dengan strategi penghapusan terukur (ON DELETE SET NULL / SoftDeletes), serta mengunci nominal honorarium masa lalu agar bersifat kekal (immutable).
3. Kemandirian Aset Frontend: Sistem dilarang bergantung pada CDN publik pihak ketiga. Seluruh dependensi UI (peta Leaflet, ikon, script kamera, dan CSS tema gelap) wajib dibundel dan dilayani secara lokal melalui repositori internal aplikasi.
4. Alur Izin Native Android: Pada aplikasi mobile Capacitor, inisialisasi dan permintaan izin akses kamera dan lokasi ke sistem operasi Android wajib dieksekusi secara sekuensial dan terisolasi untuk menghindari crash pada WebView native.
5. Regulasi dan Legalitas: Struktur data akademik dan kurikulum wajib mematuhi standar pendidikan non-formal Kementerian Pendidikan Dasar dan Menengah RI, serta mengacu pada SK Kepala PKBM Nomor 014/SK/PKBM-PIKAT/IX/2026 mengenai skema penggajian.

### 2.6 Dokumentasi Pengguna
Sistem dilengkapi dengan dokumentasi pengguna terpadu untuk memastikan kemudahan adopsi operasional:
1. Manual Pengguna Lengkap (User Manual): Dokumen digital berformat PDF interaktif yang memuat panduan langkah demi langkah pengoperasian sistem untuk masing-masing peran pengguna (Admin, Kepala Sekolah, Tutor, Siswa, Magang).
2. Buku Panduan Cepat (Quick Guide Card): Kartu panduan ringkas dengan ilustrasi visual sederhana yang menjelaskan tata cara swafoto presensi kedatangan siswa dan presensi magang.
3. Halaman Bantuan dan Tooltip Kontekstual: Antarmuka sistem menyematkan help tooltip, dialog panduan perizinan sensor perangkat, dan banner informatif interaktif untuk memandu pengguna secara mandiri ketika mengalami kendala jarak geofence atau sesi menggantung.

---

## 3. Spesifikasi Kebutuhan Antarmuka Eksternal

### 3.1 Antarmuka Pengguna (User Interfaces)
Antarmuka pengguna Sistem Presensi PKBM Pikat dirancang dengan pendekatan Mobile-First Responsive Design yang mengutamakan ergonomi pengoperasian satu tangan pada layar ponsel pintar tanpa mengurangi kenyamanan pada komputer meja. Desain visual mengadopsi tema pendidikan modern dengan palet warna hijau zamrud (emerald) sebagai warna identitas institusi dan warna netral berkontras tinggi sesuai pedoman aksesibilitas WCAG AAA. Sistem menyediakan peralihan instan antara Tema Terang (Light Mode) dan Tema Gelap (Dark Mode). Pada tema gelap, ubin peta digital OpenStreetMap disesuaikan secara visual menggunakan filter CSS mandiri tanpa watermark komersial. Area presensi menampilkan viewport kamera interaktif yang dilengkapi kendali mirror flip horizontal, pengalih kamera depan/belakang, garis bantu komposisi 3x3, radar jarak dinamis, tombol petunjuk arah ke Google Maps, serta peta interaktif Leaflet. Navigasi pada perangkat bergerak menggunakan Bottom Navigation Bar yang mudah dijangkau ibu jari, sedangkan pada desktop menggunakan bilah sisi (collapsible sidebar).

### 3.2 Antarmuka Perangkat Keras (Hardware Interfaces)
Sistem berinteraksi langsung dengan komponen perangkat keras gawai pengguna melalui Web API standar peramban modern:
1. Modul Kamera: Diakses melalui MediaDevices API (navigator.mediaDevices.getUserMedia) untuk menangkap streaming video resolusi minimal 720p. Citra presensi dikonversi dan dikompresi langsung pada kanvas HTML5 sisi klien menjadi format JPEG optimal sebelum dikirim ke server.
2. Modul Penentu Posisi Global (GPS): Diakses melalui Geolocation API dengan parameter enableHighAccuracy: true, membaca koordinat lintang dan bujur serta tingkat ketelitian sinyal dalam satuan meter secara kontinu.
3. Motor Getar Perangkat: Memanfaatkan Vibration API untuk memberikan getaran haptic singkat saat pengguna memasuki zona radius aman atau ketika tombol presensi berhasil diproses.

### 3.3 Antarmuka Perangkat Lunak (Software Interfaces)
Sistem berinteraksi dengan komponen perangkat lunak dan pustaka pendukung berikut:
1. DBMS MySQL / MariaDB: Berkomunikasi melalui ekstensi PDO PHP dan Laravel Eloquent ORM yang mendukung manajemen transaksi basis data dan prepared statements.
2. Pustaka DomPDF: Menghasilkan dokumen cetak resmi berformat PDF untuk slip gaji tutor individual (potret) dan rekapitulasi presensi (lanskap) berkop resmi lembaga.
3. Pustaka Maatwebsite Excel: Memproses impor massal data siswa dan jadwal dari berkas .xlsx, serta mengekspor rekapitulasi presensi ke dalam berkas Excel 14 kolom terstruktur.
4. Layanan Web Push Gateway: Terhubung dengan protokol Web Push standar VAPID (RFC 8292) melalui pustaka minishlink/web-push untuk pengiriman notifikasi seketika.
5. Portal SIMPIKAT: Terhubung dengan basis data sekunder SIMPIKAT untuk verifikasi kredensial terpusat pada alur Single Sign-On.

### 3.4 Antarmuka Komunikasi (Communications Interfaces)
Seluruh pertukaran data antara klien dan server wajib menggunakan protokol aman Hypertext Transfer Protocol Secure (HTTPS) melalui porta standar 443 dengan enkripsi Transport Layer Security (TLS) versi 1.2 atau 1.3 guna melindungi kerahasiaan data kredensial, koordinat lokasi, dan foto biometrik. Format pertukaran data asinkronus menggunakan JSON standar dengan struktur payload semantik yang memuat status keberhasilan, pesan deskriptif, dan payload data. Komunikasi notifikasi web push menggunakan enkripsi muatan RFC 8291 berbasis kurva eliptik P-256.

---

## 4. Fitur dan Kebutuhan Fungsional Sistem

Bagian ini menguraikan 12 fitur utama Sistem Presensi PKBM Pikat. Berdasarkan pedoman rekayasa kebutuhan perangkat lunak IEEE 830 dan materi perkuliahan, setiap fitur didefinisikan secara komprehensif melalui: Deskripsi dan Prioritas, User Story, Urutan Stimulus/Respons, Kriteria Keberterimaan (Acceptance Criteria), serta Pernyataan Persyaratan Fungsional terindeks unik (REQ-001 s/d REQ-054).

### 4.1 Autentikasi Pengguna, Keamanan Sesi, Quick Login, dan Single Sign-On (SSO) SIMPIKAT

**Deskripsi dan Prioritas:**
Fitur ini mengelola seluruh gerbang masuk pengguna ke dalam sistem, menyediakan fleksibilitas masukan identitas ganda menggunakan email atau Nomor Induk Kependudukan (NIK), menerapkan pertahanan terhadap serangan brute force, mengarahkan pengguna secara otomatis ke dasbor utama sesuai perannya, menyediakan fasilitas Quick Login untuk pengujian, serta menghubungkan autentikasi dengan portal induk SIMPIKAT. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai pengguna sistem (Admin, Kepala Sekolah, Tutor, Siswa, atau Magang), saya ingin masuk ke sistem secara aman dan fleksibel menggunakan email atau NIK maupun melalui Single Sign-On SIMPIKAT, sehingga saya dapat langsung mengakses dasbor operasional sesuai hak akses peran saya tanpa hambatan teknis.

**Urutan Stimulus dan Respons:**
1. Pengguna membuka tautan aplikasi melalui peramban web atau aplikasi mobile Android Capacitor.
2. Sistem menyajikan formulir login responsif dengan opsi masukan fleksibel (email atau NIK) dan fitur tampilkan kata sandi.
3. Pengguna memasukkan kredensial login dan menekan tombol 'Masuk'.
4. Sistem memeriksa batas laju permintaan (Rate Limiting) maksimal 5 kali per menit per alamat IP dan akun.
5. Jika kredensial valid, sistem meregenerasi identitas sesi pengguna, mereset counter percobaan gagal, dan mengarahkan pengguna ke dasbor perannya.
6. Jika pengguna memilih 'Login via SIMPIKAT', sistem mengalihkan ke penyedia SSO SIMPIKAT, memverifikasi token kriptografi, melakukan provisi akun otomatis (Just-In-Time Provisioning) jika pengguna belum terdaftar, dan menginisiasi sesi lokal.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Pengguna dapat masuk menggunakan email terdaftar maupun NIK dengan kata sandi yang sesuai.
2. Sistem memblokir sementara percobaan login ke-6 dalam kurun waktu 1 menit dari IP/kredensial yang sama dengan pesan peringatan 'Terlalu banyak percobaan login'.
3. Sesi pengguna diarahkan dengan tepat ke rute masing-masing peran (admin/dashboard, kepsek/dashboard, tutor/dashboard, murid/dashboard, magang/dashboard).
4. Token otentikasi SSO SIMPIKAT yang sah berhasil membuat akun lokal otomatis dan membuka akses dasbor tanpa meminta registrasi manual ulang.

**Persyaratan Fungsional:**
- **REQ-001:** Sistem harus menyediakan formulir masuk yang menerima identifikasi pengguna melalui alamat email terdaftar atau Nomor Induk Kependudukan (NIK).
- **REQ-002:** Sistem harus membatasi percobaan masuk maksimal lima kali per menit untuk setiap alamat IP dan kombinasi akun guna mencegah serangan brute force.
- **REQ-003:** Sistem harus mengarahkan pengguna ke antarmuka dasbor yang sesuai dengan perannya, yaitu admin, kepala sekolah, tutor, siswa, atau magang setelah autentikasi berhasil diverifikasi.
- **REQ-004:** Sistem harus menyediakan layanan Single Sign-On yang terintegrasi dengan basis data SIMPIKAT dan mampu melakukan pembuatan data profil pengguna secara otomatis saat login pertama kali.
- **REQ-005:** Sistem harus menyediakan fasilitas Quick Login untuk lima persona akun uji coba pada lingkungan pengembangan perangkat lunak.
- **REQ-006:** Sistem harus menyediakan fasilitas pembaruan data profil dan pergantian kata sandi mandiri bagi pengguna yang sedang aktif.


### 4.2 Presensi Multi-Moda dan Validasi Geofencing Lanjutan bagi Tutor

**Deskripsi dan Prioritas:**
Fitur ini memfasilitasi pencatatan kehadiran mengajar tutor dalam tiga moda bimbingan belajar yang berbeda (Sekolah, Kunjungan Rumah, dan Pembelajaran Jarak Jauh) yang dilengkapi verifikasi swafoto kamera, validasi jarak matematis Haversine terhadap multi-titik lokasi presensi, pencegahan manipulasi lokasi palsu (anti-mock location), kontrol kamera peramban tingkat lanjut, dan pembukaan waktu absen pulang cerdas berbasis capaian durasi bimbingan. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai tutor bimbingan belajar, saya ingin mencatat jam mulai dan jam selesai mengajar sesuai moda bimbingan (di sekolah, di rumah murid, atau daring) dengan verifikasi foto dan validasi lokasi yang akurat, sehingga pelaksanaan KBM terdokumentasi secara transparan dan honor mengajar saya dapat dihitung dengan sah.

**Urutan Stimulus dan Respons:**
1. Tutor membuka modul Presensi Mengajar pada aplikasi.
2. Peramban meminta izin akses sensor kamera dan geolokasi perangkat secara sekuensial.
3. Tutor memilih jadwal sesi bimbingan belajar yang akan diajar dan menentukan moda pembelajaran.
4. Pada moda Sekolah, tutor memilih titik lokasi presensi (kampus pusat, cabang, atau mitra). Peta Leaflet memvisualisasikan posisi tutor, perimeter radius batas, dan radar proximity card.
5. Tutor mengambil swafoto bersama murid menggunakan kendali kamera (mirror flip, switch kamera, grid 3x3) dan menekan tombol 'Mulai Bimbingan'.
6. Sistem memvalidasi koordinat terhadap radius toleransi (Haversine) dan memeriksa integritas sinyal GPS (akurasi < 200m dan non-mock). Jika sah, status sesi menjadi 'berlangsung' dan hitung mundur pulang aktif.
7. Saat waktu KBM mencapai 10 menit sebelum jam rencana selesai atau durasi efektif telah mencapai 80%, formulir kepulangan terbuka otomatis. Tutor mengambil foto penutupan dan menekan tombol 'Selesai Bimbingan'.
8. Sistem mencatat waktu kepulangan, mengubah status kehadiran menjadi 'hadir', dan membekukan nominal honorarium SK.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Sistem berhasil mendeteksi dan mendukung 3 moda presensi: Tatap Muka di Sekolah, Kunjungan ke Rumah Siswa, dan Jarak Jauh / Daring.
2. Sistem menolak pengiriman presensi moda sekolah jika jarak pengguna melebihi radius toleransi titik lokasi yang dipilih.
3. Sistem menolak pengiriman presensi apabila terdeteksi menggunakan aplikasi mock location atau akurasi GPS lebih besar dari 200 meter.
4. Tombol kepulangan hanya aktif jika memenuhi ambang batas hibrida (10 menit sebelum selesai ATAU 80% durasi rencana sesi).
5. Sesi mengajar masa lalu yang lupa di-clock-out tidak memblokir presensi hari baru dan menyajikan banner pengajuan lupa lapor.

**Persyaratan Fungsional:**
- **REQ-007:** Sistem harus mendukung pencatatan presensi mengajar dalam tiga moda pembelajaran yaitu tatap muka di sekolah, kunjungan ke rumah siswa, dan pembelajaran daring.
- **REQ-008:** Sistem harus memvalidasi jarak posisi pengguna ke titik lokasi presensi yang dipilih menggunakan algoritma Haversine dan menolak pengajuan jika berada di luar batas radius toleransi.
- **REQ-009:** Sistem harus mendeteksi dan menolak presensi yang menggunakan aplikasi pemalsu lokasi atau memiliki akurasi sinyal satelit lebih buruk dari dua ratus meter.
- **REQ-010:** Sistem harus menyediakan kontrol kamera peramban yang meliputi pembalik horizontal, pengalih kamera depan dan belakang, garis bantu komposisi tiga kali tiga, dan lampu kilat.
- **REQ-011:** Sistem harus menerapkan aturan kelayakan absen pulang cerdas di mana formulir kepulangan dibuka saat waktu mencapai sepuluh menit sebelum jadwal selesai atau tutor telah mengajar minimal delapan puluh persen dari durasi rencana bimbingan.
- **REQ-012:** Sistem harus mendeteksi sesi mengajar lampau yang belum diselesaikan tanpa memblokir presensi hari baru dan menyajikan banner pengajuan lupa lapor satu klik.


### 4.3 Presensi Mandiri Murid (Peserta Didik), Smart Time-Gating, dan Evaluasi Keterlambatan

**Deskripsi dan Prioritas:**
Fitur ini menyediakan fasilitas bagi murid program kesetaraan atau vokasi untuk mencatat kehadiran kedatangan harian di sekolah secara mandiri berbasis swafoto dan validasi radius lokasi sekolah, disertai penguncian kamera sebelum waktu belajar dimulai (Smart Time-Gating H-30 menit) dan penandaan otomatis status keterlambatan jika melewati ambang batas toleransi. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai murid bimbingan belajar PKBM Pikat, saya ingin mencatat kedatangan saya di sekolah dengan mengambil swafoto saat tiba di kelas, sehingga catatan kehadiran harian saya terekam resmi oleh pihak sekolah tanpa membebani tutor.

**Urutan Stimulus dan Respons:**
1. Murid masuk ke portal siswa dan memilih menu 'Presensi Mandiri'.
2. Sistem mengevaluasi jadwal KBM murid pada hari berjalan.
3. Jika waktu saat ini lebih awal dari 30 menit sebelum jam mulai KBM (H-30 menit), sistem mengunci akses kamera dan menampilkan live countdown timer hingga jendela presensi dibuka.
4. Ketika jendela presensi terbuka, kamera aktif otomatis. Murid mengambil swafoto di area sekolah dan menekan tombol 'Kirim Kehadiran'.
5. Sistem memvalidasi koordinat GPS murid terhadap radius resmi sekolah (100 meter).
6. Jika waktu presensi melewati 30 menit setelah jam mulai KBM, sistem otomatis menandai status 'terlambat' dan mencatat akumulasi menit keterlambatan.
7. Sistem menyimpan transaksi presensi, memperbarui statistik kehadiran unik harian, dan menampilkan badge status pada dasbor murid.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Murid hanya wajib melakukan 1 kali pencatatan presensi kedatangan per hari (tanpa kewajiban presensi pulang).
2. Kamera terkunci rapat dengan hitung mundur jika waktu saat ini < (Jam Mulai KBM - 30 menit).
3. Presensi yang dilakukan > (Jam Mulai KBM + 30 menit) otomatis berstatus 'terlambat' dengan selisih menit keterlambatan tercatat di basis data.
4. Agregasi statistik kehadiran murid menghitung tanggal unik (DISTINCT date) untuk mencegah penggelembungan hitungan ganda saat tutor dan murid sama-sama absen.

**Persyaratan Fungsional:**
- **REQ-013:** Sistem harus menerapkan mekanisme satu kali pencatatan presensi kedatangan per hari bagi murid tanpa mewajibkan presensi pulang.
- **REQ-014:** Sistem harus mengunci formulir presensi murid sebelum tiga puluh menit dari jadwal kegiatan belajar mengajar dimulai.
- **REQ-015:** Sistem harus secara otomatis menetapkan status terlambat dan mencatat jumlah menit keterlambatan apabila murid melakukan presensi lebih dari tiga puluh menit setelah jam mulai belajar.
- **REQ-016:** Sistem harus menghitung statistik kehadiran murid berdasarkan tanggal unik kehadiran guna mencegah perhitungan ganda saat tutor dan murid sama-sama mencatat kehadiran.
- **REQ-017:** Sistem harus menampilkan jadwal belajar harian, riwayat kehadiran, dan status keterlambatan pada dasbor murid secara ringkas.


### 4.4 Presensi Karyawan, Pimpinan Lembaga, dan Mahasiswa Magang

**Deskripsi dan Prioritas:**
Fitur ini mencatat kehadiran harian masuk (Clock-In) dan pulang (Clock-Out) bagi tenaga kependidikan/karyawan, pimpinan lembaga dengan kebijakan dispensasi bebas radius saat dinas luar, serta mahasiswa magang/PKL dengan radius geofence ketat 100 meter dari titik sekolah. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai karyawan, pimpinan lembaga, atau mahasiswa magang, saya ingin mencatat kehadiran masuk dan pulang kerja setiap hari dengan verifikasi foto dan lokasi, serta mendapatkan kemudahan dispensasi saat bertugas di luar kantor, sehingga akuntabilitas kerja harian saya terbukti sah.

**Urutan Stimulus dan Respons:**
1. Pengguna membuka modul presensi kerja pada portal masing-masing.
2. Pengguna mengambil swafoto masuk kerja dan sistem membaca koordinat posisi pengguna.
3. Untuk admin dan kepala sekolah yang berstatus dinas luar, sistem memberlakukan dispensasi bebas batas radius dengan tetap merekam koordinat riil di database.
4. Untuk mahasiswa magang, sistem memvalidasi keberadaan pengguna dalam radius 100 meter dari titik PKBM Pikat.
5. Saat jam kepulangan tiba, pengguna mengambil swafoto pulang dan mengirimkan presensi akhir.
6. Khusus admin dan kepala sekolah, sistem memberlakukan bypass waktu tunggu minimal kepulangan sehingga dapat melakukan checkout sewaktu-waktu.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Karyawan dan pimpinan dapat melakukan presensi masuk dan pulang kerja harian.
2. Admin dan Kepala Sekolah dapat melakukan presensi dari luar radius tanpa ditolak oleh sistem, dengan koordinat lokasi tersimpan akurat.
3. Mahasiswa magang ditolak oleh sistem apabila berada di luar radius 100 meter dari titik kampus PKBM Pikat.
4. Mahasiswa magang dapat mengunduh dokumen rekapitulasi kehadiran magang berformat PDF bertanda tangan resmi pimpinan.

**Persyaratan Fungsional:**
- **REQ-018:** Sistem harus menyediakan formulir presensi masuk dan pulang kerja harian berbasis foto kamera dan koordinat lokasi untuk karyawan dan pimpinan.
- **REQ-019:** Sistem harus memberikan dispensasi bebas batas radius bagi peran admin dan kepala sekolah saat melaksanakan dinas luar dengan tetap mencatat koordinat lokasi aktual.
- **REQ-020:** Sistem harus membebaskan peran admin dan kepala sekolah dari waktu tunggu minimal kepulangan kerja.
- **REQ-021:** Sistem harus menyediakan portal presensi khusus bagi mahasiswa magang dengan pembatasan radius lokasi seratus meter dari titik sekolah dan fasilitas unduh rekap kehadiran PDF.


### 4.5 Pengelolaan Penjadwalan Sesi Belajar Mengajar, Pola Rutin, Desentralisasi Tutor, dan Reschedule Terstruktur

**Deskripsi dan Prioritas:**
Fitur ini mengelola siklus perencanaan KBM terpadu yang memadukan master pola jadwal rutin mingguan terstruktur, generator otomatis jadwal sesi bimbingan 4 pekan ke depan, otonomi tutor dalam menyusun jadwal sesi mandiri bersama murid, alur penundaan jadwal (reschedule) terstruktur yang mencatat alasan tertulis dan mempertahankan jejak audit, serta peleburan kalender agenda terpadu. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai tutor dan admin akademik, saya ingin membuat jadwal rutin mingguan berulang otomatis, menyepakati jadwal sesi belajar mandiri dengan murid, dan memproses penundaan jadwal belajar jika berhalangan dengan alasan yang tercatat, sehingga jadwal bimbingan selalu mutakhir dan tertib audit.

**Urutan Stimulus dan Respons:**
1. Admin atau tutor membuka menu Penjadwalan Belajar Mengajar.
2. Pengguna dapat membuat pola Jadwal Rutin mingguan dengan memilih hari, jam bimbingan, tutor, siswa, dan kategori layanan tutorial.
3. Sistem secara terjadwal (cron scheduler) membangkitkan jadwal sesi konkret selama 4 pekan ke depan berdasarkan pola rutin aktif.
4. Tutor dapat menambahkan jadwal sesi ad-hoc tunggal secara mandiri langsung dari portal tutor.
5. Bila sesi bimbingan tertunda, tutor menekan tombol 'Reschedule Sesi', memilih tanggal/jam baru, serta memasukkan alasan penundaan.
6. Sistem membatalkan sesi lama berstatus 'dibatalkan' dengan alasan tertulis, menerbitkan entitas sesi pengganti baru, dan mengunci sesi yang telah berstatus 'hadir' dari perubahan.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Master jadwal rutin berhasil menghasilkan sesi bimbingan konkret untuk 4 pekan ke depan tanpa duplikasi.
2. Tutor memiliki kewenangan penuh menyusun jadwal sesi bimbingan mandiri tanpa bergantung pada persetujuan awal admin.
3. Alur reschedule mempertahankan data historis (sesi lama dibatalkan ber-alasan, sesi baru dibuat saling bertaut).
4. Sesi bimbingan yang telah memiliki catatan presensi hadir terkunci dari upaya pengeditan maupun penghapusan.
5. Kalender agenda KBM terpadu memadukan titik sesi bimbingan dan agenda umum lembaga dalam satu tampilan responsif.

**Persyaratan Fungsional:**
- **REQ-022:** Sistem harus menyediakan master data jadwal rutin untuk menyimpan pola kegiatan belajar mengajar berulang setiap pekan.
- **REQ-023:** Sistem harus menyediakan fungsi pembangkit jadwal otomatis yang mampu menerbitkan jadwal sesi belajar untuk empat pekan ke depan secara berkala.
- **REQ-024:** Sistem harus memberikan kewenangan kepada tutor untuk membuat dan mengelola jadwal sesi belajar secara mandiri.
- **REQ-025:** Sistem harus mendukung alur penundaan jadwal terstruktur yang membatalkan sesi lama berlandaskan alasan tertulis, menerbitkan sesi pengganti, dan mengunci sesi yang telah memiliki catatan presensi hadir.
- **REQ-026:** Sistem harus menyediakan tampilan kalender terpadu yang memadukan jadwal sesi bimbingan belajar dan agenda kegiatan umum lembaga.


### 4.6 Pengajuan dan Verifikasi Lupa Lapor Kehadiran Retroaktif

**Deskripsi dan Prioritas:**
Fitur ini menyediakan alur kerja pelaporan kehadiran susulan bagi pendidik yang mengalami kendala teknis (ketiadaan sinyal internet, baterai habis, atau gangguan gawai) saat bimbingan berlangsung, dengan mekanisme pengajuan berlampiran foto bukti dan kewenangan verifikasi mutlak oleh Kepala Sekolah. Prioritas implementasi: Wajib (Sedang).

**User Story:**
> Sebagai tutor bimbingan belajar, saya ingin mengajukan permohonan lupa lapor kehadiran untuk sesi mengajar masa lalu yang terkendala dengan mengunggah foto bukti dan alasan yang jelas, sehingga kehadiran saya tetap diverifikasi oleh Kepala Sekolah dan hak honorarium saya tidak hilang.

**Urutan Stimulus dan Respons:**
1. Tutor membuka formulir pengajuan Lupa Lapor pada aplikasi.
2. Tutor memilih tanggal KBM lampau, memilih siswa yang diajar, mengisi jam mulai dan selesai, mengunggah foto bukti bimbingan, serta menuliskan alasan pengajuan.
3. Pengajuan tersimpan di sistem dengan status 'pending' dan memicu notifikasi Web Push ke akun Kepala Sekolah.
4. Kepala Sekolah membuka panel 'Verifikasi Lupa Lapor', meninjau bukti foto dan alasan, lalu memilih opsi 'Setujui' atau 'Tolak' disertai catatan resmi.
5. Jika disetujui, sistem secara otomatis melakukan upsert catatan kehadiran resmi pada tabel presensi, mengubah status sesi KBM terkait menjadi 'selesai', dan memperhitungkan honorarium pada rekapitulasi penggajian.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Formulir lupa lapor memvalidasi kelengkapan berkas bukti foto dan teks alasan pengajuan.
2. Hanya Kepala Sekolah yang memiliki wewenang memberikan keputusan Setujui atau Tolak terhadap permohonan lupa lapor.
3. Persetujuan lupa lapor langsung menerbitkan catatan presensi sah berstatus 'hadir' dan mengunci jadwal KBM terkait.
4. Penolakan lupa lapor menyimpan catatan alasan penolakan dan tidak menerbitkan hak honorarium.

**Persyaratan Fungsional:**
- **REQ-027:** Sistem harus menyediakan formulir pengajuan lupa lapor untuk tanggal yang telah lewat dengan validasi kelayakan data yang ketat.
- **REQ-028:** Sistem harus menyediakan antarmuka bagi Kepala Sekolah untuk menyetujui atau menolak permohonan lupa lapor secara interaktif disertai catatan resmi.
- **REQ-029:** Sistem harus secara otomatis membuat atau memperbarui catatan kehadiran resmi pada tabel presensi dan memperbarui status jadwal sesi menjadi selesai saat pengajuan disetujui.


### 4.7 Pengajuan dan Persetujuan Dispensasi Izin serta Sakit Mandiri Berlampiran Dokumen Resmi

**Deskripsi dan Prioritas:**
Fitur ini memfasilitasi pengajuan dispensasi ketidakhadiran bimbingan belajar bagi pendidik secara digital dengan kewajiban melampirkan berkas bukti pendukung (surat dokter atau surat izin resmi) dan persetujuan Kepala Sekolah. Prioritas implementasi: Wajib (Sedang).

**User Story:**
> Sebagai tutor bimbingan belajar, saya ingin mengajukan dispensasi izin atau sakit secara mandiri dengan melampirkan foto surat dokter/keterangan resmi, sehingga Kepala Sekolah dapat menyetujui dispensasi saya dan jadwal KBM murid dapat disesuaikan secara tertib.

**Urutan Stimulus dan Respons:**
1. Tutor membuka modul Pengajuan Izin / Sakit pada aplikasi.
2. Tutor memilih jenis dispensasi (Izin atau Sakit), menentukan rentang tanggal berhalangan, memilih siswa terdampak, menuliskan keterangan alasan, dan mengunggah berkas surat dokter/keterangan resmi (PDF/JPG/PNG).
3. Permohonan tersimpan dengan status 'pending' dan Kepala Sekolah menerima notifikasi verifikasi.
4. Kepala Sekolah meninjau berkas dokumen melalui penampil dokumen aman dan menetapkan persetujuan atau penolakan.
5. Jika disetujui, sistem mencatat status dispensasi pada rekapitulasi kehadiran dan membatalkan jadwal KBM reguler pada rentang tanggal tersebut.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Pengajuan izin/sakit wajib melampirkan berkas bukti dokumen yang tersimpan pada direktori privat terisolasi.
2. Kepala Sekolah dapat melihat pratinjau dokumen lampiran secara langsung melalui antarmuka sistem.
3. Persetujuan izin otomatis menandai rekapitulasi kehadiran dengan status izin/sakit dan membatalkan jadwal sesi pada tanggal terkait.

**Persyaratan Fungsional:**
- **REQ-030:** Sistem harus menyediakan formulir pengajuan izin dan sakit mandiri bagi tutor dengan fasilitas unggah berkas surat keterangan pendukung.
- **REQ-031:** Sistem harus menyediakan modul evaluasi bagi Kepala Sekolah untuk menyetujui atau menolak permohonan izin.
- **REQ-032:** Sistem harus menyinkronkan data permohonan izin yang disetujui ke dalam rekapitulasi presensi dan membatalkan jadwal kegiatan belajar mengajar reguler pada tanggal tersebut.


### 4.8 Pengelolaan Kategori Tutorial, Tarif Surat Keputusan (SK), dan Otomasi Penggajian (Payroll) dengan Immutability Snapshot

**Deskripsi dan Prioritas:**
Fitur ini mengotomatisasi perhitungan honorarium mengajar tutor berdasarkan Surat Keputusan Kepala PKBM Pikat Nomor 014/SK/PKBM-PIKAT/IX/2026, mendukung master 6 tarif layanan, deteksi otomatis kelas multi-rombel, pembekuan nominal honorarium (immutability snapshot) pada setiap transaksi presensi selesai, serta penerbitan slip gaji PDF resmi dan ekspor Excel. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai bendahara, pimpinan lembaga, dan tutor, saya ingin sistem menghitung honorarium mengajar secara otomatis berdasarkan SK Kepala PKBM yang berlaku dan mengunci nilai nominal tersebut secara permanen, sehingga tidak terjadi kesalahan hitung dan slip gaji dapat dicetak resmi kapan saja.

**Urutan Stimulus dan Respons:**
1. Admin mengelola master data kategori tutorial dan menetapkan tarif honorarium per sesi pertemuan sesuai Surat Keputusan.
2. Saat tutor menyelesaikan sesi KBM berstatus hadir, PayrollService mencocokkan kategori tutorial, memeriksa keterlibatan siswa dari rombel berbeda, dan menghitung total honorarium.
3. Sistem mengunci (snapshot) nominal honorarium dan durasi sesi pada baris transaksi presensi.
4. Pimpinan dan tutor dapat membuka menu Payroll, melihat rekapitulasi bulanan, dan mengunduh slip honorarium format PDF berstandar cetak lengkap dengan kop resmi lembaga.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Master kategori tutorial mengakomodasi minimal 6 skema tarif SK (Komunitas Paket A, B, C, Distance Learning, ABK, dan Gabungan Rombel).
2. Sistem mendeteksi otomatis sesi gabungan yang melibatkan siswa dari >1 kelas/rombel dan menerapkan tarif Rp 50.000 per rombel.
3. Nominal honorarium pada data presensi masa lalu tidak berubah meskipun master tarif SK diperbarui di masa mendatang.
4. Slip gaji individual dapat diunduh dalam format PDF potret resmi dengan rincian per pertemuan dan tanda tangan pengesahan.

**Persyaratan Fungsional:**
- **REQ-033:** Sistem harus mengelola master data kategori tutorial dan tarif honorarium sesuai Surat Keputusan Kepala PKBM Pikat untuk minimal enam jenis layanan bimbingan.
- **REQ-034:** Sistem harus mendeteksi secara otomatis sesi tutorial gabungan komunitas yang melibatkan siswa dari lebih dari satu rombongan belajar dan menerapkan perhitungan tarif kelipatan rombel.
- **REQ-035:** Sistem harus membekukan nominal honorarium pada setiap transaksi presensi yang selesai agar data keuangan historis bersifat kekal dan tidak berubah saat tarif master diperbarui.
- **REQ-036:** Sistem harus menyediakan fitur cetak slip gaji tutor dalam format PDF resmi dengan rincian sesi mengajar dan pengesahan lembaga.
- **REQ-037:** Sistem harus menyediakan fitur ekspor rekapitulasi penggajian bulanan ke dalam format Excel dan PDF.


### 4.9 Pengelolaan Data Induk Akademik, Jenjang Paket Kesetaraan, Rombel Kelas, Siklus Hidup Siswa, dan Multi-Titik Lokasi Presensi

**Deskripsi dan Prioritas:**
Fitur ini mengelola struktur master data institusi yang mencakup jenjang paket kesetaraan (Paket A, B, C), rombongan belajar kelas, data induk murid beserta siklus status belajarnya, akun pengguna, multi-titik geofence lokasi presensi, serta pertukaran data massal via Excel. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai administrator sistem, saya ingin mengelola master akademik, siklus hidup siswa, titik lokasi presensi, dan mengimpor/mengekspor data massal secara efisien, sehingga basis data operasional sekolah selalu terstruktur dan mutakhir.

**Urutan Stimulus dan Respons:**
1. Admin mengakses modul Master Data pada portal admin.
2. Admin dapat menambah, mengedit, atau menonaktifkan data jenjang paket, rombel kelas, siswa, karyawan, dan lokasi presensi.
3. Admin dapat mengunggah berkas Excel (.xlsx) untuk melakukan impor massal data siswa dan jadwal.
4. Sistem memvalidasi integritas data, mencegah duplikasi NIK/NISN, menerapkan SoftDeletes, dan menyimpan entitas baru.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Master jenjang paket terhubung secara relasional hierarkis dengan rombel kelas dan siswa.
2. Data siswa mendukung siklus hidup status: aktif, alumni, cuti, dan nonaktif tanpa menghapus riwayat presensi lampau (SoftDeletes).
3. Master lokasi presensi mendukung konfigurasi multi-titik (kampus pusat, cabang, mitra) dengan koordinat dan radius dinamis.
4. Fasilitas impor massal Excel memvalidasi format kolom dan memberikan laporan jumlah baris yang berhasil diimpor.

**Persyaratan Fungsional:**
- **REQ-038:** Sistem harus menyediakan master data jenjang paket yang terelasi secara terstruktur dengan data kelas atau rombongan belajar.
- **REQ-039:** Sistem harus mengelola data induk siswa beserta status siklus hidupnya yang meliputi aktif, alumni, cuti, atau nonaktif, serta relasi tutor pembimbing.
- **REQ-040:** Sistem harus menyediakan master data lokasi presensi yang memungkinkan pengelolaan titik kampus pusat, cabang, atau mitra belajar dengan koordinat dan radius dinamis.
- **REQ-041:** Sistem harus menyediakan fasilitas impor massal dan ekspor data siswa, karyawan, dan jadwal menggunakan format berkas spreadsheet Excel.
- **REQ-042:** Sistem harus menyediakan fasilitas pengaturan ulang kata sandi instan untuk akun siswa dan karyawan oleh administrator.


### 4.10 Dasbor Analitik Eksekutif, Pemantauan Presensi Harian Real-Time, dan Leaderboard Kedisiplinan

**Deskripsi dan Prioritas:**
Fitur ini menyajikan visualisasi data kinerja kehadiran civitas akademika, metrik kedisiplinan belajar mengajar, grafik tren semesteran, tabel pemantauan langsung presensi harian, dan papan peringkat kedisiplinan bagi pimpinan dan admin. Prioritas implementasi: Wajib (Sedang).

**User Story:**
> Sebagai Kepala Sekolah dan Administrator, saya ingin memantau kehadiran harian civitas akademika secara langsung dan melihat grafik tren kehadiran serta peringkat kedisiplinan tutor pada dasbor visual, sehingga pengambilan keputusan manajemen dapat dilakukan berbasis data akurat.

**Urutan Stimulus dan Respons:**
1. Pimpinan membuka dasbor eksekutif setelah proses login berhasil.
2. Sistem menyajikan metrik ringkasan kartu: total kehadiran hari ini, izin, sakit, sesi berlangsung, dan pengajuan menunggu persetujuan.
3. Sistem memvisualisasikan grafik garis tren kehadiran 6 bulan terakhir dan tabel pemantauan langsung sesi bimbingan hari ini.
4. Pimpinan dapat melihat papan peringkat (leaderboard) kedisiplinan tutor berdasarkan ketepatan waktu dan jam mengajar.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Dasbor menampilkan ringkasan data operasional harian secara akurat tanpa kalkulasi berulang.
2. Grafik tren menampilkan perbandingan kehadiran, izin, dan sakit selama 6 bulan terakhir.
3. Leaderboard menyajikan akumulasi jam mengajar dan rasio ketepatan waktu tutor.
4. Tabel monitoring live memperbarui status kehadiran sesi KBM yang sedang berlangsung.

**Persyaratan Fungsional:**
- **REQ-043:** Sistem harus menampilkan statistik kehadiran harian, jumlah izin, sakit, sesi aktif, dan permohonan yang menunggu persetujuan pada dasbor utama.
- **REQ-044:** Sistem harus menampilkan grafik tren kehadiran enam bulan terakhir berbasis visualisasi diagram garis pada dasbor pimpinan.
- **REQ-045:** Sistem harus menyajikan papan peringkat kedisiplinan tutor yang memuat metrik ketepatan waktu dan akumulasi jam mengajar bulanan.
- **REQ-046:** Sistem harus menyediakan tabel pemantauan langsung presensi harian tutor dan siswa yang diperbarui secara sistematis.


### 4.11 Layanan Notifikasi Web Push Real-Time Standar VAPID dan Pengingat Otomatis Presensi/Sesi

**Deskripsi dan Prioritas:**
Fitur ini menyiarkan notifikasi seketika berstandar VAPID (RFC 8292) langsung ke peramban dan aplikasi ponsel pengguna untuk mempercepat persetujuan dispensasi, menginformasikan perubahan jadwal sesi KBM, dan memberikan pengingat otomatis sebelum jam presensi. Prioritas implementasi: Wajib (Sedang).

**User Story:**
> Sebagai pengguna sistem (Kepala Sekolah, Tutor, Siswa), saya ingin menerima notifikasi instan pada gawai saya ketika ada pengajuan baru, jadwal yang diubah, atau pengingat presensi KBM, sehingga saya dapat segera merespons tanpa harus terus-menerus membuka aplikasi.

**Urutan Stimulus dan Respons:**
1. Pengguna mengaktifkan izin notifikasi pada peramban web atau aplikasi mobile.
2. Sistem mendaftarkan token langganan Web Push pengguna ke basis data dengan enkripsi VAPID.
3. Saat tutor mengajukan lupa lapor atau izin, sistem otomatis mengirimkan Web Push notification ke gawai Kepala Sekolah.
4. Saat terjadi perubahan atau reschedule jadwal KBM, sistem mengirimkan notifikasi ke tutor dan murid terkait.
5. Sistem menjalankan scheduler berkala untuk mengirimkan pengingat presensi 15 menit sebelum jam bimbingan dimulai.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Pengguna dapat mendaftar dan berhenti berlangganan notifikasi push secara transparan.
2. Kepala Sekolah menerima notifikasi instan saat ada permohonan baru lupa lapor atau izin.
3. Tutor dan murid menerima notifikasi real-time saat sesi KBM di-reschedule.
4. Notifikasi push tetap dapat diterima saat aplikasi berada di latar belakang atau peramban ditutup.

**Persyaratan Fungsional:**
- **REQ-047:** Sistem harus mendukung pendaftaran langganan Web Push Notification berbasis standar VAPID pada peramban web dan aplikasi mobile.
- **REQ-048:** Sistem harus mengirimkan notifikasi seketika kepada Kepala Sekolah saat terdapat pengajuan baru lupa lapor atau izin.
- **REQ-049:** Sistem harus mengirimkan notifikasi seketika kepada tutor dan siswa saat terjadi penundaan atau perubahan jadwal belajar mengajar.
- **REQ-050:** Sistem harus mendukung pengiriman notifikasi pengingat presensi otomatis kepada pengguna sebelum jam kegiatan belajar dimulai.


### 4.12 Pelaporan Presensi Terpadu, Ekspor Rekapitulasi Excel 14 Kolom, Cetak PDF Resmi, dan Jejak Audit (Audit Trail)

**Deskripsi dan Prioritas:**
Fitur ini menyediakan fasilitas pencarian data presensi terpadu multi-filter, ekspor rekapitulasi data lembar sebar Excel komprehensif 14 kolom, pencetakan berkas PDF lanskap resmi berkop surat lembaga, serta pencatatan jejak audit (audit trail) pada setiap modifikasi jadwal dan approval. Prioritas implementasi: Wajib (Tinggi).

**User Story:**
> Sebagai administrator dan Kepala Sekolah, saya ingin menyaring data kehadiran dengan berbagai filter, mengekspor laporan ke Excel 14 kolom, mencetak dokumen PDF resmi, dan memeriksa jejak audit perubahan, sehingga pertanggungjawaban operasional dan audit lembaga terpenuhi secara akuntabel.

**Urutan Stimulus dan Respons:**
1. Pengguna membuka menu Laporan Presensi.
2. Pengguna menentukan kriteria filter: rentang tanggal, nama tutor, nama siswa, moda pembelajaran, status kehadiran, dan kelas.
3. Sistem menyajikan tabel data presensi yang sesuai kriteria pencarian.
4. Pengguna menekan tombol 'Ekspor Excel' untuk mengunduh rekapitulasi 14 kolom lengkap (waktu, durasi, tutor, murid, rombel, koordinat, status, nominal honorarium).
5. Pengguna menekan tombol 'Cetak PDF' untuk mengunduh berkas laporan bertata letak lanskap berstempel dan kop surat resmi lembaga.
6. Setiap aksi pembatalan jadwal, reschedule sesi, dan verifikasi lupa lapor tercatat permanen pada tabel log audit.

**Kriteria Keberterimaan (Acceptance Criteria):**
1. Laporan presensi dapat difilter secara fleksibel dengan kombinasi multi-parameter.
2. Berkas Excel yang diekspor menyajikan 14 kolom data terstruktur lengkap dengan header formal.
3. Berkas PDF yang dihasilkan memiliki tata letak lanskap rapi, kop surat PKBM Pikat, dan tabel yang terformat jelas.
4. Tabel audit trail merekam identitas pengguna, waktu kejadian, aksi, dan nilai data sebelum serta sesudah perubahan.

**Persyaratan Fungsional:**
- **REQ-051:** Sistem harus menyediakan tabel laporan presensi dengan filter terpadu berdasarkan rentang tanggal, tutor, siswa, moda pembelajaran, status, dan kelas.
- **REQ-052:** Sistem harus menyediakan fitur ekspor laporan presensi ke dalam format Excel empat belas kolom yang memuat data waktu presensi, durasi, identitas pengajar, murid, kelas, koordinat GPS, status verifikasi, dan nominal honorarium.
- **REQ-053:** Sistem harus menyediakan fitur ekspor laporan presensi ke dalam format PDF dokumen resmi bertata letak lanskap dengan kop surat resmi PKBM Pikat.
- **REQ-054:** Sistem harus mencatat seluruh riwayat pembatalan jadwal, perubahan sesi belajar, dan persetujuan lupa lapor ke dalam tabel log audit yang tidak dapat dihapus.


---

## 5. Kebutuhan Non-Fungsional Lainnya

### 5.1 Kebutuhan Kinerja (Performance Requirements)
1. Waktu Respon Sistem: Sistem harus mampu merespons setiap permintaan halaman web utama dan dasbor dalam waktu maksimal 2 detik pada kondisi beban kerja normal dengan hingga 100 pengguna aktif bersamaan.
2. Waktu Eksekusi Geofencing: Algoritma validasi jarak Haversine dan verifikasi aturan kelayakan kepulangan pada backend harus diselesaikan dalam waktu kurang dari 200 milidetik per request presensi.
3. Ukuran Aset Awal: Ukuran total aset statis yang dimuat pada akses awal tidak boleh melebihi 2 MB guna menjamin kecepatan akses pada jaringan seluler di daerah rural.
4. Efisiensi Kueri Basis Data: Seluruh pemanggilan relasi antar-tabel wajib menerapkan teknik eager loading untuk mencegah terjadinya problem N+1 Query pada ORM.

### 5.2 Kebutuhan Keselamatan dan Toleransi Kesalahan (Safety Requirements)
1. Toleransi Sesi Presensi Menggantung: Sistem tidak boleh memblokir presensi hari baru apabila tutor lupa melakukan checkout pada hari sebelumnya; sistem secara toleran menampilkan banner peringatan edukatif yang memandu pengajuan lupa lapor satu klik.
2. Pencadangan Basis Data Otomatis: Basis data utama wajib dicadangkan secara otomatis setiap hari pada pukul 02.00 WIB dengan retensi penyimpanan minimal 30 hari kalender pada direktori penyimpanan terpisah.
3. Ketahanan Jaringan Terputus (Offline Resilience): Aplikasi Progressive Web App (PWA) harus mampu menyimpan antrean data presensi sementara pada media penyimpanan lokal perangkat saat jaringan terputus dan menyinkronkannya kembali ketika koneksi pulih.

### 5.3 Kebutuhan Keamanan dan Privasi Data (Security Requirements)
1. Hashing Kata Sandi: Seluruh kata sandi pengguna wajib dienkripsi menggunakan algoritma hashing satu arah Bcrypt (work factor minimal 12) atau Argon2id sebelum disimpan dalam basis data.
2. Enkripsi Transport Layer: Seluruh komunikasi data antara peramban klien dan server wajib menggunakan protokol HTTPS dengan enkripsi TLS 1.3.
3. Pertahanan Siber Bawaan: Sistem wajib menerapkan perlindungan terhadap serangan Cross-Site Request Forgery (token CSRF), Cross-Site Scripting (Blade template auto-escaping), dan SQL Injection (PDO prepared statements).
4. Manajemen Sesi: Sesi pengguna otomatis berakhir (timeout) jika tidak ada aktivitas selama 30 menit. Batas laju percobaan login dibatasi 5 kali per menit per alamat IP.
5. Isolasi Berkas Privat: Foto presensi dan berkas dokumen surat izin disimpan pada direktori privat terisolasi (storage/app/public/) dan hanya dapat diakses melalui rute resmi terautentikasi.

### 5.4 Atribut Kualitas Perangkat Lunak (Software Quality Attributes)
Mengacu pada materi standar kualitas perangkat lunak IEEE, sistem presensi ini memenuhi 8 atribut kualitas utama:

- **Availability (Ketersediaan):** Sistem harus menjamin ketersediaan layanan minimal 99,5% selama jam operasional kegiatan belajar mengajar (pukul 07.00 hingga 21.00 WIB setiap hari) tanpa downtime yang tidak terencana.
- **Reliability (Keandalan):** Tingkat keberhasilan transaksi pencatatan presensi, penjadwalan, dan perhitungan honorarium harus mencapai minimal 99% per minggu tanpa adanya kegagalan integritas data relasional.
- **Ergonomy / Usability (Kenyamanan Pakai):** Antarmuka sistem dirancang sangat ergonomis untuk pengoperasian satu tangan pada layar ponsel pintar, menyajikan tombol navigasi bawah, teks kontras tinggi, kendali kamera lengkap, serta dukungan tema terang dan gelap.
- **Portability (Kemudahan Portabilitas):** Sistem dapat dijalankan secara konsisten pada berbagai peramban web modern (Google Chrome, Mozilla Firefox, Microsoft Edge, Safari) lintas sistem operasi (Android, iOS, Windows, Linux, macOS) dan dapat dikemas sebagai APK Android native Capacitor.
- **Maintainability (Kemudahan Pemeliharaan):** Struktur kode sumber menerapkan Clean Architecture berbasis Dedicated Service Layer, mematuhi standar penulisan program PHP PSR-12, dan tervalidasi secara berkala menggunakan perkakas Laravel Pint.
- **Response Time & Performance (Batasan Waktu):** Sistem menetapkan batasan waktu respons halaman maksimal 2 detik dan sinkronisasi data presensi maksimal 500 milidetik pada kondisi jaringan stabil.
- **Safety (Toleransi Kesalahan):** Menerapkan proteksi terhadap kesalahan input manusia, mekanisme pemulihan sesi menggantung, dan penguncian data transaksi masa lalu dari modifikasi tidak disengaja.
- **Security (Keamanan dan Privasi):** Penerapan kontrol akses berbasis peran (RBAC) berlapis, pertahanan anti-fake GPS (mock location detection), perlindungan data pribadi geolokasi dan biometrik wajah civitas akademika.

### 5.5 Aturan Bisnis (Business Rules)
- 1. Regulasi Tarif Honorarium SK: Perhitungan honorarium mengajar tutor wajib berpedoman mutlak pada Surat Keputusan Kepala PKBM Pikat Nomor 014/SK/PKBM-PIKAT/IX/2026 yang mencakup 6 kategori tutorial dan perhitungan tarif kelipatan untuk sesi gabungan multi-rombel.
- 2. Kelayakan Kepulangan Hibrida: Formulir presensi pulang tutor hanya dapat dibuka jika waktu bimbingan telah mencapai 10 menit sebelum jam jadwal berakhir ATAU tutor telah menyelesaikan minimal 80% dari durasi rencana bimbingan.
- 3. Hak Otoritas Persetujuan Tunggal: Kewenangan mutlak pemberian persetujuan atau penolakan permohonan lupa lapor retroaktif dan dispensasi izin/sakit berada sepenuhnya pada peran Kepala Sekolah.
- 4. Pembatasan Presensi Mandiri Siswa: Siswa hanya berhak mencatat presensi kedatangan satu kali per hari dalam radius 100 meter dari sekolah, dengan toleransi keterlambatan 30 menit setelah jam mulai KBM.
- 5. Kekekalan Data Finansial (Immutability): Setiap transaksi presensi yang telah diverifikasi hadir membekukan nilai nominal honorarium dan durasi mengajar secara permanen sehingga tidak terpengaruh oleh revisi tarif SK di masa depan.
- 6. Dispensasi Bebas Radius Pimpinan: Peran Administrator dan Kepala Sekolah yang melaksanakan dinas luar diberikan dispensasi presensi bebas batas radius dengan tetap merekam titik koordinat riil di basis data.

---

## 6. Kebutuhan Tambahan dan Legalitas

### 6.1 Persyaratan Basis Data (Database Requirements)
Sistem menggunakan Database Management System (DBMS) relasional MySQL 8.0 atau MariaDB 10.6 dengan storage engine InnoDB. Seluruh struktur tabel telah dinormalisasi hingga Bentuk Normal Ketiga (3NF) guna mengeliminasi anomali penyisipan, pembaruan, dan penghapusan data. Seluruh hubungan antar-tabel menerapkan batasan kunci asing (Foreign Key Constraints) dengan aturan referensial yang ketat (ON DELETE RESTRICT untuk entitas induk berelasi transaksi keuangan, dan ON DELETE SET NULL / SoftDeletes untuk entitas pendukung). Data siswa dan kelas menerapkan mekanisme SoftDeletes guna melindungi riwayat presensi masa lalu dari kehilangan data permanen.

### 6.2 Persyaratan Hukum dan Legalitas (Legal Requirements)
Sistem wajib mematuhi ketentuan perundang-undangan Republik Indonesia yang berlaku, antara lain: (1) Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional, khususnya regulasi terkait penjaminan mutu jalur pendidikan non-formal; (2) Undang-Undang Nomor 11 Tahun 2008 dan Undang-Undang Nomor 1 Tahun 2024 tentang Informasi dan Transaksi Elektronik (ITE) mengenai keabsahan bukti digital foto dan log presensi; (3) Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP) terkait pengamanan koordinat geografis dan citra biometrik wajah civitas akademika; serta (4) Peraturan Menteri Pendidikan mengenai standar operasional PKBM.

### 6.3 Integritas Data dan Jejak Audit (Data Integrity)
Sistem menjamin integritas data secara menyeluruh melalui tiga pilar: (1) Prinsip Kekekalan Data Historis (Immutability): Nilai nominal honorarium mengajar dibekukan langsung pada baris data transaksi presensi pada saat presensi diselesaikan, menjamin data audit keuangan masa lalu tidak berubah meskipun terjadi revisi tarif master SK di masa depan. (2) Pencegahan Perhitungan Ganda (Anti Double-Counting): Perhitungan statistik kehadiran siswa diagregasikan berdasarkan tanggal kalender unik (DISTINCT date) untuk mencegah penggelembungan angka kehadiran saat tutor dan siswa sama-sama mencatat presensi. (3) Jejak Audit Komprehensif (Audit Trail): Seluruh perubahan status jadwal KBM, penundaan sesi bimbingan, pembatalan jadwal, dan persetujuan lupa lapor dicatat permanen dalam tabel log audit yang tidak dapat dimanipulasi.

---

## Lampiran A: Glosarium Istilah (Glossary)

| Istilah / Singkatan | Definisi dan Keterangan Teknis |
|---|---|
| **API (Application Programming Interface)** | Sekumpulan protokol dan antarmuka pemrograman yang memungkinkan pertukaran data dan komunikasi antar-komponen perangkat lunak secara terstandarisasi. |
| **Bcrypt** | Algoritma fungsi hashing kunci adaptif satu arah yang digunakan untuk mengamankan kata sandi dengan menambahkan nilai salt acak guna menangkal serangan rainbow table. |
| **Capacitor** | Kerangka kerja lintas platform modern yang memungkinkan pembungkusan aplikasi web berbasis HTML5/JS menjadi aplikasi native Android siap pasang. |
| **CSRF (Cross-Site Request Forgery)** | Bentuk serangan siber di mana penyerang mengecoh peramban korban untuk mengeksekusi aksi yang tidak diinginkan pada aplikasi terotentikasi. |
| **DBMS (Database Management System)** | Perangkat lunak sistem yang bertugas menyimpan, mengelola, mengamankan, dan memanipulasi data dalam struktur tabel relasional. |
| **Eager Loading** | Teknik optimasi pengambilan data relasional pada Object-Relational Mapping (ORM) yang memuat entitas terkait secara sekaligus guna mengeliminasi masalah N+1 Query. |
| **Geofencing** | Teknologi perimeter virtual geografis nyata yang membatasi area presensi berdasarkan titik koordinat pusat dan radius batas toleransi tertentu. |
| **Haversine Formula** | Persamaan matematika navigasi bola yang digunakan untuk menghitung jarak lingkaran besar antara dua titik koordinat lintang dan bujur di permukaan bumi. |
| **HTTPS (Hypertext Transfer Protocol Secure)** | Protokol komunikasi jaringan aman yang mengenkripsi seluruh transmisi data menggunakan protokol Transport Layer Security (TLS). |
| **Just-In-Time Provisioning** | Mekanisme pembuatan akun dan profil pengguna lokal secara otomatis pada saat otentikasi Single Sign-On pertama kali berhasil diverifikasi. |
| **Mock Location** | Metode atau aplikasi pada perangkat gawai yang memalsukan koordinat penerima GPS asli menjadi koordinat tiruan di tempat lain. |
| **PWA (Progressive Web App)** | Metodologi rekayasa web yang mengombinasikan Service Worker dan Web App Manifest untuk menghadirkan pengalaman aplikasi native pada peramban. |
| **RBAC (Role-Based Access Control)** | Pendekatan keamanan sistem yang membatasi otorisasi akses menu dan fitur berdasarkan peran operasional pengguna. |
| **SoftDeletes** | Pola penghapusan data secara logis di mana baris data ditandai dengan timestamp penghapusan tanpa menghapus data secara fisik dari basis data. |
| **VAPID (Voluntary Application Server Identification)** | Spesifikasi standar terbuka (RFC 8292) untuk mengautentikasi dan mengamankan pengiriman notifikasi Web Push dari server aplikasi ke agen peramban. |

---

## Lampiran B: Model Analisis Sistem (Analysis Models)

### Business Process Model and Notation (BPMN) / Diagram Swimlane
Menggambarkan alur operasional lintas 5 peran pengguna, mulai dari penjadwalan KBM oleh tutor, validasi geofence dan swafoto presensi mengajar, verifikasi mandiri kedatangan murid, eskalasi permohonan lupa lapor ke Kepala Sekolah, hingga otomasi kalkulasi honorarium SK dan penerbitan slip gaji.

### Entity Relationship Diagram (ERD)
Menggambarkan rancangan konseptual, logis, dan fisik basis data yang menghubungkan 18 tabel relasional fisik aktif sistem, yaitu: `users`, `admin`, `kepala_sekolah`, `tutor`, `murid`, `magang`, `jenjang_paket`, `kelas`, `kategori_tutorial`, `jadwal_rutin`, `jadwal_sesi`, `jadwal_kerja`, `lokasi_presensi`, `presensi`, `presensi_mandiri_murid`, `presensi_karyawan`, `pengajuan_lupa_lapor`, `pengajuan_izin_sakit`, dan `push_subscriptions`.

### Use Case Diagram
Memodelkan seluruh interaksi antara 5 aktor (Super Admin, Kepala Sekolah, Tutor, Murid, Magang) dengan 12 paket fungsional utama sistem presensi digital.

### State Transition Diagram
Menggambarkan siklus hidup perubahan status sesi bimbingan belajar (dijadwalkan -> berlangsung -> selesai / dibatalkan / direschedule), siklus status presensi (pending -> hadir / terlambat / ditolak), serta siklus status pengajuan lupa lapor dan izin (pending -> disetujui / ditolak).

---

## Lampiran C: Daftar Keputusan dan Resolusi Teknis (To Be Determined List / TBD)

| Item Teknis Lanjutan | Uraian Keputusan dan Realisasi Arsitektur | Status Resolusi |
|---|---|---|
| **Infrastruktur Peta Digital Mandiri** | Meniadakan dependensi CDN eksternal dengan memasang paket lokal Leaflet dan menerapkan filter CSS dark mode mandiri pada ubin OpenStreetMap. | 🟢 Selesai / Terimplementasi Penuh |
| **Standar Skema Honorarium SK** | Menggantikan tarif statis per jam dengan master 6 tarif SK Kepala PKBM, deteksi multi-rombel, dan immutability snapshot data keuangan. | 🟢 Selesai / Terimplementasi Penuh |
| **Aturan Kelayakan Pulang Hibrida** | Menggantikan aturan kaku minimal 1 jam dengan formula cerdas 10 menit sebelum jam selesai ATAU 80% capaian durasi bimbingan efektif. | 🟢 Selesai / Terimplementasi Penuh |
| **Presensi Mandiri Murid & Time-Gating** | Menerapkan single check-in harian murid dengan penguncian kamera sebelum H-30 menit dan penandaan otomatis status keterlambatan >30 menit. | 🟢 Selesai / Terimplementasi Penuh |
| **Single Sign-On (SSO) SIMPIKAT** | Integrasi otentikasi lintas basis data dengan portal induk SIMPIKAT dan mekanisme Just-In-Time Provisioning profil akun otomatis. | 🟢 Selesai / Terimplementasi Penuh |
| **Manajemen Izin Aplikasi Android Capacitor** | Menyusun alur inisialisasi izin kamera dan geolokasi web secara sekuensial guna mencegah terjadinya tabrakan antarmuka WebView native. | 🟢 Selesai / Terimplementasi Penuh |
| **Layanan Web Push Notification VAPID** | Mengirimkan notifikasi seketika ke peramban dan ponsel pengguna untuk persetujuan lupa lapor, izin, pengingat KBM, dan reschedule. | 🟢 Selesai / Terimplementasi Penuh |
| **Format Ekspor Rekapitulasi Data** | Standardisasi ekspor laporan presensi ke dalam lembar sebar Excel 14 kolom terstruktur dan berkas cetak PDF bertata letak lanskap berstempel resmi. | 🟢 Selesai / Terimplementasi Penuh |


# ANALISIS PEMBARUAN DAN PENGEMBANGAN SISTEM PRESENSI DIGITAL PUSAT KEGIATAN BELAJAR MASYARAKAT (PKBM) PINTAR BERBAKAT

---

## 1. Latar Belakang Transformasi Sistem

Sistem Presensi Digital PKBM Pikat pada mulanya dikembangkan untuk memenuhi kebutuhan pencatatan kehadiran tutor bimbingan belajar secara digital melalui foto kamera dan pencatatan koordinat GPS. Namun, seiring dengan dinamika operasional bimbingan belajar non-formal yang semakin kompleks, sistem terdahulu menghadapi berbagai kendala teknis, inkonsistensi arsitektur data, celah keamanan, dan keterbatasan fungsional. 

Pada sistem terdahulu, logika penggajian tutor masih didasarkan pada perkalian tarif per jam yang tersimpan statis pada tabel siswa. Pola ini tidak mencerminkan realitas hukum dan manajemen sekolah yang berpedoman pada Surat Keputusan (SK) Kepala PKBM mengenai tarif honor per pertemuan berdasarkan kategori tutorial (Komunitas, Distance Learning, ABK, dan Gabungan Rombel). Selain itu, sistem lama belum memiliki mekanisme penjadwalan sesi belajar mandiri oleh tutor, belum mengakomodasi peran siswa untuk melakukan presensi mandiri, belum mampu mendeteksi manipulasi sinyal GPS, rentan terhadap kegagalan infrastruktur akibat dependensi CDN pihak ketiga, dan kerap mengalami pesan peringatan usang pada lingkungan runtime PHP 8.5+.

Menanggapi berbagai tantangan tersebut, dilakukan restrukturisasi menyeluruh dan pengembangan bertahap yang mencakup perbaikan keamanan, penguatan integritas basis data relasional, otomasi payroll berskema SK, desentralisasi penjadwalan KBM, implementasi portal presensi siswa, penyediaan Web Push Notification real-time, hingga modernisasi antarmuka pengguna berbasis *Mobile-First Design System*.

---

## 2. Tujuan Dokumen Analisis Pembaruan

Dokumen ini disusun sebagai instrumen pelaporan komprehensif yang menguraikan secara sistematis seluruh pembaruan teknis yang telah diterapkan pada Sistem Presensi Digital PKBM Pikat. Dokumen ini bertujuan untuk:
1. Menyajikan daftar lengkap seluruh item pembaruan sistem yang telah diselesaikan berdasarkan pelacak dokumen Progress Pengembangan.
2. Memberikan penjelasan mendalam dalam bentuk paragraf deskriptif mengenai latar belakang permasalahan pada sistem terdahulu, solusi teknis yang diimplementasikan, serta argumentasi atau alasan logis yang mendasari setiap keputusan arsitektur.
3. Menjadi acuan formal bagi penyusunan Laporan Praktik Kerja Lapangan (PKL), Laporan Analisis Sistem, maupun dokumentasi serah-terima teknis bagi pemangku kepentingan PKBM Pikat.

---

## 3. Daftar Pembaruan dan Analisis Deskriptif Pengembangan Sistem

### 3.1 Keamanan, Infrastruktur, dan Fondasi Sistem

* **Upgrade Framework ke Laravel 13 dan Kompatibilitas PHP 8.5**  
  Sistem diperbarui ke versi framework Laravel 13 (v13.31.0) dengan penyesuaian seluruh dependensi pustaka pendukung. Pembaruan ini mengatasi pesan peringatan usang (*deprecation warning*) pada koneksi basis data PDO MySQL di PHP 8.5, sehingga sistem berjalan stabil, terhindar dari potensi kegagalan koneksi di masa depan, dan memiliki fondasi teknologi jangka panjang yang aman.

* **Penerapan Pembatasan Percobaan Login (Rate Limiting)**  
  Sistem menambahkan pembatasan frekuensi autentikasi maksimal 5 kali percobaan login per menit untuk setiap alamat IP atau akun pengguna. Fitur ini dirancang untuk mencegah serangan siber berbasis *brute force* dan penebakan kata sandi secara otomatis, dilengkapi dengan pesan peringatan interaktif bagi pengguna agar keamanan akun tenaga pendidik maupun admin tetap terjaga.

* **Pengamanan Koneksi Reverse Proxy dan Penegakan HTTPS**  
  Konfigurasi perantara proxy tepercaya diterapkan pada sistem untuk mendeteksi header SSL secara otomatis dan memaksakan skema koneksi HTTPS. Langkah ini menjamin seluruh fitur modern peramban seperti akses kamera, perekaman titik koordinat GPS, *Progressive Web App* (PWA), serta *Web Push Notification* dapat beroperasi tanpa terhambat oleh pemblokiran keamanan konten campuran (*Mixed Content Warning*).

* **Pembersihan Log Sensitif dan Tata Kelola Berkas Server**  
  File log kesalahan (*error_log*) bawaan server yang berpotensi membocorkan struktur direktori dan kredensial aplikasi telah dibersihkan secara total dari repositori kode. Seluruh mekanisme pencatatan log kini dialihkan secara terpusat ke direktori internal penyimpanan Laravel dan dilindungi oleh konfigurasi pengabaian berkas publik.

---

### 3.2 Fitur Inti Presensi dan Validasi Lokasi

* **Presensi Multi-Moda (Sekolah, Kunjungan Rumah, dan Kelas Daring)**  
  Sistem kini mendukung tiga moda pembelajaran sesuai dengan kebutuhan nyata bimbingan belajar non-formal. Moda Tatap Muka menerapkan pembatasan radius lokasi sekolah secara ketat, moda Kunjungan Rumah (*home visit*) mencatat koordinat lokasi rumah murid beserta foto kegiatan, dan moda Daring (*online*) meniadakan batas radius dengan mewajibkan penginputan tautan ruang pertemuan (Zoom/Google Meet) serta tangkapan layar sesi belajar.

* **Master Multi-Titik Lokasi Presensi dengan Radius Dinamis**  
  Admin sekolah kini dapat mengelola banyak titik presensi (kampus pusat, gedung cabang, maupun mitra belajar) melalui modul khusus. Setiap titik lokasi dapat diatur koordinat lintang/bujurnya beserta radius toleransi meter yang berbeda-beda, sehingga pengguna dapat memilih lokasi tempat bertugas dan sistem akan memvalidasi posisi pengguna secara akurat terhadap titik yang dipilih.

* **Validasi Jarak Haversine dan Deteksi Anti-Fake GPS**  
  Pengecekan jarak antara posisi pengguna dan titik lokasi presensi dihitung di sisi server (*backend*) menggunakan rumus matematis Haversine untuk menjamin keakuratan jarak lengkung bumi. Sistem juga dilengkapi dengan algoritma pendeteksi aplikasi pemalsu lokasi (*Fake GPS / Mock Location*) dan penolak sinyal GPS dengan akurasi buruk di atas 200 meter demi mencegah manipulasi kehadiran.

* **Workflow Digital Lupa Lapor dengan Persetujuan Kepala Sekolah**  
  Tutor yang mengalami kendala teknis perangkat saat hari mengajar kini dapat mengajukan permohonan kehadiran susulan melalui modul Lupa Lapor. Pengajuan ini dilengkapi antarmuka persetujuan interaktif bagi Kepala Sekolah (Setuju/Tolak), di mana saat pengajuan disetujui, sistem secara otomatis mencatat kehadiran resmi pada rekap presensi tanpa perlu manipulasi data manual.

* **Penyederhanaan dan Standarisasi Arsitektur Penyimpanan Foto**  
  Seluruh berkas foto presensi dan foto profil dipindahkan dari direktori publik biasa ke dalam disk penyimpanan terkelola Laravel. Penulisan kode di seluruh controller diseragamkan dengan menghapus folder redundan, menyambungkan symlink publik yang bersih, dan memanfaatkan *Eloquent Accessor* agar pemanggilan URL gambar di antarmuka web selalu konsisten dan aman.

* **Kebijakan Pengecualian Bebas Radius untuk Admin dan Kepala Sekolah**  
  Sistem memberikan fleksibilitas khusus bagi peran Administrator dan Kepala Sekolah agar terbebas dari pembatasan radius presensi saat harus menghadiri rapat dinas luar atau tugas operasional mendesak di luar sekolah. Koordinat GPS riil tetap tercatat secara transparan di basis data dan status dinas luar ditampilkan pada peta tanpa membatasi tugas kepemimpinan.

---

### 3.3 Infrastruktur Peta Digital Leaflet

* **Kemandirian Pustaka Peta Tanpa Ketergantungan CDN Eksternal**  
  Ketergantungan terhadap server penyedia pustaka daring (*CDN*) dihapus sepenuhnya dengan mengunduh dan mem-bundle pustaka Leaflet secara lokal ke dalam aset aplikasi via Vite. Pendekatan ini membuat peta tetap dapat dimuat dengan cepat dan stabil meskipun koneksi internet publik melambat atau terjadi pemblokiran pihak ketiga.

* **Penyelesaian Bug Ikon Subpath HTTP 500 dan Pin SVG Lokal**  
  Masalah teknis pada sistem lama di mana peramban mencari gambar ikon Leaflet pada alamat URL subpath yang salah hingga memicu error 500 telah diperbaiki total. Aset gambar ikon disediakan secara fisik di direktori publik lokal dan penanda peta (*marker pin*) digantikan dengan grafik SVG modern beranimasi ring halus yang tajam di layar ponsel retina.

* **Peta Mode Gelap (Dark Mode) Mandiri Tanpa Biaya API Key**  
  Sistem menghadirkan tampilan peta tema gelap yang harmonis dengan menggunakan filter grafis CSS pada layer peta OpenStreetMap standar. Solusi kreatif ini menggantikan layanan pihak ketiga komersial terdahulu, sehingga tampilan peta gelap bebas dari tanda air (*watermark*) tagihan lisensi dan tidak membebani anggaran sekolah untuk pembelian API Key.

* **Konsolidasi Modul Peta Bersama**  
  Duplikasi kode penampil peta yang sebelumnya tersebar di berbagai tampilan kamera presensi disatukan ke dalam satu modul terpadu. Modul ini secara otomatis mengelola lingkaran akurasi lokasi, garis panduan rute interaktif menuju sekolah, widget radar jarak, serta pengaturan batas pandang peta secara instan.

---

### 3.4 Sistem Penggajian dan Honorarium Berbasis SK Kepala PKBM

* **Master Kategori Tutorial dan Skema Tarif Resmi SK**  
  Struktur penggajian dirombak total agar tunduk pada Surat Keputusan (SK) resmi Kepala PKBM Pikat. Sistem menyediakan master tarif berdasar 6 kategori utama (Komunitas 2 Jam, Komunitas ABK 2 Jam, Komunitas 3 Jam, Gabungan Rombel, Distance Learning 1,5 Jam, dan DL ABK), menggantikan logika perkalian tarif per jam yang tidak relevan dengan ketentuan lembaga.

* **Engine Honor Otomatis dan Penguncian Nilai Historis (Snapshot Immutability)**  
  Sistem membangun kalkulator otomatis yang mencocokkan kategori sesi belajar, durasi pilihan tutor, dan karakteristik siswa. Nominal honor langsung dikunci (*snapshot*) ke dalam riwayat presensi saat absensi dibuat, menjamin bahwa laporan honorarium masa lalu tidak akan pernah berubah atau rusak meskipun master tarif diperbarui di masa mendatang. Kolom lama tarif per jam pada tabel siswa pun dihapus bersih.

* **Dinamisasi Penambahan Layanan dan Perlindungan Relasi Berlapis**  
  Admin sekolah diberikan keleluasaan mendaftarkan kategori program bimbingan belajar baru secara fleksibel tanpa batasan sistem. Untuk menjaga keutuhan laporan keuangan, kategori yang sudah pernah digunakan pada data jadwal atau presensi tidak dapat dihapus permanen oleh admin, melainkan dialihkan statusnya menjadi non-aktif secara otomatis sehingga arsip riwayat gaji masa lalu tetap terlindungi 100%.

* **Penerbitan Slip Gaji Digital dan Rekapitulasi Anggaran**  
  Tutor kini dapat melihat rincian honorarium secara transparan melalui slip gaji digital interaktif dan mengunduhnya dalam format dokumen PDF resmi. Admin dan Kepala Sekolah juga dibekali laporan rekapitulasi anggaran bulanan untuk memantau pengeluaran honorarium secara menyeluruh.

---

### 3.5 Penjadwalan Sesi KBM, Pola Rutin, dan Reschedule Terstruktur

* **Entitas Penjadwalan Sesi Belajar KBM yang Fleksibel**  
  Sistem menghadirkan tabel dan relasi jadwal sesi tersendiri untuk menghubungkan tutor, murid, mata pelajaran, tanggal rencana, dan jam belajar. Jam masuk sesi bimbingan kini dievaluasi secara proporsional terhadap jam yang dijadwalkan, membebaskan tutor dari kendala jam shift pagi kaku saat harus mengajar di sore atau malam hari.

* **Master Jadwal Rutin dan Generator Kalender Otomatis**  
  Sistem menyediakan master pola berulang mingguan yang dilengkapi generator otomatis untuk menyusun jadwal sesi 4 minggu ke depan. Generator ini terpasang pada penjadwal latar belakang (*scheduler cron*) server dan memiliki kecerdasan untuk mendeteksi serta melewati hari libur atau cuti resmi sekolah.

* **Desentralisasi Penjadwalan Mandiri oleh Tenaga Pendidik**  
  Beban kerja admin sekolah dalam menyusun jadwal ditiadakan. Tutor dan siswa diberikan otonomi penuh untuk menyepakati waktu belajar bersama, lalu Tutor dapat menginput jadwalnya sendiri melalui portal tutor menggunakan jendela input 2-in-1 (pilihan pola rutin mingguan berkelanjutan atau sesi belajar sekali jalan).

* **Mekanisme Reschedule Sesi Terstruktur Berjejak Audit**  
  Ketika sesi belajar berhalangan dan disepakati waktu penggantinya, jadwal lama tidak dihapus agar tidak menghilangkan riwayat operasional. Sesi lama dialihkan statusnya menjadi dibatalkan disertai alasan yang jelas, lalu sistem membuat sesi baru bertipe pengganti yang otomatis terhubung ke jadwal asli dan mengirimkan notifikasi ke siswa.

* **Integrasi Kalender Tiga Tab dan Penyatuan Navigasi**  
  Dua halaman kalender yang sebelumnya terpisah kini dilebur menjadi satu halaman terpadu dengan 3 tab navigasi: Kalender Sesi Belajar Murid, Master Pola Rutin Mengajar, dan Agenda Pengumuman Resmi PKBM. Seluruh tautan lama diarahkan secara otomatis sehingga tidak merusak riwayat peramban pengguna.

---

### 3.6 Portal dan Presensi Mandiri Peserta Didik (Siswa)

* **Presensi Kedatangan Mandiri Siswa (Single Check-In)**  
  Peserta didik kini memiliki hak akses mandiri ke dalam sistem untuk mencatat kedatangan belajar harian melalui swafoto kamera dan verifikasi geofencing sekolah. Alur presensi siswa disederhanakan menjadi satu kali pencatatan masuk tanpa membebani siswa dengan keharusan absen pulang atau jeda waktu tunggu yang membingungkan.

* **Proteksi Jam Presensi Cerdas (Smart Time-Gating)**  
  Formulir kamera absensi siswa dilindungi oleh sistem pengunci waktu otomatis. Siswa tidak dapat mengakses kamera presensi jika hari tersebut bukan jadwal belajarnya atau jika waktu kehadiran dilakukan lebih awal dari 30 menit sebelum sesi dimulai. Halaman dilengkapi hitungan mundur (*countdown timer*) yang menginformasikan sisa waktu sebelum pintu absensi dibuka.

* **Pencatatan Status Keterlambatan dan Toleransi Belajar**  
  Sistem secara otomatis mengevaluasi jam kedatangan siswa terhadap jam rencana KBM dengan batas toleransi 30 menit. Keterlambatan dihitung secara presisi dalam satuan menit, disematkan pada kartu kehadiran siswa secara visual, dan dicatat pada riwayat kehadiran sebagai bahan evaluasi kedisiplinan belajar.

* **Perbaikan Siklus Sesi dan Pencegahan Hitung Ganda Kehadiran**  
  Sistem memperbaiki logika sesi belajar agar siswa tetap dapat melakukan presensi mandiri meskipun tutor telah lebih dahulu melakukan absensi masuk. Selain itu, perhitungan statistik kehadiran bulanan siswa disempurnakan berbasis tanggal unik, sehingga presensi mandiri siswa dan presensi kelas oleh tutor di hari yang sama dihitung tepat sebagai satu hari aktif belajar.

* **Otomasi Akun Siswa dan Fleksibilitas Format Identitas Login**  
  Pembuatan akun login siswa kini berlangsung otomatis saat data profil siswa didaftarkan oleh admin. Sistem login dirancang fleksibel sehingga siswa dapat masuk menggunakan nomor induk (SW001 / sw001), angka absen sederhana, alamat email, maupun nomor identitas kependudukan (NIK).

---

### 3.7 Harmonisasi Sesi Durasi Khusus (Non-SK) Lintas 4 Peran

* **Sinkronisasi Sesi Durasi Khusus untuk Tutor, Siswa, Admin, dan Kepala Sekolah**  
  Untuk mengakomodasi kelas bimbingan kilat (misal 30–45 menit) atau kelas intensif di luar ketentuan durasi SK resmi, sistem menerapkan logika adaptif:
  - *Tutor:* Diberikan peringatan edukatif saat membuat jadwal, serta ambang batas absen pulang yang adaptif (dapat absen pulang setelah 70% durasi tercapai, bukan dipatok kaku minimal 1 jam).
  - *Siswa:* Toleransi keterlambatan disesuaikan secara proporsional (30% dari durasi singkat) agar siswa yang terlambat di sesi kilat tidak keliru tercatat tepat waktu.
  - *Admin:* Sistem menyematkan label transparan khusus pada catatan jadwal untuk kemudahan audit kurikulum.
  - *Kepala Sekolah:* Sesi di luar durasi resmi diberi tanda pengenal khusus pada lembar rekapitulasi gaji dengan penerapan honor flat default SK yang jelas dan bebas perselisihan.

---

### 3.8 Tata Kelola Data Akademik dan Siklus Siswa

* **Pemisahan Entitas Jenjang Pendidikan dan Kelas Rombel**  
  Struktur basis data dinormalisasi dengan memisahkan master jenjang paket (Paket A, Paket B, Paket C, Vokasi) dari tingkatan kelas rombel melalui relasi kunci asing (*Foreign Key*) yang terstandarisasi. Hal ini menjaga integritas data akademik sekolah dan mempermudah penambahan program kesetaraan baru.

* **Pengelolaan Status Siklus Siswa dan Proteksi Anti Kehilangan Data**  
  Data siswa kini dilengkapi penanda status siklus hidup (Aktif, Alumni, Cuti, Non-Aktif) dan fitur penghapusan lunak (*SoftDeletes*). Ketika siswa telah lulus atau keluar, arsip riwayat kehadiran dan catatan belajarnya di masa lalu tetap tersimpan utuh di basis data untuk kebutuhan verifikasi ijazah dan akreditasi sekolah.

* **Deteksi Otomatis Kelas Tutorial Gabungan Rombel**  
  Sistem memiliki kemampuan cerdas untuk menganalisis data siswa dalam suatu sesi belajar. Jika murid yang hadir terdeteksi berasal dari beberapa rombongan belajar yang berbeda, sistem secara otomatis menetapkan skema honor Gabungan Rombel (Rp 50.000,- per rombel) tanpa menuntut pengaturan manual dari tutor.

---

### 3.9 Notifikasi Real-Time dan Pengelolaan Data Massal

* **Infrastruktur Web Push Notification Multi-Role**  
  Aplikasi dilengkapi teknologi notifikasi peramban langsung (*Web Push*) berstandar VAPID yang dapat diterima pada perangkat ponsel pintar seluruh pengguna (Admin, Kepala Sekolah, Tutor, Siswa, dan Magang). Notifikasi dikirimkan secara otomatis saat ada jadwal baru, penundaan sesi (*reschedule*), kehadiran siswa di sekolah, pengajuan permohonan izin, maupun pengumuman slip gaji.

* **Pengingat Presensi Otomatis Berbasis Penjadwal Server (Cron)**  
  Perintah terjadwal disematkan pada server untuk mengirimkan pengingat kehadiran secara otomatis: pengingat presensi pagi hari, peringatan absen pulang bagi yang masih aktif mengajar, serta pengingat persetujuan berkas izin/lupa lapor yang masih tertunda bagi Kepala Sekolah.

* **Fitur Impor dan Ekspor Spreadsheet Excel Terstandarisasi**  
  Pengelolaan data dalam jumlah besar kini dipermudah melalui fasilitas ekspor laporan presensi 14 kolom lengkap beserta metrik kehadiran. Admin sekolah juga dibekali modul impor massal Excel untuk data siswa, data staf, dan jadwal kalender dengan formulir modern yang dilengkapi validasi format dan deteksi ukuran berkas otomatis.

---

### 3.10 Antarmuka Pengguna, Responsivitas Mobile, dan Pengalaman Pengguna

* **Desain Tata Letak Responsif Khusus Perangkat Seluler (Mobile-First)**  
  Tampilan antarmuka pada seluruh modul dirancang ulang agar nyaman digunakan melalui smartphone. Tabel data yang padat pada desktop secara cerdas bertransformasi menjadi kartu data (*mobile cards*) yang rapi dan mudah disentuh jari saat diakses melalui layar ponsel berukuran kecil.

* **Modernisasi Halaman Login Autentikasi**  
  Halaman masuk dirancang dengan konsep *split-card* modern pada layar komputer dan kartu ringkas pada layar ponsel. Halaman dilengkapi tombol intip sandi (*show/hide password*), efek visual fokus pada bidang isian, saklar mode gelap instan, dan integrasi notifikasi pesan mengambang (*toast*) yang bersih.

* **Pusat Pengelolaan Akun Terpadu Satu Pintu**  
  Seluruh akun pengguna dari berbagai peran kini dikelola melalui satu halaman terpadu di menu admin karyawan. Halaman ini memuat ringkasan statistik metrik pengguna, penyaring peran interaktif, tombol reset kata sandi instan (`password123`), serta tautan cepat menuju data profil masing-masing pengguna.

* **Standarisasi Navigasi Halaman (Pagination) Berbahasa Indonesia**  
  Komponen navigasi halaman di seluruh tabel sistem diseragamkan ke dalam bahasa Indonesia dan dioptimalkan agar tidak memicu pergeseran layar mendatar (*horizontal overflow*) pada ponsel pintar.

---

### 3.11 Pengujian Kualitas Sistem dan Fasilitas Uji Cepat

* **Rangkaian Pengujian Otomatis Menyeluruh (Automated Testing Suite)**  
  Seluruh alur bisnis inti sistem dipayungi oleh 142 pengujian fitur otomatis (*Feature Tests*) berbasis PHPUnit yang mencakup validasi lokasi GPS, perhitungan honor SK, siklus sesi KBM, hingga alur presensi mandiri siswa, dengan tingkat kelulusan 100%.

* **Panel Login Cepat Pengujian (Quick Login Panel)**  
  Untuk mempermudah pengujian dan demonstrasi aplikasi tanpa perlu repot mengetik kredensial berulang kali, sistem menyediakan panel uji 5 peran demo dengan tombol masuk satu-klik, rute URL cepat, serta tombol pengalih peran instan pada navigasi atas saat aplikasi berjalan di lingkungan pengujian (*development mode*).

---
*Dokumen ini disusun sebagai instrumen pelaporan dan analisis resmi pengembangan Sistem Presensi Digital PKBM Pintar Berbakat (PKBM Pikat).*

# SOFTWARE REQUIREMENTS SPECIFICATION
## SISTEM INFORMASI PRESENSI DIGITAL DAN MANAJEMEN PEMBELAJARAN BERBASIS WEB DAN MOBILE (PRESENSI PKBM PIKAT)
### Versi 1.0 Resmi Disetujui
### Pusat Kegiatan Belajar Masyarakat (PKBM) Pikat, Kabupaten Tuban, Jawa Timur
### September 2026

---

## Daftar Isi

1. Pendahuluan
   1.1 Tujuan Penulisan Dokumen
   1.2 Konvensi Dokumen
   1.3 Audiens yang Dituju dan Saran Pembacaan
   1.4 Batasan dan Ruang Lingkup Produk
   1.5 Definisi, Singkatan, dan Istilah
   1.6 Dokumen Rujukan
2. Gambaran Umum Sistem
   2.1 Perspektif Produk
   2.2 Fungsi Utama Produk
   2.3 Karakteristik dan Kelas Pengguna
   2.4 Lingkungan Operasional
   2.5 Batasan Desain dan Implementasi
   2.6 Dokumentasi Pengguna
3. Spesifikasi Kebutuhan Antarmuka Eksternal
   3.1 Antarmuka Pengguna
   3.2 Antarmuka Perangkat Keras
   3.3 Antarmuka Perangkat Lunak
   3.4 Antarmuka Komunikasi
4. Fitur dan Kebutuhan Fungsional Sistem
   4.1 Autentikasi Pengguna, Keamanan Sesi, dan Integrasi Single Sign-On SIMPIKAT
   4.2 Presensi Multi-Moda dan Validasi Geofencing Lanjutan bagi Tutor
   4.3 Presensi Mandiri Peserta Didik dan Evaluasi Keterlambatan
   4.4 Presensi Karyawan, Pimpinan Lembaga, dan Mahasiswa Magang
   4.5 Pengelolaan Penjadwalan Sesi Belajar Mengajar, Pola Rutin, dan Penundaan Terstruktur
   4.6 Pengajuan dan Verifikasi Lupa Lapor Kehadiran Retroaktif
   4.7 Pengajuan dan Persetujuan Dispensasi Izin serta Sakit Mandiri
   4.8 Pengelolaan Kategori Tutorial dan Otomasi Penggajian Berdasarkan Surat Keputusan
   4.9 Pengelolaan Data Induk Akademik, Siklus Hidup Siswa, dan Multi-Titik Lokasi Presensi
   4.10 Dasbor Analitik Eksekutif dan Pemantauan Operasional Harian
   4.11 Layanan Notifikasi Web Push Real-Time dan Pengingat Otomatis
   4.12 Pelaporan Presensi, Jejak Audit, dan Ekspor Rekapitulasi Data
5. Kebutuhan Non-Fungsional Lainnya
   5.1 Kebutuhan Kinerja
   5.2 Kebutuhan Keselamatan dan Toleransi Kesalahan
   5.3 Kebutuhan Keamanan dan Privasi Data
   5.4 Atribut Kualitas Perangkat Lunak
   5.5 Aturan Bisnis
6. Kebutuhan Tambahan dan Legalitas
Lampiran A: Glosarium Istilah
Lampiran B: Model Analisis Sistem
Lampiran C: Daftar Keputusan dan Resolusi Teknis

---

## Riwayat Revisi Dokumen

| Penyusun | Tanggal | Deskripsi Perubahan | Versi |
|---|---|---|---|
| Tim Pengembang Sistem Presensi PKBM Pikat | 6 September 2026 | Penyusunan awal spesifikasi kebutuhan perangkat lunak untuk modul presensi foto dan pencatatan kehadiran tutor. | Versi 1.0 Draf |
| Tim Pengembang Sistem Presensi PKBM Pikat | 27 September 2026 | Penyempurnaan menyeluruh dokumen spesifikasi kebutuhan perangkat lunak berstandar IEEE dengan mengintegrasikan arsitektur lima peran, presensi multi-moda, geofencing Haversine, otomasi payroll berbasis Surat Keputusan, penjadwalan desentralisasi, presensi mandiri siswa, Single Sign-On SIMPIKAT, dan aplikasi mobile Capacitor. | Versi 1.0 Final |

---

## 1. Pendahuluan

### 1.1 Tujuan Penulisan Dokumen
Dokumen Software Requirements Specification ini disusun untuk menjabarkan seluruh spesifikasi kebutuhan fungsional dan non-fungsional dalam pengembangan Sistem Informasi Presensi Digital dan Manajemen Pembelajaran PKBM Pikat. Dokumen ini menjadi rujukan legal dan teknis utama dalam mengawal siklus hidup rekayasa perangkat lunak, mulai dari tahap analisis kebutuhan, perancangan arsitektur, implementasi baris kode, hingga pengujian otomatis dan evaluasi akhir.

Dengan tersusunnya dokumen ini secara mendalam, seluruh pemangku kepentingan memiliki pemahaman yang selaras mengenai perilaku sistem yang diharapkan. Dokumen ini juga berfungsi sebagai instrumen audit untuk memastikan bahwa sistem yang dibangun benar-benar menyelesaikan permasalahan operasional pada lembaga pendidikan non-formal PKBM Pikat, khususnya dalam hal transparansi kehadiran, akuntabilitas honorarium pendidik, dan kedisiplinan belajar mengajar.

Bagi tim pengembang dan penguji kualitas perangkat lunak, spesifikasi ini menetapkan kriteria kelayakan rilis yang mengikat. Setiap fitur yang dijelaskan di dalam dokumen ini harus dapat diverifikasi melalui serangkaian pengujian perangkat lunak, baik pengujian unit maupun pengujian fitur secara otomatis.

### 1.2 Konvensi Dokumen
Dokumen ini disusun menggunakan tata bahasa Indonesia formal yang baku dan terstruktur secara deskriptif. Format penulisan menghindari penggunaan tanda visual informal, singkatan yang tidak dibakukan, dan simbol penekanan teks yang berlebihan guna menjaga sifat resmi dokumen rekayasa perangkat lunak.

Setiap pernyataan kebutuhan fungsional diidentifikasi secara unik menggunakan format kode alfanumerik terindeks, yaitu REQ diikuti dengan tiga digit angka numerik mulai dari REQ-001 sampai dengan REQ-054. Pengodean ini bertujuan mempermudah penelusuran balik antara kebutuhan bisnis, modul kode sumber, dan berkas skenario pengujian otomatis.

Tingkat prioritas kebutuhan fungsional diatur secara ketat berdasarkan dampaknya terhadap integritas operasional lembaga. Seluruh kebutuhan fungsional dalam dokumen ini berstatus wajib dipenuhi agar sistem dapat beroperasi secara utuh dan aman pada lingkungan produksi.

### 1.3 Audiens yang Dituju dan Saran Pembacaan
Dokumen ini dirancang untuk dibaca oleh berbagai kalangan dengan peran dan kepentingan yang berbeda. Kelompok pembaca utama meliputi:
1. Tim Pengembang Perangkat Lunak, yang memerlukan panduan detail mengenai logika bisnis, struktur relasi basis data, rumus validasi geolokasi, dan mekanisme integrasi antarmuka peramban maupun aplikasi mobile.
2. Tim Penjamin Mutu Perangkat Lunak, yang membutuhkan tolok ukur pengujian fungsional dan batas toleransi performa sistem untuk menyusun skenario uji otomatis.
3. Pimpinan Lembaga atau Kepala Sekolah, yang berkepentingan memastikan kesesuaian sistem dengan regulasi Surat Keputusan honorarium, tata tertib kerja, dan pengawasan manajerial.
4. Tenaga Pendidik atau Tutor, yang berkepentingan memahami alur pencatatan kehadiran multi-moda, pengelolaan jadwal belajar, dan prosedur pengajuan dispensasi.
5. Staf Administrasi Akademik, yang bertugas mengelola data induk kependidikan dan penerbitan laporan berkala.

Pembaca disarankan membaca Bab 2 terlebih dahulu untuk memahami konteks umum arsitektur sistem, kemudian mendalami Bab 3 dan Bab 4 untuk rincian antarmuka dan kebutuhan fungsional, serta meninjau Bab 5 untuk parameter kualitas non-fungsional dan aturan bisnis yang berlaku.

### 1.4 Batasan dan Ruang Lingkup Produk
Sistem Informasi Presensi Digital PKBM Pikat adalah sebuah ekosistem perangkat lunak terintegrasi yang dirancang khusus untuk melayani kebutuhan operasional Pusat Kegiatan Belajar Masyarakat Pikat yang berlokasi di Kabupaten Tuban, Jawa Timur. Sistem ini mencakup digitalisasi presensi berbasis kamera dan koordinat geografis untuk seluruh civitas akademika, yang terdiri dari tutor, peserta didik kesetaraan Paket A, Paket B, Paket C, peserta pelatihan vokasi, staf administrasi, pimpinan lembaga, serta mahasiswa magang.

Ruang lingkup fungsional produk mencakup perekaman presensi mengajar dalam tiga moda bimbingan belajar, yaitu tatap muka di lingkungan sekolah, kunjungan ke rumah peserta didik, dan pembelajaran jarak jauh berbasis ruang kelas daring. Sistem juga mengelola siklus penjadwalan belajar mandiri terdesentralisasi, alur persetujuan permohonan lupa lapor retroaktif, pengajuan izin berlampiran dokumen, perhitungan honorarium tutor berbasis Surat Keputusan dengan sifat data historis yang kekal, dasbor analitik eksekutif, serta pengiriman notifikasi peramban secara seketika.

Batasan dari sistem ini adalah ketergantungan pada ketersediaan koneksi internet dan sensor perangkat klien seperti kamera dan penentu posisi global. Sistem tidak mencakup pengelolaan nilai rapor akademik peserta didik secara penuh, karena fungsi penilaian akademik dikelola pada portal induk SIMPIKAT yang terhubung melalui mekanisme Single Sign-On.

### 1.5 Definisi, Singkatan, dan Istilah
1. Software Requirements Specification adalah dokumen spesifikasi teknis formal yang menjelaskan seluruh kapabilitas dan batasan sistem perangkat lunak yang akan dikembangkan.
2. Pusat Kegiatan Belajar Masyarakat Pikat adalah satuan pendidikan non-formal di Kabupaten Tuban yang menyelenggarakan pendidikan kesetaraan dan pemberdayaan keterampilan masyarakat.
3. Role-Based Access Control adalah mekanisme pengaturan hak akses pengguna ke menu dan fungsi sistem berdasarkan peran atau jabatan yang dimiliki.
4. Algoritma Haversine adalah persamaan navigasi trigonometri bola yang digunakan untuk menghitung jarak lengkung lingkaran besar antara dua koordinat lintang dan bujur di permukaan bumi.
5. Geofencing adalah perimeter virtual pada area geografis nyata yang ditentukan berdasarkan titik koordinat pusat dan radius batas jarak tertentu.
6. Surat Keputusan Kepala PKBM Pikat adalah ketetapan hukum internal lembaga yang mengatur pedoman tarif honorarium mengajar per sesi pertemuan berdasarkan kategori layanan tutorial.
7. Single Sign-On adalah teknologi autentikasi yang memungkinkan pengguna mengakses berbagai aplikasi terintegrasi hanya dengan satu set kredensial akun.
8. Just-In-Time Provisioning adalah proses pembuatan akun lokal dan profil pendukung secara otomatis pada saat pengguna berhasil login pertama kali melalui penyedia Single Sign-On.
9. Web Push Notification adalah protokol pengiriman pesan singkat dari server web langsung ke antarmuka peramban pengguna secara real-time berdasarkan standar VAPID.
10. Sesi Pembelajaran Menggantung adalah kondisi di mana tutor telah melakukan pencatatan jam masuk mengajar pada hari sebelumnya tetapi belum melakukan pencatatan jam selesai hingga hari berikutnya berganti.

### 1.6 Dokumen Rujukan
Penyusunan dokumen spesifikasi ini mengacu pada sejumlah dokumen standar rekayasa perangkat lunak dan regulasi hukum, antara lain:
1. Standar IEEE 830 tahun 1998 mengenai pedoman penyusunan spesifikasi kebutuhan perangkat lunak.
2. Standar ISO/IEC/IEEE 29148 tahun 2018 mengenai rekayasa sistem dan perangkat lunak dalam proses siklus hidup kebutuhan.
3. Undang-Undang Republik Indonesia Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional, khususnya ketentuan terkait jalur pendidikan non-formal.
4. Surat Keputusan Kepala PKBM Pikat Nomor 014/SK/PKBM-PIKAT/IX/2026 tentang Penetapan Skema Honorarium Tutor dan Standar Waktu Kegiatan Belajar Mengajar.
5. Dokumen Roadmap dan Laporan Analisis Pembaruan Sistem Presensi Digital PKBM Pikat versi 2026.

---

## 2. Gambaran Umum Sistem

### 2.1 Perspektif Produk
Sistem Informasi Presensi Digital PKBM Pikat merupakan platform perangkat lunak generasi terbaru yang dikembangkan untuk menggantikan pencatatan presensi manual dan menyempurnakan sistem terdahulu yang memiliki berbagai keterbatasan arsitektural. Pada sistem terdahulu, perhitungan gaji masih mengandalkan tarif statis per jam yang rawan kesalahan hitung dan tidak mencerminkan kebijakan Surat Keputusan pimpinan lembaga. Selain itu, sistem lama belum memiliki mekanisme penjadwalan mandiri oleh tutor, belum mendukung peran presensi siswa, rentan terhadap manipulasi lokasi palsu, dan masih bergantung pada jaringan distribusi konten pihak ketiga yang rentan gagal muat.

Dalam ekosistem teknologi informasi PKBM Pikat, sistem presensi ini berkedudukan sebagai platform operasional harian yang terhubung langsung dengan portal induk SIMPIKAT. Arsitektur sistem menerapkan pola Model-View-Controller berbasis Framework Laravel 13 yang dipadukan dengan dedicated service layer untuk memisahkan logika kalkulasi jarak geofencing, otomasi penggajian, penjadwalan berulang, dan penyiaran notifikasi web push. Pada sisi klien, sistem dapat diakses secara fleksibel melalui peramban web responsif maupun aplikasi Android mandiri berbasis Capacitor.

Sistem beroperasi di atas lingkungan basis data relasional MySQL yang telah ternormalisasi hingga bentuk normal ketiga. Seluruh berkas dokumentasi foto dan lampiran surat izin dikelola melalui sistem penyimpanan berkas terabstraksi pada direktori privat server dengan tautan pengaksesan yang aman.

### 2.2 Fungsi Utama Produk
Fungsi utama yang disediakan oleh Sistem Informasi Presensi Digital PKBM Pikat terbagi ke dalam tujuh pilar operasional sebagai berikut:

Pilar pertama adalah Pengelolaan Autentikasi dan Akses Pengguna. Sistem menyediakan mekanisme login fleksibel menggunakan email atau Nomor Induk Kependudukan, pembatasan laju percobaan login untuk menangkal serangan siber, integrasi Single Sign-On dengan pembuatan profil instan, serta pemisahan hak akses ke dalam lima tingkatan peran pengguna.

Pilar kedua adalah Perekaman Presensi Multi-Moda. Sistem memfasilitasi pencatatan kehadiran tutor dalam tiga moda pembelajaran yang meliputi tatap muka di sekolah dengan validasi geofence ketat, kunjungan ke rumah siswa dengan perekaman koordinat riil, dan pembelajaran daring dengan pelampiran tautan pertemuan video serta tangkapan layar. Modul ini dilengkapi dengan deteksi pemalsuan sinyal satelit, kontrol kamera peramban tingkat lanjut, dan pembukaan waktu absen pulang cerdas berbasis capaian durasi bimbingan.

Pilar ketiga adalah Presensi Mandiri Peserta Didik. Sistem memberikan fasilitas bagi siswa untuk mencatat kedatangan harian di sekolah secara mandiri melalui swafoto dan validasi radius lokasi sekolah, disertai penguncian kamera sebelum waktu belajar dimulai dan penandaan otomatis status keterlambatan.

Pilar keempat adalah Presensi Karyawan dan Mahasiswa Magang. Sistem menyediakan pencatatan kehadiran kerja harian untuk staf administrasi, pimpinan lembaga dengan kebijakan dispensasi bebas radius saat berdinas luar, serta mahasiswa magang dengan pembatasan radius lokasi seratus meter dari lingkungan sekolah.

Pilar kelima adalah Manajemen Penjadwalan Belajar Mengajar. Sistem menyediakan master jadwal rutin mingguan, pembangkit otomatis jadwal sesi empat pekan ke depan, keleluasaan bagi tutor untuk menyusun jadwal bimbingan mandiri bersama murid, serta alur penundaan jadwal terstruktur yang mencatat alasan perubahan demi transparansi audit.

Pilar keenam adalah Alur Persetujuan dan Dispensasi. Sistem menyediakan modul permohonan lupa lapor presensi retroaktif dan permohonan izin atau sakit mandiri oleh tutor yang dilengkapi bukti berkas, dengan antarmuka peninjauan interaktif bagi Kepala Sekolah untuk memberikan keputusan persetujuan atau penolakan.

Pilar ketujuh adalah Otomasi Penggajian dan Pelaporan Eksekutif. Sistem secara otomatis menghitung nominal honorarium mengajar berdasarkan kategori layanan Surat Keputusan dan deteksi kelas multi-rombel, membekukan nominal honorarium pada setiap transaksi, mencetak slip gaji individual format PDF, menyajikan grafik analitik kehadiran pada dasbor pimpinan, dan mengekspor rekapitulasi data presensi ke format lembar sebar Excel empat belas kolom.

### 2.3 Karakteristik dan Kelas Pengguna
Sistem membagi pengguna ke dalam lima kelas yang memiliki wewenang, tanggung jawab, dan karakteristik operasional yang berbeda:

1. Kelas Administrator
Administrator merupakan staf operasional dan pengelola teknologi informasi lembaga. Pengguna dalam kelas ini bertanggung jawab mengelola seluruh data master organisasi, jenjang paket pendidikan, rombongan belajar, data induk siswa, data akun karyawan, master titik lokasi presensi, master tarif Surat Keputusan, jadwal kerja staf, serta konfigurasi parameter sistem. Administrator memiliki kemampuan teknis menengah hingga tinggi dan mengoperasikan sistem mayoritas menggunakan komputer meja atau laptop pada jam kerja kantor.

2. Kelas Kepala Sekolah
Kepala Sekolah merupakan pimpinan tertinggi lembaga yang memegang otoritas pengawasan dan pengambilan keputusan manajerial. Pengguna dalam kelas ini bertugas memantau grafik kedisiplinan lembaga pada dasbor eksekutif, meneliti dan memberikan persetujuan terhadap pengajuan lupa lapor kehadiran tutor, memvalidasi permohonan izin atau sakit, serta mengevaluasi rekapitulasi penggajian bulanan. Kepala Sekolah memiliki mobilitas tinggi dan mengakses sistem melalui perangkat tablet, telepon pintar, maupun laptop.

3. Kelas Tutor
Tutor merupakan tenaga pendidik profesional yang bertugas menyelenggarakan kegiatan belajar mengajar bersama peserta didik. Pengguna dalam kelas ini bertanggung jawab melakukan presensi masuk dan pulang pada setiap sesi bimbingan belajar, mengatur kesepakatan jadwal belajar mandiri dengan siswa, melakukan penundaan jadwal jika terjadi kendala, mengajukan permohonan lupa lapor atau izin secara tertib, dan memeriksa rincian slip honorarium mengajar. Tutor memiliki kemampuan teknis dasar hingga menengah dan mengakses sistem mayoritas melalui telepon pintar di lokasi bimbingan.

4. Kelas Murid (Peserta Ajar)
Murid merupakan peserta didik program kesetaraan (Paket A, B, C) atau pelatihan vokasi. Pengguna dalam kelas ini bertugas melakukan presensi mandiri saat tiba di sekolah untuk mengikuti bimbingan belajar, melihat jadwal bimbingan harian, dan memantau catatan kehadiran pribadi. Murid merupakan pengguna awam dengan latar belakang usia yang beragam, sehingga antarmuka portal murid dirancang sangat ringkas, intuitif, dan responsif pada layar telepon pintar.

5. Kelas Mahasiswa Magang
Mahasiswa Magang merupakan mahasiswa atau siswa Praktik Kerja Lapangan yang melaksanakan tugas magang di PKBM Pikat. Pengguna dalam kelas ini bertanggung jawab melakukan presensi masuk dan pulang kerja harian di lingkungan sekolah berbasis foto dan geofence lokasi, serta mengunduh dokumen rekapitulasi kehadiran magang untuk keperluan pelaporan institusi pendidikan asalnya. Mahasiswa magang memiliki kemampuan teknis menengah dan mengoperasikan sistem melalui telepon pintar.

### 2.4 Lingkungan Operasional
Sistem Presensi PKBM Pikat dirancang untuk beroperasi secara optimal pada lingkungan terdistribusi berbasis web dan mobile dengan rincian spesifikasi sebagai berikut:

Pada sisi klien, pengguna dapat mengakses aplikasi melalui berbagai peramban web modern yang mendukung standar HTML5, CSS3, JavaScript ES6, Geolocation API, MediaDevices API, dan Service Worker. Peramban yang didukung mencakup Google Chrome versi 100 ke atas, Mozilla Firefox versi 100 ke atas, Microsoft Edge versi 100 ke atas, dan Apple Safari versi 15 ke atas. Untuk pengguna perangkat bergerak Android, sistem dapat dipasang sebagai aplikasi mandiri yang berjalan pada sistem operasi Android versi 8.0 Oreo hingga Android versi 14 ke atas. Perangkat klien wajib memiliki kamera yang berfungsi dan sensor penerima sinyal satelit penentu posisi global.

Pada sisi server, aplikasi dijalankan pada server fisik atau Virtual Private Server dengan sistem operasi Linux Ubuntu versi 22.04 LTS atau 24.04 LTS. Web server yang digunakan adalah Nginx atau Apache yang telah dikonfigurasi dengan sertifikat enkripsi SSL/TLS resmi. Lingkungan eksekusi backend menggunakan PHP runtime versi 8.2 hingga versi 8.5 dengan ekstensi pdo_mysql, mbstring, openssl, gd, zip, intl, dan bcmath. Sistem manajemen basis data menggunakan MySQL versi 8.0 atau MariaDB versi 10.6 dengan mesin penyimpanan InnoDB.

Kebutuhan jaringan mensyaratkan server terhubung dengan jaringan internet publik berkecepatan tinggi dengan alamat IP publik statis. Klien memerlukan koneksi internet seluler minimal generasi 4G atau sambungan nirkabel Wi-Fi untuk melakukan sinkronisasi data presensi dan pengunggahan berkas dokumentasi foto.

### 2.5 Batasan Desain dan Implementasi
Dalam merekayasa perangkat lunak ini, ditetapkan sejumlah batasan arsitektur dan implementasi yang wajib dipatuhi:

Pertama, kerangka kerja backend wajib menggunakan Framework Laravel versi 13 dengan struktur pengkodean yang rapi dan mematuhi standar penulisan kode program PSR-12 melalui pemeriksaan otomatis Laravel Pint. Seluruh logika kalkulasi kompleks wajib ditempatkan pada lapisan service tersendiri untuk mencegah penumpukan logika pada lapisan controller.

Kedua, basis data relasional harus menerapkan batasan kunci asing dengan strategi integritas referensial yang tepat, seperti penggunaan soft deletes pada data master siswa dan kelas guna mencegah hilangnya keterkaitan riwayat kehadiran masa lalu. Nilai nominal honorarium mengajar yang telah terbentuk pada transaksi presensi masa lalu wajib dikunci secara permanen sehingga tidak mengalami perubahan jika terjadi revisi tarif Surat Keputusan di masa depan.

Ketiga, antarmuka sistem dilarang keras bergantung pada jaringan distribusi konten publik pihak ketiga. Seluruh pustaka antarmuka seperti peta digital Leaflet, lembar gaya tema gelap, ikon visual, dan berkas JavaScript pendukung wajib disimpan dan dibundel secara lokal di dalam repositori aplikasi guna menjamin kemandirian infrastruktur sistem.

Keempat, pada aplikasi mobile Android berbasis Capacitor, proses permintaan izin akses kamera dan lokasi geolokasi ke sistem operasi wajib dijalankan secara berurutan dan terisolasi untuk menghindari kegagalan antarmuka WebView native yang dapat menyebabkan aplikasi menutup secara tiba-tiba.

### 2.6 Dokumentasi Pengguna
Untuk menjamin kelancaran transisi operasional dan pemanfaatan sistem secara optimal oleh seluruh kalangan pengguna, sistem dilengkapi dengan materi dokumentasi terpadu:

Sistem menyediakan buku petunjuk manual pengguna dalam format dokumen digital interaktif yang menguraikan prosedur operasional sistem untuk masing-masing peran, mulai dari tata cara masuk sistem, perekaman presensi multi-moda, penanganan lupa lapor, pengelolaan jadwal, hingga pencetakan laporan.

Sistem juga menyediakan kartu panduan cepat yang dirancang dengan ilustrasi visual sederhana untuk membantu peserta didik dan mahasiswa magang dalam melakukan presensi mandiri. Pada antarmuka aplikasi web dan mobile, sistem menyematkan pesan bantuan interaktif dan banner informasi kontekstual yang memandu pengguna saat menghadapi kendala perizinan perangkat atau peringatan batas jarak lokasi.

---

## 3. Spesifikasi Kebutuhan Antarmuka Eksternal

### 3.1 Antarmuka Pengguna
Antarmuka pengguna Sistem Presensi PKBM Pikat dirancang dengan menerapkan prinsip Mobile-First Responsive Design yang mengutamakan kenyamanan pengoperasian pada layar sentuh perangkat bergerak tanpa mengorbankan fungsionalitas pada layar komputer meja. Desain visual mengusung tema pendidikan modern dengan perpaduan warna hijau zamrud sebagai warna identitas lembaga dan palet warna abu-abu netral yang memiliki kontras tinggi sesuai standar aksesibilitas WCAG AAA.

Sistem menyediakan fitur peralihan tema tampilan antara Tema Terang dan Tema Gelap secara instan. Pada tema gelap, sistem menerapkan penataan warna khusus pada seluruh komponen kartu, teks, navigasi, dan ubin peta digital OpenStreetMap menggunakan filter lembar gaya mandiri tanpa memerlukan kunci API eksternal dan tanpa menampilkan tanda air komersial.

Antarmuka modul presensi menyajikan area tangkapan kamera terintegrasi yang responsif. Di atas bidang kamera, disediakan tombol kendali pembalik gambar horizontal untuk kenyamanan swafoto, tombol pengalih kamera depan dan belakang, garis bantu panduan komposisi tiga kali tiga, serta tombol lampu kilat jika perangkat mendukung. Tepat di bawah bidang kamera, sistem menampilkan widget radar jarak yang memvisualisasikan sisa jarak menuju titik lokasi presensi dalam satuan meter, tombol petunjuk arah ke Google Maps, serta peta digital Leaflet yang menampilkan garis lintasan posisi pengguna secara real-time.

Navigasi antarmuka pada perangkat bergerak menggunakan bilah navigasi bawah yang mudah dijangkau dengan ibu jari satu tangan, sedangkan pada layar komputer meja sistem menyajikan bilah sisi navigasi yang dapat dilipat dan diperluas secara dinamis.

### 3.2 Antarmuka Perangkat Keras
Sistem berkomunikasi langsung dengan komponen perangkat keras pada gawai klien melalui antarmuka pemrograman aplikasi standar peramban web modern:

Sistem mengakses sensor kamera melalui MediaDevices API untuk menangkap streaming video langsung dengan resolusi minimal 720p. Citra foto kehadiran diproses dan dikonversi langsung pada elemen kanvas HTML5 sebelum dikirimkan ke server guna mengoptimalkan ukuran berkas tanpa mengurangi ketajaman visual wajah dan bukti kegiatan.

Sistem mengakses modul penentu posisi global pada perangkat melalui Geolocation API dengan mengaktifkan mode akurasi tinggi. Sensor GPS membaca koordinat garis lintang dan bujur serta tingkat ketelitian sinyal dalam satuan meter. Sistem membaca pergerakan posisi pengguna secara kontinu untuk memperbarui garis panduan visual pada peta lokasi.

Sistem memanfaatkan motor getar perangkat keras melalui Vibration API untuk memberikan getaran umpan balik singkat pada saat pengguna berhasil memasuki radius zona aman presensi atau ketika penekanan tombol presensi berhasil diproses oleh server.

### 3.3 Antarmuka Perangkat Lunak
Dalam menyelenggarakan fungsinya, Sistem Presensi PKBM Pikat berinteraksi dengan beberapa komponen dan pustaka perangkat lunak eksternal:

Pada lapisan basis data, sistem berkomunikasi dengan sistem manajemen basis data MySQL menggunakan pustaka koneksi PDO bawaan PHP. Seluruh manipulasi data dijalankan melalui Object Relational Mapping Eloquent Laravel yang dilengkapi dengan mekanisme perlindungan transaksi basis data untuk menjamin integritas data multi-tabel.

Untuk kebutuhan penerbitan dokumen laporan resmi dan slip honorarium, sistem berinteraksi dengan pustaka rendering dokumen DomPDF. Pustaka ini mengonversi tampilan templat Blade menjadi dokumen PDF berstandar cetak dengan tata letak potret untuk slip gaji dan lanskap untuk rekapitulasi presensi lengkap dengan kop surat resmi lembaga.

Untuk kebutuhan pertukaran data massal, sistem berinteraksi dengan pustaka Maatwebsite Excel yang bertugas memproses impor berkas lembar sebar format XLSX untuk data siswa, karyawan, dan jadwal, serta mengekspor data laporan presensi ke dalam berkas Excel terstruktur empat belas kolom.

Untuk layanan notifikasi seketika, sistem terhubung dengan gateway penyedia Web Push Notification standar protokol RFC 8292 menggunakan pasangan kunci kriptografi VAPID. Sistem juga terhubung dengan basis data sekunder SIMPIKAT untuk melakukan verifikasi kredensial pengguna pada alur Single Sign-On.

### 3.4 Antarmuka Komunikasi
Seluruh pertukaran data antara perangkat klien dan server aplikasi diwajibkan menggunakan protokol komunikasi aman Hypertext Transfer Protocol Secure melalui porta standar 443 dengan perlindungan sertifikat enkripsi Transport Layer Security versi 1.2 atau versi 1.3. Hal ini diperlukan untuk menjamin kerahasiaan data kredensial akun, data koordinat lokasi, dan foto presensi dari ancaman penyadapan data pada jaringan publik.

Format pertukaran data pada komunikasi asinkronus menggunakan struktur JavaScript Object Notation yang memuat atribut status keberhasilan, pesan deskriptif, dan payload data terkait. Setiap respons komunikasi menyertakan kode status HTTP semantik yang baku guna memudahkan penanganan kondisi sukses maupun galat pada sisi antarmuka pengguna.

---

## 4. Fitur dan Kebutuhan Fungsional Sistem

### 4.1 Autentikasi Pengguna, Keamanan Sesi, dan Integrasi Single Sign-On SIMPIKAT
#### 4.1.1 Deskripsi Fitur
Fitur ini mengelola seluruh gerbang masuk pengguna ke dalam sistem, menyediakan fleksibilitas masukan identitas ganda menggunakan email atau Nomor Induk Kependudukan, menerapkan pertahanan terhadap serangan brute force, mengarahkan pengguna secara otomatis ke dasbor utama sesuai perannya, serta menghubungkan autentikasi dengan portal induk SIMPIKAT.
#### 4.1.2 Urutan Stimulus dan Respons
1. Pengguna membuka halaman masuk sistem melalui peramban web atau aplikasi mobile.
2. Sistem menyajikan formulir login responsif dengan opsi masukan fleksibel dan fitur tampilkan kata sandi.
3. Pengguna memasukkan alamat email atau Nomor Induk Kependudukan beserta kata sandi, lalu menekan tombol masuk.
4. Sistem memeriksa batas laju permintaan pada alamat IP dan akun terkait. Jika batas terlampaui, sistem menolak permintaan dan mengembalikan respons peringatan pembatasan waktu.
5. Jika batas aman, sistem mencocokkan kredensial dengan basis data lokal. Apabila cocok, sistem meregenerasi identitas sesi pengguna, menghapus catatan percobaan gagal, dan mengarahkan pengguna ke dasbor perannya.
6. Pada alur Single Sign-On SIMPIKAT, sistem menerima token otentikasi dari portal SIMPIKAT, memverifikasi tanda tangan kriptografi, melakukan pembuatan profil akun otomatis jika pengguna baru pertama kali masuk, dan langsung menginisiasi sesi login resmi.
#### 4.1.3 Persyaratan Fungsional
REQ-001: Sistem harus menyediakan formulir masuk yang menerima identifikasi pengguna melalui alamat email terdaftar atau Nomor Induk Kependudukan.
REQ-002: Sistem harus membatasi percobaan masuk maksimal lima kali per menit untuk setiap alamat IP dan kombinasi akun guna mencegah serangan brute force.
REQ-003: Sistem harus mengarahkan pengguna ke antarmuka dasbor yang sesuai dengan perannya, yaitu admin, kepala sekolah, tutor, siswa, atau magang setelah autentikasi berhasil diverifikasi.
REQ-004: Sistem harus menyediakan layanan Single Sign-On yang terintegrasi dengan basis data SIMPIKAT dan mampu melakukan pembuatan data profil pengguna secara otomatis saat login pertama kali.
REQ-005: Sistem harus menyediakan fasilitas Quick Login untuk lima persona akun uji coba pada lingkungan pengembangan perangkat lunak.
REQ-006: Sistem harus menyediakan fasilitas pembaruan data profil dan pergantian kata sandi mandiri bagi pengguna yang sedang aktif.

### 4.2 Presensi Multi-Moda dan Validasi Geofencing Lanjutan bagi Tutor
#### 4.2.1 Deskripsi Fitur
Fitur ini memungkinkan tenaga pendidik mencatat waktu mulai dan selesai mengajar dalam tiga moda bimbingan belajar yang berbeda dengan dukungan verifikasi swafoto, validasi jarak matematis Haversine terhadap multi-titik lokasi presensi, pencegahan manipulasi lokasi palsu, dan evaluasi kelayakan waktu pulang secara cerdas.
#### 4.2.2 Urutan Stimulus dan Respons
1. Tutor membuka menu presensi mengajar pada aplikasi.
2. Sistem meminta izin akses kamera dan lokasi secara berurutan pada peramban klien.
3. Tutor memilih jadwal sesi bimbingan belajar yang akan diajar dan memilih moda pembelajaran yang sesuai.
4. Pada moda sekolah, tutor memilih titik lokasi presensi yang dituju. Peta Leaflet menampilkan posisi tutor dan batas radius lokasi.
5. Tutor mengambil swafoto bersama peserta didik menggunakan fitur kamera dan menekan tombol mulai mengajar.
6. Sistem memverifikasi koordinat terhadap radius lokasi dan memeriksa integritas sinyal GPS. Jika valid, sistem menyimpan data presensi dengan status berlangsung dan menampilkan hitung mundur waktu pulang.
7. Setelah durasi bimbingan memenuhi syarat kelayakan pulang, formulir absen pulang terbuka. Tutor mengambil foto penyelesaian sesi dan mengirimkan presensi akhir. Sistem mencatat waktu selesai dan mengubah status presensi menjadi hadir.
#### 4.2.3 Persyaratan Fungsional
REQ-007: Sistem harus mendukung pencatatan presensi mengajar dalam tiga moda pembelajaran yaitu tatap muka di sekolah, kunjungan ke rumah siswa, dan pembelajaran daring.
REQ-008: Sistem harus memvalidasi jarak posisi pengguna ke titik lokasi presensi yang dipilih menggunakan algoritma Haversine dan menolak pengajuan jika berada di luar batas radius toleransi.
REQ-009: Sistem harus mendeteksi dan menolak presensi yang menggunakan aplikasi pemalsu lokasi atau memiliki akurasi sinyal satelit lebih buruk dari dua ratus meter.
REQ-010: Sistem harus menyediakan kontrol kamera peramban yang meliputi pembalik horizontal, pengalih kamera depan dan belakang, garis bantu komposisi tiga kali tiga, dan lampu kilat.
REQ-011: Sistem harus menerapkan aturan kelayakan absen pulang cerdas di mana formulir kepulangan dibuka saat waktu mencapai sepuluh menit sebelum jadwal selesai atau tutor telah mengajar minimal delapan puluh persen dari durasi rencana bimbingan.
REQ-012: Sistem harus mendeteksi sesi mengajar lampau yang belum diselesaikan tanpa memblokir presensi hari baru dan menyajikan banner pengajuan lupa lapor satu klik.

### 4.3 Presensi Mandiri Murid (Peserta Didik) dan Evaluasi Keterlambatan
#### 4.3.1 Deskripsi Fitur
Fitur ini diperuntukkan bagi murid bimbingan belajar untuk mencatat kedatangan harian di sekolah secara mandiri menggunakan swafoto dan validasi geofence sekolah, disertai mekanisme penguncian waktu sebelum KBM dimulai dan pencatatan otomatis status keterlambatan.
#### 4.3.2 Urutan Stimulus dan Respons
1. Murid masuk ke akun sistem dan membuka menu presensi mandiri.
2. Sistem memeriksa jadwal bimbingan belajar murid pada hari tersebut.
3. Jika waktu saat ini lebih awal dari tiga puluh menit sebelum jam belajar dimulai, sistem mengunci kamera dan menampilkan hitung mundur pembukaan waktu presensi.
4. Saat jendela presensi telah terbuka, murid mengaktifkan kamera, mengambil swafoto di lingkungan sekolah, dan menekan tombol kirim kehadiran.
5. Sistem memverifikasi posisi koordinat terhadap radius sekolah. Jika waktu presensi melewati tiga puluh menit setelah jam belajar dimulai, sistem secara otomatis menetapkan status terlambat dan mencatat menit keterlambatan.
6. Sistem menyimpan data kehadiran harian dan memperbarui statistik kehadiran pada dasbor murid.
#### 4.3.3 Persyaratan Fungsional
REQ-013: Sistem harus menerapkan mekanisme satu kali pencatatan presensi kedatangan per hari bagi murid tanpa mewajibkan presensi pulang.
REQ-014: Sistem harus mengunci formulir presensi murid sebelum tiga puluh menit dari jadwal kegiatan belajar mengajar dimulai.
REQ-015: Sistem harus secara otomatis menetapkan status terlambat dan mencatat jumlah menit keterlambatan apabila murid melakukan presensi lebih dari tiga puluh menit setelah jam mulai belajar.
REQ-016: Sistem harus menghitung statistik kehadiran murid berdasarkan tanggal unik kehadiran guna mencegah perhitungan ganda saat tutor dan murid sama-sama mencatat kehadiran.
REQ-017: Sistem harus menampilkan jadwal belajar harian, riwayat kehadiran, dan status keterlambatan pada dasbor murid secara ringkas.

### 4.4 Presensi Karyawan, Pimpinan Lembaga, dan Mahasiswa Magang
#### 4.4.1 Deskripsi Fitur
Fitur ini mencatat kehadiran harian masuk dan pulang bagi staf tenaga kependidikan, pimpinan lembaga, dan mahasiswa magang dengan kebijakan operasional khusus yang mendukung fleksibilitas penugasan luar.
#### 4.4.2 Urutan Stimulus dan Respons
1. Pengguna membuka formulir presensi kerja pada menu masing-masing.
2. Pengguna mengambil foto masuk dan sistem memverifikasi posisi koordinat lokasi.
3. Khusus untuk peran admin dan kepala sekolah yang sedang melaksanakan dinas luar, sistem memberikan dispensasi bebas batas radius dengan tetap merekam titik koordinat GPS aktual di basis data.
4. Untuk mahasiswa magang, sistem memvalidasi keberadaan pengguna dalam radius seratus meter dari titik resmi PKBM Pikat.
5. Saat jam kerja berakhir, pengguna mengambil foto kepulangan. Khusus admin dan kepala sekolah, sistem memberlakukan bypass waktu tunggu sehingga dapat melakukan presensi pulang sewaktu-waktu.
#### 4.4.3 Persyaratan Fungsional
REQ-018: Sistem harus menyediakan formulir presensi masuk dan pulang kerja harian berbasis foto kamera dan koordinat lokasi untuk karyawan dan pimpinan.
REQ-019: Sistem harus memberikan dispensasi bebas batas radius bagi peran admin dan kepala sekolah saat melaksanakan dinas luar dengan tetap mencatat koordinat lokasi aktual.
REQ-020: Sistem harus membebaskan peran admin dan kepala sekolah dari waktu tunggu minimal kepulangan kerja.
REQ-021: Sistem harus menyediakan portal presensi khusus bagi mahasiswa magang dengan pembatasan radius lokasi seratus meter dari titik sekolah dan fasilitas unduh rekap kehadiran PDF.

### 4.5 Pengelolaan Penjadwalan Sesi Belajar Mengajar, Pola Rutin, dan Penundaan Terstruktur
#### 4.5.1 Deskripsi Fitur
Fitur ini mengelola seluruh kegiatan belajar mengajar secara terdesentralisasi, memadukan pola jadwal rutin mingguan terstruktur, pembuatan sesi mandiri oleh tutor, serta penundaan jadwal bimbingan dengan catatan riwayat audit yang lengkap.
#### 4.5.2 Urutan Stimulus dan Respons
1. Administrator atau tutor menetapkan pola jadwal rutin mingguan pada sistem dengan menentukan hari, jam bimbingan, tutor pengajar, siswa, dan kategori layanan tutorial.
2. Sistem secara otomatis menjalankan generator berkala untuk membangkitkan jadwal sesi bimbingan selama empat pekan ke depan.
3. Tutor dapat menambahkan jadwal sesi tunggal secara mandiri di luar jadwal rutin melalui antarmuka jadwal sesi.
4. Jika sesi pembelajaran tertunda, tutor menjalankan alur penundaan dengan memasukkan tanggal dan jam baru serta alasan penundaan.
5. Sistem mengubah status sesi lama menjadi dibatalkan disertai alasan tertulis, menerbitkan entitas sesi pengganti baru, dan mengunci sesi yang telah memiliki catatan presensi hadir dari upaya perubahan.
#### 4.5.3 Persyaratan Fungsional
REQ-022: Sistem harus menyediakan master data jadwal rutin untuk menyimpan pola kegiatan belajar mengajar berulang setiap pekan.
REQ-023: Sistem harus menyediakan fungsi pembangkit jadwal otomatis yang mampu menerbitkan jadwal sesi belajar untuk empat pekan ke depan secara berkala.
REQ-024: Sistem harus memberikan kewenangan kepada tutor untuk membuat dan mengelola jadwal sesi belajar secara mandiri.
REQ-025: Sistem harus mendukung alur penundaan jadwal terstruktur yang membatalkan sesi lama berlandaskan alasan tertulis, menerbitkan sesi pengganti, dan mengunci sesi yang telah memiliki catatan presensi hadir.
REQ-026: Sistem harus menyediakan tampilan kalender terpadu yang memadukan jadwal sesi bimbingan belajar dan agenda kegiatan umum lembaga.

### 4.6 Pengajuan dan Verifikasi Lupa Lapor Kehadiran Retroaktif
#### 4.6.1 Deskripsi Fitur
Fitur ini menyediakan mekanisme pelaporan kehadiran susulan bagi tutor atau karyawan yang terkendala melakukan presensi langsung pada hari bimbingan berlangsung, dengan proses verifikasi resmi oleh Kepala Sekolah.
#### 4.6.2 Urutan Stimulus dan Respons
1. Tutor membuka formulir lupa lapor, memilih tanggal lampau, memilih siswa yang diajar, mengisi jam mulai dan jam selesai bimbingan, mengunggah foto bukti pembelajaran, serta menuliskan alasan pengajuan secara rinci.
2. Pengajuan tersimpan dengan status pending dan sistem mengirimkan notifikasi seketika kepada Kepala Sekolah.
3. Kepala Sekolah membuka panel verifikasi lupa lapor, menelaah rincian data dan foto bukti yang diajukan, lalu memilih opsi setujui atau tolak disertai catatan evaluasi.
4. Jika disetujui, sistem secara otomatis membuat catatan kehadiran resmi pada tabel presensi, mengubah status jadwal sesi belajar terkait menjadi selesai, dan memperhitungkan honorarium sesi tersebut ke dalam rekapitulasi penggajian.
#### 4.6.3 Persyaratan Fungsional
REQ-027: Sistem harus menyediakan formulir pengajuan lupa lapor untuk tanggal yang telah lewat dengan validasi kelayakan data yang ketat.
REQ-028: Sistem harus menyediakan antarmuka bagi Kepala Sekolah untuk menyetujui atau menolak permohonan lupa lapor secara interaktif disertai catatan resmi.
REQ-029: Sistem harus secara otomatis membuat atau memperbarui catatan kehadiran resmi pada tabel presensi dan memperbarui status jadwal sesi menjadi selesai saat pengajuan disetujui.

### 4.7 Pengajuan dan Persetujuan Dispensasi Izin serta Sakit Mandiri
#### 4.7.1 Deskripsi Fitur
Fitur ini memfasilitasi permohonan dispensasi ketidakhadiran mengajar atau bekerja bagi tenaga pendidik secara digital dengan kewajiban melampirkan berkas bukti pendukung resmi.
#### 4.7.2 Urutan Stimulus dan Respons
1. Tutor membuka menu pengajuan izin, memilih jenis dispensasi izin atau sakit, menentukan rentang tanggal berhalangan, memilih daftar siswa yang terdampak, menuliskan keterangan alasan, dan mengunggah berkas surat dokter atau surat keterangan resmi.
2. Permohonan masuk ke antarmuka evaluasi Kepala Sekolah dengan status pending.
3. Kepala Sekolah meninjau dokumen lampiran dan memberikan keputusan persetujuan atau penolakan.
4. Jika disetujui, sistem mencatat status dispensasi pada rekapitulasi kehadiran dan secara otomatis membatalkan jadwal kegiatan belajar mengajar reguler pada rentang tanggal tersebut.
#### 4.7.3 Persyaratan Fungsional
REQ-030: Sistem harus menyediakan formulir pengajuan izin dan sakit mandiri bagi tutor dengan fasilitas unggah berkas surat keterangan pendukung.
REQ-031: Sistem harus menyediakan modul evaluasi bagi Kepala Sekolah untuk menyetujui atau menolak permohonan izin.
REQ-032: Sistem harus menyinkronkan data permohonan izin yang disetujui ke dalam rekapitulasi presensi dan membatalkan jadwal kegiatan belajar mengajar reguler pada tanggal tersebut.

### 4.8 Pengelolaan Kategori Tutorial dan Otomasi Penggajian Berdasarkan Surat Keputusan
#### 4.8.1 Deskripsi Fitur
Fitur ini mengotomatisasi perhitungan honorarium mengajar tutor berdasarkan ketentuan resmi Surat Keputusan Kepala PKBM Pikat, mendukung pengenalan sesi gabungan multi-rombel, dan menjamin sifat kekal pada data transaksi keuangan masa lalu.
#### 4.8.2 Urutan Stimulus dan Respons
1. Administrator mengelola master kategori tutorial dan menetapkan tarif honorarium per sesi pertemuan sesuai Surat Keputusan.
2. Setiap kali sesi bimbingan belajar diselesaikan dan terverifikasi hadir, mesin penggajian sistem mencocokkan kategori layanan, menghitung durasi bimbingan, dan memeriksa keterlibatan siswa dari rombongan belajar yang berbeda.
3. Sistem menghitung total honorarium dan membekukan nilai nominal tersebut pada baris data transaksi presensi.
4. Administrator dan Kepala Sekolah dapat melihat rekapitulasi gaji bulanan, mencetak slip honorarium individual format PDF dengan kop resmi lembaga, atau mengekspor rekapitulasi seluruh tutor ke dalam berkas Excel.
#### 4.8.3 Persyaratan Fungsional
REQ-033: Sistem harus mengelola master data kategori tutorial dan tarif honorarium sesuai Surat Keputusan Kepala PKBM Pikat untuk minimal enam jenis layanan bimbingan.
REQ-034: Sistem harus mendeteksi secara otomatis sesi tutorial gabungan komunitas yang melibatkan siswa dari lebih dari satu rombongan belajar dan menerapkan perhitungan tarif kelipatan rombel.
REQ-035: Sistem harus membekukan nominal honorarium pada setiap transaksi presensi yang selesai agar data keuangan historis bersifat kekal dan tidak berubah saat tarif master diperbarui.
REQ-036: Sistem harus menyediakan fitur cetak slip gaji tutor dalam format PDF resmi dengan rincian sesi mengajar dan pengesahan lembaga.
REQ-037: Sistem harus menyediakan fitur ekspor rekapitulasi penggajian bulanan ke dalam format Excel dan PDF.

### 4.9 Pengelolaan Data Induk Akademik, Siklus Hidup Siswa, dan Multi-Titik Lokasi Presensi
#### 4.9.1 Deskripsi Fitur
Fitur ini mengelola struktur data master organisasi yang mencakup jenjang paket pendidikan kesetaraan, rombongan belajar kelas, data induk peserta didik beserta siklus hidup status belajarnya, serta sebaran titik lokasi presensi fisik.
#### 4.9.2 Persyaratan Fungsional
REQ-038: Sistem harus menyediakan master data jenjang paket yang terelasi secara terstruktur dengan data kelas atau rombongan belajar.
REQ-039: Sistem harus mengelola data induk siswa beserta status siklus hidupnya yang meliputi aktif, alumni, cuti, atau nonaktif, serta relasi tutor pembimbing.
REQ-040: Sistem harus menyediakan master data lokasi presensi yang memungkinkan pengelolaan titik kampus pusat, cabang, atau mitra belajar dengan koordinat dan radius dinamis.
REQ-041: Sistem harus menyediakan fasilitas impor massal dan ekspor data siswa, karyawan, dan jadwal menggunakan format berkas spreadsheet Excel.
REQ-042: Sistem harus menyediakan fasilitas pengaturan ulang kata sandi instan untuk akun siswa dan karyawan oleh administrator.

### 4.10 Dasbor Analitik Eksekutif dan Pemantauan Operasional Harian
#### 4.10.1 Deskripsi Fitur
Fitur ini menyajikan visualisasi data kinerja kehadiran, tingkat kedisiplinan belajar mengajar, dan indikator operasional lembaga secara grafis bagi pimpinan dan pengelola lembaga.
#### 4.10.2 Persyaratan Fungsional
REQ-043: Sistem harus menampilkan statistik kehadiran harian, jumlah izin, sakit, sesi aktif, dan permohonan yang menunggu persetujuan pada dasbor utama.
REQ-044: Sistem harus menampilkan grafik tren kehadiran enam bulan terakhir berbasis visualisasi diagram garis pada dasbor pimpinan.
REQ-045: Sistem harus menyajikan papan peringkat kedisiplinan tutor yang memuat metrik ketepatan waktu dan akumulasi jam mengajar bulanan.
REQ-046: Sistem harus menyediakan tabel pemantauan langsung presensi harian tutor dan siswa yang diperbarui secara sistematis.

### 4.11 Layanan Notifikasi Web Push Real-Time dan Pengingat Otomatis
#### 4.11.1 Deskripsi Fitur
Fitur ini mengirimkan notifikasi seketika ke peramban dan aplikasi gawai pengguna untuk mempercepat alur kerja persetujuan dan mengingatkan jadwal kegiatan belajar mengajar.
#### 4.11.2 Persyaratan Fungsional
REQ-047: Sistem harus mendukung pendaftaran langganan Web Push Notification berbasis standar VAPID pada peramban web dan aplikasi mobile.
REQ-048: Sistem harus mengirimkan notifikasi seketika kepada Kepala Sekolah saat terdapat pengajuan baru lupa lapor atau izin.
REQ-049: Sistem harus mengirimkan notifikasi seketika kepada tutor dan siswa saat terjadi penundaan atau perubahan jadwal belajar mengajar.
REQ-050: Sistem harus mendukung pengiriman notifikasi pengingat presensi otomatis kepada pengguna sebelum jam kegiatan belajar dimulai.

### 4.12 Pelaporan Presensi, Jejak Audit, dan Ekspor Rekapitulasi Data
#### 4.12.1 Deskripsi Fitur
Fitur ini menyediakan fasilitas pencarian data presensi terpadu, pembuatan berkas laporan pertanggungjawaban operasional, dan pencatatan riwayat perubahan data krusial untuk keperluan audit lembaga.
#### 4.12.2 Persyaratan Fungsional
REQ-051: Sistem harus menyediakan tabel laporan presensi dengan filter terpadu berdasarkan rentang tanggal, tutor, siswa, moda pembelajaran, status, dan kelas.
REQ-052: Sistem harus menyediakan fitur ekspor laporan presensi ke dalam format Excel empat belas kolom yang memuat data waktu presensi, durasi, identitas pengajar, murid, kelas, koordinat GPS, status verifikasi, dan nominal honorarium.
REQ-053: Sistem harus menyediakan fitur ekspor laporan presensi ke dalam format PDF dokumen resmi bertata letak lanskap dengan kop surat resmi PKBM Pikat.
REQ-054: Sistem harus mencatat seluruh riwayat pembatalan jadwal, perubahan sesi belajar, dan persetujuan lupa lapor ke dalam tabel log audit yang tidak dapat dihapus.

---

## 5. Kebutuhan Non-Fungsional Lainnya

### 5.1 Kebutuhan Kinerja
Sistem ditargetkan mampu melayani permintaan halaman web dan merespons setiap transaksi presensi dalam waktu maksimal dua detik pada kondisi lalu lintas normal dengan beban hingga seratus pengguna aktif bersamaan. Waktu kalkulasi jarak geofencing matematis dan evaluasi aturan kelayakan kepulangan pada lapisan backend ditargetkan selesai dalam waktu di bawah dua ratus milidetik per permintaan.

Ukuran total berkas aset statis yang dimuat pada saat pertama kali membuka halaman tidak boleh melebihi dua koma lima megabita. Seluruh pemanggilan relasi data antar-tabel pada lapisan backend diwajibkan menerapkan teknik eager loading guna mengeliminasi penurunan performa yang disebabkan oleh masalah kueri berulang ke basis data.

### 5.2 Kebutuhan Keselamatan dan Toleransi Kesalahan
Sistem wajib menerapkan mekanisme penanganan sesi presensi menggantung yang toleran terhadap kesalahan manusia. Apabila tutor lupa melakukan pencatatan jam selesai mengajar pada hari sebelumnya, sistem tidak boleh memblokir pencatatan presensi pada hari baru, melainkan menampilkan banner peringatan edukatif yang memandu tutor untuk mengajukan permohonan lupa lapor satu klik.

Basis data utama wajib dicadangkan secara otomatis setiap hari pada pukul dua dini hari Waktu Indonesia Barat dan disimpan pada direktori penyimpanan terpisah dengan retensi pencadangan minimal tiga puluh hari kalender. Pada situasi peramban kehilangan koneksi internet saat presensi berlangsung, antarmuka Progressive Web App harus mampu menyimpan antrean data presensi sementara pada basis data lokal perangkat dan menyinkronkannya kembali saat koneksi pulih.

### 5.3 Kebutuhan Keamanan dan Privasi Data
Seluruh kata sandi pengguna wajib dienkripsi menggunakan algoritma hashing satu arah Bcrypt dengan faktor kerja minimal dua belas atau algoritma Argon2id. Seluruh transmisi data antara perangkat klien dan server aplikasi wajib dilindungi dengan protokol komunikasi aman Hypertext Transfer Protocol Secure dan enkripsi Transport Layer Security versi 1.3.

Aplikasi wajib menerapkan mekanisme perlindungan bawaan terhadap ancaman siber yang meliputi pemalsuan permintaan lintas situs menggunakan token CSRF, serangan injeksi skrip lintas situs melalui pembersihan otomatis pada mesin templat Blade, serta serangan injeksi Structured Query Language melalui penggunaan prepared statements pada lapisan ORM. Seluruh berkas dokumentasi foto presensi dan surat izin disimpan pada direktori terisolasi dan hanya dapat diakses melalui fungsi pengakses resmi yang memverifikasi kepemilikan sesi pengguna.

### 5.4 Atribut Kualitas Perangkat Lunak
Sistem harus menjamin tingkat ketersediaan layanan minimal sembilan puluh sembilan koma lima persen selama jam operasional kegiatan belajar mengajar antara pukul tujuh pagi hingga sembilan malam Waktu Indonesia Barat. Antarmuka pengguna harus memenuhi standar kegunaan yang tinggi, ergonomis dioperasikan satu tangan pada layar ponsel pintar, dan mendukung keterbacaan yang jelas pada tema terang maupun gelap.

Seluruh baris kode sumber backend wajib mematuhi standar penulisan program PHP PSR-12 dan terverifikasi secara berkala melalui perkakas Laravel Pint. Sistem harus memiliki portabilitas yang tinggi sehingga dapat diakses secara konsisten pada berbagai peramban web modern dan dapat dikompilasi menjadi paket aplikasi Android mandiri siap pasang.

### 5.5 Aturan Bisnis
Aturan bisnis yang mengikat operasional sistem meliputi ketentuan sebagai berikut:
1. Tenaga pendidik hanya berhak mencatat presensi mengajar pada sesi bimbingan belajar yang terdaftar secara sah pada jadwal kegiatan belajar mengajar hari terkait.
2. Formulir presensi pulang tutor hanya dapat dikirim jika telah memenuhi ketentuan kelayakan waktu kepulangan hibrida, yaitu telah mencapai sepuluh menit sebelum jadwal berakhir atau telah menyelesaikan minimal delapan puluh persen durasi bimbingan efektif.
3. Pencairan honorarium mengajar hanya diproses untuk sesi bimbingan belajar yang memiliki status presensi hadir terverifikasi atau melalui permohonan lupa lapor yang telah disetujui secara resmi oleh Kepala Sekolah.
4. Peserta didik hanya diperbolehkan melakukan pencatatan presensi mandiri kedatangan di sekolah satu kali per hari dalam batas radius lokasi dan jendela waktu yang telah ditetapkan.
5. Kewenangan mutlak pemberian persetujuan atau penolakan permohonan dispensasi lupa lapor dan izin berada sepenuhnya pada peran Kepala Sekolah.

---

## 6. Kebutuhan Tambahan dan Legalitas

Sistem Informasi Presensi Digital PKBM Pikat wajib mematuhi seluruh ketentuan perundang-undangan mengenai perlindungan data pribadi dan transaksi elektronik yang berlaku di wilayah Republik Indonesia. Seluruh struktur data induk peserta didik, rombongan belajar, jenjang pendidikan kesetaraan, dan rekapitulasi kehadiran diselaraskan dengan standar pendataan pendidikan non-formal Kementerian Pendidikan Dasar dan Menengah Republik Indonesia guna menjamin keabsahan pelaporan dan kemudahan audit berkala oleh instansi pembina pendidikan.

---

## Lampiran A: Glosarium Istilah

1. Application Programming Interface adalah sekumpulan protokol dan alat bantu perangkat lunak yang memungkinkan dua aplikasi saling bertukar data dan fungsionalitas secara terstandarisasi.
2. Cross-Origin Resource Sharing adalah mekanisme keamanan berbasis header HTTP yang mengatur perizinan akses sumber daya web dari domain yang berbeda.
3. Database Management System adalah perangkat lunak sistem yang bertugas mengorganisasikan, menyimpan, dan mengamankan data relasional dalam tabel-tabel terstruktur.
4. Eager Loading adalah metode pengambilan data relasional dari basis data secara sekaligus di awal guna meminimalkan frekuensi eksekusi kueri ke server basis data.
5. Hypertext Transfer Protocol Secure adalah protokol komunikasi data web yang dilengkapi dengan lapisan enkripsi kriptografi untuk mengamankan pertukaran data dari ancaman intersepsi.
6. JSON Web Token adalah format standar terbuka berbasis JSON yang ringkas dan mandiri untuk mentransmisikan klaim identitas keamanan secara aman antar-pihak.
7. Model View Controller adalah pola arsitektur rekayasa perangkat lunak yang memisahkan logika pengelolaan data, logika presentasi antarmuka, dan logika penerimaan alur permintaan pengguna.
8. Progressive Web App adalah teknologi pengembangan aplikasi web yang memanfaatkan service worker dan manifest untuk memberikan kapabilitas setara aplikasi native pada peramban web.
9. Structured Query Language adalah bahasa pemrograman deklaratif standar yang digunakan untuk mengelola dan memanipulasi data pada basis data relasional.
10. Voluntary Application Server Identification adalah standar kriptografi terbuka yang digunakan untuk mengidentifikasi server pengirim notifikasi push ke agen peramban web.

---

## Lampiran B: Model Analisis Sistem

### B.1 Model Alur Proses Bisnis Presensi Tutor
Alur presensi tutor dimulai ketika tutor membuka menu presensi dan peramban meminta izin akses sensor kamera serta geolokasi perangkat. Sistem melakukan perhitungan jarak posisi koordinat tutor terhadap titik lokasi bimbingan yang dipilih menggunakan algoritma Haversine. Jika tutor berada di dalam perimeter radius yang sah dan perangkat tidak terdeteksi menggunakan aplikasi pemalsu lokasi, tutor mengambil swafoto bersama murid dan mengirimkan presensi masuk. Sistem mencatat waktu mulai, menyimpan berkas foto ke media penyimpanan terkelola, dan mengaktifkan hitung mundur kepulangan. Setelah durasi bimbingan mencapai jam rencana selesai atau melampaui delapan puluh persen durasi efektif, formulir absen pulang terbuka. Tutor mengambil foto penyelesaian pembelajaran dan mengirimkan presensi akhir. Sistem mencatat waktu kepulangan, mengubah status presensi menjadi hadir, dan membekukan nominal honorarium Surat Keputusan ke dalam basis data.

### B.2 Model Alur Pengajuan dan Persetujuan Lupa Lapor
Dalam kondisi tutor tidak dapat melakukan presensi secara langsung saat kegiatan belajar mengajar berlangsung, tutor dapat memanfaatkan formulir pengajuan lupa lapor. Tutor memilih tanggal kegiatan lampau, memilih nama siswa yang diajar, menginputkan jam mulai dan jam selesai belajar, mengunggah foto bukti kegiatan, serta menuliskan alasan pengajuan secara lengkap. Pengajuan tersimpan pada sistem dengan status pending dan memicu notifikasi seketika ke akun Kepala Sekolah. Kepala Sekolah membuka panel verifikasi pimpinan untuk meninjau berkas permohonan. Jika Kepala Sekolah menyetujui permohonan, sistem secara otomatis menerbitkan data presensi resmi hadir pada tabel presensi, memperbarui status jadwal sesi belajar menjadi selesai, dan memperhitungkan nominal honorarium sesi tersebut ke dalam rekapitulasi penggajian bulanan tutor.

### B.3 Model Relasi Data Entitas
Sistem mengelola struktur relasi data yang menghubungkan tabel pengguna dengan tabel profil spesifik untuk peran siswa dan magang. Tabel jenjang paket terhubung secara hierarkis dengan tabel kelas dan tabel siswa. Tabel kategori tutorial memuat master tarif honorarium Surat Keputusan yang terhubung dengan tabel jadwal rutin dan tabel jadwal sesi. Tabel lokasi presensi menampung data multi-titik koordinat dan radius yang terhubung dengan jadwal bimbingan dan transaksi presensi. Tabel presensi merekam seluruh peristiwa kehadiran tutor dan terhubung langsung dengan tabel pengguna, siswa, jadwal sesi, dan lokasi presensi. Tabel pengajuan lupa lapor dan pengajuan izin mencatat riwayat permohonan dispensasi yang terhubung dengan data pengguna pemohon dan data otorisasi Kepala Sekolah.

---

## Lampiran C: Daftar Keputusan dan Resolusi Teknis

1. Infrastruktur Peta Digital Mandiri: Sistem menetapkan keputusan untuk meniadakan seluruh ketergantungan pada jaringan distribusi konten publik dengan memasang pustaka Leaflet secara lokal dan menerapkan filter lembar gaya mandiri pada ubin OpenStreetMap saat tema gelap aktif, sehingga peta digital dapat beroperasi stabil tanpa memerlukan kunci API berbayar.
2. Aturan Kelayakan Kepulangan Hibrida Cerdas: Sistem menggantikan aturan kaku minimal satu jam dengan model kelayakan hibrida cerdas yang membuka formulir kepulangan saat waktu mencapai sepuluh menit sebelum jadwal berakhir atau tutor telah mengajar minimal delapan puluh persen durasi bimbingan efektif, serta memberlakukan bypass instan bagi peran admin dan kepala sekolah.
3. Struktur Honorarium Berdasarkan Surat Keputusan Resmi: Sistem menggantikan skema tarif statis per jam pada tabel siswa dengan master tabel kategori tutorial yang memuat enam jenis layanan bimbingan resmi, menyematkan deteksi otomatis multi-rombel, dan menerapkan pembekuan nominal tarif pada setiap baris data transaksi kehadiran.
4. Manajemen Izin Sekuensial Aplikasi Android: Sistem mengatasi potensi kegagalan aplikasi Android berbasis Capacitor dengan menginisialisasi perizinan native saat aplikasi pertama kali dibuka dan mengeksekusi permintaan izin kamera dan geolokasi web secara sekuensial guna mencegah tabrakan antarmuka pada peramban native.
5. Arsitektur Single Sign-On SIMPIKAT Terpadu: Sistem menerapkan integrasi basis data sekunder dengan portal induk SIMPIKAT yang dilengkapi dengan mekanisme pembuatan profil otomatis secara langsung saat pengguna berhasil melakukan autentikasi lintas sistem.

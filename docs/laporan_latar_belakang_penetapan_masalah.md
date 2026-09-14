# LAPORAN PRAKTIK KERJA LAPANGAN (PKL)
## PENGEMBANGAN DAN PERBAIKAN SISTEM PRESENSI DIGITAL
## PKBM PIKAT BERBASIS FRAMEWORK LARAVEL

---

**Program Studi :**  *(Diisi sesuai prodi mahasiswa)*
**Nama Mahasiswa :**  *(Diisi nama mahasiswa)*
**NIM :**  *(Diisi NIM)*
**Tempat PKL :**  PKBM Pikat
**Periode PKL :**  *(Diisi periode PKL)*

---

---

# BAB I  
# PENDAHULUAN

---

## 1.1 Latar Belakang

Perkembangan teknologi informasi yang semakin pesat telah memberikan dampak yang signifikan terhadap berbagai sektor kehidupan, termasuk bidang pendidikan. Digitalisasi proses administrasi pendidikan kini bukan lagi sekadar pilihan, melainkan kebutuhan yang mendesak agar pengelolaan lembaga pendidikan dapat berjalan secara efisien, transparan, dan akuntabel. Salah satu aspek administrasi yang paling fundamental dalam pengelolaan lembaga pendidikan adalah pencatatan kehadiran (presensi), baik bagi tenaga pendidik maupun peserta didik. Sistem presensi yang akurat menjadi dasar penilaian kinerja tenaga pendidik, dasar pembayaran honorarium, serta instrumen pengawasan mutu pembelajaran yang tidak dapat diabaikan.

Pusat Kegiatan Belajar Masyarakat (PKBM) Pikat merupakan salah satu lembaga pendidikan non-formal yang berperan aktif dalam menyelenggarakan program pembelajaran bagi masyarakat. Sebagaimana lembaga pendidikan pada umumnya, PKBM Pikat memiliki kebutuhan yang nyata terhadap sistem pencatatan kehadiran tutor yang dapat diandalkan, transparan, dan dapat diakses secara real-time oleh pihak manajemen. Dalam konteks lembaga bimbingan belajar non-formal, pencatatan kehadiran tutor menjadi krusial karena tutor mengajar di berbagai lokasi dan jadwal yang beragam, sehingga mekanisme verifikasi kehadiran yang terstandarisasi mutlak diperlukan guna memastikan akuntabilitas dan kualitas proses pembelajaran.

Menyadari kebutuhan tersebut, PKBM Pikat telah mengambil langkah proaktif dengan mengembangkan sebuah sistem presensi digital berbasis web yang diberi nama **Presensi App** dan telah di-deploy pada domain `presensi.pkbmpikat.com`. Sistem ini dibangun menggunakan framework Laravel versi 12 dengan bahasa pemrograman PHP versi 8.2 ke atas, serta didukung oleh sistem manajemen basis data MySQL. Secara arsitektur, sistem mengadopsi pola MVC (*Model-View-Controller*) dan dilengkapi dengan mekanisme kontrol akses berbasis peran (*Role-Based Access Control*/RBAC) yang membagi pengguna ke dalam tiga peran utama, yaitu Admin, Tutor, dan Kepala Sekolah. Masing-masing peran memiliki kewenangan dan akses yang berbeda sesuai dengan fungsi dan tanggung jawabnya dalam organisasi, serta diarahkan ke antarmuka dashboard yang berbeda setelah proses login berhasil dilakukan.

Fitur unggulan yang menjadi inti dari sistem ini adalah presensi berbasis foto (*photo-based attendance*). Melalui fitur ini, tutor diwajibkan mengambil foto sebagai bukti fisik kehadiran pada saat memulai sesi mengajar (*clock in*) maupun saat mengakhiri sesi (*clock out*). Sistem secara otomatis mencatat waktu dari server menggunakan zona waktu WIB (Asia/Jakarta) sehingga tidak dapat dimanipulasi dari sisi klien. Selain itu, terdapat aturan bisnis yang mensyaratkan jarak waktu minimal satu jam antara absen masuk dan absen pulang, guna memastikan tutor benar-benar menjalankan sesi pembelajaran dan bukan sekadar melakukan pencatatan formalitas. Data lokasi dalam bentuk koordinat GPS atau nama lokasi juga dapat dicatat sebagai informasi tambahan untuk keperluan verifikasi. Sistem ini juga dilengkapi dengan fitur pelaporan komprehensif yang mampu menghasilkan rekap kehadiran dalam format Excel (XLSX) maupun PDF, sehingga memudahkan manajemen dalam melakukan evaluasi kinerja tutor secara periodik.

Di samping fitur presensi tutor, sistem juga menyediakan beberapa modul pendukung yang memperkaya fungsionalitas keseluruhan. Modul manajemen data mencakup pengelolaan data siswa, kelas, jadwal kegiatan, dan akun karyawan yang keseluruhannya dikelola oleh Admin. Modul Lupa Lapor (*Retroactive Attendance*) memungkinkan tutor yang karena alasan tertentu tidak dapat melakukan presensi digital pada hari yang bersangkutan untuk mengajukan laporan kehadiran secara retroaktif dengan menyertakan alasan yang dapat dipertanggungjawabkan. Kepala Sekolah diberikan akses khusus untuk memantau seluruh rekap presensi tutor, melihat pengajuan lupa lapor, dan mengunduh laporan dalam format PDF. Terdapat pula modul presensi karyawan yang memungkinkan Admin dan Kepala Sekolah untuk mencatat kehadiran diri mereka sendiri menggunakan mekanisme yang serupa dengan presensi tutor.

Meskipun sistem ini telah berhasil dibangun dan di-deploy ke lingkungan produksi, analisis mendalam terhadap kode sumber, konfigurasi sistem, dan log *error* yang tersimpan mengungkapkan sejumlah permasalahan yang perlu segera mendapat perhatian. Temuan paling kritis adalah keberadaan file `error_log` di dalam direktori source code yang mencatat ratusan PHP Fatal Error dari server produksi selama periode April hingga September 2026. *Error* tersebut berupa `Class "Illuminate\Support\Facades\Route" not found` dan `Class "Illuminate\Support\Facades\Artisan" not found`, yang merupakan indikasi klasik bahwa direktori `vendor/` — tempat seluruh dependensi framework tersimpan — tidak terinstal dengan benar di server produksi. Kondisi ini mengakibatkan sistem mengalami kegagalan total (*downtime*) secara berkala dan tidak dapat diakses oleh pengguna.

Permasalahan teknis lain yang ditemukan mencakup penggunaan konstanta PHP yang telah dinyatakan usang (*deprecated*) sejak PHP 8.5, yaitu `PDO::MYSQL_ATTR_SSL_CA` pada file `config/database.php`. Konstanta ini memunculkan pesan peringatan (*deprecation warning*) setiap kali sistem dijalankan, dan apabila tidak segera diperbaiki, berpotensi menjadi *Fatal Error* pada versi PHP berikutnya sehingga koneksi ke basis data dapat gagal sepenuhnya. Permasalahan keamanan juga teridentifikasi, di antaranya tidak adanya mekanisme pembatasan percobaan login (*rate limiting*) pada *endpoint* autentikasi yang membuat sistem rentan terhadap serangan *brute force*, serta penyimpanan foto presensi di direktori publik (`public/uploads/`) yang memungkinkan siapa pun dengan URL yang tepat dapat mengakses foto tersebut tanpa melalui proses autentikasi terlebih dahulu.

Dari sisi fungsionalitas, ditemukan kesenjangan yang cukup signifikan pada fitur Lupa Lapor. Pengajuan lupa lapor yang dilakukan oleh tutor tersimpan di tabel basis data yang terpisah (`lapor__lapors`) dan tidak memiliki mekanisme persetujuan (*approval workflow*) dari Kepala Sekolah. Kepala Sekolah hanya dapat melihat dan menghapus pengajuan tersebut, namun tidak dapat memberikan status persetujuan atau penolakan secara digital. Akibatnya, data kehadiran tutor yang diajukan melalui mekanisme lupa lapor tidak terefleksikan dalam rekap presensi resmi, sehingga laporan kehadiran yang dihasilkan sistem tidak sepenuhnya mencerminkan kondisi kehadiran yang sesungguhnya. Selain itu, validasi di sisi server saat proses presensi tidak memiliki pengecekan yang memastikan bahwa siswa yang dipilih tutor benar-benar merupakan siswa yang ditugaskan kepada tutor tersebut, sehingga membuka celah manipulasi data jika seseorang melakukan modifikasi pada parameter *request* HTTP.

Dari aspek kualitas kode (*code quality*) dan pemeliharaan (*maintainability*), ditemukan adanya pengecekan kompatibilitas skema basis data menggunakan `Schema::hasColumn()` yang tersebar di setidaknya delapan titik pada berbagai controller. Pola ini muncul sebagai sisa dari proses pengembangan bertahap di mana kolom-kolom baru ditambahkan melalui migrasi inkremental, namun kode lama tidak pernah di-*refactor* setelah migrasi selesai. Selain menghasilkan kueri tambahan ke basis data pada setiap *request*, pola ini juga menyulitkan pemeliharaan kode karena setiap perubahan logika harus dilacak di banyak titik sekaligus. Ditemukan pula duplikasi logika pada beberapa controller yang seharusnya dapat diekstrak ke dalam *Trait* atau *Service class* untuk menghindari pengulangan kode dan menerapkan prinsip *Don't Repeat Yourself* (DRY).

Bertolak dari temuan-temuan tersebut, maka terdapat kebutuhan mendesak untuk melakukan serangkaian perbaikan dan pengembangan terhadap sistem yang sudah ada. Kegiatan Praktik Kerja Lapangan (PKL) ini hadir sebagai upaya nyata untuk merespons kebutuhan tersebut. Melalui PKL ini, mahasiswa berkontribusi secara langsung dalam menganalisis permasalahan sistem secara komprehensif, merumuskan solusi yang terukur dan dapat dibuktikan, serta mengimplementasikan perbaikan-perbaikan tersebut tanpa merusak fungsionalitas yang sudah berjalan. Hasil dari kegiatan ini diharapkan dapat meningkatkan keandalan, keamanan, dan integritas data sistem presensi digital PKBM Pikat, sekaligus menjadi pembelajaran berharga bagi mahasiswa dalam menghadapi tantangan pengembangan sistem perangkat lunak di lingkungan produksi yang nyata.

---

## 1.2 Identifikasi Masalah

Berdasarkan analisis komprehensif terhadap kode sumber, konfigurasi, log sistem, dan struktur basis data dari Sistem Presensi Digital PKBM Pikat, ditemukan permasalahan-permasalahan berikut:

1. Sistem menggunakan konstanta PHP yang telah dinyatakan usang (*deprecated*), yaitu `PDO::MYSQL_ATTR_SSL_CA` pada file `config/database.php` baris 61 dan 81, yang berpotensi menyebabkan kegagalan koneksi basis data pada PHP versi 8.5 ke atas.

2. File *error log* dari server produksi tersimpan di dalam direktori source code (ditemukan di 9 folder berbeda), yang berpotensi mengekspos informasi sensitif seperti jalur absolut server, nama basis data, dan *stack trace* aplikasi apabila kode tersebut di-*commit* ke repositori publik.

3. Sistem produksi mengalami kegagalan operasional berulang, terbukti dari 429 baris PHP Fatal Error yang tercatat di `routes/error_log` selama periode April–September 2026, yang disebabkan oleh ketidaklengkapan instalasi dependensi (*vendor*) di server produksi sehingga framework tidak dapat diinisialisasi.

4. Tidak terdapat mekanisme pembatasan percobaan login (*rate limiting*) pada *endpoint* autentikasi (`POST /login`) di `routes/web.php`, sehingga sistem rentan terhadap serangan *brute force* yang dapat mengancam keamanan seluruh akun pengguna.

5. Foto presensi yang menjadi bukti kehadiran tutor disimpan di direktori publik (`public/uploads/presensi/`) dan dapat diakses langsung melalui URL browser tanpa melalui proses autentikasi terlebih dahulu, sebagaimana ditemukan di `PresensiFotoController.php` baris 162 dan 187.

6. Fitur Lupa Lapor tidak memiliki mekanisme persetujuan (*approval workflow*) yang terstruktur; Kepala Sekolah hanya dapat menghapus pengajuan namun tidak dapat memberikan status persetujuan atau penolakan secara digital, sehingga data kehadiran yang diajukan melalui fitur ini tidak terefleksikan dalam rekap presensi resmi.

7. Tidak terdapat validasi di sisi server yang memastikan bahwa `siswa_id` yang dikirimkan tutor saat melakukan presensi merupakan siswa yang benar-benar ditugaskan kepada tutor tersebut, sebagaimana teridentifikasi pada `PresensiFotoController.php` baris 150–151.

8. Terdapat duplikasi logika pada beberapa controller, yaitu method `countHadir()` yang identik didefinisikan di dua controller berbeda (`DashboardController.php` baris 85–89 dan `KepsekDashboardController.php` baris 337–342), serta logika `rekapTutor` yang diulang di dua method berbeda pada `KepsekDashboardController.php`, yang melanggar prinsip *Don't Repeat Yourself* (DRY) dan menyulitkan pemeliharaan kode.

---

## 1.3 Batasan Masalah

Mengingat keterbatasan waktu pelaksanaan PKL dan cakupan pekerjaan yang realistis, permasalahan yang ditangani dalam kegiatan ini dibatasi pada hal-hal berikut:

1. **Perbaikan kompatibilitas PHP:** Pembaruan konstanta `PDO::MYSQL_ATTR_SSL_CA` menjadi `Pdo\Mysql::ATTR_SSL_CA` pada file `config/database.php` agar sistem kompatibel dengan PHP 8.5 ke atas.

2. **Penambahan keamanan autentikasi:** Implementasi mekanisme *rate limiting* pada *endpoint* `POST /login` di `routes/web.php` untuk membatasi percobaan login berulang dari alamat IP yang sama dalam periode satu menit.

3. **Perbaikan validasi data presensi:** Penambahan validasi kepemilikan siswa pada proses presensi tutor di `PresensiFotoController.php`, sehingga tutor hanya dapat melakukan presensi untuk siswa yang secara eksplisit ditugaskan kepadanya.

4. **Pengembangan alur persetujuan Lupa Lapor:** Penambahan kolom `status` dan `catatan_kepsek` pada tabel `lapor__lapors` melalui migrasi baru, beserta pembaruan alur di `LupaLaporController.php`, `KepsekDashboardController.php`, dan tampilan (*view*) yang relevan.

5. **Pengelolaan file error log:** Penambahan entri `error_log` pada file `.gitignore` untuk mencegah file *error log* ter-*commit* ke repositori kode sumber.

Adapun hal-hal yang berada **di luar batasan** pekerjaan PKL ini antara lain:

- Migrasi atau perubahan infrastruktur server produksi.
- Perubahan arsitektur sistem secara fundamental (penggantian framework atau restrukturisasi skema basis data secara menyeluruh).
- Implementasi fitur-fitur baru yang tidak berkaitan langsung dengan permasalahan yang telah diidentifikasi.
- Pengujian performa (*load testing*) dan pengujian keamanan (*penetration testing*) secara menyeluruh.
- Pemindahan mekanisme penyimpanan foto presensi dari direktori publik ke direktori privat (memerlukan perubahan signifikan pada konfigurasi server dan seluruh logika akses foto di seluruh controller).

---

## 1.4 Rumusan Masalah

Berdasarkan identifikasi dan batasan masalah yang telah diuraikan, rumusan masalah dalam kegiatan PKL ini adalah:

1. Bagaimana cara memperbaiki konfigurasi basis data pada file `config/database.php` agar Sistem Presensi Digital PKBM Pikat kompatibel dengan PHP versi 8.5 ke atas dan terbebas dari *deprecation warning*?

2. Bagaimana cara mengimplementasikan mekanisme *rate limiting* pada *endpoint* login sistem untuk mencegah serangan *brute force* terhadap akun pengguna?

3. Bagaimana cara memperbaiki validasi data presensi di sisi server agar tutor hanya dapat melakukan pencatatan kehadiran untuk siswa yang secara resmi ditugaskan kepadanya?

4. Bagaimana cara mengembangkan fitur Lupa Lapor dengan menambahkan alur persetujuan (*approval workflow*) oleh Kepala Sekolah sehingga proses verifikasi laporan kehadiran retroaktif dapat dilakukan secara digital dan terstruktur?

5. Bagaimana cara mengamankan repositori kode dari potensi kebocoran informasi sensitif yang berasal dari file *error log* yang tersimpan di dalam direktori source code?

---

## 1.5 Tujuan

Kegiatan Praktik Kerja Lapangan ini bertujuan untuk menganalisis, memperbaiki, dan mengembangkan Sistem Presensi Digital PKBM Pikat sehingga sistem tersebut memiliki tingkat keandalan, keamanan, dan integritas data yang lebih baik dibandingkan kondisi sebelumnya. Secara lebih rinci, tujuan kegiatan ini adalah:

1. Memperbaiki file `config/database.php` dengan mengganti konstanta PHP yang sudah *deprecated* (`PDO::MYSQL_ATTR_SSL_CA`) dengan `Pdo\Mysql::ATTR_SSL_CA` agar sistem dapat berjalan tanpa pesan peringatan pada PHP 8.5 ke atas.

2. Mengimplementasikan *rate limiting* sebanyak 5 percobaan per menit pada rute `POST /login` di `routes/web.php` untuk meningkatkan ketahanan sistem terhadap serangan *brute force*.

3. Menambahkan validasi `Rule::in()` berbasis daftar ID siswa milik tutor yang sedang login pada `PresensiFotoController.php`, sehingga manipulasi parameter `siswa_id` melalui modifikasi *request* HTTP tidak dapat berhasil.

4. Membuat migrasi basis data baru untuk menambahkan kolom `status` (dengan nilai `pending`, `disetujui`, `ditolak`) dan `catatan_kepsek` pada tabel `lapor__lapors`, memperbarui `LupaLaporController.php` dan `KepsekDashboardController.php` untuk menangani alur persetujuan, serta memperbarui tampilan (*view*) terkait pada panel Kepala Sekolah.

5. Menambahkan pola `error_log` pada file `.gitignore` di root project untuk mencegah file *error log* ter-*commit* ke dalam repositori kode sumber.

---

## 1.6 Manfaat

Kegiatan PKL ini diharapkan memberikan manfaat yang dapat dirasakan oleh berbagai pihak, baik secara teoritis maupun praktis.

Secara teoritis, laporan PKL ini memberikan studi kasus nyata mengenai praktik pengembangan dan pemeliharaan perangkat lunak berbasis framework Laravel 12 dalam lingkungan produksi yang sesungguhnya, yang dapat menjadi referensi akademis bagi penelitian selanjutnya di bidang rekayasa perangkat lunak. Selain itu, laporan ini berkontribusi terhadap pemahaman tentang implementasi *Role-Based Access Control* (RBAC), mekanisme presensi berbasis bukti foto, dan pola desain *User + Profile* dalam konteks sistem informasi pendidikan non-formal di Indonesia. Dokumen ini juga mendokumentasikan praktik terbaik (*best practice*) dalam proses analisis, identifikasi masalah, dan perbaikan sistem perangkat lunak yang sudah berjalan di lingkungan produksi, khususnya dalam aspek keamanan autentikasi dan integritas data.

Secara praktis, manfaat yang diperoleh oleh masing-masing pihak adalah sebagai berikut:

1. Bagi PKBM Pikat: meningkatnya kompatibilitas dan stabilitas sistem presensi dengan versi PHP terbaru sehingga mengurangi risiko kegagalan sistem; meningkatnya keamanan autentikasi melalui *rate limiting* yang mencegah serangan *brute force*; meningkatnya integritas data presensi dengan validasi kepemilikan siswa di sisi server; tersedianya alur persetujuan digital yang terstruktur untuk fitur Lupa Lapor; serta berkurangnya risiko kebocoran informasi sensitif dari file *error log* dalam repositori kode.

2. Bagi mahasiswa PKL: mendapatkan pengalaman langsung menganalisis dan memperbaiki sistem produksi nyata; meningkatnya kompetensi dalam pengembangan aplikasi web berbasis Laravel khususnya pada aspek keamanan, validasi, dan migrasi basis data; serta terlatihnya kemampuan analitis dalam merumuskan solusi teknis yang tepat sasaran.

3. Bagi institusi pendidikan (universitas): terjalinnya hubungan kerja sama dengan lembaga pendidikan masyarakat sebagai mitra penelitian dan pengabdian; serta tersedianya studi kasus nyata yang dapat digunakan sebagai bahan pembelajaran dalam mata kuliah rekayasa perangkat lunak dan keamanan sistem informasi.

---

---

*Dokumen ini disusun berdasarkan analisis menyeluruh terhadap kode sumber project yang tersedia di direktori `c:\laragon\www\presensi-pkbmpikat`. Seluruh pernyataan faktual yang tercantum — termasuk nama domain, versi teknologi, jumlah baris error log, nama file, dan nomor baris kode — didasarkan pada bukti yang ditemukan secara langsung dalam kode sumber dan konfigurasi project.*

---

### Referensi File Kunci

| Klaim dalam Dokumen | File Sumber | Lokasi |
|---------------------|-------------|--------|
| Nama sistem "Presensi App" | `.env` | Baris 1 |
| Domain production | `routes/error_log` | Baris 1–4 |
| Versi Laravel 12 & PHP 8.2 | `composer.json` | Baris 9, 11 |
| Struktur RBAC 3 role | `app/Http/Middleware/RoleMiddleware.php` | Baris 45–57 |
| Fitur foto & aturan 1 jam | `app/Http/Controllers/Tutor/PresensiFotoController.php` | Baris 23–51, 244 |
| Deprecated PDO constant | `config/database.php` | Baris 61, 81 |
| 429 baris Fatal Error | `routes/error_log` | Seluruh file |
| Tidak ada rate limiting | `routes/web.php` | Baris 27–28 |
| Foto di folder public/ | `app/Http/Controllers/Tutor/PresensiFotoController.php` | Baris 162, 187 |
| Lupa Lapor tanpa approval | `app/Models/Lapor_Lapor.php` | Baris 43–44 |
| Validasi siswa_id tidak ketat | `app/Http/Controllers/Tutor/PresensiFotoController.php` | Baris 150–151 |
| Duplikasi countHadir | `DashboardController.php`, `KepsekDashboardController.php` | Baris 85–89, 337–342 |

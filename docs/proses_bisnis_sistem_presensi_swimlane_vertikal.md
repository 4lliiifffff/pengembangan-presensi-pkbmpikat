# ALUR LENGKAP PROSES BISNIS SISTEM PRESENSI PKBM PIKAT
## Diagram Swimlane Vertikal Sekuensial (4 Aktor Terintegrasi)

---

## 1. AKTOR SISTEM YANG TERINVOLVASI

Berdasarkan arsitektur dan hak akses di aplikasi Presensi PKBM Pikat, terdapat **4 Aktor Utama**:

1. **Admin (Administrator Sistem):** Memulai alur dengan mengelola data master (Siswa, Kelas, User/Karyawan, Jadwal Agenda), mencatat izin tutor, dan mengekspor laporan presensi.
2. **Tutor (Pengajar):** Menerima hak akses login, melakukan presensi harian berbasis foto & GPS (*Clock-In* & *Clock-Out* min 1 jam), serta mengajukan *Lupa Lapor* jika ada kendala.
3. **Kepala Sekolah (Kepsek):** Memantau statistik kehadiran harian/mingguan, meninjau & memverifikasi pengajuan *Lupa Lapor*, serta mengunduh laporan rekapitulasi PDF per tutor.
4. **Sistem (Laravel Backend & Database):** Mengeksekusi otentikasi dual-field, validasi aturan bisnis (WIB server, durasi 1 jam, relasi data), penyimpanan berkas foto, dan penyusunan file laporan.

*(Catatan: Siswa tidak dimasukkan sebagai aktor swimlane karena siswa tidak memiliki akun login/interaksi langsung dengan sistem web).*

---

## 2. DIAGRAM SWIMLANE VERTIKAL SEKUANSIAL LENGKAP (MERMAID)

Diagram berikut disusun sekuensial dimulai dari **pojok kiri atas (Admin)** dan mengalir secara kontinu (*tanpa ada langkah yang terputus atau tiba-tiba muncul*):

```mermaid
graph TB
    subgraph Admin [" Aktor 1: Admin "]
        direction TB
        START([1. Mulai: Admin Login]) --> A1[Input Data Master: Kelas, Siswa, Karyawan, Agenda]
        A1 --> A2[Kirim Data Master ke Sistem]
        
        A10[Admin Akses Menu /admin/izin] --> A11[Pilih Tutor & Siswa Bimbingan via AJAX]
        A11 --> A12[Input Tanggal Izin & Simpan]
        
        A20[Admin Akses /admin/laporan] --> A21[Filter Periode, Tutor, Siswa, Status]
        A21 --> A22[Klik Ekspor Excel / PDF]
    end

    subgraph Tutor [" Aktor 2: Tutor Pengajar "]
        direction TB
        T1[2. Tutor Login NIK/Email] --> T2[Buka Dashboard Tutor]
        T2 --> T3{Cek Status Presensi}
        T3 -- Belum Mulai --> T4[Pilih Siswa & Ambil Foto Selfie + GPS]
        T4 --> T5[Kirim Request Clock-In]
        
        T6[Tunggu Countdown Timer Min 1 Jam] --> T7[Ambil Foto Pulang + GPS]
        T7 --> T8[Kirim Request Clock-Out]
        T9[Status Presensi Hari Ini: Selesai]
        
        T3 -- Kendala/Lupa Absen --> T10[Akses Menu /tutor/lupa-lapor]
        T10 --> T11[Isi Form Retroaktif & Alasan Min 10 Karakter]
        T11 --> T12[Kirim Pengajuan Lupa Lapor]
        T13[Lihat Status Pengajuan: Disetujui / Ditolak]
    end

    subgraph Kepsek [" Aktor 3: Kepala Sekolah "]
        direction TB
        K1[3. Kepsek Login NIK/Email] --> K2[Lihat Dashboard & Monitoring Real-Time]
        
        K10[Akses Menu /kepsek/lupa-lapor] --> K11[Tinjau Alasan Pengajuan Retroaktif]
        K11 --> K12[Pilih Status: Disetujui / Ditolak + Catatan]
        K12 --> K13[Kirim Hasil Verifikasi]
        
        K20[Akses Menu /kepsek/laporan] --> K21[Tinjau Rekap Total Jam Mengajar per Tutor]
        K21 --> K22[Klik Ekspor Laporan Rekap PDF]
    end

    subgraph Backend [" Aktor 4: Sistem Backend & Database "]
        direction TB
        START --> B1[Terima HTTP POST /login]
        B1 --> B2{Validasi Credential NIK/Email}
        B2 -- Gagal --> B3[Kembalikan Error Invalid Credentials]
        B2 -- Sukses --> B4[Regenerasi Session & Redirect ke Dashboard Role]
        
        A2 --> B5[Validasi & Insert Data Master ke Database]
        B5 --> T1
        B5 --> K1
        
        T5 --> B6{Validasi Foto, GPS & Relasi Siswa}
        B6 -- Gagal --> T4
        B6 -- Lolos --> B7[Simpan Foto & Insert presensis: jam_mulai=WIB, status=pending]
        B7 --> T6
        
        T8 --> B8{Validasi Jeda Waktu >= 60 Menit}
        B8 -- Kurang 60 Min --> T6
        B8 -- Lolos --> B9[Simpan Foto Pulang & Update presensis: jam_selesai=WIB, status=hadir]
        B9 --> T9
        
        T12 --> B10[Validasi Input & Insert ke lapor__lapors status=pending]
        B10 --> K10
        
        K13 --> B11[Update status & catatan_kepsek di lapor__lapors]
        B11 --> B12{Apakah Disetujui?}
        B12 -- Ya --> B13[Upsert Row Kehadiran Resmi di presensis]
        B12 -- Tidak --> B14[Simpan Penolakan]
        B13 --> T13
        B14 --> T13
        
        A12 --> B15[Insert Row presensis dengan status=izin]
        B15 --> A20
        
        A22 --> B16[Render & Stream Spreadsheet .xlsx / DomPDF]
        B16 --> FINISH_A([Selesai: File Laporan Admin Berhasil Diunduh])
        
        K22 --> B17[Render & Stream PDF Rekap per Tutor]
        B17 --> FINISH_K([Selesai: File Laporan Kepsek Berhasil Diunduh])
    end

    B4 --> T2
    B4 --> K2
    A22 --> Backend
    K22 --> Backend
    T5 --> Backend
    T8 --> Backend
    T12 --> Backend
    K13 --> Backend
    A12 --> Backend
```

---

## 3. PENJABARAN ALUR OPERASIONAL BERKESINAMBUNGAN (STEP-BY-STEP)

### Langkah 1: Inisialisasi Data Master (Admin -> Backend)
1. **Admin** membuka sistem dan menginput data Kelas, Siswa (penugasan tutor pembimbing), Akun Pengguna (Role Tutor & Kepsek), serta Agenda Kegiatan.
2. **Sistem Backend** memvalidasi dan menyimpan data ke basis data MySQL. Akun Tutor dan Kepsek kini aktif dan siap digunakan.

### Langkah 2: Autentikasi Dual-Field (Pengguna -> Backend)
1. **Tutor** dan **Kepala Sekolah** melakukan login menggunakan NIK atau Email beserta Password.
2. **Sistem Backend** memverifikasi kredensial, melakukan regenerasi Session ID, dan mengarahkan pengguna ke dashboard sesuai perannya (`/tutor/dashboard` atau `/kepsek/dashboard`).

### Langkah 3: Presensi Mengajar Real-Time (Tutor -> Backend)
1. **Tutor** yang berada di lokasi bimbingan membuka `/tutor/dashboard` dan memilih siswa yang diajar.
2. Tutor mengambil foto *selfie* mengajar & lokasi GPS, lalu mengirimkan request **Clock-In**.
3. **Sistem Backend** memvalidasi foto & kepemilikan siswa, menyimpan file ke `public/uploads/presensi/`, dan mencatat `jam_mulai` berbasis waktu server WIB (`Asia/Jakarta`). Status presensi berubah menjadi **Pending (Proses)** dengan timer 1 jam.
4. Sesi mengajar berlangsung $\ge 60$ menit.
5. Setelah durasi terpenuhi, Tutor mengambil foto *Clock-Out*.
6. **Sistem Backend** memverifikasi jeda waktu ($\ge 1$ jam), memperbarui `jam_selesai`, dan memperbarui status presensi menjadi **Hadir (Selesai)**.

### Langkah 4: Pengajuan & Verifikasi Lupa Lapor (Tutor -> Kepsek -> Backend)
1. Jika Tutor terkendala presensi pada hari yang lalu, Tutor mengisi form pengajuan di `/tutor/lupa-lapor` (siswa, tanggal, jam, alasan $\ge 10$ karakter).
2. **Sistem Backend** menyimpan pengajuan ke `lapor__lapors` dengan `status = pending`.
3. **Kepala Sekolah** meninjau pengajuan di `/kepsek/lupa-lapor` dan menentukan status (*Disetujui/Ditolak*) serta memberikan catatan.
4. **Sistem Backend** memperbarui record di `lapor__lapors`. Jika disetujui, sistem secara otomatis memasukkan data kehadiran resmi ke tabel `presensis`.

### Langkah 5: Pencatatan Izin Tutor (Admin -> Backend)
1. Apabila Tutor berhalangan mengajar, **Admin** membuka `/admin/izin`.
2. Admin memilih nama Tutor (sistem memuat daftar siswa bimbingan via AJAX) dan menginput tanggal izin.
3. **Sistem Backend** menyimpan record presensi dengan `status = izin`.

### Langkah 6: Monitoring & Ekspor Laporan (Admin & Kepsek -> Backend -> Selesai)
1. **Admin** menyaring data di `/admin/laporan` dan mengekspor laporan ke **Excel (XLSX)** atau **PDF**.
2. **Kepala Sekolah** meninjau rekapitulasi total jam mengajar di `/kepsek/laporan` dan mengunduh laporan rekap **PDF**.
3. File laporan terunduh dan proses bisnis selesai.

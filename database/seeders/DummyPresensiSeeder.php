<?php

namespace Database\Seeders;

use App\Models\KategoriTutorial;
use App\Models\PengajuanIzinSakit;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DummyPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today('Asia/Jakarta');
        $kepsekUser = User::where('role', 'kepala_sekolah')->first();

        // Ambil Data Master Kategori SK
        $katKomunitas2 = KategoriTutorial::where('jenis_layanan', 'komunitas')->where('durasi_jam', 2)->where('is_abk', false)->where('is_gabungan', false)->first();
        $katKomunitasABK = KategoriTutorial::where('jenis_layanan', 'komunitas')->where('durasi_jam', 2)->where('is_abk', true)->first();
        $katKomunitas3 = KategoriTutorial::where('jenis_layanan', 'komunitas')->where('durasi_jam', 3)->first();
        $katGabungan = KategoriTutorial::where('is_gabungan', true)->first();
        $katDL = KategoriTutorial::where('jenis_layanan', 'dl')->where('is_abk', false)->first();
        $katDLABK = KategoriTutorial::where('jenis_layanan', 'dl')->where('is_abk', true)->first();

        // Ambil Tutors & Siswas
        $tutor1 = Tutor::where('email', 'tutor@pkbmpikat.com')->first();
        $tutor2 = Tutor::where('email', 'tutor2@pkbmpikat.com')->first() ?? $tutor1;
        $tutor3 = Tutor::where('email', 'tutor3@pkbmpikat.com')->first() ?? $tutor1;

        $siswa1 = Siswa::where('no_absen', '001')->first(); // Ahmad Rizky Pratama (Reguler)
        $siswa2 = Siswa::where('no_absen', '002')->first(); // Siti Nurhaliza (Reguler)
        $siswa3 = Siswa::where('no_absen', '003')->first(); // Kevin Sanjaya (ABK)
        $siswa5 = Siswa::where('no_absen', '005')->first(); // Dewi Anggraini
        $siswa7 = Siswa::where('no_absen', '007')->first(); // Rian Ardianto (ABK)
        $siswa11 = Siswa::where('no_absen', '011')->first(); // Dimas Bagaskara (Vokasi)

        // =========================================================================
        // 1. DATA SAMPLE PRESENSI MENGAJAR TUTOR (Bulan Berjalan & Historis)
        // =========================================================================
        if ($tutor1 && $siswa1 && $katKomunitas2) {
            // Sesi 1: Komunitas Reguler Tatap Muka di Sekolah (Tutor 1)
            Presensi::updateOrCreate(
                [
                    'tutor_id' => $tutor1->id,
                    'siswa_id' => $siswa1->id,
                    'tgl_presensi' => $today->copy()->subDays(6)->toDateString(),
                ],
                [
                    'moda_pembelajaran' => 'sekolah',
                    'durasi_pilihan' => 2.0,
                    'kategori_tutorial_id' => $katKomunitas2->id,
                    'nominal_honor_snapshot' => $katKomunitas2->nominal_honor,
                    'jam_mulai' => '08:00:00',
                    'jam_selesai' => '10:00:00',
                    'lokasi_mulai' => '-7.257500, 112.752100',
                    'lokasi_selesai' => '-7.257500, 112.752100',
                    'lokasi_akurasi' => 12.5,
                    'is_mocked' => false,
                    'status' => 'hadir',
                ]
            );

            // Sesi 2: Sesi Komunitas ABK (Tutor 1 mengajar Kevin Sanjaya)
            if ($siswa3 && $katKomunitasABK) {
                Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutor1->id,
                        'siswa_id' => $siswa3->id,
                        'tgl_presensi' => $today->copy()->subDays(4)->toDateString(),
                    ],
                    [
                        'moda_pembelajaran' => 'sekolah',
                        'durasi_pilihan' => 2.0,
                        'kategori_tutorial_id' => $katKomunitasABK->id,
                        'nominal_honor_snapshot' => $katKomunitasABK->nominal_honor,
                        'jam_mulai' => '10:15:00',
                        'jam_selesai' => '12:15:00',
                        'lokasi_mulai' => '-7.257500, 112.752100',
                        'lokasi_selesai' => '-7.257500, 112.752100',
                        'lokasi_akurasi' => 15.0,
                        'is_mocked' => false,
                        'status' => 'hadir',
                    ]
                );
            }

            // Sesi 3: Sesi Kunjungan Rumah / Home Visit (Tutor 1 mengajar Siti Nurhaliza)
            if ($siswa2 && $katKomunitas2) {
                Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutor1->id,
                        'siswa_id' => $siswa2->id,
                        'tgl_presensi' => $today->copy()->subDays(2)->toDateString(),
                    ],
                    [
                        'moda_pembelajaran' => 'kunjungan_rumah',
                        'durasi_pilihan' => 2.0,
                        'kategori_tutorial_id' => $katKomunitas2->id,
                        'nominal_honor_snapshot' => $katKomunitas2->nominal_honor,
                        'jam_mulai' => '13:00:00',
                        'jam_selesai' => '15:00:00',
                        'lokasi_mulai' => '-7.289100, 112.734500 (Rumah Murid)',
                        'lokasi_selesai' => '-7.289100, 112.734500 (Rumah Murid)',
                        'lokasi_akurasi' => 18.0,
                        'is_mocked' => false,
                        'status' => 'hadir',
                    ]
                );
            }

            // Sesi 4: Pembelajaran Online / Distance Learning (Tutor 1)
            if ($siswa1 && $katDL) {
                Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutor1->id,
                        'siswa_id' => $siswa1->id,
                        'tgl_presensi' => $today->copy()->subDays(1)->toDateString(),
                    ],
                    [
                        'moda_pembelajaran' => 'online',
                        'link_daring' => 'https://meet.google.com/pikat-tutorial-online',
                        'durasi_pilihan' => 1.5,
                        'kategori_tutorial_id' => $katDL->id,
                        'nominal_honor_snapshot' => $katDL->nominal_honor,
                        'jam_mulai' => '15:30:00',
                        'jam_selesai' => '17:00:00',
                        'lokasi_mulai' => 'Online Daring Session',
                        'lokasi_selesai' => 'Online Daring Session',
                        'lokasi_akurasi' => 5.0,
                        'is_mocked' => false,
                        'status' => 'hadir',
                    ]
                );
            }

            // Sesi 5: Sesi Gabungan Komunitas (Multi-Rombel)
            if ($siswa1 && $katGabungan) {
                Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutor1->id,
                        'siswa_id' => $siswa1->id,
                        'tgl_presensi' => $today->toDateString(),
                    ],
                    [
                        'moda_pembelajaran' => 'sekolah',
                        'durasi_pilihan' => 2.0,
                        'kategori_tutorial_id' => $katGabungan->id,
                        'nominal_honor_snapshot' => $katGabungan->nominal_honor,
                        'jam_mulai' => '08:00:00',
                        'jam_selesai' => '10:00:00',
                        'lokasi_mulai' => '-7.257500, 112.752100',
                        'lokasi_selesai' => '-7.257500, 112.752100',
                        'lokasi_akurasi' => 10.0,
                        'is_mocked' => false,
                        'status' => 'hadir',
                    ]
                );
            }
        }

        // Sesi Presensi Tutor 2 (Siti Aminah)
        if ($tutor2 && $siswa5 && $katKomunitas2) {
            Presensi::updateOrCreate(
                [
                    'tutor_id' => $tutor2->id,
                    'siswa_id' => $siswa5->id,
                    'tgl_presensi' => $today->copy()->subDays(3)->toDateString(),
                ],
                [
                    'moda_pembelajaran' => 'sekolah',
                    'durasi_pilihan' => 2.0,
                    'kategori_tutorial_id' => $katKomunitas2->id,
                    'nominal_honor_snapshot' => $katKomunitas2->nominal_honor,
                    'jam_mulai' => '08:30:00',
                    'jam_selesai' => '10:30:00',
                    'lokasi_mulai' => '-7.257500, 112.752100',
                    'lokasi_selesai' => '-7.257500, 112.752100',
                    'lokasi_akurasi' => 14.0,
                    'is_mocked' => false,
                    'status' => 'hadir',
                ]
            );

            if ($siswa7 && $katKomunitasABK) {
                Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutor2->id,
                        'siswa_id' => $siswa7->id,
                        'tgl_presensi' => $today->copy()->subDays(1)->toDateString(),
                    ],
                    [
                        'moda_pembelajaran' => 'sekolah',
                        'durasi_pilihan' => 2.0,
                        'kategori_tutorial_id' => $katKomunitasABK->id,
                        'nominal_honor_snapshot' => $katKomunitasABK->nominal_honor,
                        'jam_mulai' => '11:00:00',
                        'jam_selesai' => '13:00:00',
                        'lokasi_mulai' => '-7.257500, 112.752100',
                        'lokasi_selesai' => '-7.257500, 112.752100',
                        'lokasi_akurasi' => 12.0,
                        'is_mocked' => false,
                        'status' => 'hadir',
                    ]
                );
            }
        }

        // =========================================================================
        // 2. DATA SAMPLE PENGAJUAN IZIN & SAKIT TUTOR
        // =========================================================================
        if ($tutor1) {
            // Pengajuan 1: Pending (untuk diuji coba persetujuan oleh Kepala Sekolah)
            PengajuanIzinSakit::updateOrCreate(
                [
                    'tutor_id' => $tutor1->id,
                    'tgl_mulai' => $today->copy()->addDays(1)->toDateString(),
                ],
                [
                    'jenis' => 'izin',
                    'tgl_selesai' => $today->copy()->addDays(2)->toDateString(),
                    'alasan' => 'Menghadiri Seminar Nasional Pendidikan Kesetaraan & Workshop Kurikulum Merdeka.',
                    'dokumen_surat' => null,
                    'status' => 'pending',
                ]
            );
        }

        if ($tutor2) {
            // Pengajuan 2: Disetujui
            PengajuanIzinSakit::updateOrCreate(
                [
                    'tutor_id' => $tutor2->id,
                    'tgl_mulai' => $today->copy()->subDays(8)->toDateString(),
                ],
                [
                    'jenis' => 'sakit',
                    'tgl_selesai' => $today->copy()->subDays(7)->toDateString(),
                    'alasan' => 'Kondisi demam tinggi dan flu, istirahat sesuai anjuran dokter.',
                    'dokumen_surat' => null,
                    'status' => 'disetujui',
                    'disetujui_oleh' => $kepsekUser?->id,
                    'catatan_verifikasi' => 'Disetujui. Semoga lekas sembuh.',
                ]
            );
        }

        // =========================================================================
        // 3. DATA SAMPLE PENGAJUAN LUPA LAPOR (RETROACTIVE REQUESTS)
        // =========================================================================
        if ($tutor1 && $siswa1) {
            // Pengajuan Lupa Lapor 1: Pending (untuk diuji review oleh Kepala Sekolah)
            PengajuanLupaLapor::updateOrCreate(
                [
                    'tutor_id' => $tutor1->id,
                    'siswa_id' => $siswa1->id,
                    'tanggal' => $today->copy()->subDays(5)->toDateString(),
                ],
                [
                    'jam_mulai' => '09:00',
                    'jam_selesai' => '11:00',
                    'alasan' => 'Koneksi internet bermasalah di lokasi belajar saat sesi mengajar berakhir.',
                    'status' => 'pending',
                ]
            );
        }

        if ($tutor2 && $siswa5) {
            // Pengajuan Lupa Lapor 2: Disetujui
            PengajuanLupaLapor::updateOrCreate(
                [
                    'tutor_id' => $tutor2->id,
                    'siswa_id' => $siswa5->id,
                    'tanggal' => $today->copy()->subDays(9)->toDateString(),
                ],
                [
                    'jam_mulai' => '08:30',
                    'jam_selesai' => '10:30',
                    'alasan' => 'Baterai smartphone habis saat jam kepulangan.',
                    'status' => 'disetujui',
                    'catatan_kepsek' => 'Diverifikasi dengan wali murid, kehadiran valid.',
                ]
            );
        }

        // =========================================================================
        // 4. DATA SAMPLE PRESENSI KARYAWAN / MAGANG
        // =========================================================================
        $magangUser1 = User::where('email', 'magang@pkbmpikat.com')->first();
        $magangUser2 = User::where('email', 'magang2@pkbmpikat.com')->first();

        if ($magangUser1) {
            PresensiKaryawan::updateOrCreate(
                [
                    'user_id' => $magangUser1->id,
                    'tgl_presensi' => $today->toDateString(),
                ],
                [
                    'jam_mulai' => '07:55:00',
                    'jam_selesai' => '16:05:00',
                    'lokasi_mulai' => '-7.257500, 112.752100 (PKBM Pikat)',
                    'lokasi_selesai' => '-7.257500, 112.752100 (PKBM Pikat)',
                    'status' => 'hadir',
                ]
            );

            PresensiKaryawan::updateOrCreate(
                [
                    'user_id' => $magangUser1->id,
                    'tgl_presensi' => $today->copy()->subDays(1)->toDateString(),
                ],
                [
                    'jam_mulai' => '07:50:00',
                    'jam_selesai' => '16:00:00',
                    'lokasi_mulai' => '-7.257500, 112.752100 (PKBM Pikat)',
                    'lokasi_selesai' => '-7.257500, 112.752100 (PKBM Pikat)',
                    'status' => 'hadir',
                ]
            );
        }

        if ($magangUser2) {
            PresensiKaryawan::updateOrCreate(
                [
                    'user_id' => $magangUser2->id,
                    'tgl_presensi' => $today->toDateString(),
                ],
                [
                    'jam_mulai' => '08:00:00',
                    'jam_selesai' => '16:10:00',
                    'lokasi_mulai' => '-7.257500, 112.752100 (PKBM Pikat)',
                    'lokasi_selesai' => '-7.257500, 112.752100 (PKBM Pikat)',
                    'status' => 'hadir',
                ]
            );
        }
    }
}

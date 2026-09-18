<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Jadwal;
use App\Models\JenjangPaket;
use App\Models\KategoriTutorial;
use App\Models\kelas;
use App\Models\Kepala_Sekolah;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RealDataSeeder extends Seeder
{
    /**
     * Run the database seeds based on real production dump data.
     */
    public function run(): void
    {
        // 1. Pastikan Data Master Dasar (Jenjang Paket & Kategori Tutorial) Terisi
        $this->call([
            JenjangPaketSeeder::class,
            KategoriTutorialSeeder::class,
        ]);

        $paketC = JenjangPaket::where('kode', 'paket_c')->first();
        $kategoriReguler = KategoriTutorial::where('jenis_layanan', 'komunitas')->first();

        // 2. Disable Foreign Key Checks saat seeding data riil untuk menjaga urutan ID
        Schema::disableForeignKeyConstraints();

        // ==========================================
        // 3. USERS (9 Pengguna Riil)
        // ==========================================
        $users = [
            [
                'id' => 1,
                'nik' => '12345',
                'nama_lengkap' => 'Admin PKBM',
                'email' => 'admin@admin.com',
                'password' => '$2y$12$lGU4RPanBmTwRkoH8cJtYuMyYVo4ejDFgjrILKvOH9MfbS4yUkFA.',
                'role' => 'admin',
                'no_hp' => '081511112222',
                'foto' => 'foto_karyawan/foto_1779208529_6a0c9151299c1.jpg',
                'is_active' => 1,
                'created_at' => '2026-04-17 00:38:26',
                'updated_at' => '2026-05-19 09:35:29',
            ],
            [
                'id' => 2,
                'nik' => '10110010',
                'nama_lengkap' => 'Amalludin',
                'email' => '10110009@local.test',
                'password' => '$2y$12$Ky5kqGfVHND6G5z/DDKXDOVD2ieNJkSlCFidaF0sKc1ipxGN4N0ge',
                'role' => 'tutor',
                'no_hp' => '085862854633',
                'foto' => 'foto_karyawan/foto_1778772907_6a05ebab6a8b4.png',
                'is_active' => 1,
                'created_at' => '2026-04-17 00:39:17',
                'updated_at' => '2026-06-09 02:32:16',
            ],
            [
                'id' => 3,
                'nik' => '00000000',
                'nama_lengkap' => 'Falqi',
                'email' => '00000000@local.test',
                'password' => '$2y$12$Hta0oe2XTZkuEmcBtP9ltOSa93KMss1gLb0akEEImRAusKADHkNKi',
                'role' => 'kepala_sekolah',
                'no_hp' => '-',
                'foto' => null,
                'is_active' => 1,
                'created_at' => '2026-04-17 01:02:01',
                'updated_at' => '2026-05-14 23:09:32',
            ],
            [
                'id' => 4,
                'nik' => '33333333',
                'nama_lengkap' => 'Dian Hendra',
                'email' => '33333333@local.test',
                'password' => '$2y$12$a0CT.KvUrtrSVyljNXES9enQXrnosrkcLRE.U1J/W6IP4/uKXJ1Aa',
                'role' => 'tutor',
                'no_hp' => '-',
                'foto' => 'foto_karyawan/foto_1780414972_6a1ef9fcd1d97.png',
                'is_active' => 1,
                'created_at' => '2026-04-18 09:40:39',
                'updated_at' => '2026-06-02 08:42:52',
            ],
            [
                'id' => 8,
                'nik' => '44444444',
                'nama_lengkap' => 'Dhendi Herdiasyah',
                'email' => '44444444@local.test',
                'password' => '$2y$12$tLltgjcYRZ.hlWptCDUYleFLkTvQ9jRGFYu6GjQRnkxAKPO6JCSNK',
                'role' => 'tutor',
                'no_hp' => '-',
                'foto' => 'foto_karyawan/foto_1778086644_69fb72f4cf332.png',
                'is_active' => 1,
                'created_at' => '2026-05-06 09:56:57',
                'updated_at' => '2026-05-06 09:57:24',
            ],
            [
                'id' => 10,
                'nik' => '1011006',
                'nama_lengkap' => 'Admin PKBM',
                'email' => '1011006@local.test',
                'password' => '$2y$12$Xs50.LZNS.CYMBiNS7YV6OhImDx5OtV4RrfSLYx56UQm2FjqSsRNy',
                'role' => 'admin',
                'no_hp' => '8977868668',
                'foto' => null,
                'is_active' => 1,
                'created_at' => '2026-05-19 01:00:19',
                'updated_at' => '2026-05-19 01:00:19',
            ],
            [
                'id' => 11,
                'nik' => '4444444',
                'nama_lengkap' => 'El Shiraj',
                'email' => '4444444@local.test',
                'password' => '$2y$12$Iwl4SNFGDUm3P5TmUBaJKuuGx1BwlcK9V7HGbNVWAKSXZGnB/kC36',
                'role' => 'tutor',
                'no_hp' => '-',
                'foto' => null,
                'is_active' => 1,
                'created_at' => '2026-05-19 09:13:34',
                'updated_at' => '2026-05-19 09:13:34',
            ],
            [
                'id' => 12,
                'nik' => '12121212',
                'nama_lengkap' => 'Amalludin',
                'email' => '12121212@local.test',
                'password' => '$2y$12$7EIRHOXq7DtAkhpaY6eCs.Vt8LInGCiLUJsvQ7dBi010Fl.5vyegi',
                'role' => 'tutor',
                'no_hp' => '-',
                'foto' => null,
                'is_active' => 1,
                'created_at' => '2026-06-02 18:17:37',
                'updated_at' => '2026-06-02 18:17:59',
            ],
            [
                'id' => 13,
                'nik' => '3273072503050004',
                'nama_lengkap' => 'Asep Balon',
                'email' => '3273072503050004@local.test',
                'password' => '$2y$12$gtx0C6shmaiho319MYx/H.WP0YMD4lTSUvXIeW3bTaCcHJC9aHv2S',
                'role' => 'tutor',
                'no_hp' => '085862854632',
                'foto' => 'foto_karyawan/foto_1780997654_6a27de1673309.jpg',
                'is_active' => 1,
                'created_at' => '2026-06-09 02:34:14',
                'updated_at' => '2026-06-09 02:38:57',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['id' => $userData['id']], $userData);
        }

        // ==========================================
        // 4. ADMINS
        // ==========================================
        $admins = [
            [
                'id' => 1,
                'user_id' => 1,
                'nik' => '12345',
                'nama_lengkap' => 'Admin PKBM',
                'email' => 'admin@admin.com',
                'no_hp' => '081511112222',
                'foto' => 'foto_karyawan/foto_1779208529_6a0c9151299c1.jpg',
                'created_at' => '2026-04-17 00:38:43',
                'updated_at' => '2026-05-19 09:35:29',
            ],
        ];

        foreach ($admins as $adminData) {
            Admin::updateOrCreate(['id' => $adminData['id']], $adminData);
        }

        // ==========================================
        // 5. KEPALA SEKOLAH
        // ==========================================
        $kepsekList = [
            [
                'id' => 1,
                'user_id' => 3,
                'nik' => '00000000',
                'nama_lengkap' => 'Chepy Perdana, S.Kom., M.Pd.',
                'email' => '11111111@local.test',
                'no_hp' => '-',
                'foto' => null,
                'created_at' => '2026-05-09 00:32:41',
                'updated_at' => '2026-05-09 00:32:41',
            ],
        ];

        foreach ($kepsekList as $kepsekData) {
            Kepala_Sekolah::updateOrCreate(['id' => $kepsekData['id']], $kepsekData);
        }

        // ==========================================
        // 6. TUTORS (5 Tutor Riil)
        // ==========================================
        $tutors = [
            [
                'id' => 1,
                'user_id' => 2,
                'nik' => '10110010',
                'nama_lengkap' => 'Amalludin',
                'jabatan' => null,
                'email' => '10110009@local.test',
                'alamat' => 'Kab. Subang',
                'no_hp' => '085862854633',
                'foto' => 'foto_karyawan/foto_1778772907_6a05ebab6a8b4.png',
                'created_at' => '2026-04-17 00:50:08',
                'updated_at' => '2026-06-09 02:31:23',
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'nik' => '33333333',
                'nama_lengkap' => 'Dian Hendra',
                'jabatan' => null,
                'email' => '33333333@local.test',
                'alamat' => null,
                'no_hp' => '-',
                'foto' => 'profiles/1777859723_AdobeStock_159183621_Preview.jpeg',
                'created_at' => '2026-04-18 09:41:17',
                'updated_at' => '2026-05-03 18:55:23',
            ],
            [
                'id' => 4,
                'user_id' => 8,
                'nik' => '44444444',
                'nama_lengkap' => 'Dhendi Herdiasyah',
                'jabatan' => null,
                'email' => '44444444@local.test',
                'alamat' => null,
                'no_hp' => '-',
                'foto' => 'foto_karyawan/foto_1778086644_69fb72f4cf332.png',
                'created_at' => '2026-05-06 09:57:17',
                'updated_at' => '2026-05-06 09:57:24',
            ],
            [
                'id' => 6,
                'user_id' => 3,
                'nik' => '00000000',
                'nama_lengkap' => 'Chepy Perdana, S.Kom., M.Pd.',
                'jabatan' => 'Kepala Sekolah',
                'email' => '11111111@local.test',
                'alamat' => null,
                'no_hp' => '-',
                'foto' => null,
                'created_at' => '2026-06-02 08:59:28',
                'updated_at' => '2026-06-02 08:59:28',
            ],
            [
                'id' => 7,
                'user_id' => 13,
                'nik' => '3273072503050004',
                'nama_lengkap' => 'Asep Balon',
                'jabatan' => null,
                'email' => '3273072503050004@local.test',
                'alamat' => null,
                'no_hp' => '085862854632',
                'foto' => 'foto_karyawan/foto_1780997654_6a27de1673309.jpg',
                'created_at' => '2026-06-09 02:35:21',
                'updated_at' => '2026-06-09 02:35:21',
            ],
        ];

        foreach ($tutors as $tutorData) {
            Tutor::updateOrCreate(['id' => $tutorData['id']], $tutorData);
        }

        // ==========================================
        // 7. KELAS (4 Kelas Riil Terpetakan ke Jenjang Paket C)
        // ==========================================
        $kelasList = [
            [
                'id' => 1,
                'nama_kelas' => 'XII TKJ',
                'jenjang_paket_id' => $paketC?->id,
                'tingkat' => 12,
                'created_at' => '2026-04-17 00:39:45',
                'updated_at' => '2026-04-17 00:39:45',
            ],
            [
                'id' => 2,
                'nama_kelas' => 'XII TPM',
                'jenjang_paket_id' => $paketC?->id,
                'tingkat' => 12,
                'created_at' => '2026-04-17 00:39:51',
                'updated_at' => '2026-04-17 00:39:51',
            ],
            [
                'id' => 3,
                'nama_kelas' => 'XII IPA 1',
                'jenjang_paket_id' => $paketC?->id,
                'tingkat' => 12,
                'created_at' => '2026-04-18 09:38:18',
                'updated_at' => '2026-04-18 09:38:18',
            ],
            [
                'id' => 4,
                'nama_kelas' => '1 SI',
                'jenjang_paket_id' => $paketC?->id,
                'tingkat' => 10,
                'created_at' => '2026-05-19 09:14:55',
                'updated_at' => '2026-05-19 09:14:55',
            ],
        ];

        foreach ($kelasList as $kelasData) {
            kelas::updateOrCreate(['id' => $kelasData['id']], $kelasData);
        }

        // ==========================================
        // 8. SISWAS (5 Siswa Riil)
        // ==========================================
        $siswas = [
            [
                'id' => 1,
                'no_absen' => '1',
                'nama_siswa' => 'Ardy Damar',
                'no_hp' => '-',
                'nama_wali' => 'Dadan',
                'kelas_id' => 1,
                'tutor_id' => 1,
                'is_abk' => false,
                'status_siswa' => 'aktif',
                'created_at' => '2026-04-17 00:40:07',
                'updated_at' => '2026-05-19 08:22:36',
            ],
            [
                'id' => 2,
                'no_absen' => '3',
                'nama_siswa' => 'Falqi El Shiraj',
                'no_hp' => '-',
                'nama_wali' => 'Dadan',
                'kelas_id' => 2,
                'tutor_id' => 1,
                'is_abk' => false,
                'status_siswa' => 'aktif',
                'created_at' => '2026-04-17 02:11:05',
                'updated_at' => '2026-05-19 08:22:28',
            ],
            [
                'id' => 3,
                'no_absen' => '2',
                'nama_siswa' => 'Ardy',
                'no_hp' => '081511112222',
                'nama_wali' => 'fajar',
                'kelas_id' => 2,
                'tutor_id' => 2,
                'is_abk' => false,
                'status_siswa' => 'aktif',
                'created_at' => '2026-05-19 01:02:47',
                'updated_at' => '2026-05-19 08:22:47',
            ],
            [
                'id' => 4,
                'no_absen' => '4',
                'nama_siswa' => 'Gagan',
                'no_hp' => '081234340808',
                'nama_wali' => 'Ardy Damar',
                'kelas_id' => 1,
                'tutor_id' => 2,
                'is_abk' => false,
                'status_siswa' => 'aktif',
                'created_at' => '2026-05-19 09:15:23',
                'updated_at' => '2026-05-19 09:15:23',
            ],
            [
                'id' => 5,
                'no_absen' => '5',
                'nama_siswa' => 'Ryen',
                'no_hp' => 'Ardy',
                'nama_wali' => '-',
                'kelas_id' => 4,
                'tutor_id' => 7,
                'is_abk' => false,
                'status_siswa' => 'aktif',
                'created_at' => '2026-06-02 18:21:44',
                'updated_at' => '2026-06-09 02:41:03',
            ],
        ];

        foreach ($siswas as $siswaData) {
            Siswa::updateOrCreate(['id' => $siswaData['id']], $siswaData);
        }

        // ==========================================
        // 9. JADWALS (5 Jadwal Riil)
        // ==========================================
        $jadwals = [
            [
                'id' => 2,
                'judul' => 'Rapat Wali',
                'deskripsi' => null,
                'lokasi' => null,
                'tanggal' => '2026-04-17',
                'created_at' => '2026-04-17 00:48:52',
                'updated_at' => '2026-04-17 00:48:52',
            ],
            [
                'id' => 3,
                'judul' => 'Ulangan Tengah Semester (UTS)',
                'deskripsi' => null,
                'lokasi' => null,
                'tanggal' => '2026-04-20',
                'created_at' => '2026-04-18 23:49:02',
                'updated_at' => '2026-04-18 23:49:02',
            ],
            [
                'id' => 4,
                'judul' => 'Rapat Wali',
                'deskripsi' => null,
                'lokasi' => null,
                'tanggal' => '2026-05-07',
                'created_at' => '2026-05-06 08:24:58',
                'updated_at' => '2026-05-06 08:24:58',
            ],
            [
                'id' => 5,
                'judul' => 'Rapat Tenaga Pendidik',
                'deskripsi' => null,
                'lokasi' => null,
                'tanggal' => '2026-05-20',
                'created_at' => '2026-05-19 09:16:19',
                'updated_at' => '2026-05-19 09:16:19',
            ],
            [
                'id' => 6,
                'judul' => 'Rapat',
                'deskripsi' => null,
                'lokasi' => null,
                'tanggal' => '2026-06-05',
                'created_at' => '2026-06-02 18:23:22',
                'updated_at' => '2026-06-02 18:23:22',
            ],
        ];

        foreach ($jadwals as $jadwalData) {
            Jadwal::updateOrCreate(['id' => $jadwalData['id']], $jadwalData);
        }

        // ==========================================
        // 10. PRESENSIS (16 Sesi Presensi Riil)
        // ==========================================
        $presensis = [
            [
                'id' => 1,
                'tutor_id' => 1,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-04-17',
                'jam_mulai' => '14:58:38',
                'jam_selesai' => '15:59:56',
                'foto_mulai' => 'presensi/1/2026-04-17/masuk_1776412718_presensi-1776412716610.jpg',
                'foto_selesai' => 'presensi/1/2026-04-17/keluar_1776416396_presensi-1776416393180.jpg',
                'lokasi_mulai' => '-6.464880,107.769325',
                'lokasi_selesai' => '-6.464880,107.769325',
                'status' => 'hadir',
                'created_at' => '2026-04-17 00:58:38',
                'updated_at' => '2026-04-17 01:59:56',
            ],
            [
                'id' => 2,
                'tutor_id' => 1,
                'siswa_id' => 2,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-04-17',
                'jam_mulai' => '16:12:22',
                'jam_selesai' => '17:20:14',
                'foto_mulai' => 'presensi/1/2026-04-17/masuk_1776417142_presensi-1776417139393.jpg',
                'foto_selesai' => 'presensi/1/2026-04-17/keluar_1776421214_presensi-1776421211706.jpg',
                'lokasi_mulai' => '-6.464880,107.769325',
                'lokasi_selesai' => '-6.464880,107.769325',
                'status' => 'hadir',
                'created_at' => '2026-04-17 02:12:22',
                'updated_at' => '2026-04-17 03:20:14',
            ],
            [
                'id' => 3,
                'tutor_id' => 1,
                'siswa_id' => 2,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-04-19',
                'jam_mulai' => '13:09:58',
                'jam_selesai' => '15:49:02',
                'foto_mulai' => 'presensi/1/2026-04-19/masuk_1776578998_presensi-1776578995095.jpg',
                'foto_selesai' => 'presensi/1/2026-04-19/keluar_1776588542_presensi-1776588540863.jpg',
                'lokasi_mulai' => '-6.464880,107.769325',
                'lokasi_selesai' => '-6.464880,107.769325',
                'status' => 'hadir',
                'created_at' => '2026-04-18 23:09:58',
                'updated_at' => '2026-04-19 01:49:02',
            ],
            [
                'id' => 4,
                'tutor_id' => 1,
                'siswa_id' => 2,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-04-30',
                'jam_mulai' => '10:10:18',
                'jam_selesai' => '12:08:07',
                'foto_mulai' => 'presensi/1/2026-04-30/masuk_1777518618_presensi-1777518616343.jpg',
                'foto_selesai' => 'presensi/1/2026-04-30/keluar_1777525687_presensi-1777525685596.jpg',
                'lokasi_mulai' => '-6.556669,107.824936',
                'lokasi_selesai' => '-6.556669,107.824931',
                'status' => 'hadir',
                'created_at' => '2026-04-29 20:10:18',
                'updated_at' => '2026-04-29 22:08:07',
            ],
            [
                'id' => 5,
                'tutor_id' => 2,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-04',
                'jam_mulai' => '09:01:47',
                'jam_selesai' => '10:19:58',
                'foto_mulai' => 'presensi/2/2026-05-04/masuk_1777860107_presensi-1777860105990.jpg',
                'foto_selesai' => 'presensi/2/2026-05-04/keluar_1777864798_presensi-1777864796431.jpg',
                'lokasi_mulai' => '-6.565231,107.827358',
                'lokasi_selesai' => '-6.565231,107.827358',
                'status' => 'hadir',
                'created_at' => '2026-05-03 19:01:47',
                'updated_at' => '2026-05-03 20:19:58',
            ],
            [
                'id' => 6,
                'tutor_id' => 2,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-05',
                'jam_mulai' => '21:04:22',
                'jam_selesai' => '22:45:12',
                'foto_mulai' => 'presensi/2/2026-05-05/masuk_1777989862_presensi-1777989817256.jpg',
                'foto_selesai' => 'presensi/2/2026-05-05/keluar_1777995912_presensi-1777995910674.jpg',
                'lokasi_mulai' => '-6.554501,107.806261',
                'lokasi_selesai' => '-6.554501,107.806261',
                'status' => 'hadir',
                'created_at' => '2026-05-05 07:04:22',
                'updated_at' => '2026-05-05 08:45:12',
            ],
            [
                'id' => 7,
                'tutor_id' => 1,
                'siswa_id' => 2,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-05',
                'jam_mulai' => '21:18:43',
                'jam_selesai' => '22:46:02',
                'foto_mulai' => 'presensi/1/2026-05-05/masuk_1777990723_presensi-1777990720521.jpg',
                'foto_selesai' => 'presensi/1/2026-05-05/keluar_1777995962_presensi-1777995959518.jpg',
                'lokasi_mulai' => '-6.554501,107.806261',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-05 07:18:43',
                'updated_at' => '2026-05-05 08:46:02',
            ],
            [
                'id' => 10,
                'tutor_id' => 1,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-06',
                'jam_mulai' => '22:03:41',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi/1/2026-05-06/masuk_1778079821_presensi-1778079819083.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.464850,107.769306',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-06 08:03:41',
                'updated_at' => '2026-05-06 08:03:41',
            ],
            [
                'id' => 11,
                'tutor_id' => 1,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-16',
                'jam_mulai' => '11:48:36',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi/1/2026-05-16/masuk_1778906916_presensi-1778906905498.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-7.026741,107.592538',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-15 21:48:36',
                'updated_at' => '2026-05-15 21:48:36',
            ],
            [
                'id' => 12,
                'tutor_id' => 1,
                'siswa_id' => 2,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-18',
                'jam_mulai' => '11:22:40',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi/1/2026-05-18/masuk_1779078160_presensi-1779078160551.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.565989,107.827852',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-17 21:22:40',
                'updated_at' => '2026-05-17 21:22:40',
            ],
            [
                'id' => 13,
                'tutor_id' => 2,
                'siswa_id' => 3,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-19',
                'jam_mulai' => '22:25:23',
                'jam_selesai' => '23:29:43',
                'foto_mulai' => 'presensi/2/2026-05-19/masuk_1779204323_presensi-1779204324024.jpg',
                'foto_selesai' => 'presensi/2/2026-05-19/keluar_1779208183_presensi-1779208184565.jpg',
                'lokasi_mulai' => '-6.464876,107.769391',
                'lokasi_selesai' => '-6.464868,107.769399',
                'status' => 'hadir',
                'created_at' => '2026-05-19 08:25:23',
                'updated_at' => '2026-05-19 09:29:43',
            ],
            [
                'id' => 14,
                'tutor_id' => 1,
                'siswa_id' => 2,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-05-19',
                'jam_mulai' => '23:20:52',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi/1/2026-05-19/masuk_1779207652_presensi-1779207652854.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.464880,107.769387',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-19 09:20:52',
                'updated_at' => '2026-05-19 09:20:52',
            ],
            [
                'id' => 15,
                'tutor_id' => 1,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-06-03',
                'jam_mulai' => '06:40:57',
                'jam_selesai' => '08:29:29',
                'foto_mulai' => 'presensi/1/2026-06-03/masuk_1780443657_presensi-1780443656233.jpg',
                'foto_selesai' => 'presensi/1/2026-06-03/keluar_1780450169_presensi-1780450158042.jpg',
                'lokasi_mulai' => '-6.556603,107.827158',
                'lokasi_selesai' => '-6.565392,107.827347',
                'status' => 'hadir',
                'created_at' => '2026-06-02 16:40:57',
                'updated_at' => '2026-06-02 18:29:29',
            ],
            [
                'id' => 16,
                'tutor_id' => 1,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-06-03',
                'jam_mulai' => '08:32:59',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi/1/2026-06-03/masuk_1780450379_presensi-1780450378548.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.565324,107.827314',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-06-02 18:32:59',
                'updated_at' => '2026-06-02 18:32:59',
            ],
            [
                'id' => 17,
                'tutor_id' => 1,
                'siswa_id' => 1,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-06-07',
                'jam_mulai' => '14:18:19',
                'jam_selesai' => '18:13:18',
                'foto_mulai' => 'presensi/1/2026-06-07/masuk_1780816699_presensi-1780816698164.jpg',
                'foto_selesai' => 'presensi/1/2026-06-07/keluar_1780830798_presensi-1780830799379.jpg',
                'lokasi_mulai' => null,
                'lokasi_selesai' => '-6.464873,107.769399',
                'status' => 'hadir',
                'created_at' => '2026-06-07 00:18:19',
                'updated_at' => '2026-06-07 04:13:18',
            ],
            [
                'id' => 18,
                'tutor_id' => 2,
                'siswa_id' => 3,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-06-07',
                'jam_mulai' => '14:35:39',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi/2/2026-06-07/masuk_1780817739_presensi-1780817737423.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => null,
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-06-07 00:35:39',
                'updated_at' => '2026-06-07 00:35:39',
            ],
            [
                'id' => 19,
                'tutor_id' => 2,
                'siswa_id' => 3,
                'moda_pembelajaran' => 'sekolah',
                'kategori_tutorial_id' => $kategoriReguler?->id,
                'tgl_presensi' => '2026-06-09',
                'jam_mulai' => null,
                'jam_selesai' => null,
                'foto_mulai' => null,
                'foto_selesai' => null,
                'lokasi_mulai' => null,
                'lokasi_selesai' => null,
                'status' => 'izin',
                'created_at' => '2026-06-09 02:44:23',
                'updated_at' => '2026-06-09 02:44:23',
            ],
        ];

        foreach ($presensis as $presensiData) {
            Presensi::updateOrCreate(['id' => $presensiData['id']], $presensiData);
        }

        // ==========================================
        // 11. PRESENSI KARYAWAN (3 Presensi Riil)
        // ==========================================
        $presensiKaryawan = [
            [
                'id' => 1,
                'user_id' => 1,
                'tgl_presensi' => '2026-05-19',
                'jam_mulai' => '23:17:13',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi_karyawan/1/2026-05-19/masuk_1779207433_presensi-1779207432135.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.464874,107.769392',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-19 09:17:13',
                'updated_at' => '2026-05-19 09:17:13',
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'tgl_presensi' => '2026-05-19',
                'jam_mulai' => '23:28:03',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi_karyawan/3/2026-05-19/masuk_1779208083_presensi-1779208085276.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.464873,107.769392',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-05-19 09:28:03',
                'updated_at' => '2026-05-19 09:28:03',
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'tgl_presensi' => '2026-06-03',
                'jam_mulai' => '08:27:16',
                'jam_selesai' => null,
                'foto_mulai' => 'presensi_karyawan/1/2026-06-03/masuk_1780450036_presensi-1780450035793.jpg',
                'foto_selesai' => null,
                'lokasi_mulai' => '-6.565335,107.827339',
                'lokasi_selesai' => null,
                'status' => 'hadir',
                'created_at' => '2026-06-02 18:27:16',
                'updated_at' => '2026-06-02 18:27:16',
            ],
        ];

        foreach ($presensiKaryawan as $pkData) {
            PresensiKaryawan::updateOrCreate(['id' => $pkData['id']], $pkData);
        }

        // ==========================================
        // 12. PENGAJUAN LUPA LAPOR (1 Pengajuan Riil Lama)
        // ==========================================
        $pengajuanLupaLapor = [
            [
                'id' => 1,
                'tutor_id' => 2,
                'siswa_id' => 1,
                'tanggal' => '2026-05-13',
                'jam_mulai' => '10:00:00',
                'jam_selesai' => '12:00:00',
                'alasan' => 'Ada perbaikan smartphone',
                'status' => 'pending',
                'catatan_kepsek' => null,
                'created_at' => '2026-05-14 23:20:35',
                'updated_at' => '2026-05-14 23:20:35',
            ],
        ];

        foreach ($pengajuanLupaLapor as $pllData) {
            PengajuanLupaLapor::updateOrCreate(['id' => $pllData['id']], $pllData);
        }

        // Enable kembali Foreign Key Constraints
        Schema::enableForeignKeyConstraints();
    }
}

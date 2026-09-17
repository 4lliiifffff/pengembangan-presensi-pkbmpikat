<?php

namespace Database\Seeders;

use App\Models\JadwalKerja;
use App\Models\KategoriTutorial;
use Illuminate\Database\Seeder;

class JadwalKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $katKomunitas2 = KategoriTutorial::where('jenis_layanan', 'komunitas')->where('durasi_jam', 2.00)->first();
        $katDl15 = KategoriTutorial::where('jenis_layanan', 'dl')->where('durasi_jam', 1.50)->first();

        $shifts = [
            [
                'nama_shift' => 'Shift Pagi (Staf & Magang)',
                'kode_shift' => 'pagi',
                'jenis_shift' => 'umum',
                'kategori_tutorial_id' => null,
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '16:00:00',
                'durasi_jam' => 8.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'is_aktif' => true,
                'urutan' => 1,
                'keterangan' => 'Jadwal kerja operasional reguler untuk staf tata usaha, pengelola, dan mahasiswa magang/PKL.',
            ],
            [
                'nama_shift' => 'Shift Siang (Staf & Magang)',
                'kode_shift' => 'siang',
                'jenis_shift' => 'umum',
                'kategori_tutorial_id' => null,
                'jam_masuk' => '13:00:00',
                'jam_pulang' => '17:00:00',
                'durasi_jam' => 4.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'is_aktif' => true,
                'urutan' => 2,
                'keterangan' => 'Jadwal kerja operasional sesi siang kantor PKBM Pikat.',
            ],
            [
                'nama_shift' => 'Shift KBM Komunitas Pagi (2 Jam)',
                'kode_shift' => 'kbm_komunitas_pagi',
                'jenis_shift' => 'kbm',
                'kategori_tutorial_id' => $katKomunitas2?->id,
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '10:00:00',
                'durasi_jam' => $katKomunitas2?->durasi_jam ?? 2.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'is_aktif' => true,
                'urutan' => 3,
                'keterangan' => 'Sesi KBM Tutorial Tatap Muka Komunitas durasi 2 jam tatap muka.',
            ],
            [
                'nama_shift' => 'Shift KBM Distance Learning Siang (1.5 Jam)',
                'kode_shift' => 'kbm_dl_siang',
                'jenis_shift' => 'kbm',
                'kategori_tutorial_id' => $katDl15?->id,
                'jam_masuk' => '13:00:00',
                'jam_pulang' => '14:30:00',
                'durasi_jam' => $katDl15?->durasi_jam ?? 1.50,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'is_aktif' => true,
                'urutan' => 4,
                'keterangan' => 'Sesi KBM Tutorial Online (Distance Learning) durasi 1.5 jam daring.',
            ],
        ];

        foreach ($shifts as $shift) {
            JadwalKerja::updateOrCreate(
                ['kode_shift' => $shift['kode_shift']],
                $shift
            );
        }
    }
}

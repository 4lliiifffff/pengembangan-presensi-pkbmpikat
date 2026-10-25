<?php

namespace Database\Seeders;

use App\Models\JadwalKerja;
use Illuminate\Database\Seeder;

class JadwalKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'nama_shift' => 'Shift Reguler Magang (09:00 - 15:00)',
                'kode_shift' => 'pagi',
                'jenis_shift' => 'umum',
                'kategori_tutorial_id' => null,
                'jam_masuk' => '09:00:00',
                'jam_pulang' => '15:00:00',
                'durasi_jam' => 6.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'is_aktif' => true,
                'urutan' => 1,
                'keterangan' => 'Jadwal operasional mahasiswa magang/PKL (09:00 - 15:00 WIB, fleksibel disesuaikan oleh Admin).',
            ],
            [
                'nama_shift' => 'Shift Siang Magang (12:00 - 17:00)',
                'kode_shift' => 'siang',
                'jenis_shift' => 'umum',
                'kategori_tutorial_id' => null,
                'jam_masuk' => '12:00:00',
                'jam_pulang' => '17:00:00',
                'durasi_jam' => 5.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'is_aktif' => true,
                'urutan' => 2,
                'keterangan' => 'Jadwal alternatif sesi siang mahasiswa magang/PKL jika diperlukan.',
            ],
        ];

        foreach ($shifts as $shift) {
            JadwalKerja::updateOrCreate(
                ['kode_shift' => $shift['kode_shift']],
                $shift
            );
        }

        // Bersihkan shift KBM lama jika tidak memiliki riwayat presensi
        JadwalKerja::whereIn('kode_shift', ['kbm_komunitas_pagi', 'kbm_dl_siang'])
            ->whereDoesntHave('presensis')
            ->delete();
    }
}

<?php

namespace Database\Seeders;

use App\Models\JadwalKerja;
use App\Models\JadwalRutin;
use App\Models\KategoriTutorial;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\JadwalRutinService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JadwalRutinSeeder extends Seeder
{
    /**
     * Run the database seeds for Master Jadwal Rutin KBM Siswa & Generate Sesi Kalender.
     */
    public function run(JadwalRutinService $service): void
    {
        // 1. Ambil Tutor Aktif
        $tutors = Tutor::whereHas('user', fn ($u) => $u->where('is_active', true))->get();
        if ($tutors->isEmpty()) {
            $tutors = Tutor::all();
        }

        $tutor1 = $tutors->get(0);
        $tutor2 = $tutors->get(1) ?? $tutor1;
        $tutor3 = $tutors->get(2) ?? $tutor1;

        // 2. Ambil Siswa Aktif
        $siswas = Siswa::aktif()->get();
        if ($siswas->isEmpty()) {
            $siswas = Siswa::all();
        }

        // 3. Ambil Kategori Tutorial
        $katReguler = KategoriTutorial::where('is_abk', false)->first() ?? KategoriTutorial::first();
        $katAbk = KategoriTutorial::where('is_abk', true)->first() ?? $katReguler;

        // 4. Ambil Jadwal Kerja KBM
        $jadwalKerjaKbm = JadwalKerja::where('jenis_shift', 'kbm')->first();

        // 5. Tentukan hari ini (untuk memastikan ada siswa yang punya jadwal hari ini)
        $todayIso = (int) Carbon::now('Asia/Jakarta')->format('N'); // 1 = senin, ..., 7 = minggu
        $todayHari = JadwalRutin::isoToHariName($todayIso);

        $hariLain1 = JadwalRutin::isoToHariName(($todayIso % 7) + 1);
        $hariLain2 = JadwalRutin::isoToHariName((($todayIso + 1) % 7) + 1);
        $hariLain3 = JadwalRutin::isoToHariName((($todayIso + 2) % 7) + 1);

        $jadwalRutinData = [];

        // Siswa 1: Jadwal hari ini (jam pagi 09:00 - 11:00) dan hari lain
        if ($siswa1 = $siswas->get(0)) {
            $jadwalRutinData[] = [
                'siswa_id' => $siswa1->id,
                'tutor_id' => $tutor1?->id,
                'kategori_tutorial_id' => $katReguler?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $todayHari,
                'jam_masuk' => '09:00:00',
                'jam_pulang' => '11:00:00',
                'durasi_jam' => 2.00,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Rutin Mingguan (Sesi Pagi)',
            ];

            $jadwalRutinData[] = [
                'siswa_id' => $siswa1->id,
                'tutor_id' => $tutor1?->id,
                'kategori_tutorial_id' => $katReguler?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $hariLain2,
                'jam_masuk' => '09:00:00',
                'jam_pulang' => '11:00:00',
                'durasi_jam' => 2.00,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Rutin Mingguan (Sesi Pagi)',
            ];
        }

        // Siswa 2: Jadwal hari ini (jam siang 13:00 - 15:00) dan hari lain
        if ($siswa2 = $siswas->get(1)) {
            $jadwalRutinData[] = [
                'siswa_id' => $siswa2->id,
                'tutor_id' => $tutor2?->id,
                'kategori_tutorial_id' => $katReguler?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $todayHari,
                'jam_masuk' => '13:00:00',
                'jam_pulang' => '15:00:00',
                'durasi_jam' => 2.00,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Rutin Mingguan (Sesi Siang)',
            ];

            $jadwalRutinData[] = [
                'siswa_id' => $siswa2->id,
                'tutor_id' => $tutor2?->id,
                'kategori_tutorial_id' => $katReguler?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $hariLain1,
                'jam_masuk' => '13:00:00',
                'jam_pulang' => '15:00:00',
                'durasi_jam' => 2.00,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Rutin Mingguan (Sesi Siang)',
            ];
        }

        // Siswa 3: ABK (durasi 1.5 jam) pada hari lain (tidak hari ini, untuk menguji state terkunci)
        if ($siswa3 = $siswas->get(2)) {
            $jadwalRutinData[] = [
                'siswa_id' => $siswa3->id,
                'tutor_id' => $tutor3?->id,
                'kategori_tutorial_id' => $katAbk?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $hariLain1,
                'jam_masuk' => '10:00:00',
                'jam_pulang' => '11:30:00',
                'durasi_jam' => 1.50,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Khusus Bimbingan Individual ABK',
            ];

            $jadwalRutinData[] = [
                'siswa_id' => $siswa3->id,
                'tutor_id' => $tutor3?->id,
                'kategori_tutorial_id' => $katAbk?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $hariLain3,
                'jam_masuk' => '10:00:00',
                'jam_pulang' => '11:30:00',
                'durasi_jam' => 1.50,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Khusus Bimbingan Individual ABK',
            ];
        }

        // Siswa 4: Pada hari lain
        if ($siswa4 = $siswas->get(3)) {
            $jadwalRutinData[] = [
                'siswa_id' => $siswa4->id,
                'tutor_id' => $tutor1?->id,
                'kategori_tutorial_id' => $katReguler?->id,
                'jadwal_kerja_id' => $jadwalKerjaKbm?->id,
                'hari' => $hariLain2,
                'jam_masuk' => '14:00:00',
                'jam_pulang' => '16:00:00',
                'durasi_jam' => 2.00,
                'is_active' => true,
                'berlaku_mulai' => Carbon::now('Asia/Jakarta')->subWeeks(2)->toDateString(),
                'keterangan' => 'KBM Rutin Sore Hari',
            ];
        }

        // 6. Simpan Master Jadwal Rutin
        foreach ($jadwalRutinData as $item) {
            if (! $item['siswa_id'] || ! $item['tutor_id']) {
                continue;
            }

            JadwalRutin::updateOrCreate(
                [
                    'siswa_id' => $item['siswa_id'],
                    'hari' => $item['hari'],
                    'jam_masuk' => $item['jam_masuk'],
                ],
                $item
            );
        }

        // 7. Auto-generate Sesi Kalender (jadwal_sesis) untuk 4 Minggu ke Depan
        // Catatan: Ini HANYA membuat instance jadwal_sesis (status 'terjadwal', status_kehadiran 'belum_presensi')
        // tanpa menambahkan record presensi mandiri / presensi tutor, sesuai ketentuan "kecuali di data presensi".
        $service->generateForNextWeeks(4);
    }
}

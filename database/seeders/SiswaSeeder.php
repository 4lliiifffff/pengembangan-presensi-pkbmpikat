<?php

namespace Database\Seeders;

use App\Models\kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Data Kelas Sample jika belum ada
        $kelasA = kelas::firstOrCreate(['nama_kelas' => 'Paket A (Setara SD)']);
        $kelasB = kelas::firstOrCreate(['nama_kelas' => 'Paket B (Setara SMP)']);
        $kelasC = kelas::firstOrCreate(['nama_kelas' => 'Paket C (Setara SMA)']);

        // 2. Ambil ID Tutor Pertama (Tutor Default dari UserRoleSeeder)
        $defaultTutor = Tutor::first();
        $tutorId = $defaultTutor?->id;

        // 3. Data Sample Siswa
        $siswaData = [
            [
                'no_absen' => '001',
                'nama_siswa' => 'Ahmad Rizky Pratama',
                'no_hp' => '081234567001',
                'nama_wali' => 'Bambang Pratama',
                'kelas_id' => $kelasC->id,
                'tutor_id' => $tutorId,
            ],
            [
                'no_absen' => '002',
                'nama_siswa' => 'Siti Nurhaliza',
                'no_hp' => '081234567002',
                'nama_wali' => 'Suryani',
                'kelas_id' => $kelasC->id,
                'tutor_id' => $tutorId,
            ],
            [
                'no_absen' => '003',
                'nama_siswa' => 'Dewi Anggraini',
                'no_hp' => '081234567003',
                'nama_wali' => 'Hendro Utomo',
                'kelas_id' => $kelasB->id,
                'tutor_id' => $tutorId,
            ],
            [
                'no_absen' => '004',
                'nama_siswa' => 'Muhammad Fikri',
                'no_hp' => '081234567004',
                'nama_wali' => 'Khadijah',
                'kelas_id' => $kelasB->id,
                'tutor_id' => $tutorId,
            ],
            [
                'no_absen' => '005',
                'nama_siswa' => 'Anisa Rahmawati',
                'no_hp' => '081234567005',
                'nama_wali' => 'Subagyo',
                'kelas_id' => $kelasA->id,
                'tutor_id' => $tutorId,
            ],
            [
                'no_absen' => '006',
                'nama_siswa' => 'Bintang Permana',
                'no_hp' => '081234567006',
                'nama_wali' => 'Retno Lestari',
                'kelas_id' => $kelasA->id,
                'tutor_id' => $tutorId,
            ],
        ];

        foreach ($siswaData as $data) {
            Siswa::updateOrCreate(
                [
                    'no_absen' => $data['no_absen'],
                    'nama_siswa' => $data['nama_siswa'],
                ],
                $data
            );
        }
    }
}

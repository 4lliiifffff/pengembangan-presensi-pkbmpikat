<?php

namespace Database\Seeders;

use App\Models\JenjangPaket;
use App\Models\kelas;
use App\Models\Murid;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class MuridSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil Data Master Jenjang Paket Pokok (Paket A, Paket B, Paket C)
        $jpA = JenjangPaket::where('kode', 'paket_a')->first();
        $jpB = JenjangPaket::where('kode', 'paket_b')->first();
        $jpC = JenjangPaket::where('kode', 'paket_c')->first();

        // 2. Master Data Kelas Terstruktur per Paket A, B, dan C (Relasi Foreign Key jenjang_paket_id)
        $daftarKelas = [
            // Paket A (Setara SD: Kelas 1 - 6)
            'Paket A - Kelas 1' => kelas::updateOrCreate(['nama_kelas' => 'Paket A - Kelas 1'], ['jenjang_paket_id' => $jpA?->id, 'tingkat' => '1']),
            'Paket A - Kelas 2' => kelas::updateOrCreate(['nama_kelas' => 'Paket A - Kelas 2'], ['jenjang_paket_id' => $jpA?->id, 'tingkat' => '2']),
            'Paket A - Kelas 3' => kelas::updateOrCreate(['nama_kelas' => 'Paket A - Kelas 3'], ['jenjang_paket_id' => $jpA?->id, 'tingkat' => '3']),
            'Paket A - Kelas 4' => kelas::updateOrCreate(['nama_kelas' => 'Paket A - Kelas 4'], ['jenjang_paket_id' => $jpA?->id, 'tingkat' => '4']),
            'Paket A - Kelas 5' => kelas::updateOrCreate(['nama_kelas' => 'Paket A - Kelas 5'], ['jenjang_paket_id' => $jpA?->id, 'tingkat' => '5']),
            'Paket A - Kelas 6' => kelas::updateOrCreate(['nama_kelas' => 'Paket A - Kelas 6'], ['jenjang_paket_id' => $jpA?->id, 'tingkat' => '6']),

            // Paket B (Setara SMP: Kelas 7 - 9)
            'Paket B - Kelas 7' => kelas::updateOrCreate(['nama_kelas' => 'Paket B - Kelas 7'], ['jenjang_paket_id' => $jpB?->id, 'tingkat' => '7']),
            'Paket B - Kelas 8' => kelas::updateOrCreate(['nama_kelas' => 'Paket B - Kelas 8'], ['jenjang_paket_id' => $jpB?->id, 'tingkat' => '8']),
            'Paket B - Kelas 9' => kelas::updateOrCreate(['nama_kelas' => 'Paket B - Kelas 9'], ['jenjang_paket_id' => $jpB?->id, 'tingkat' => '9']),

            // Paket C (Setara SMA: Kelas 10 - 12)
            'Paket C - Kelas 10' => kelas::updateOrCreate(['nama_kelas' => 'Paket C - Kelas 10'], ['jenjang_paket_id' => $jpC?->id, 'tingkat' => '10']),
            'Paket C - Kelas 11' => kelas::updateOrCreate(['nama_kelas' => 'Paket C - Kelas 11'], ['jenjang_paket_id' => $jpC?->id, 'tingkat' => '11']),
            'Paket C - Kelas 12' => kelas::updateOrCreate(['nama_kelas' => 'Paket C - Kelas 12'], ['jenjang_paket_id' => $jpC?->id, 'tingkat' => '12']),
        ];

        // 3. Ambil Data Tutor yang Tersedia
        $tutor1 = Tutor::where('email', 'tutor@pkbmpikat.com')->first() ?? Tutor::first();
        $tutor2 = Tutor::where('email', 'tutor2@pkbmpikat.com')->first() ?? $tutor1;
        $tutor3 = Tutor::where('email', 'tutor3@pkbmpikat.com')->first() ?? $tutor1;

        $t1Id = $tutor1?->id;
        $t2Id = $tutor2?->id;
        $t3Id = $tutor3?->id;

        // 4. Data Sample Murid Tersebar di Jenjang Paket A, B, dan C
        $muridData = [
            // Paket C - Kelas 10 (Single Rombel Skenario 1)
            [
                'no_absen' => '001',
                'nama_murid' => 'Zeldi',
                'is_abk' => false,
                'no_hp' => '081234567001',
                'nama_wali' => 'Bambang Pratama',
                'kelas_id' => $daftarKelas['Paket C - Kelas 10']->id,
                'tutor_id' => $t1Id,
                'status_murid' => 'aktif',
            ],
            // Paket C - Kelas 10 (Multi Rombel Skenario 2: ABK Autisme Ringan)
            [
                'no_absen' => '002',
                'nama_murid' => 'Siti Nurhaliza',
                'is_abk' => true,
                'no_hp' => '081234567002',
                'nama_wali' => 'Suryani',
                'kelas_id' => $daftarKelas['Paket C - Kelas 10']->id,
                'tutor_id' => $t1Id,
                'status_murid' => 'aktif',
            ],
            // Paket C - Kelas 11
            [
                'no_absen' => '003',
                'nama_murid' => 'Rahmat Hidayat',
                'is_abk' => false,
                'no_hp' => '081234567003',
                'nama_wali' => 'Hidayatullah',
                'kelas_id' => $daftarKelas['Paket C - Kelas 11']->id,
                'tutor_id' => $t1Id,
                'status_murid' => 'aktif',
            ],
            // Paket C - Kelas 12
            [
                'no_absen' => '004',
                'nama_murid' => 'Dewi Sartika',
                'is_abk' => false,
                'no_hp' => '081234567004',
                'nama_wali' => 'Raden Somanagara',
                'kelas_id' => $daftarKelas['Paket C - Kelas 12']->id,
                'tutor_id' => $t1Id,
                'status_murid' => 'aktif',
            ],
            // Paket B - Kelas 7 (Multi Jenjang Skenario 3)
            [
                'no_absen' => '005',
                'nama_murid' => 'Budi Santoso',
                'is_abk' => false,
                'no_hp' => '081234567005',
                'nama_wali' => 'Santoso Broto',
                'kelas_id' => $daftarKelas['Paket B - Kelas 7']->id,
                'tutor_id' => $t1Id,
                'status_murid' => 'aktif',
            ],
            // Paket B - Kelas 8 (ABK ADHD)
            [
                'no_absen' => '006',
                'nama_murid' => 'Anisa Rahma',
                'is_abk' => true,
                'no_hp' => '081234567006',
                'nama_wali' => 'Rahmawati',
                'kelas_id' => $daftarKelas['Paket B - Kelas 8']->id,
                'tutor_id' => $t2Id,
                'status_murid' => 'aktif',
            ],
            // Paket B - Kelas 9
            [
                'no_absen' => '007',
                'nama_murid' => 'Farhan Maulana',
                'is_abk' => false,
                'no_hp' => '081234567007',
                'nama_wali' => 'Maulana Malik',
                'kelas_id' => $daftarKelas['Paket B - Kelas 9']->id,
                'tutor_id' => $t2Id,
                'status_murid' => 'aktif',
            ],
            // Paket A - Kelas 1 (Multi Jenjang Skenario 3: ABK Down Syndrome)
            [
                'no_absen' => '008',
                'nama_murid' => 'Gita Gutawa',
                'is_abk' => true,
                'no_hp' => '081234567008',
                'nama_wali' => 'Erwin Gutawa',
                'kelas_id' => $daftarKelas['Paket A - Kelas 1']->id,
                'tutor_id' => $t1Id,
                'status_murid' => 'aktif',
            ],
            // Paket A - Kelas 2
            [
                'no_absen' => '009',
                'nama_murid' => 'Hendra Setiawan',
                'is_abk' => false,
                'no_hp' => '081234567009',
                'nama_wali' => 'Setiawan Djodi',
                'kelas_id' => $daftarKelas['Paket A - Kelas 2']->id,
                'tutor_id' => $t3Id,
                'status_murid' => 'aktif',
            ],
            // Paket A - Kelas 3
            [
                'no_absen' => '010',
                'nama_murid' => 'Indah Permatasari',
                'is_abk' => false,
                'no_hp' => '081234567010',
                'nama_wali' => 'Permata Hati',
                'kelas_id' => $daftarKelas['Paket A - Kelas 3']->id,
                'tutor_id' => $t3Id,
                'status_murid' => 'aktif',
            ],
            // Paket A - Kelas 4 (ABK Disleksia)
            [
                'no_absen' => '011',
                'nama_murid' => 'Joko Anwar',
                'is_abk' => true,
                'no_hp' => '081234567011',
                'nama_wali' => 'Anwar Sanusi',
                'kelas_id' => $daftarKelas['Paket A - Kelas 4']->id,
                'tutor_id' => $t2Id,
                'status_murid' => 'aktif',
            ],
            // Paket A - Kelas 5
            [
                'no_absen' => '012',
                'nama_murid' => 'Kartika Putri',
                'is_abk' => false,
                'no_hp' => '081234567012',
                'nama_wali' => 'Putri Ayu',
                'kelas_id' => $daftarKelas['Paket A - Kelas 5']->id,
                'tutor_id' => $t3Id,
                'status_murid' => 'aktif',
            ],
            // Paket A - Kelas 6
            [
                'no_absen' => '013',
                'nama_murid' => 'Lukman Sardi',
                'is_abk' => false,
                'no_hp' => '081234567013',
                'nama_wali' => 'Idris Sardi',
                'kelas_id' => $daftarKelas['Paket A - Kelas 6']->id,
                'tutor_id' => $t3Id,
                'status_murid' => 'aktif',
            ],
        ];

        // 5. Eksekusi Upsert Data Murid
        foreach ($muridData as $data) {
            Murid::updateOrCreate(
                ['no_absen' => $data['no_absen']],
                $data
            );
        }
    }
}

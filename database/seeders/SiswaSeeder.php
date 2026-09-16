<?php

namespace Database\Seeders;

use App\Models\JenjangPaket;
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
        // 1. Ambil Data Master Jenjang Paket
        $jpA = JenjangPaket::where('kode', 'paket_a')->first();
        $jpB = JenjangPaket::where('kode', 'paket_b')->first();
        $jpC = JenjangPaket::where('kode', 'paket_c')->first();
        $jpVokasi = JenjangPaket::where('kode', 'vokasi')->first();

        // 2. Master Data Kelas Terstruktur per Paket & Jenjang (Relasi Foreign Key jenjang_paket_id)
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

            // Vokasi & Keterampilan
            'Vokasi - Desain Komputer' => kelas::updateOrCreate(['nama_kelas' => 'Vokasi - Desain Komputer'], ['jenjang_paket_id' => $jpVokasi?->id, 'tingkat' => 'Terampil']),
            'Vokasi - Tata Busana' => kelas::updateOrCreate(['nama_kelas' => 'Vokasi - Tata Busana'], ['jenjang_paket_id' => $jpVokasi?->id, 'tingkat' => 'Dasar']),
        ];

        // 3. Ambil Data Tutor yang Tersedia
        $tutor1 = Tutor::where('email', 'tutor@pkbmpikat.com')->first() ?? Tutor::first();
        $tutor2 = Tutor::where('email', 'tutor2@pkbmpikat.com')->first() ?? $tutor1;
        $tutor3 = Tutor::where('email', 'tutor3@pkbmpikat.com')->first() ?? $tutor1;

        $t1Id = $tutor1?->id;
        $t2Id = $tutor2?->id;
        $t3Id = $tutor3?->id;

        // 4. Data Sample Siswa Tersebar di Beberapa Kelas & Paket
        $siswaData = [
            // Paket C - Kelas 10 (Single Rombel Skenario 1)
            [
                'no_absen' => '001',
                'nama_siswa' => 'Ahmad Rizky Pratama',
                'is_abk' => false,
                'no_hp' => '081234567001',
                'nama_wali' => 'Bambang Pratama',
                'kelas_id' => $daftarKelas['Paket C - Kelas 10']->id,
                'tutor_id' => $t1Id,
            ],
            [
                'no_absen' => '002',
                'nama_siswa' => 'Siti Nurhaliza',
                'is_abk' => false,
                'no_hp' => '081234567002',
                'nama_wali' => 'Suryani',
                'kelas_id' => $daftarKelas['Paket C - Kelas 10']->id,
                'tutor_id' => $t1Id,
            ],
            // Paket C - Kelas 11 (Multi Rombel Skenario bersama Kelas 10)
            [
                'no_absen' => '003',
                'nama_siswa' => 'Kevin Sanjaya (ABK)',
                'is_abk' => true,
                'no_hp' => '081234567003',
                'nama_wali' => 'Sanjaya Putra',
                'kelas_id' => $daftarKelas['Paket C - Kelas 11']->id,
                'tutor_id' => $t1Id,
            ],
            [
                'no_absen' => '004',
                'nama_siswa' => 'Nanda Prasetya',
                'is_abk' => false,
                'no_hp' => '081234567004',
                'nama_wali' => 'Prasetya Utama',
                'kelas_id' => $daftarKelas['Paket C - Kelas 12']->id,
                'tutor_id' => $t1Id,
            ],

            // Paket B - Kelas 7 & 8 (Tutor 2)
            [
                'no_absen' => '005',
                'nama_siswa' => 'Dewi Anggraini',
                'is_abk' => false,
                'no_hp' => '081234567005',
                'nama_wali' => 'Hendro Utomo',
                'kelas_id' => $daftarKelas['Paket B - Kelas 7']->id,
                'tutor_id' => $t2Id,
            ],
            [
                'no_absen' => '006',
                'nama_siswa' => 'Muhammad Fikri',
                'is_abk' => false,
                'no_hp' => '081234567006',
                'nama_wali' => 'Khadijah',
                'kelas_id' => $daftarKelas['Paket B - Kelas 7']->id,
                'tutor_id' => $t2Id,
            ],
            [
                'no_absen' => '007',
                'nama_siswa' => 'Rian Ardianto (ABK)',
                'is_abk' => true,
                'no_hp' => '081234567007',
                'nama_wali' => 'Ardianto',
                'kelas_id' => $daftarKelas['Paket B - Kelas 8']->id,
                'tutor_id' => $t2Id,
            ],
            [
                'no_absen' => '008',
                'nama_siswa' => 'Zahra Amelia',
                'is_abk' => false,
                'no_hp' => '081234567008',
                'nama_wali' => 'Lukman Hakim',
                'kelas_id' => $daftarKelas['Paket B - Kelas 9']->id,
                'tutor_id' => $t2Id,
            ],

            // Paket A - Kelas 5 & 6 (Tutor 1 & Tutor 2)
            [
                'no_absen' => '009',
                'nama_siswa' => 'Anisa Rahmawati',
                'is_abk' => false,
                'no_hp' => '081234567009',
                'nama_wali' => 'Subagyo',
                'kelas_id' => $daftarKelas['Paket A - Kelas 5']->id,
                'tutor_id' => $t1Id,
            ],
            [
                'no_absen' => '010',
                'nama_siswa' => 'Bintang Permana (ABK)',
                'is_abk' => true,
                'no_hp' => '081234567010',
                'nama_wali' => 'Retno Lestari',
                'kelas_id' => $daftarKelas['Paket A - Kelas 6']->id,
                'tutor_id' => $t1Id,
            ],

            // Vokasi (Tutor 3)
            [
                'no_absen' => '011',
                'nama_siswa' => 'Dimas Bagaskara',
                'is_abk' => false,
                'no_hp' => '081234567011',
                'nama_wali' => 'Gunawan',
                'kelas_id' => $daftarKelas['Vokasi - Desain Komputer']->id,
                'tutor_id' => $t3Id,
            ],
            [
                'no_absen' => '012',
                'nama_siswa' => 'Putri Ayu Wandira',
                'is_abk' => false,
                'no_hp' => '081234567012',
                'nama_wali' => 'Sri Rahayu',
                'kelas_id' => $daftarKelas['Vokasi - Tata Busana']->id,
                'tutor_id' => $t3Id,
            ],
        ];

        foreach ($siswaData as $data) {
            Siswa::updateOrCreate(
                [
                    'no_absen' => $data['no_absen'],
                ],
                $data
            );
        }
    }
}

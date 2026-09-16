<?php

namespace Database\Seeders;

use App\Models\JenjangPaket;
use Illuminate\Database\Seeder;

class JenjangPaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenjangList = [
            [
                'kode' => 'paket_a',
                'nama_jenjang' => 'Paket A (Setara SD)',
                'tingkat_label' => 'Kelas 1 - 6',
                'keterangan' => 'Pendidikan kesetaraan tingkat dasar setara Sekolah Dasar (SD/MI).',
                'urutan' => 1,
                'is_aktif' => true,
            ],
            [
                'kode' => 'paket_b',
                'nama_jenjang' => 'Paket B (Setara SMP)',
                'tingkat_label' => 'Kelas 7 - 9',
                'keterangan' => 'Pendidikan kesetaraan tingkat menengah pertama setara SMP/MTs.',
                'urutan' => 2,
                'is_aktif' => true,
            ],
            [
                'kode' => 'paket_c',
                'nama_jenjang' => 'Paket C (Setara SMA)',
                'tingkat_label' => 'Kelas 10 - 12',
                'keterangan' => 'Pendidikan kesetaraan tingkat menengah atas setara SMA/MA/SMK.',
                'urutan' => 3,
                'is_aktif' => true,
            ],
            [
                'kode' => 'vokasi',
                'nama_jenjang' => 'Vokasi / Keterampilan Kejuruan',
                'tingkat_label' => 'Dasar / Terampil / Mahir',
                'keterangan' => 'Program pelatihan kejuruan kerja (Desain Komputer, Tata Busana, Otomotif, dll.).',
                'urutan' => 4,
                'is_aktif' => true,
            ],
            [
                'kode' => 'kursus',
                'nama_jenjang' => 'Kursus & Pelatihan Singkat',
                'tingkat_label' => 'Level 1 - 3',
                'keterangan' => 'Kursus intensif dan workshop bersertifikat.',
                'urutan' => 5,
                'is_aktif' => true,
            ],
            [
                'kode' => 'umum',
                'nama_jenjang' => 'Umum / Non-Paket',
                'tingkat_label' => 'Reguler',
                'keterangan' => 'Kelas bimbingan umum atau program non-kesetaraan.',
                'urutan' => 6,
                'is_aktif' => true,
            ],
        ];

        foreach ($jenjangList as $item) {
            JenjangPaket::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}

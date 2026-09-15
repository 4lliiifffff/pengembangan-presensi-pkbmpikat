<?php

namespace Database\Seeders;

use App\Models\KategoriTutorial;
use Illuminate\Database\Seeder;

class KategoriTutorialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriList = [
            [
                'nama_kategori' => 'Tutorial Komunitas',
                'jenis_layanan' => 'komunitas',
                'durasi_jam' => 2.00,
                'is_abk' => false,
                'is_gabungan' => false,
                'nominal_honor' => 75000.00,
                'is_aktif' => true,
                'urutan' => 1,
            ],
            [
                'nama_kategori' => 'Tutorial Komunitas ABK',
                'jenis_layanan' => 'komunitas',
                'durasi_jam' => 2.00,
                'is_abk' => true,
                'is_gabungan' => false,
                'nominal_honor' => 100000.00,
                'is_aktif' => true,
                'urutan' => 2,
            ],
            [
                'nama_kategori' => 'Tutorial Komunitas (Durasi Panjang)',
                'jenis_layanan' => 'komunitas',
                'durasi_jam' => 3.00,
                'is_abk' => false,
                'is_gabungan' => false,
                'nominal_honor' => 100000.00,
                'is_aktif' => true,
                'urutan' => 3,
            ],
            [
                'nama_kategori' => 'Gabungan Komunitas (Per Rombel)',
                'jenis_layanan' => 'komunitas',
                'durasi_jam' => 2.00,
                'is_abk' => false,
                'is_gabungan' => true,
                'nominal_honor' => 50000.00,
                'is_aktif' => true,
                'urutan' => 4,
            ],
            [
                'nama_kategori' => 'Tutorial Distance Learning (DL)',
                'jenis_layanan' => 'dl',
                'durasi_jam' => 1.50,
                'is_abk' => false,
                'is_gabungan' => false,
                'nominal_honor' => 100000.00,
                'is_aktif' => true,
                'urutan' => 5,
            ],
            [
                'nama_kategori' => 'Tutorial Distance Learning (DL) ABK',
                'jenis_layanan' => 'dl',
                'durasi_jam' => 1.50,
                'is_abk' => true,
                'is_gabungan' => false,
                'nominal_honor' => 130000.00,
                'is_aktif' => true,
                'urutan' => 6,
            ],
        ];

        foreach ($kategoriList as $kategori) {
            KategoriTutorial::updateOrCreate(
                [
                    'jenis_layanan' => $kategori['jenis_layanan'],
                    'durasi_jam' => $kategori['durasi_jam'],
                    'is_abk' => $kategori['is_abk'],
                    'is_gabungan' => $kategori['is_gabungan'],
                ],
                $kategori
            );
        }
    }
}

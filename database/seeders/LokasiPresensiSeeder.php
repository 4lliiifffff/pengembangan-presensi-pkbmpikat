<?php

namespace Database\Seeders;

use App\Models\LokasiPresensi;
use Illuminate\Database\Seeder;

class LokasiPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nama = config('lokasi.sekolah_nama', 'PKBM Pikat');
        $lat = (float) config('lokasi.sekolah_lat', -7.8011945);
        $lng = (float) config('lokasi.sekolah_lng', 110.364917);
        $radius = (int) config('lokasi.radius_meter', 100);

        LokasiPresensi::updateOrCreate(
            ['nama_lokasi' => $nama],
            [
                'alamat' => 'Jl. Bantul No. 123, Yogyakarta',
                'tipe' => 'pusat',
                'latitude' => $lat,
                'longitude' => $lng,
                'radius_meter' => $radius,
                'is_active' => true,
                'keterangan' => 'Gedung Utama PKBM Pikat (Pusat Pembelajaran & Kantor)',
            ]
        );
    }
}

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
        $lat = (float) config('lokasi.sekolah_lat', -6.9308526492339295);
        $lng = (float) config('lokasi.sekolah_lng', 107.60746330217363);
        $radius = (int) config('lokasi.radius_meter', 100);

        LokasiPresensi::updateOrCreate(
            ['nama_lokasi' => $nama],
            [
                'alamat' => 'Bandung, Jawa Barat',
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

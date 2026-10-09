<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Fondasi Master Data Sistem
            JenjangPaketSeeder::class,
            KategoriTutorialSeeder::class,
            LokasiPresensiSeeder::class,
            JadwalKerjaSeeder::class,

            // 2. Akun Karyawan & Tutor Resmi (Termasuk Admin & Kepsek)
            KaryawanSeeder::class,

            // 3. Data Murid / Peserta Didik
            MuridSeeder::class,
        ]);
    }
}

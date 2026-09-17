<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserRoleSeeder::class,
            LokasiPresensiSeeder::class,
            JenjangPaketSeeder::class,
            KategoriTutorialSeeder::class,
            JadwalKerjaSeeder::class,
            SiswaSeeder::class,
            SiswaUserSeeder::class,
            MagangSeeder::class,
            JadwalSeeder::class,
            // DummyPresensiSeeder::class,
        ]);
    }
}

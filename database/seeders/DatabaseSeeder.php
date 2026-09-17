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
            SiswaSeeder::class,
            MagangSeeder::class,
            JadwalSeeder::class,
            DummyPresensiSeeder::class,
        ]);
    }
}

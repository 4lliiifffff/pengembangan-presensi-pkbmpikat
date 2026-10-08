<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Master Fondasi Sistem
            JenjangPaketSeeder::class,
            KategoriTutorialSeeder::class,
            LokasiPresensiSeeder::class,
            JadwalKerjaSeeder::class,

            // 2. Akun Pengguna & Profil Entitas
            UserRoleSeeder::class,
            MagangSeeder::class,
            MuridSeeder::class,
            MuridUserSeeder::class,

            // 3. Kalender & Jadwal Operasional KBM
            JadwalSeeder::class,
            JadwalRutinSeeder::class,
        ]);
    }
}

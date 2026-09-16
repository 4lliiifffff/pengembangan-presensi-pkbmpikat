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
            KategoriTutorialSeeder::class,
            SiswaSeeder::class,
            MagangSeeder::class,
            JadwalSeeder::class,
            DummyPresensiSeeder::class,
        ]);
    }
}

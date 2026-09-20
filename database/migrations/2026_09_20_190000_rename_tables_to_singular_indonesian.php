<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $renames = [
            'kepala__sekolahs' => 'kepala_sekolah',
            'jadwal_kerjas' => 'jadwal_kerja',
            'jadwal_rutins' => 'jadwal_rutin',
            'jadwal_sesis' => 'jadwal_sesi',
            'jadwals' => 'jadwal',
            'jenjang_pakets' => 'jenjang_paket',
            'kategori_tutorials' => 'kategori_tutorial',
            'lokasi_presensis' => 'lokasi_presensi',
            'magangs' => 'magang',
            'presensis' => 'presensi',
            'presensi_karyawans' => 'presensi_karyawan',
            'presensi_mandiri_siswas' => 'presensi_mandiri_siswa',
            'siswas' => 'siswa',
            'tutors' => 'tutor',
            'admins' => 'admin',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $reverses = [
            'kepala_sekolah' => 'kepala__sekolahs',
            'jadwal_kerja' => 'jadwal_kerjas',
            'jadwal_rutin' => 'jadwal_rutins',
            'jadwal_sesi' => 'jadwal_sesis',
            'jadwal' => 'jadwals',
            'jenjang_paket' => 'jenjang_pakets',
            'kategori_tutorial' => 'kategori_tutorials',
            'lokasi_presensi' => 'lokasi_presensis',
            'magang' => 'magangs',
            'presensi' => 'presensis',
            'presensi_karyawan' => 'presensi_karyawans',
            'presensi_mandiri_siswa' => 'presensi_mandiri_siswas',
            'siswa' => 'siswas',
            'tutor' => 'tutors',
            'admin' => 'admins',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($reverses as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
        Schema::enableForeignKeyConstraints();
    }
};

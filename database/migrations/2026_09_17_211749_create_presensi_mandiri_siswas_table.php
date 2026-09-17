<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel presensi mandiri siswa — absensi harian siswa di lokasi PKBM.
     *
     * Berbeda dari tabel presensis (yang mencatat sesi mengajar tutor),
     * tabel ini mencatat kehadiran mandiri siswa di lokasi PKBM/komunitas
     * berdasarkan foto dan validasi GPS radius.
     */
    public function up(): void
    {
        Schema::create('presensi_mandiri_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('lokasi_presensi_id')->nullable()->constrained('lokasi_presensis')->nullOnDelete();

            $table->date('tgl_presensi');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();

            $table->string('lokasi_masuk')->nullable();   // "lat,lng,akurasi"
            $table->string('lokasi_pulang')->nullable();

            $table->float('lokasi_akurasi')->nullable();  // akurasi GPS terakhir (meter)
            $table->boolean('is_mocked')->default(false);  // flag fake GPS

            // Status: hadir, izin, sakit, alpha
            $table->string('status', 20)->default('hadir');

            $table->timestamps();

            $table->unique(['siswa_id', 'tgl_presensi'], 'unique_siswa_presensi_per_hari');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_mandiri_siswas');
    }
};

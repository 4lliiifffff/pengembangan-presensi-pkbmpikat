<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_sesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kategori_tutorial_id')->nullable()->constrained('kategori_tutorials')->nullOnDelete();
            $table->foreignId('jadwal_kerja_id')->nullable()->constrained('jadwal_kerjas')->nullOnDelete();
            $table->date('tanggal_rencana');
            $table->time('jam_masuk_rencana');
            $table->time('jam_pulang_rencana');
            $table->decimal('durasi_jam', 4, 2)->default(2.00);
            $table->string('jenis_sesi', 20)->default('pengganti'); // 'reguler', 'pengganti', 'tambahan'
            $table->date('tanggal_asli')->nullable(); // Tanggal sesi awal yang digantikan jika sesi pengganti
            $table->text('alasan_penggantian')->nullable();
            $table->string('status', 20)->default('terjadwal'); // 'terjadwal', 'selesai', 'dibatalkan'
            $table->foreignId('presensi_id')->nullable()->constrained('presensis')->nullOnDelete(); // Presensi Tutor
            $table->unsignedBigInteger('presensi_siswa_id')->nullable(); // Presensi Mandiri Siswa (Masa Depan)
            $table->string('status_kehadiran_siswa', 20)->default('belum_presensi'); // 'belum_presensi', 'hadir', 'izin', 'sakit', 'alpha'
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tutor_id', 'tanggal_rencana']);
            $table->index(['siswa_id', 'tanggal_rencana']);
            $table->index('status');
        });

        Schema::table('presensis', function (Blueprint $table) {
            $table->foreignId('jadwal_sesi_id')->nullable()->after('jadwal_kerja_id')->constrained('jadwal_sesis')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['jadwal_sesi_id']);
            $table->dropColumn('jadwal_sesi_id');
        });

        Schema::dropIfExists('jadwal_sesis');
    }
};

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
        Schema::create('jadwal_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift', 100);
            $table->string('kode_shift', 50)->unique();
            $table->string('jenis_shift', 20)->default('umum'); // 'umum' (karyawan/magang) atau 'kbm' (tutor)
            $table->foreignId('kategori_tutorial_id')->nullable()->constrained('kategori_tutorials')->nullOnDelete();
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->decimal('durasi_jam', 4, 2)->default(8.00);
            $table->unsignedInteger('earliest_minutes')->default(30);
            $table->unsignedInteger('tolerance_minutes')->default(30);
            $table->boolean('is_aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::table('presensis', function (Blueprint $table) {
            $table->foreignId('jadwal_kerja_id')->nullable()->after('status_kehadiran')->constrained('jadwal_kerjas')->nullOnDelete();
        });

        Schema::table('presensi_karyawans', function (Blueprint $table) {
            $table->foreignId('jadwal_kerja_id')->nullable()->after('status_kehadiran')->constrained('jadwal_kerjas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kerja_id']);
            $table->dropColumn('jadwal_kerja_id');
        });

        Schema::table('presensi_karyawans', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kerja_id']);
            $table->dropColumn('jadwal_kerja_id');
        });

        Schema::dropIfExists('jadwal_kerjas');
    }
};

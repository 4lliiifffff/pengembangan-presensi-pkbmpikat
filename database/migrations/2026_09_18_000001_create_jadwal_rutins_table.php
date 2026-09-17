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
        Schema::create('jadwal_rutins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete();
            $table->foreignId('kategori_tutorial_id')->nullable()->constrained('kategori_tutorials')->nullOnDelete();
            $table->foreignId('jadwal_kerja_id')->nullable()->constrained('jadwal_kerjas')->nullOnDelete();
            $table->string('hari', 10); // 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->decimal('durasi_jam', 4, 2)->default(2.00);
            $table->boolean('is_active')->default(true);
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'hari']);
            $table->index(['tutor_id', 'hari']);
            $table->index('is_active');
        });

        Schema::table('jadwal_sesis', function (Blueprint $table) {
            $table->foreignId('jadwal_rutin_id')->nullable()->after('jadwal_kerja_id')->constrained('jadwal_rutins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_sesis', function (Blueprint $table) {
            $table->dropForeign(['jadwal_rutin_id']);
            $table->dropColumn('jadwal_rutin_id');
        });

        Schema::dropIfExists('jadwal_rutins');
    }
};

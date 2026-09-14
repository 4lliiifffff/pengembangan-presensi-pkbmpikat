<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            // Kolom baru untuk agenda
            if (! Schema::hasColumn('jadwals', 'judul')) {
                $table->string('judul', 200)->after('id');
            }
            if (! Schema::hasColumn('jadwals', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('judul');
            }
            if (! Schema::hasColumn('jadwals', 'lokasi')) {
                $table->string('lokasi', 255)->nullable()->after('deskripsi');
            }

            // Kolom lama dijadikan nullable (agar data lama tidak rusak)
            if (Schema::hasColumn('jadwals', 'tutor_id')) {
                $table->foreignId('tutor_id')->nullable()->change();
            }
            if (Schema::hasColumn('jadwals', 'siswa_id')) {
                $table->foreignId('siswa_id')->nullable()->change();
            }
            if (Schema::hasColumn('jadwals', 'mata_pelajaran')) {
                $table->string('mata_pelajaran', 100)->nullable()->change();
            }
            if (Schema::hasColumn('jadwals', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->change();
            }
            if (Schema::hasColumn('jadwals', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            foreach (['judul', 'deskripsi', 'lokasi'] as $col) {
                if (Schema::hasColumn('jadwals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

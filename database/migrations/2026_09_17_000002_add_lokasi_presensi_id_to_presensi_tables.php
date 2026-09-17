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
        Schema::table('presensis', function (Blueprint $table) {
            $table->foreignId('lokasi_presensi_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('lokasi_presensis')
                ->nullOnDelete();
        });

        Schema::table('presensi_karyawans', function (Blueprint $table) {
            $table->foreignId('lokasi_presensi_id')
                ->nullable()
                ->after('user_id')
                ->constrained('lokasi_presensis')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['lokasi_presensi_id']);
            $table->dropColumn('lokasi_presensi_id');
        });

        Schema::table('presensi_karyawans', function (Blueprint $table) {
            $table->dropForeign(['lokasi_presensi_id']);
            $table->dropColumn('lokasi_presensi_id');
        });
    }
};

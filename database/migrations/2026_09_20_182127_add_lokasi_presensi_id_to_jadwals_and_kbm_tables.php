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
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreignId('lokasi_presensi_id')->nullable()->after('lokasi')->constrained('lokasi_presensis')->nullOnDelete();
        });

        Schema::table('jadwal_rutins', function (Blueprint $table) {
            $table->foreignId('lokasi_presensi_id')->nullable()->after('jadwal_kerja_id')->constrained('lokasi_presensis')->nullOnDelete();
        });

        Schema::table('jadwal_sesis', function (Blueprint $table) {
            $table->foreignId('lokasi_presensi_id')->nullable()->after('jadwal_kerja_id')->constrained('lokasi_presensis')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_sesis', function (Blueprint $table) {
            $table->dropForeign(['lokasi_presensi_id']);
            $table->dropColumn('lokasi_presensi_id');
        });

        Schema::table('jadwal_rutins', function (Blueprint $table) {
            $table->dropForeign(['lokasi_presensi_id']);
            $table->dropColumn('lokasi_presensi_id');
        });

        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropForeign(['lokasi_presensi_id']);
            $table->dropColumn('lokasi_presensi_id');
        });
    }
};

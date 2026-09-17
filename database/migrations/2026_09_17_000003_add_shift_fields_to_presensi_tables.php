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
            $table->string('shift_nama', 50)->nullable()->after('status');
            $table->string('status_kehadiran', 30)->default('tepat_waktu')->after('shift_nama');
            $table->unsignedInteger('menit_keterlambatan')->default(0)->after('status_kehadiran');
        });

        Schema::table('presensi_karyawans', function (Blueprint $table) {
            $table->string('shift_nama', 50)->nullable()->after('status');
            $table->string('status_kehadiran', 30)->default('tepat_waktu')->after('shift_nama');
            $table->unsignedInteger('menit_keterlambatan')->default(0)->after('status_kehadiran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn(['shift_nama', 'status_kehadiran', 'menit_keterlambatan']);
        });

        Schema::table('presensi_karyawans', function (Blueprint $table) {
            $table->dropColumn(['shift_nama', 'status_kehadiran', 'menit_keterlambatan']);
        });
    }
};

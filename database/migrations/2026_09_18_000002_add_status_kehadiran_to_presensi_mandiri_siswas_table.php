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
        Schema::table('presensi_mandiri_siswas', function (Blueprint $table) {
            $table->string('status_kehadiran', 20)->default('tepat_waktu')->after('status');
            $table->unsignedInteger('menit_keterlambatan')->default(0)->after('status_kehadiran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_mandiri_siswas', function (Blueprint $table) {
            $table->dropColumn(['status_kehadiran', 'menit_keterlambatan']);
        });
    }
};

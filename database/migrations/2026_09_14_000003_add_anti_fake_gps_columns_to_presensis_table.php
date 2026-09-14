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
            $table->decimal('lokasi_akurasi', 8, 2)->nullable()->after('lokasi_selesai');
            $table->boolean('is_mocked')->default(false)->after('lokasi_akurasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn(['lokasi_akurasi', 'is_mocked']);
        });
    }
};

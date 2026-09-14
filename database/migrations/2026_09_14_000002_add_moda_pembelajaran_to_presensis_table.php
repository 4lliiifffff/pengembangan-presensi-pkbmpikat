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
            if (! Schema::hasColumn('presensis', 'moda_pembelajaran')) {
                $table->enum('moda_pembelajaran', ['sekolah', 'kunjungan_rumah', 'online'])
                    ->default('sekolah')
                    ->after('siswa_id');
            }
            if (! Schema::hasColumn('presensis', 'link_daring')) {
                $table->string('link_daring', 255)->nullable()->after('moda_pembelajaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            if (Schema::hasColumn('presensis', 'link_daring')) {
                $table->dropColumn('link_daring');
            }
            if (Schema::hasColumn('presensis', 'moda_pembelajaran')) {
                $table->dropColumn('moda_pembelajaran');
            }
        });
    }
};

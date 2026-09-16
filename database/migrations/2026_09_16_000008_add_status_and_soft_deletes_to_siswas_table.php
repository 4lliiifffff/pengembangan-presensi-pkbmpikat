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
        Schema::table('siswas', function (Blueprint $table) {
            if (! Schema::hasColumn('siswas', 'status_siswa')) {
                $table->string('status_siswa', 20)->default('aktif')->after('nama_wali');
            }
            if (! Schema::hasColumn('siswas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'status_siswa')) {
                $table->dropColumn('status_siswa');
            }
            if (Schema::hasColumn('siswas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

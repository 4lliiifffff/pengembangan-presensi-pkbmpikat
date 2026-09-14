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
        if (! Schema::hasTable('siswas')) {
            return;
        }

        Schema::table('siswas', function (Blueprint $table) {
            if (! Schema::hasColumn('siswas', 'nama_wali')) {
                $table->string('nama_wali', 120)->nullable()->after('no_hp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('siswas')) {
            return;
        }

        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'nama_wali')) {
                $table->dropColumn('nama_wali');
            }
        });
    }
};

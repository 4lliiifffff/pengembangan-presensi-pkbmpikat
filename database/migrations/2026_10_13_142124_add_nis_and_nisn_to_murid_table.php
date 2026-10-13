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
        $tableName = Schema::hasTable('murid') ? 'murid' : (Schema::hasTable('siswa') ? 'siswa' : 'siswas');

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'nis')) {
                $table->string('nis', 30)->nullable()->index()->after('no_absen');
            }
            if (! Schema::hasColumn($tableName, 'nisn')) {
                $table->string('nisn', 30)->nullable()->index()->after('nis');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('murid') ? 'murid' : (Schema::hasTable('siswa') ? 'siswa' : 'siswas');

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'nisn')) {
                $table->dropColumn('nisn');
            }
            if (Schema::hasColumn($tableName, 'nis')) {
                $table->dropColumn('nis');
            }
        });
    }
};

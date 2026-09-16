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
        Schema::table('kelas', function (Blueprint $table) {
            if (! Schema::hasColumn('kelas', 'jenjang_paket')) {
                $table->string('jenjang_paket', 50)->default('umum')->after('nama_kelas');
            }
            if (! Schema::hasColumn('kelas', 'tingkat')) {
                $table->string('tingkat', 50)->nullable()->after('jenjang_paket');
            }
            if (! Schema::hasColumn('kelas', 'keterangan')) {
                $table->string('keterangan', 255)->nullable()->after('tingkat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('kelas', 'jenjang_paket')) {
                $columnsToDrop[] = 'jenjang_paket';
            }
            if (Schema::hasColumn('kelas', 'tingkat')) {
                $columnsToDrop[] = 'tingkat';
            }
            if (Schema::hasColumn('kelas', 'keterangan')) {
                $columnsToDrop[] = 'keterangan';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

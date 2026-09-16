<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (! Schema::hasColumn('kelas', 'jenjang_paket_id')) {
                $table->foreignId('jenjang_paket_id')
                    ->nullable()
                    ->after('nama_kelas')
                    ->constrained('jenjang_pakets')
                    ->nullOnDelete();
            }
        });

        // Backfill data: petakan nilai string jenjang_paket lama ke id di tabel jenjang_pakets
        if (Schema::hasColumn('kelas', 'jenjang_paket') && Schema::hasTable('jenjang_pakets')) {
            $jenjangs = DB::table('jenjang_pakets')->get();
            foreach ($jenjangs as $jp) {
                DB::table('kelas')
                    ->where('jenjang_paket', $jp->kode)
                    ->update(['jenjang_paket_id' => $jp->id]);
            }

            // Hapus kolom string legacy setelah data berhasil dipindahkan
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropColumn('jenjang_paket');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (! Schema::hasColumn('kelas', 'jenjang_paket')) {
                $table->string('jenjang_paket', 50)->default('umum')->after('nama_kelas');
            }
        });

        if (Schema::hasColumn('kelas', 'jenjang_paket_id') && Schema::hasTable('jenjang_pakets')) {
            $jenjangs = DB::table('jenjang_pakets')->get()->keyBy('id');
            $allKelas = DB::table('kelas')->whereNotNull('jenjang_paket_id')->get();
            foreach ($allKelas as $k) {
                if (isset($jenjangs[$k->jenjang_paket_id])) {
                    DB::table('kelas')
                        ->where('id', $k->id)
                        ->update(['jenjang_paket' => $jenjangs[$k->jenjang_paket_id]->kode]);
                }
            }

            Schema::table('kelas', function (Blueprint $table) {
                $table->dropForeign(['jenjang_paket_id']);
                $table->dropColumn('jenjang_paket_id');
            });
        }
    }
};

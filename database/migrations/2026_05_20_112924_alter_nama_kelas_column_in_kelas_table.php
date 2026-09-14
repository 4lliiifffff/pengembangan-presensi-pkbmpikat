<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perlebar kolom nama_kelas (mis. dari VARCHAR(20)) agar nama panjang bisa disimpan.
     */
    public function up(): void
    {
        if (! Schema::hasTable('kelas') || ! Schema::hasColumn('kelas', 'nama_kelas')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE kelas MODIFY nama_kelas VARCHAR(255) NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite tidak mendukung MODIFY; kolom string default sudah cukup panjang
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('kelas') || ! Schema::hasColumn('kelas', 'nama_kelas')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE kelas MODIFY nama_kelas VARCHAR(20) NOT NULL');
        }
    }
};

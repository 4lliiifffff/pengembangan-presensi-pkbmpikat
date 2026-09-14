<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 20)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'tutor', 'kepala_sekolah'])->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('users', 'nik')) {
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            }
        });
    }
};

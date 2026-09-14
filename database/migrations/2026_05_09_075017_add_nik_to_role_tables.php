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
        // Untuk tabel admins
        if (Schema::hasTable('admins') && ! Schema::hasColumn('admins', 'nik')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('nik', 20)->nullable()->unique()->after('user_id');
            });
        }

        // Untuk tabel tutors
        if (Schema::hasTable('tutors') && ! Schema::hasColumn('tutors', 'nik')) {
            Schema::table('tutors', function (Blueprint $table) {
                $table->string('nik', 20)->nullable()->unique()->after('user_id');
            });
        }

        // Untuk tabel kepala__sekolahs
        if (Schema::hasTable('kepala__sekolahs') && ! Schema::hasColumn('kepala__sekolahs', 'nik')) {
            Schema::table('kepala__sekolahs', function (Blueprint $table) {
                $table->string('nik', 20)->nullable()->unique()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'nik')) {
            Schema::table('admins', function (Blueprint $table) {
                // Drop index unique terlebih dahulu, namanya biasanya <table_name>_<column>_unique
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            });
        }

        if (Schema::hasTable('tutors') && Schema::hasColumn('tutors', 'nik')) {
            Schema::table('tutors', function (Blueprint $table) {
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            });
        }

        if (Schema::hasTable('kepala__sekolahs') && Schema::hasColumn('kepala__sekolahs', 'nik')) {
            Schema::table('kepala__sekolahs', function (Blueprint $table) {
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            });
        }
    }
};

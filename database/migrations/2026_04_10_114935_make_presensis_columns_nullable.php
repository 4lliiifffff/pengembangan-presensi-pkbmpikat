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
            $table->unsignedBigInteger('siswa_id')->nullable()->change();
            $table->time('jam_mulai')->nullable()->change();
            $table->time('jam_selesai')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->unsignedBigInteger('siswa_id')->nullable(false)->change();
            $table->time('jam_mulai')->nullable(false)->change();
            $table->time('jam_selesai')->nullable(false)->change();
        });
    }
};

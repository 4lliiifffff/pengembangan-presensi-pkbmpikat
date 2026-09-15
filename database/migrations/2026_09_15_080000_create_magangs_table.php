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
        Schema::create('magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nim_nisn', 50)->nullable()->index();
            $table->string('nama_lengkap', 150)->nullable();
            $table->string('asal_instansi', 150)->nullable();
            $table->string('jurusan', 150)->nullable();
            $table->string('jurusan_prodi', 150)->nullable();
            $table->string('pembimbing_lapangan', 150)->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->date('tgl_mulai_magang')->nullable();
            $table->date('tgl_selesai_magang')->nullable();
            $table->string('status', 30)->default('aktif');
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magangs');
    }
};

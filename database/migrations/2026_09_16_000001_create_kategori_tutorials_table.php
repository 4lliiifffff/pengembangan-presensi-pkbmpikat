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
        Schema::create('kategori_tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori'); // e.g. "Tutorial Komunitas (2 Jam)", "Tutorial Distance Learning ABK (1.5 Jam)"
            $table->string('jenis_layanan')->default('komunitas'); // 'komunitas', 'dl', 'lainnya'
            $table->decimal('durasi_jam', 4, 2)->default(2.00); // 1.50, 2.00, 3.00
            $table->boolean('is_abk')->default(false);
            $table->boolean('is_gabungan')->default(false);
            $table->decimal('nominal_honor', 12, 2)->default(75000.00);
            $table->boolean('is_aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_tutorials');
    }
};

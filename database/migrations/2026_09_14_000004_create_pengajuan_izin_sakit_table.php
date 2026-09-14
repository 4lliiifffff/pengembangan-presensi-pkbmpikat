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
        Schema::create('pengajuan_izin_sakit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
            $table->enum('jenis', ['izin', 'sakit']);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->text('alasan');
            $table->string('dokumen_surat')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin_sakit');
    }
};

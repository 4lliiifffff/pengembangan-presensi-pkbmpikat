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
        if (Schema::hasTable('lapor__lapors') && ! Schema::hasTable('pengajuan_lupa_lapor')) {
            Schema::rename('lapor__lapors', 'pengajuan_lupa_lapor');
        }

        if (! Schema::hasTable('pengajuan_lupa_lapor')) {
            Schema::create('pengajuan_lupa_lapor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
                $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
                $table->date('tanggal');
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->text('alasan');
                $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
                $table->text('catatan_kepsek')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('pengajuan_lupa_lapor', function (Blueprint $table) {
                if (! Schema::hasColumn('pengajuan_lupa_lapor', 'status')) {
                    $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending')->after('alasan');
                }
                if (! Schema::hasColumn('pengajuan_lupa_lapor', 'catatan_kepsek')) {
                    $table->text('catatan_kepsek')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pengajuan_lupa_lapor')) {
            Schema::table('pengajuan_lupa_lapor', function (Blueprint $table) {
                if (Schema::hasColumn('pengajuan_lupa_lapor', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('pengajuan_lupa_lapor', 'catatan_kepsek')) {
                    $table->dropColumn('catatan_kepsek');
                }
            });
        }
    }
};

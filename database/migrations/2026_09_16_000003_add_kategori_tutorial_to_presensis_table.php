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
            $table->decimal('durasi_pilihan', 4, 2)->nullable()->after('link_daring');
            $table->foreignId('kategori_tutorial_id')->nullable()->after('durasi_pilihan')->constrained('kategori_tutorials')->nullOnDelete();
            $table->decimal('nominal_honor_snapshot', 12, 2)->nullable()->after('kategori_tutorial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['kategori_tutorial_id']);
            $table->dropColumn(['durasi_pilihan', 'kategori_tutorial_id', 'nominal_honor_snapshot']);
        });
    }
};

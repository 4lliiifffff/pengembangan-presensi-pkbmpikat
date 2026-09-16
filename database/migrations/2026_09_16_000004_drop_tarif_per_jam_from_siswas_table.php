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
        if (Schema::hasColumn('siswas', 'tarif_per_jam')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn('tarif_per_jam');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('siswas', 'tarif_per_jam')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->decimal('tarif_per_jam', 12, 2)->default(75000.00)->after('is_abk');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Rename tabel utama
        if (Schema::hasTable('siswa') && ! Schema::hasTable('murid')) {
            Schema::rename('siswa', 'murid');
        } elseif (Schema::hasTable('siswas') && ! Schema::hasTable('murid')) {
            Schema::rename('siswas', 'murid');
        }

        if (Schema::hasTable('presensi_mandiri_siswa') && ! Schema::hasTable('presensi_mandiri_murid')) {
            Schema::rename('presensi_mandiri_siswa', 'presensi_mandiri_murid');
        } elseif (Schema::hasTable('presensi_mandiri_siswas') && ! Schema::hasTable('presensi_mandiri_murid')) {
            Schema::rename('presensi_mandiri_siswas', 'presensi_mandiri_murid');
        }

        // 2. Rename kolom di tabel murid
        if (Schema::hasTable('murid')) {
            Schema::table('murid', function (Blueprint $table) {
                if (Schema::hasColumn('murid', 'nama_siswa') && ! Schema::hasColumn('murid', 'nama_murid')) {
                    $table->renameColumn('nama_siswa', 'nama_murid');
                }
                if (Schema::hasColumn('murid', 'status_siswa') && ! Schema::hasColumn('murid', 'status_murid')) {
                    $table->renameColumn('status_siswa', 'status_murid');
                }
            });
        }

        // 3. Rename kolom di tabel presensi_mandiri_murid
        if (Schema::hasTable('presensi_mandiri_murid')) {
            Schema::table('presensi_mandiri_murid', function (Blueprint $table) {
                if (Schema::hasColumn('presensi_mandiri_murid', 'siswa_id') && ! Schema::hasColumn('presensi_mandiri_murid', 'murid_id')) {
                    $table->renameColumn('siswa_id', 'murid_id');
                }
            });
        }

        // 4. Rename kolom di tabel jadwal_sesi
        if (Schema::hasTable('jadwal_sesi')) {
            Schema::table('jadwal_sesi', function (Blueprint $table) {
                if (Schema::hasColumn('jadwal_sesi', 'siswa_id') && ! Schema::hasColumn('jadwal_sesi', 'murid_id')) {
                    $table->renameColumn('siswa_id', 'murid_id');
                }
                if (Schema::hasColumn('jadwal_sesi', 'presensi_siswa_id') && ! Schema::hasColumn('jadwal_sesi', 'presensi_murid_id')) {
                    $table->renameColumn('presensi_siswa_id', 'presensi_murid_id');
                }
                if (Schema::hasColumn('jadwal_sesi', 'status_kehadiran_siswa') && ! Schema::hasColumn('jadwal_sesi', 'status_kehadiran_murid')) {
                    $table->renameColumn('status_kehadiran_siswa', 'status_kehadiran_murid');
                }
            });
        }

        // 5. Rename kolom di tabel jadwal_rutin
        if (Schema::hasTable('jadwal_rutin')) {
            Schema::table('jadwal_rutin', function (Blueprint $table) {
                if (Schema::hasColumn('jadwal_rutin', 'siswa_id') && ! Schema::hasColumn('jadwal_rutin', 'murid_id')) {
                    $table->renameColumn('siswa_id', 'murid_id');
                }
            });
        }

        // 6. Rename kolom di tabel presensi
        if (Schema::hasTable('presensi')) {
            Schema::table('presensi', function (Blueprint $table) {
                if (Schema::hasColumn('presensi', 'siswa_id') && ! Schema::hasColumn('presensi', 'murid_id')) {
                    $table->renameColumn('siswa_id', 'murid_id');
                }
            });
        }

        // 7. Rename kolom di tabel pengajuan_lupa_lapor
        if (Schema::hasTable('pengajuan_lupa_lapor')) {
            Schema::table('pengajuan_lupa_lapor', function (Blueprint $table) {
                if (Schema::hasColumn('pengajuan_lupa_lapor', 'siswa_id') && ! Schema::hasColumn('pengajuan_lupa_lapor', 'murid_id')) {
                    $table->renameColumn('siswa_id', 'murid_id');
                }
            });
        }

        // 8. Update role users dari siswa ke murid
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'siswa')->update(['role' => 'murid']);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'murid')->update(['role' => 'siswa']);
        }

        if (Schema::hasTable('pengajuan_lupa_lapor')) {
            Schema::table('pengajuan_lupa_lapor', function (Blueprint $table) {
                if (Schema::hasColumn('pengajuan_lupa_lapor', 'murid_id') && ! Schema::hasColumn('pengajuan_lupa_lapor', 'siswa_id')) {
                    $table->renameColumn('murid_id', 'siswa_id');
                }
            });
        }

        if (Schema::hasTable('presensi')) {
            Schema::table('presensi', function (Blueprint $table) {
                if (Schema::hasColumn('presensi', 'murid_id') && ! Schema::hasColumn('presensi', 'siswa_id')) {
                    $table->renameColumn('murid_id', 'siswa_id');
                }
            });
        }

        if (Schema::hasTable('jadwal_rutin')) {
            Schema::table('jadwal_rutin', function (Blueprint $table) {
                if (Schema::hasColumn('jadwal_rutin', 'murid_id') && ! Schema::hasColumn('jadwal_rutin', 'siswa_id')) {
                    $table->renameColumn('murid_id', 'siswa_id');
                }
            });
        }

        if (Schema::hasTable('jadwal_sesi')) {
            Schema::table('jadwal_sesi', function (Blueprint $table) {
                if (Schema::hasColumn('jadwal_sesi', 'murid_id') && ! Schema::hasColumn('jadwal_sesi', 'siswa_id')) {
                    $table->renameColumn('murid_id', 'siswa_id');
                }
                if (Schema::hasColumn('jadwal_sesi', 'presensi_murid_id') && ! Schema::hasColumn('jadwal_sesi', 'presensi_siswa_id')) {
                    $table->renameColumn('presensi_murid_id', 'presensi_siswa_id');
                }
                if (Schema::hasColumn('jadwal_sesi', 'status_kehadiran_murid') && ! Schema::hasColumn('jadwal_sesi', 'status_kehadiran_siswa')) {
                    $table->renameColumn('status_kehadiran_murid', 'status_kehadiran_siswa');
                }
            });
        }

        if (Schema::hasTable('presensi_mandiri_murid')) {
            Schema::table('presensi_mandiri_murid', function (Blueprint $table) {
                if (Schema::hasColumn('presensi_mandiri_murid', 'murid_id') && ! Schema::hasColumn('presensi_mandiri_murid', 'siswa_id')) {
                    $table->renameColumn('murid_id', 'siswa_id');
                }
            });
        }

        if (Schema::hasTable('murid')) {
            Schema::table('murid', function (Blueprint $table) {
                if (Schema::hasColumn('murid', 'nama_murid') && ! Schema::hasColumn('murid', 'nama_siswa')) {
                    $table->renameColumn('nama_murid', 'nama_siswa');
                }
                if (Schema::hasColumn('murid', 'status_murid') && ! Schema::hasColumn('murid', 'status_siswa')) {
                    $table->renameColumn('status_murid', 'status_siswa');
                }
            });
        }

        if (Schema::hasTable('presensi_mandiri_murid') && ! Schema::hasTable('presensi_mandiri_siswa')) {
            Schema::rename('presensi_mandiri_murid', 'presensi_mandiri_siswa');
        }

        if (Schema::hasTable('murid') && ! Schema::hasTable('siswa')) {
            Schema::rename('murid', 'siswa');
        }

        Schema::enableForeignKeyConstraints();
    }
};

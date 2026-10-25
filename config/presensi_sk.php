<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Aturan Presensi Berdasarkan SK
    |--------------------------------------------------------------------------
    |
    | Batas paling awal: 30 menit sebelum jam shift dimulai.
    | Toleransi keterlambatan: 30 menit setelah jam shift dimulai.
    |
    */

    'earliest_minutes' => (int) env('PRESENSI_EARLIEST_MINUTES', 30),
    'tolerance_minutes' => (int) env('PRESENSI_TOLERANCE_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Daftar Jadwal Shift Kerja
    |--------------------------------------------------------------------------
    */
    'shifts' => [
        'pagi' => [
            'id' => 'pagi',
            'nama' => 'Shift Reguler Magang',
            'jam_masuk' => '09:00',
            'jam_pulang' => '15:00',
        ],
        'siang' => [
            'id' => 'siang',
            'nama' => 'Shift Siang Magang',
            'jam_masuk' => '12:00',
            'jam_pulang' => '17:00',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Shift Key
    |--------------------------------------------------------------------------
    */
    'default_shift' => 'pagi',

    /*
    |--------------------------------------------------------------------------
    | Kebijakan Validasi Jadwal Presensi Siswa (Schedule Gating)
    |--------------------------------------------------------------------------
    |
    | true  : Mode Ketat (Strict) - Siswa WAJIB memiliki jadwal sesi KBM aktif
    |         hari ini (JadwalSesi). Jika tidak ada jadwal sama sekali di DB,
    |         halaman presensi terkunci dengan status "Tidak Ada Jadwal Belajar".
    | false : Mode Longgar (Graceful) - Siswa yang belum pernah memiliki jadwal
    |         dapat melakukan presensi mandiri terbuka.
    |
    */
    'strict_schedule_gating' => (bool) env('PRESENSI_STRICT_SCHEDULE_GATING', true),
];

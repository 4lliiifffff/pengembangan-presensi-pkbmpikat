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
            'nama' => 'Shift Pagi',
            'jam_masuk' => '08:00',
            'jam_pulang' => '16:00',
        ],
        'siang' => [
            'id' => 'siang',
            'nama' => 'Shift Siang',
            'jam_masuk' => '13:00',
            'jam_pulang' => '17:00',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Shift Key
    |--------------------------------------------------------------------------
    */
    'default_shift' => 'pagi',
];

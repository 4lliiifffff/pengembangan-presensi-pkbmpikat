<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi mengajar di sekolah (tetap)
    |--------------------------------------------------------------------------
    |
    | Tautan Google Maps untuk alamat sekolah. Bisa diubah lewat .env.
    |
    */

    'sekolah_nama' => env('SEKOLAH_NAMA', 'PKBM Pikat'),

    'sekolah_maps_url' => env(
        'SEKOLAH_MAPS_URL',
        'https://maps.app.goo.gl/ahQ61nnpNQ9Z6RcWA'
    ),

    /*
    |--------------------------------------------------------------------------
    | Geofencing Titik Koordinat & Radius Toleransi (Meter)
    |--------------------------------------------------------------------------
    |
    | Titik koordinat pusat sekolah PKBM Pikat dan radius toleransi geofence.
    |
    */
    'sekolah_lat' => (float) env('SEKOLAH_LAT', -7.8011945),
    'sekolah_lng' => (float) env('SEKOLAH_LNG', 110.364917),
    'radius_meter' => (float) env('SEKOLAH_RADIUS_METER', 100),

    /*
    |--------------------------------------------------------------------------
    | Anti Fake GPS & Batas Akurasi Sinyal (Meter)
    |--------------------------------------------------------------------------
    |
    | Toleransi maksimal tingkat akurasi lokasi GPS dari peramban/perangkat.
    |
    */
    'max_accuracy_meter' => (float) env('SEKOLAH_MAX_ACCURACY_METER', 200),
];

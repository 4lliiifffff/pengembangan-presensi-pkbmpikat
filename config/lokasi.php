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

    'sekolah_nama' => env('SEKOLAH_NAMA', 'Lokasi sekolah'),

    'sekolah_maps_url' => env(
        'SEKOLAH_MAPS_URL',
        'https://maps.app.goo.gl/ahQ61nnpNQ9Z6RcWA'
    ),

];

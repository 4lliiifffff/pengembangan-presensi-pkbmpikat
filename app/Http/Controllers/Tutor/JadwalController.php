<?php

namespace App\Http\Controllers\Tutor;

/**
 * Controller utama untuk mengelola jadwal bimbingan belajar tutor,
 * pola rutin mingguan, reschedule sesi, integrasi agenda PKBM, serta impor/ekspor jadwal.
 */
class JadwalController extends JadwalSesiController
{
    // Mewarisi seluruh kapabilitas terpadu dari JadwalSesiController
    // untuk standardisasi penamaan resource route /tutor/jadwal.
}

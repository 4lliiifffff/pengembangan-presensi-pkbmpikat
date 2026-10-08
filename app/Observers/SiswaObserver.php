<?php

namespace App\Observers;

/**
 * SiswaObserver (Legacy Compatibility Alias untuk MuridObserver)
 *
 * Seluruh logika pembuatan, pembaruan, dan sinkronisasi akun pengguna
 * telah distandardisasi dan dipusatkan pada MuridObserver.
 */
class SiswaObserver extends MuridObserver
{
    // Mewarisi seluruh lifecycle hooks dari MuridObserver
}

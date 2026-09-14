<?php

use App\Http\Controllers\Admin\IzinController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanPresensiController;
use App\Http\Controllers\Kepsek\KepsekDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tutor\LupaLaporController;
use App\Http\Controllers\Tutor\PresensiFotoController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/login', function () {
    return view('auth.login');
});

Route::post('/login', [AuthWebController::class, 'process'])
    ->middleware('throttle:login')
    ->name('login.process');

Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('profil')->name('profil.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::patch('/update', [ProfileController::class, 'update'])->name('update');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.exportExcel');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.exportPdf');

    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('jadwal', JadwalController::class);
    Route::resource('karyawan', KaryawanController::class);

    Route::patch('karyawan/{id}/status', [KaryawanController::class, 'status'])->name('karyawan.status');

    // Route presensi karyawan (Admin/Kepsek)
    Route::get('/presensi', [KaryawanPresensiController::class, 'index'])->name('presensi');
    Route::post('/presensi', [KaryawanPresensiController::class, 'store'])->name('presensi.store');
    // Kelola Izin Tutor
    Route::get('/izin', [IzinController::class, 'index'])->name('izin.index');
    Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');
    Route::delete('/izin/{id}', [IzinController::class, 'destroy'])->name('izin.destroy');
    Route::get('/izin/siswa/{tutorId}', [IzinController::class, 'getSiswaByTutor'])->name('izin.siswa');
});

Route::middleware(['auth', 'role:tutor'])->prefix('tutor')->name('tutor.')->group(function () {
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat', [TutorDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/jadwal', [TutorDashboardController::class, 'jadwal'])->name('jadwal');
    Route::get('/presensi', [PresensiFotoController::class, 'index'])->name('presensi');
    Route::post('/presensi', [PresensiFotoController::class, 'store'])->name('presensi.store');
    Route::get('/lupa-lapor', [LupaLaporController::class, 'index'])->name('lupa-lapor');
    Route::post('/lupa-lapor', [LupaLaporController::class, 'store'])->name('lupa-lapor.store');
    Route::delete('/lupa-lapor/{id}', [LupaLaporController::class, 'destroy'])->name('lupa-lapor.destroy');
});

Route::middleware(['auth', 'role:kepala_sekolah'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepsekDashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [KepsekDashboardController::class, 'laporan'])->name('laporan');
    Route::get('/laporan/export/pdf', [KepsekDashboardController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/presensi', [KaryawanPresensiController::class, 'index'])->name('presensi');
    Route::post('/presensi', [KaryawanPresensiController::class, 'store'])->name('presensi.store');
    Route::get('/presensi-tutor', [KepsekDashboardController::class, 'presensi'])->name('presensi-tutor');
    Route::get('/lupa-lapor', [KepsekDashboardController::class, 'lupaLapor'])->name('lupa-lapor');
    Route::delete('/lupa-lapor/{id}', [KepsekDashboardController::class, 'lupaLaporDestroy'])->name('lupa-lapor.destroy');
});

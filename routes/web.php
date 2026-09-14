<?php

use App\Http\Controllers\Admin\IzinController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanPresensiController;
use App\Http\Controllers\Kepsek\KepsekDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tutor\LupaLaporController;
use App\Http\Controllers\Tutor\PengajuanIzinController;
use App\Http\Controllers\Tutor\PresensiFotoController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use App\Http\Controllers\Tutor\TutorPayrollController;
use Illuminate\Support\Facades\Route;

Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'), ['Content-Type' => 'application/json']);
});

Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), ['Content-Type' => 'text/javascript']);
});

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

    // Modul Payroll & Honorarium
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/rekap/pdf', [PayrollController::class, 'exportRekapPdf'])->name('payroll.rekap-pdf');
    Route::get('/payroll/{tutorId}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/{tutorId}/slip-pdf', [PayrollController::class, 'exportSlipPdf'])->name('payroll.slip-pdf');
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
    Route::get('/pengajuan-izin', [PengajuanIzinController::class, 'index'])->name('pengajuan-izin');
    Route::post('/pengajuan-izin', [PengajuanIzinController::class, 'store'])->name('pengajuan-izin.store');
    Route::delete('/pengajuan-izin/{id}', [PengajuanIzinController::class, 'destroy'])->name('pengajuan-izin.destroy');
    Route::get('/payroll', [TutorPayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/pdf', [TutorPayrollController::class, 'exportPdf'])->name('payroll.pdf');
});

Route::middleware(['auth', 'role:kepala_sekolah'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepsekDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [KepsekDashboardController::class, 'index'])->name('analytics');
    Route::get('/laporan', [KepsekDashboardController::class, 'laporan'])->name('laporan');
    Route::get('/laporan/export/pdf', [KepsekDashboardController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/presensi', [KaryawanPresensiController::class, 'index'])->name('presensi');
    Route::post('/presensi', [KaryawanPresensiController::class, 'store'])->name('presensi.store');
    Route::get('/presensi-tutor', [KepsekDashboardController::class, 'presensi'])->name('presensi-tutor');
    Route::get('/lupa-lapor', [KepsekDashboardController::class, 'lupaLapor'])->name('lupa-lapor');
    Route::patch('/lupa-lapor/{id}/setujui', [KepsekDashboardController::class, 'setujuiLupaLapor'])->name('lupa-lapor.setujui');
    Route::patch('/lupa-lapor/{id}/tolak', [KepsekDashboardController::class, 'tolakLupaLapor'])->name('lupa-lapor.tolak');
    Route::delete('/lupa-lapor/{id}', [KepsekDashboardController::class, 'lupaLaporDestroy'])->name('lupa-lapor.destroy');
    Route::get('/pengajuan-izin', [KepsekDashboardController::class, 'pengajuanIzin'])->name('pengajuan-izin');
    Route::patch('/pengajuan-izin/{id}/setujui', [KepsekDashboardController::class, 'setujuiPengajuanIzin'])->name('pengajuan-izin.setujui');
    Route::patch('/pengajuan-izin/{id}/tolak', [KepsekDashboardController::class, 'tolakPengajuanIzin'])->name('pengajuan-izin.tolak');
    Route::delete('/pengajuan-izin/{id}', [KepsekDashboardController::class, 'destroyPengajuanIzin'])->name('pengajuan-izin.destroy');
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/rekap/pdf', [PayrollController::class, 'exportRekapPdf'])->name('payroll.rekap-pdf');
    Route::get('/payroll/{tutorId}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/{tutorId}/slip-pdf', [PayrollController::class, 'exportSlipPdf'])->name('payroll.slip-pdf');
});

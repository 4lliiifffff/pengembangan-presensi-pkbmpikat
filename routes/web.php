<?php

use App\Http\Controllers\Admin\IzinController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\JenjangPaketController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\KategoriTutorialController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MagangController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanPresensiController;
use App\Http\Controllers\Kepsek\KepsekDashboardController;
use App\Http\Controllers\Magang\MagangDashboardController;
use App\Http\Controllers\Magang\MagangPresensiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushNotificationController;
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

// Push Notification Routes
Route::middleware('auth')->prefix('push')->name('push.')->group(function () {
    Route::get('/key', [PushNotificationController::class, 'getPublicKey'])->name('key');
    Route::post('/subscribe', [PushNotificationController::class, 'subscribe'])->name('subscribe');
    Route::post('/unsubscribe', [PushNotificationController::class, 'unsubscribe'])->name('unsubscribe');
    Route::post('/test', [PushNotificationController::class, 'sendTest'])->name('test');
});

Route::match(['get', 'post'], '/logout', [AuthWebController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'kepala_sekolah' => redirect()->route('kepsek.dashboard'),
            'tutor' => redirect()->route('tutor.dashboard'),
            'magang' => redirect()->route('magang.dashboard'),
            default => view('auth.login'),
        };
    }

    return view('auth.login');
})->name('login');

Route::get('/login', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'kepala_sekolah' => redirect()->route('kepsek.dashboard'),
            'tutor' => redirect()->route('tutor.dashboard'),
            'magang' => redirect()->route('magang.dashboard'),
            default => view('auth.login'),
        };
    }

    return view('auth.login');
});

Route::post('/login', [AuthWebController::class, 'process'])
    ->middleware('throttle:login')
    ->name('login.process');

Route::middleware('auth')->prefix('profil')->name('profil.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::patch('/update', [ProfileController::class, 'update'])->name('update');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.exportExcel');
    Route::get('/laporan/template/excel', [LaporanController::class, 'downloadTemplate'])->name('laporan.downloadTemplate');
    Route::post('/laporan/import/excel', [LaporanController::class, 'importExcel'])->name('laporan.importExcel');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.exportPdf');

    // Siswa Spreadsheet Routes
    Route::get('/siswa/export/excel', [SiswaController::class, 'exportExcel'])->name('siswa.exportExcel');
    Route::get('/siswa/template/excel', [SiswaController::class, 'downloadTemplate'])->name('siswa.downloadTemplate');
    Route::post('/siswa/import/excel', [SiswaController::class, 'importExcel'])->name('siswa.importExcel');
    Route::resource('siswa', SiswaController::class);

    Route::resource('kelas', KelasController::class);

    // Jadwal Spreadsheet Routes
    Route::get('/jadwal/export/excel', [JadwalController::class, 'exportExcel'])->name('jadwal.exportExcel');
    Route::get('/jadwal/template/excel', [JadwalController::class, 'downloadTemplate'])->name('jadwal.downloadTemplate');
    Route::post('/jadwal/import/excel', [JadwalController::class, 'importExcel'])->name('jadwal.importExcel');
    Route::resource('jadwal', JadwalController::class);

    // Karyawan Spreadsheet Routes
    Route::get('/karyawan/export/excel', [KaryawanController::class, 'exportExcel'])->name('karyawan.exportExcel');
    Route::get('/karyawan/template/excel', [KaryawanController::class, 'downloadTemplate'])->name('karyawan.downloadTemplate');
    Route::post('/karyawan/import/excel', [KaryawanController::class, 'importExcel'])->name('karyawan.importExcel');
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
    Route::post('/payroll/broadcast-notifikasi', [PayrollController::class, 'broadcastNotifikasi'])->name('payroll.broadcast-notifikasi');
    Route::get('/payroll/rekap/excel', [PayrollController::class, 'exportRekapExcel'])->name('payroll.rekap-excel');
    Route::get('/payroll/rekap/pdf', [PayrollController::class, 'exportRekapPdf'])->name('payroll.rekap-pdf');
    Route::get('/payroll/tarif/template', [PayrollController::class, 'downloadTarifTemplate'])->name('payroll.download-tarif-template');
    Route::post('/payroll/tarif/import', [PayrollController::class, 'importBulkTarif'])->name('payroll.import-tarif');
    Route::get('/payroll/{tutorId}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/{tutorId}/slip-pdf', [PayrollController::class, 'exportSlipPdf'])->name('payroll.slip-pdf');

    // Master Kategori Tutorial & Tarif SK
    Route::patch('/kategori-tutorial/{kategoriTutorial}/toggle-status', [KategoriTutorialController::class, 'toggleStatus'])->name('kategori-tutorial.toggleStatus');
    Route::resource('kategori-tutorial', KategoriTutorialController::class);

    // Master Jenjang & Program Paket
    Route::patch('/jenjang-paket/{jenjangPaket}/toggle-status', [JenjangPaketController::class, 'toggleStatus'])->name('jenjang-paket.toggleStatus');
    Route::resource('jenjang-paket', JenjangPaketController::class);

    // Kelola Mahasiswa / Siswa Magang (PKL)
    Route::get('/magang/presensi', [MagangController::class, 'presensi'])->name('magang.presensi');
    Route::get('/magang/export/pdf', [MagangController::class, 'exportPdf'])->name('magang.exportPdf');
    Route::resource('magang', MagangController::class);
});

Route::middleware(['auth', 'role:magang'])->prefix('magang')->name('magang.')->group(function () {
    Route::get('/dashboard', [MagangDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat', [MagangDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [MagangDashboardController::class, 'profil'])->name('profil');
    Route::post('/profil/password', [MagangDashboardController::class, 'updatePassword'])->name('profil.password');
    Route::get('/presensi', [MagangPresensiController::class, 'index'])->name('presensi');
    Route::get('/presensi/foto', [MagangPresensiController::class, 'foto'])->name('presensi.foto');
    Route::post('/presensi', [MagangPresensiController::class, 'store'])->name('presensi.store');
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
    Route::get('/payroll/rekap/excel', [PayrollController::class, 'exportRekapExcel'])->name('payroll.rekap-excel');
    Route::get('/payroll/rekap/pdf', [PayrollController::class, 'exportRekapPdf'])->name('payroll.rekap-pdf');
    Route::get('/payroll/tarif/template', [PayrollController::class, 'downloadTarifTemplate'])->name('payroll.download-tarif-template');
    Route::post('/payroll/tarif/import', [PayrollController::class, 'importBulkTarif'])->name('payroll.import-tarif');
    Route::get('/payroll/{tutorId}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/{tutorId}/slip-pdf', [PayrollController::class, 'exportSlipPdf'])->name('payroll.slip-pdf');
});

<?php

use App\Http\Controllers\Admin\IzinController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\JadwalKerjaController;
use App\Http\Controllers\Admin\JadwalRutinController;
use App\Http\Controllers\Admin\JadwalSesiKbmController;
use App\Http\Controllers\Admin\JenjangPaketController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\KategoriTutorialController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LokasiPresensiController;
use App\Http\Controllers\Admin\MagangController;
use App\Http\Controllers\Admin\MuridController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Auth\SimpikatSsoController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanPresensiController;
use App\Http\Controllers\Kepsek\KepsekDashboardController;
use App\Http\Controllers\Magang\MagangDashboardController;
use App\Http\Controllers\Magang\MagangPresensiController;
use App\Http\Controllers\Murid\MuridDashboardController;
use App\Http\Controllers\Murid\MuridPresensiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\Tutor\JadwalController as TutorJadwalController;
use App\Http\Controllers\Tutor\JadwalSesiController;
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
Route::get('/push/key', [PushNotificationController::class, 'getPublicKey'])->name('push.key');
Route::middleware('auth')->prefix('push')->name('push.')->group(function () {
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
            'murid', 'siswa' => redirect()->route('murid.dashboard'),
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
            'murid', 'siswa' => redirect()->route('murid.dashboard'),
            default => view('auth.login'),
        };
    }

    return view('auth.login');
});

Route::post('/login', [AuthWebController::class, 'process'])
    ->middleware('throttle:login')
    ->name('login.process');

// Quick Login Pengujian & Pengembangan (Dev / Testing)
Route::post('/quick-login', [AuthWebController::class, 'quickLogin'])->name('login.quick');
Route::get('/quick-login/{role}', [AuthWebController::class, 'quickLoginGet'])->name('login.quick.get');

// Single Sign-On (SSO) Terpadu SIMPIKAT
Route::get('/sso/simpikat', [SimpikatSsoController::class, 'handle'])->name('sso.simpikat');
Route::middleware('auth')->get('/sso/ke-simpikat', [SimpikatSsoController::class, 'redirectToSimpikat'])->name('sso.ke-simpikat');

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

    // Murid Spreadsheet & Resource Routes
    Route::get('/murid/export/excel', [MuridController::class, 'exportExcel'])->name('murid.exportExcel');
    Route::get('/murid/template/excel', [MuridController::class, 'downloadTemplate'])->name('murid.downloadTemplate');
    Route::post('/murid/import/excel', [MuridController::class, 'importExcel'])->name('murid.importExcel');
    Route::post('/murid/{murid}/reset-password', [MuridController::class, 'resetPassword'])->name('murid.resetPassword');
    Route::resource('murid', MuridController::class);

    // Siswa Spreadsheet & Resource Routes (Legacy Alias)
    Route::get('/siswa/export/excel', [MuridController::class, 'exportExcel'])->name('siswa.exportExcel');
    Route::get('/siswa/template/excel', [MuridController::class, 'downloadTemplate'])->name('siswa.downloadTemplate');
    Route::post('/siswa/import/excel', [MuridController::class, 'importExcel'])->name('siswa.importExcel');
    Route::post('/siswa/{siswa}/reset-password', [MuridController::class, 'resetPassword'])->name('siswa.resetPassword');
    Route::resource('siswa', MuridController::class);

    Route::resource('kelas', KelasController::class);

    // Unified Schedule Hub Admin: Kalender Sesi KBM, Pola Rutin & Agenda Acara
    Route::get('/jadwal/export/excel', [JadwalController::class, 'exportExcel'])->name('jadwal.exportExcel');
    Route::get('/jadwal/template/excel', [JadwalController::class, 'downloadTemplate'])->name('jadwal.downloadTemplate');
    Route::post('/jadwal/import/excel', [JadwalController::class, 'importExcel'])->name('jadwal.importExcel');

    Route::get('/jadwal', [JadwalSesiKbmController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal', [JadwalSesiKbmController::class, 'store'])->name('jadwal.store');
    Route::delete('/jadwal/{jadwalSesi}', [JadwalSesiKbmController::class, 'destroy'])->name('jadwal.destroy');
    Route::post('/jadwal/{jadwalSesi}/reschedule', [JadwalSesiKbmController::class, 'reschedule'])->name('jadwal.reschedule');
    Route::patch('/jadwal/rutin/{jadwalRutin}/toggle-status', [JadwalSesiKbmController::class, 'toggleStatusRutin'])->name('jadwal.toggleStatusRutin');
    Route::match(['put', 'patch'], '/jadwal/rutin/{jadwalRutin}', [JadwalSesiKbmController::class, 'updateRutin'])->name('jadwal.updateRutin');
    Route::delete('/jadwal/rutin/{jadwalRutin}', [JadwalSesiKbmController::class, 'destroyRutin'])->name('jadwal.destroyRutin');
    Route::post('/jadwal/rutin/generate-manual', [JadwalSesiKbmController::class, 'generateManualRutin'])->name('jadwal.generateManualRutin');
    Route::match(['put', 'patch'], '/jadwal/{jadwalSesi}', [JadwalSesiKbmController::class, 'update'])->name('jadwal.update');
    Route::post('/jadwal/agenda', [JadwalSesiKbmController::class, 'storeAgenda'])->name('jadwal.storeAgenda');
    Route::match(['put', 'patch'], '/jadwal/agenda/{jadwal}', [JadwalSesiKbmController::class, 'updateAgenda'])->name('jadwal.updateAgenda');
    Route::delete('/jadwal/agenda/{jadwal}', [JadwalSesiKbmController::class, 'destroyAgenda'])->name('jadwal.destroyAgenda');

    // Karyawan Spreadsheet Routes
    Route::get('/karyawan/export/excel', [KaryawanController::class, 'exportExcel'])->name('karyawan.exportExcel');
    Route::get('/karyawan/template/excel', [KaryawanController::class, 'downloadTemplate'])->name('karyawan.downloadTemplate');
    Route::post('/karyawan/import/excel', [KaryawanController::class, 'importExcel'])->name('karyawan.importExcel');
    Route::post('/karyawan/{id}/reset-password', [KaryawanController::class, 'resetPassword'])->name('karyawan.resetPassword');
    Route::resource('karyawan', KaryawanController::class);
    Route::patch('karyawan/{id}/status', [KaryawanController::class, 'status'])->name('karyawan.status');
    Route::patch('karyawan/{id}/toggle-status', [KaryawanController::class, 'status'])->name('karyawan.toggleStatus');

    // Route presensi karyawan (Admin/Kepsek)
    Route::get('/presensi', [KaryawanPresensiController::class, 'index'])->name('presensi');
    Route::post('/presensi', [KaryawanPresensiController::class, 'store'])->name('presensi.store');
    // Kelola Izin Tutor
    Route::get('/izin', [IzinController::class, 'index'])->name('izin.index');
    Route::get('/pengajuan-izin', [IzinController::class, 'index'])->name('pengajuan-izin');
    Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');
    Route::delete('/izin/{id}', [IzinController::class, 'destroy'])->name('izin.destroy');
    Route::get('/izin/murid/{tutorId}', [IzinController::class, 'getMuridByTutor'])->name('izin.murid');
    Route::get('/izin/siswa/{tutorId}', [IzinController::class, 'getMuridByTutor'])->name('izin.siswa');

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

    // Master Titik Lokasi Presensi (Multi-Geofence)
    Route::patch('/lokasi-presensi/{lokasiPresensi}/toggle-status', [LokasiPresensiController::class, 'toggleStatus'])->name('lokasi-presensi.toggleStatus');
    Route::patch('/lokasi-presensi/{lokasiPresensi}/toggle-status-alias', [LokasiPresensiController::class, 'toggleStatus'])->name('lokasi-presensi.toggle-status');
    Route::resource('lokasi-presensi', LokasiPresensiController::class);

    // Master Jadwal & Shift Kerja SK
    Route::patch('/jadwal-kerja/{jadwalKerja}/toggle-status', [JadwalKerjaController::class, 'toggleStatus'])->name('jadwal-kerja.toggleStatus');
    Route::resource('jadwal-kerja', JadwalKerjaController::class);

    // Master Jadwal Rutin KBM Murid (Recurring Weekly Schedule)
    Route::post('/jadwal-rutin/generate-manual', [JadwalRutinController::class, 'generateManual'])->name('jadwal-rutin.generate-manual');
    Route::patch('/jadwal-rutin/{jadwalRutin}/toggle-status', [JadwalRutinController::class, 'toggleStatus'])->name('jadwal-rutin.toggleStatus');
    Route::resource('jadwal-rutin', JadwalRutinController::class);

    // Legacy Aliases for /admin/jadwal-sesi-kbm (Zero Breaking Changes)
    Route::get('/jadwal-sesi-kbm', [JadwalSesiKbmController::class, 'index'])->name('jadwal-sesi-kbm.index');
    Route::post('/jadwal-sesi-kbm', [JadwalSesiKbmController::class, 'store'])->name('jadwal-sesi-kbm.store');
    Route::delete('/jadwal-sesi-kbm/{jadwalSesi}', [JadwalSesiKbmController::class, 'destroy'])->name('jadwal-sesi-kbm.destroy');
    Route::post('/jadwal-sesi-kbm/{jadwalSesi}/reschedule', [JadwalSesiKbmController::class, 'reschedule'])->name('jadwal-sesi-kbm.reschedule');
    Route::patch('/jadwal-sesi-kbm/rutin/{jadwalRutin}/toggle-status', [JadwalSesiKbmController::class, 'toggleStatusRutin'])->name('jadwal-sesi-kbm.toggleStatusRutin');
    Route::match(['put', 'patch'], '/jadwal-sesi-kbm/rutin/{jadwalRutin}', [JadwalSesiKbmController::class, 'updateRutin'])->name('jadwal-sesi-kbm.updateRutin');
    Route::delete('/jadwal-sesi-kbm/rutin/{jadwalRutin}', [JadwalSesiKbmController::class, 'destroyRutin'])->name('jadwal-sesi-kbm.destroyRutin');
    Route::post('/jadwal-sesi-kbm/rutin/generate-manual', [JadwalSesiKbmController::class, 'generateManualRutin'])->name('jadwal-sesi-kbm.generateManualRutin');
    Route::match(['put', 'patch'], '/jadwal-sesi-kbm/{jadwalSesi}', [JadwalSesiKbmController::class, 'update'])->name('jadwal-sesi-kbm.update');
    Route::post('/jadwal-sesi-kbm/agenda', [JadwalSesiKbmController::class, 'storeAgenda'])->name('jadwal-sesi-kbm.storeAgenda');
    Route::match(['put', 'patch'], '/jadwal-sesi-kbm/agenda/{jadwal}', [JadwalSesiKbmController::class, 'updateAgenda'])->name('jadwal-sesi-kbm.updateAgenda');
    Route::delete('/jadwal-sesi-kbm/agenda/{jadwal}', [JadwalSesiKbmController::class, 'destroyAgenda'])->name('jadwal-sesi-kbm.destroyAgenda');

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

// Portal Murid Routes
Route::middleware(['auth', 'role:murid,siswa'])->prefix('murid')->name('murid.')->group(function () {
    Route::get('/dashboard', [MuridDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal', [MuridDashboardController::class, 'jadwal'])->name('jadwal');
    Route::get('/riwayat', [MuridDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [MuridDashboardController::class, 'profil'])->name('profil');
    Route::post('/profil/password', [MuridDashboardController::class, 'updatePassword'])->name('profil.password');
    Route::get('/presensi', [MuridPresensiController::class, 'index'])->name('presensi');
    Route::get('/presensi/foto', [MuridPresensiController::class, 'foto'])->name('presensi.foto');
    Route::post('/presensi', [MuridPresensiController::class, 'store'])->name('presensi.store');
});

// Portal Siswa Legacy Routes (Backward Compatibility)
Route::middleware(['auth', 'role:murid,siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [MuridDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal', [MuridDashboardController::class, 'jadwal'])->name('jadwal');
    Route::get('/riwayat', [MuridDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [MuridDashboardController::class, 'profil'])->name('profil');
    Route::post('/profil/password', [MuridDashboardController::class, 'updatePassword'])->name('profil.password');
    Route::get('/presensi', [MuridPresensiController::class, 'index'])->name('presensi');
    Route::get('/presensi/foto', [MuridPresensiController::class, 'foto'])->name('presensi.foto');
    Route::post('/presensi', [MuridPresensiController::class, 'store'])->name('presensi.store');
});

Route::middleware(['auth', 'role:tutor'])->prefix('tutor')->name('tutor.')->group(function () {
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat', [TutorDashboardController::class, 'riwayat'])->name('riwayat');
    // Unified Schedule Hub Tutor: Kalender Sesi Belajar, Pola Rutin & Agenda Acara
    Route::get('/jadwal', [TutorJadwalController::class, 'index'])->name('jadwal');
    Route::get('/jadwal/index', [TutorJadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal', [TutorJadwalController::class, 'store'])->name('jadwal.store');
    Route::match(['put', 'patch'], '/jadwal/{jadwalSesi}', [TutorJadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{jadwalSesi}', [TutorJadwalController::class, 'destroy'])->name('jadwal.destroy');
    Route::post('/jadwal/{jadwalSesi}/reschedule', [TutorJadwalController::class, 'reschedule'])->name('jadwal.reschedule');
    Route::patch('/jadwal/rutin/{jadwalRutin}/toggle-status', [TutorJadwalController::class, 'toggleStatusRutin'])->name('jadwal.toggleStatusRutin');
    Route::delete('/jadwal/rutin/{jadwalRutin}', [TutorJadwalController::class, 'destroyRutin'])->name('jadwal.destroyRutin');
    Route::post('/jadwal/import', [TutorJadwalController::class, 'importExcel'])->name('jadwal.import');
    Route::get('/jadwal/template', [TutorJadwalController::class, 'downloadTemplate'])->name('jadwal.template');
    Route::get('/jadwal/export', [TutorJadwalController::class, 'exportExcel'])->name('jadwal.export');

    // Legacy Aliases for /tutor/jadwal-sesi (Backward Compatibility)
    Route::post('/jadwal-sesi/{jadwalSesi}/reschedule', [JadwalSesiController::class, 'reschedule'])->name('jadwal-sesi.reschedule');
    Route::patch('/jadwal-rutin/{jadwalRutin}/toggle-status', [JadwalSesiController::class, 'toggleStatusRutin'])->name('jadwal-rutin.toggleStatus');
    Route::delete('/jadwal-rutin/{jadwalRutin}', [JadwalSesiController::class, 'destroyRutin'])->name('jadwal-rutin.destroy');
    Route::post('/jadwal-sesi/import', [JadwalSesiController::class, 'importExcel'])->name('jadwal-sesi.import');
    Route::get('/jadwal-sesi/template', [JadwalSesiController::class, 'downloadTemplate'])->name('jadwal-sesi.template');
    Route::get('/jadwal-sesi/export', [JadwalSesiController::class, 'exportExcel'])->name('jadwal-sesi.export');
    Route::resource('jadwal-sesi', JadwalSesiController::class)->only(['index', 'store', 'update', 'destroy']);
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

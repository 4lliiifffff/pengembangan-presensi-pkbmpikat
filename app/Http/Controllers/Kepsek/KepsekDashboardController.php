<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzinSakit;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\AnalyticsService;
use App\Services\LaporanPresensiService;
use App\Services\PresensiService;
use App\Services\WebPushService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KepsekDashboardController extends Controller
{
    public function __construct(
        protected LaporanPresensiService $laporanService,
        protected PresensiService $presensiService,
        protected AnalyticsService $analyticsService,
        protected WebPushService $webPushService
    ) {}

    /* ─────────────────────────────────────────────
     |  DASHBOARD & ANALYTICS
     ───────────────────────────────────────────── */
    public function index()
    {
        $today = Carbon::now()->toDateString();

        $counts = [
            'hadir' => $this->countHadir($today),
            'izin' => Presensi::whereDate('tgl_presensi', $today)->whereIn('status', ['izin', 'sakit'])->count(),
        ];

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $days = [];
        $max = 0;

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i)->toDateString();
            $count = $this->countHadir($date);
            $days[] = [
                'label' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'][$i],
                'isoDay' => $date,
                'count' => $count,
            ];
            $max = max($max, $count);
        }

        $latest = Presensi::with(['siswa', 'tutor'])
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (Presensi $p) => $this->mapStatus($p));

        $monthlyTrend = $this->analyticsService->getMonthlyAttendanceTrend(6);
        $kpiRanking = $this->analyticsService->getTutorKpiRanking();

        return view('kepsek.dashboard', [
            'today' => $today,
            'counts' => $counts,
            'weekly' => ['days' => $days, 'max' => $max ?: 1],
            'latest' => $latest,
            'monthlyTrend' => $monthlyTrend,
            'kpiRanking' => $kpiRanking,
        ]);
    }

    /* ─────────────────────────────────────────────
     |  LAPORAN (rekap per tutor)
     ───────────────────────────────────────────── */
    public function laporan(Request $request)
    {
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);
        $tutorId = $request->get('tutor_id') ? (int) $request->get('tutor_id') : null;
        $siswaId = $request->get('siswa_id') ? (int) $request->get('siswa_id') : null;

        $rekapData = $this->laporanService->getRekapTutorBulanan($bulan, $tahun, $tutorId, $siswaId);

        $startDate = $rekapData['startDate'];
        $endDate = $rekapData['endDate'];
        $totalHadir = $rekapData['totalHadir'];
        $totalIzin = 0;
        $totalSesi = $rekapData['totalSesi'];
        $rekapTutor = $rekapData['rekapTutor'];
        $totalTutorAktif = $rekapData['totalTutorAktif'];

        $tahunOptions = range(Carbon::now()->year, Carbon::now()->year - 3);

        $allTutors = Tutor::orderBy('nama_lengkap')->get();
        $allSiswas = Siswa::orderBy('nama_siswa')->get();

        return view('kepsek.laporan', compact(
            'rekapTutor', 'totalHadir', 'totalIzin', 'totalSesi',
            'totalTutorAktif', 'startDate', 'endDate',
            'bulan', 'tahun', 'tahunOptions', 'allTutors', 'allSiswas', 'tutorId', 'siswaId'
        ));
    }

    /* ─────────────────────────────────────────────
     |  EXPORT PDF
     ───────────────────────────────────────────── */
    public function exportPdf(Request $request)
    {
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);
        $tutorId = $request->get('tutor_id');
        $siswaId = $request->get('siswa_id');

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');

        $baseQuery = Presensi::whereBetween('tgl_presensi', [$startDate, $endDate]);
        if ($tutorId) {
            $baseQuery->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $baseQuery->where('siswa_id', $siswaId);
        }

        $totalHadir = (clone $baseQuery)->whereNotNull('jam_selesai')->count();
        $totalSesi = (clone $baseQuery)->count();

        $hariKerja = 0;
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $hariKerja++;
            }
            $cursor->addDay();
        }

        $tutorsQuery = Tutor::orderBy('nama_lengkap');
        if ($tutorId) {
            $tutorsQuery->where('id', $tutorId);
        }
        $tutors = $tutorsQuery->get();

        $rekapTutor = $tutors->map(function (Tutor $tutor) use ($startDate, $endDate, $hariKerja, $hasJamMulai, $siswaId) {
            $tutorPresensi = Presensi::where('tutor_id', $tutor->id)
                ->whereBetween('tgl_presensi', [$startDate, $endDate]);

            if ($siswaId) {
                $tutorPresensi->where('siswa_id', $siswaId);
            }
            $totalSesiTutor = (clone $tutorPresensi)->count();
            $hadirCount = (clone $tutorPresensi)->whereNotNull('jam_selesai')->count();

            $jamMengajar = 0;
            if ($hasJamMulai) {
                $rows = (clone $tutorPresensi)
                    ->whereNotNull('jam_mulai')->whereNotNull('jam_selesai')
                    ->get(['jam_mulai', 'jam_selesai']);
                foreach ($rows as $row) {
                    try {
                        $m = Carbon::parse($row->jam_mulai);
                        $s = Carbon::parse($row->jam_selesai);
                        if ($s->gt($m)) {
                            $jamMengajar += $m->diffInMinutes($s);
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            $pctHadir = $totalSesiTutor > 0
                ? round($hadirCount / $totalSesiTutor * 100) : 0;

            return [
                'tutor' => $tutor,
                'total_sesi' => $totalSesiTutor,
                'hadir' => $hadirCount,
                'pct_hadir' => $pctHadir,
                'jam_mengajar' => round($jamMengajar / 60, 1),
                'hari_kerja' => $hariKerja,
            ];
        })->filter(fn ($r) => $r['total_sesi'] > 0)
            ->sortByDesc('hadir')
            ->values();

        $totalTutorAktif = $rekapTutor->count();

        // Waktu cetak eksplisit WIB (timezone app default sering UTC)
        $tanggalCetak = Carbon::now('Asia/Jakarta');

        $pdf = Pdf::loadView('kepsek.laporan_pdf', compact(
            'rekapTutor', 'totalHadir', 'totalSesi', 'totalTutorAktif',
            'startDate', 'endDate', 'tanggalCetak'
        ))->setPaper('a4', 'portrait');

        $filename = 'Rekap_Kehadiran_Tutor_'.$startDate->format('Y_m').'.pdf';

        return $pdf->download($filename);
    }

    /* ─────────────────────────────────────────────
     |  KELOLA LUPA LAPOR (Interactive Approval Workflow)
     ───────────────────────────────────────────── */
    public function lupaLapor(Request $request)
    {
        $query = PengajuanLupaLapor::with(['tutor', 'siswa'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->whereHas('tutor', fn ($t) => $t->where('nama_lengkap', 'like', "%{$cari}%"))
                    ->orWhereHas('siswa', fn ($s) => $s->where('nama_siswa', 'like', "%{$cari}%"));
            });
        }

        $total = (clone $query)->count();
        $totalPending = PengajuanLupaLapor::where('status', 'pending')->count();
        $items = $query->paginate(15);

        return view('kepsek.lupa_lapor', compact('items', 'total', 'totalPending'));
    }

    public function setujuiLupaLapor(Request $request, int $id)
    {
        $item = PengajuanLupaLapor::findOrFail($id);
        $item->status = 'disetujui';
        $item->catatan_kepsek = $request->input('catatan_kepsek', 'Pengajuan disetujui');
        $item->save();

        $this->presensiService->syncApprovedAttendance(
            tutorId: $item->tutor_id,
            siswaIds: $item->siswa_id,
            startDateStr: $item->tanggal,
            status: 'hadir',
            jamMulai: $item->jam_mulai,
            jamSelesai: $item->jam_selesai,
            materi: 'Pengajuan Lupa Lapor Disetujui: '.$item->alasan
        );

        // Kirim Web Push Notification ke Tutor
        $this->webPushService->sendToUser($item->tutor_id, [
            'title' => '✅ Pengajuan Lupa Lapor Disetujui',
            'body' => 'Pengajuan Lupa Lapor Anda untuk tanggal '.$item->tanggal.' telah disetujui oleh Kepala Sekolah.',
            'url' => route('tutor.lupa-lapor'),
        ]);

        return back()->with('success', 'Pengajuan Lupa Lapor disetujui & data presensi berhasil dicatat.');
    }

    public function tolakLupaLapor(Request $request, int $id)
    {
        $item = PengajuanLupaLapor::findOrFail($id);
        $item->status = 'ditolak';
        $item->catatan_kepsek = $request->input('catatan_kepsek', 'Pengajuan ditolak oleh Kepala Sekolah');
        $item->save();

        // Kirim Web Push Notification ke Tutor
        $this->webPushService->sendToUser($item->tutor_id, [
            'title' => '❌ Pengajuan Lupa Lapor Ditolak',
            'body' => 'Pengajuan Lupa Lapor Anda untuk tanggal '.$item->tanggal.' ditolak. Catatan: '.$item->catatan_kepsek,
            'url' => route('tutor.lupa-lapor'),
        ]);

        return back()->with('warning', 'Pengajuan Lupa Lapor ditolak.');
    }

    public function lupaLaporDestroy(int $id)
    {
        PengajuanLupaLapor::findOrFail($id)->delete();

        return back()->with('success', 'Pengajuan berhasil dihapus.');
    }

    /* ─────────────────────────────────────────────
     |  KELOLA PENGAJUAN IZIN & SAKIT TUTOR
     ───────────────────────────────────────────── */
    public function pengajuanIzin(Request $request)
    {
        $query = PengajuanIzinSakit::with(['tutor', 'verifikator'])
            ->orderByDesc('tgl_mulai')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->whereHas('tutor', fn ($t) => $t->where('nama_lengkap', 'like', "%{$cari}%"));
        }

        $total = (clone $query)->count();
        $totalPending = PengajuanIzinSakit::where('status', 'pending')->count();
        $items = $query->paginate(15);

        return view('kepsek.pengajuan_izin', compact('items', 'total', 'totalPending'));
    }

    public function setujuiPengajuanIzin(Request $request, int $id)
    {
        $item = PengajuanIzinSakit::findOrFail($id);
        $item->status = 'disetujui';
        $item->disetujui_oleh = Auth::id();
        $item->catatan_verifikasi = $request->input('catatan_verifikasi', 'Pengajuan disetujui');
        $item->save();

        $this->presensiService->syncApprovedAttendance(
            tutorId: $item->tutor_id,
            siswaIds: [],
            startDateStr: $item->tgl_mulai,
            endDateStr: $item->tgl_selesai,
            status: $item->jenis
        );

        // Kirim Web Push Notification ke Tutor
        $this->webPushService->sendToUser($item->tutor_id, [
            'title' => '✅ Pengajuan '.ucfirst($item->jenis).' Disetujui',
            'body' => 'Pengajuan '.$item->jenis.' Anda untuk tanggal '.$item->tgl_mulai.' telah disetujui oleh Kepala Sekolah.',
            'url' => route('tutor.pengajuan-izin'),
        ]);

        return back()->with('success', 'Pengajuan '.ucfirst($item->jenis).' disetujui & data presensi disinkronkan.');
    }

    public function tolakPengajuanIzin(Request $request, int $id)
    {
        $item = PengajuanIzinSakit::findOrFail($id);
        $item->status = 'ditolak';
        $item->disetujui_oleh = Auth::id();
        $item->catatan_verifikasi = $request->input('catatan_verifikasi', 'Pengajuan ditolak');
        $item->save();

        // Kirim Web Push Notification ke Tutor
        $this->webPushService->sendToUser($item->tutor_id, [
            'title' => '❌ Pengajuan '.ucfirst($item->jenis).' Ditolak',
            'body' => 'Pengajuan '.$item->jenis.' Anda telah ditolak. Catatan: '.$item->catatan_verifikasi,
            'url' => route('tutor.pengajuan-izin'),
        ]);

        return back()->with('warning', 'Pengajuan '.ucfirst($item->jenis).' ditolak.');
    }

    public function destroyPengajuanIzin(int $id)
    {
        PengajuanIzinSakit::findOrFail($id)->delete();

        return back()->with('success', 'Pengajuan berhasil dihapus.');
    }

    /* ─────────────────────────────────────────────
     |  PRESENSI LIST
     ───────────────────────────────────────────── */
    public function presensi(Request $request)
    {
        $startDateStr = $request->get('start_date', Carbon::now('Asia/Jakarta')->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now('Asia/Jakarta')->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $tutorId = $request->get('tutor_id');
        $siswaId = $request->get('siswa_id');
        $statusFilter = $request->get('status');

        $query = Presensi::with(['siswa', 'tutor'])
            ->whereBetween('tgl_presensi', [$startDate, $endDate]);

        if ($tutorId) {
            $query->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }

        if ($statusFilter) {
            if ($statusFilter === 'hadir') {
                $query->where('status', 'hadir')->whereNotNull('foto_selesai');
            } elseif ($statusFilter === 'proses') {
                $query->whereNotNull('foto_mulai')->whereNull('foto_selesai');
            } elseif ($statusFilter === 'izin') {
                $query->whereIn('status', ['izin', 'sakit']);
            }
        }

        $presensi = $query->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $tutors = Tutor::orderBy('nama_lengkap')->get();
        $siswas = Siswa::orderBy('nama_siswa')->get();

        $karyawanPresensi = PresensiKaryawan::with('user')
            ->whereBetween('tgl_presensi', [$startDate, $endDate])
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->get();

        return view('kepsek.presensi', compact(
            'presensi', 'karyawanPresensi', 'startDateStr', 'endDateStr',
            'tutors', 'siswas', 'tutorId', 'siswaId', 'statusFilter'
        ));
    }

    /* ─────────────────────────────────────────────
     |  HELPERS
     ───────────────────────────────────────────── */
    private function countHadir(string $date): int
    {
        return Presensi::whereDate('tgl_presensi', $date)
            ->whereNotNull('jam_selesai')
            ->count();
    }

    private function mapStatus(Presensi $p): Presensi
    {
        if ($p->status === 'alpha') {
            $p->status_label = 'ALPHA';
            $p->status_class = 'alpha';
        } elseif ($p->foto_mulai && $p->foto_selesai) {
            $p->status_label = 'SELESAI';
            $p->status_class = 'hadir';
        } elseif ($p->foto_mulai) {
            $p->status_label = 'SEDANG BERJALAN';
            $p->status_class = 'izin';
        } else {
            $p->status_label = 'BELUM ABSEN';
            $p->status_class = 'pending';
        }

        return $p;
    }
}

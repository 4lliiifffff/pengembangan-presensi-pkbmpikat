<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Lapor_Lapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KepsekDashboardController extends Controller
{
    /* ─────────────────────────────────────────────
     |  DASHBOARD
     ───────────────────────────────────────────── */
    public function index()
    {
        $today = Carbon::now()->toDateString();

        $counts = [
            'hadir' => $this->countHadir($today),
            'izin' => 0,
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

        return view('kepsek.dashboard', [
            'today' => $today,
            'counts' => $counts,
            'weekly' => ['days' => $days, 'max' => $max ?: 1],
            'latest' => $latest,
        ]);
    }

    /* ─────────────────────────────────────────────
     |  LAPORAN (rekap per tutor)
     ───────────────────────────────────────────── */
    public function laporan(Request $request)
    {
        // Default: bulan berjalan
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);
        $tutorId = $request->get('tutor_id');
        $siswaId = $request->get('siswa_id');

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');

        // Summary global
        $baseQuery = Presensi::whereBetween('tgl_presensi', [$startDate, $endDate]);
        if ($tutorId) {
            $baseQuery->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $baseQuery->where('siswa_id', $siswaId);
        }

        $totalHadir = (clone $baseQuery)->whereNotNull('jam_selesai')->count();
        $totalIzin = 0; // izin via WA, tidak tercatat di DB
        $totalSesi = (clone $baseQuery)->count();

        // Hari kerja dalam rentang (Senin–Sabtu)
        $hariKerja = 0;
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $hariKerja++;
            }
            $cursor->addDay();
        }

        // Rekap per tutor
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

            // Hitung hadir: punya jam_selesai
            $hadirCount = (clone $tutorPresensi)->whereNotNull('jam_selesai')->count();

            // Hitung total jam mengajar (jam_mulai → jam_selesai)
            $jamMengajar = 0;
            if ($hasJamMulai) {
                $rows = (clone $tutorPresensi)
                    ->whereNotNull('jam_mulai')
                    ->whereNotNull('jam_selesai')
                    ->get(['jam_mulai', 'jam_selesai']);

                foreach ($rows as $row) {
                    try {
                        $mulai = Carbon::parse($row->jam_mulai);
                        $selesai = Carbon::parse($row->jam_selesai);
                        if ($selesai->gt($mulai)) {
                            $jamMengajar += $mulai->diffInMinutes($selesai);
                        }
                    } catch (\Throwable) {
                    }
                }
            }
            $jamMengajarJam = round($jamMengajar / 60, 1);

            // Persentase kehadiran terhadap total sesi yg dijadwalkan
            $pctHadir = $totalSesiTutor > 0
                ? round($hadirCount / $totalSesiTutor * 100)
                : 0;

            return [
                'tutor' => $tutor,
                'total_sesi' => $totalSesiTutor,
                'hadir' => $hadirCount,
                'pct_hadir' => $pctHadir,
                'jam_mengajar' => $jamMengajarJam,
                'hari_kerja' => $hariKerja,
            ];
        })->filter(fn ($r) => $r['total_sesi'] > 0)
            ->sortByDesc('hadir')
            ->values();

        $totalTutorAktif = $rekapTutor->count();

        // Tahun opsi untuk filter
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kepsek.laporan_pdf', compact(
            'rekapTutor', 'totalHadir', 'totalSesi', 'totalTutorAktif',
            'startDate', 'endDate', 'tanggalCetak'
        ))->setPaper('a4', 'portrait');

        $filename = 'Rekap_Kehadiran_Tutor_'.$startDate->format('Y_m').'.pdf';

        return $pdf->download($filename);
    }

    /* ─────────────────────────────────────────────
     |  KELOLA LUPA LAPOR
     ───────────────────────────────────────────── */
    public function lupaLapor(Request $request)
    {
        $query = Lapor_Lapor::with(['tutor', 'siswa'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

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
        $items = $query->paginate(15);

        return view('kepsek.lupa_lapor', compact('items', 'total'));
    }

    public function lupaLaporDestroy(int $id)
    {
        Lapor_Lapor::findOrFail($id)->delete();

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

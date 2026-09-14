<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PresensiExport;
use App\Exports\PresensiTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PresensiImport;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\LaporanPresensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function __construct(protected LaporanPresensiService $laporanService) {}

    public function index(Request $request)
    {
        $startDateStr = $request->get('start_date', Carbon::now('Asia/Jakarta')->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now('Asia/Jakarta')->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $tutorId = $request->get('tutor_id');
        $siswaId = $request->get('siswa_id');
        $statusFilter = $request->get('status');

        $hasStatus = Schema::hasColumn('presensis', 'status');
        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');
        $baseQuery = Presensi::whereBetween('tgl_presensi', [$startDate, $endDate]);

        if ($tutorId) {
            $baseQuery->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $baseQuery->where('siswa_id', $siswaId);
        }

        if ($statusFilter) {
            if ($statusFilter === 'hadir') {
                $baseQuery->where('status', 'hadir')->whereNotNull('foto_selesai');
            } elseif ($statusFilter === 'proses') {
                $baseQuery->whereNotNull('foto_mulai')->whereNull('foto_selesai');
            } elseif ($statusFilter === 'izin') {
                $baseQuery->whereIn('status', ['izin', 'sakit']);
            } elseif ($statusFilter === 'alpha') {
                $baseQuery->where('status', 'alpha');
            }
        }

        // Kompatibel dengan 2 skema DB: pakai status jika ada, fallback ke jam_mulai.
        if ($hasStatus) {
            $totalHadir = (clone $baseQuery)->where('status', 'hadir')->count();
            $totalIzin = (clone $baseQuery)->where('status', 'izin')->count();
            $totalAlpha = (clone $baseQuery)->where('status', 'alpha')->count();
        } else {
            $hadirColumn = Schema::hasColumn('presensis', 'jam_selesai') ? 'jam_selesai' : ($hasJamMulai ? 'jam_mulai' : null);
            $totalHadir = $hadirColumn ? (clone $baseQuery)->whereNotNull($hadirColumn)->count() : 0;
            $totalProses = $hasJamMulai ? (clone $baseQuery)->whereNotNull('jam_mulai')->whereNull('jam_selesai')->count() : 0;
            $totalIzin = 0;
            $totalAlpha = max((clone $baseQuery)->count() - $totalHadir - $totalProses, 0);
        }

        $totalSesi = (clone $baseQuery)->count();

        // Kehadiran per tutor (dihitung dari total presensi)
        $perTutor = Tutor::withCount([
            'presensis as total_jadwal' => function ($q) use ($startDate, $endDate, $siswaId) {
                $q->whereBetween('tgl_presensi', [$startDate, $endDate]);
                if ($siswaId) {
                    $q->where('siswa_id', $siswaId);
                }
            },
        ])->having('total_jadwal', '>', 0)->orderByDesc('total_jadwal')->get();

        // Presensi terbaru
        $recentQuery = Presensi::with(['siswa', 'tutor'])
            ->whereBetween('tgl_presensi', [$startDate, $endDate]);

        if ($tutorId) {
            $recentQuery->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $recentQuery->where('siswa_id', $siswaId);
        }

        $recentPresensi = $recentQuery->orderByDesc('tgl_presensi')
            ->limit(20)
            ->get()
            ->map(function (Presensi $p) use ($hasStatus, $hasJamMulai) {
                if ($hasStatus) {
                    return $p;
                }

                if ($hasJamMulai && $p->jam_mulai) {
                    $p->status = $p->jam_selesai ? 'hadir' : 'proses';
                } else {
                    $p->status = 'alpha';
                }

                return $p;
            });

        // Rekap Presensi Karyawan (Admin/Kepsek)
        $karyawanPresensi = PresensiKaryawan::with('user')
            ->whereBetween('tgl_presensi', [$startDate, $endDate])
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->get();

        // Data chart harian (Tren Kehadiran)
        $dailyStats = Presensi::selectRaw('DATE(tgl_presensi) as date, 
                SUM(CASE WHEN status = "hadir" AND foto_selesai IS NOT NULL THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status IN ("izin", "sakit") THEN 1 ELSE 0 END) as izin')
            ->whereBetween('tgl_presensi', [$startDate, $endDate]);

        if ($tutorId) {
            $dailyStats->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $dailyStats->where('siswa_id', $siswaId);
        }

        $dailyData = $dailyStats->groupBy('date')->orderBy('date')->get();

        $chartLabels = $dailyData->map(fn ($d) => Carbon::parse($d->date)->format('d M'))->toArray();
        $chartDataHadir = $dailyData->pluck('hadir')->toArray();
        $chartDataIzin = $dailyData->pluck('izin')->toArray();

        // Untuk dikembalikan ke view filter
        $inputStartDate = $startDate->toDateString();
        $inputEndDate = $endDate->toDateString();

        $tutors = Tutor::orderBy('nama_lengkap')->get();
        $siswas = Siswa::orderBy('nama_siswa')->get();

        return view('admin.laporan.index', compact(
            'totalHadir',
            'totalIzin',
            'totalAlpha',
            'totalSesi',
            'perTutor',
            'recentPresensi',
            'karyawanPresensi',
            'chartLabels',
            'chartDataHadir',
            'chartDataIzin',
            'inputStartDate',
            'inputEndDate',
            'tutors',
            'siswas',
            'tutorId',
            'siswaId',
            'statusFilter'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDateStr = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $tutorId = $request->get('tutor_id');
        $siswaId = $request->get('siswa_id');
        $statusFilter = $request->get('status');

        return Excel::download(
            new PresensiExport($startDate, $endDate, $tutorId, $siswaId, $statusFilter),
            'Laporan_Presensi_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(new PresensiTemplateExport, 'Template_Import_Presensi_PKBM_Pikat.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'Silakan pilih berkas spreadsheet Excel/CSV terlebih dahulu.',
            'file_excel.mimes' => 'Format berkas harus berekstensi .xlsx, .xls, atau .csv.',
            'file_excel.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        $import = new PresensiImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->back()->with(
            'success',
            "Impor presensi berhasil diproses: {$import->importedCount} data presensi ditambahkan, {$import->skippedCount} data dilewati."
        );
    }

    public function exportPdf(Request $request)
    {
        $startDateStr = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $tutorId = $request->get('tutor_id');
        $siswaId = $request->get('siswa_id');

        $query = Presensi::with(['siswa', 'tutor'])
            ->whereBetween('tgl_presensi', [$startDate, $endDate]);

        if ($tutorId) {
            $query->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }

        $presensis = $query->orderBy('tgl_presensi')->get();

        $hasStatus = Schema::hasColumn('presensis', 'status');
        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');

        $totalHadir = 0;
        $totalIzin = 0;
        $totalAlpha = 0;

        $totalProses = 0;

        foreach ($presensis as $p) {
            $status = 'alpha';
            if ($hasStatus) {
                $status = strtolower($p->status);
            } elseif ($hasJamMulai && $p->jam_mulai) {
                $status = $p->jam_selesai ? 'hadir' : 'proses';
            }

            if ($status === 'hadir') {
                $totalHadir++;
            } elseif ($status === 'izin') {
                $totalIzin++;
            } elseif ($status === 'proses') {
                $totalProses++;
            } else {
                $totalAlpha++;
            }

            $p->calculated_status = ucfirst($status);
        }

        $pdf = Pdf::loadView('admin.laporan.pdf', compact(
            'presensis', 'startDate', 'endDate', 'totalHadir', 'totalIzin', 'totalAlpha'
        ));

        // Format PDF kertas A4 Landscape
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Presensi_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.pdf');
    }
}

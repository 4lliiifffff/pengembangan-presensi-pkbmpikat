<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PresensiExport;
use App\Exports\PresensiTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PresensiImport;
use App\Models\JenjangPaket;
use App\Models\Kelas;
use App\Models\LokasiPresensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\LaporanPresensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function __construct(protected LaporanPresensiService $laporanService) {}

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'tutor');

        $startDateStr = $request->get('start_date', Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now('Asia/Jakarta')->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $bulan = (int) $request->get('bulan', Carbon::now('Asia/Jakarta')->month);
        $tahun = (int) $request->get('tahun', Carbon::now('Asia/Jakarta')->year);

        $filters = [
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'tutor_id' => $request->get('tutor_id'),
            'siswa_id' => $request->get('siswa_id'),
            'user_id' => $request->get('user_id'),
            'role' => $request->get('role'),
            'status' => $request->get('status'),
            'moda_pembelajaran' => $request->get('moda_pembelajaran'),
            'lokasi_presensi_id' => $request->get('lokasi_presensi_id'),
            'jenjang_paket_id' => $request->get('jenjang_paket_id'),
            'kelas_id' => $request->get('kelas_id'),
        ];

        // 1. Ambil Data Tab Spesifik & Agregat
        $tutorData = $this->laporanService->getLaporanTutorData($filters);
        $siswaData = $this->laporanService->getLaporanSiswaData($filters);
        $pegawaiData = $this->laporanService->getLaporanPegawaiData($filters);
        $magangData = $this->laporanService->getLaporanMagangData($filters);
        $perizinanData = $this->laporanService->getLaporanPerizinanData($filters);
        $matriksData = $this->laporanService->getMatriksBulananSiswa($bulan, $tahun, $filters['kelas_id'], $filters['jenjang_paket_id']);

        // 2. Data Visual Charts
        $chartData = $this->laporanService->getVisualChartData($startDate, $endDate);

        // 3. Dataset Master untuk Dropdown Filter
        $tutors = Tutor::orderBy('nama_lengkap')->get();
        $siswas = Siswa::orderBy('nama_murid')->get();
        $murids = $siswas;
        $jenjangPakets = JenjangPaket::aktif()->orderBy('urutan')->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();
        $pegawaiUsers = User::whereIn('role', ['admin', 'kepala_sekolah', 'karyawan'])->orderBy('nama_lengkap')->get();
        $magangUsers = User::where('role', 'magang')->orderBy('nama_lengkap')->get();

        return view('admin.laporan.index', array_merge(
            [
                'activeTab' => $activeTab,
                'inputStartDate' => $startDateStr,
                'inputEndDate' => $endDateStr,
                'inputBulan' => $bulan,
                'inputTahun' => $tahun,
                'filters' => $filters,
                'tutors' => $tutors,
                'siswas' => $siswas,
                'murids' => $murids,
                'jenjangPakets' => $jenjangPakets,
                'kelasList' => $kelasList,
                'lokasiPresensis' => $lokasiPresensis,
                'pegawaiUsers' => $pegawaiUsers,
                'magangUsers' => $magangUsers,
                'tutorData' => $tutorData,
                'siswaData' => $siswaData,
                'pegawaiData' => $pegawaiData,
                'magangData' => $magangData,
                'perizinanData' => $perizinanData,
                'matriksData' => $matriksData,
            ],
            $chartData
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
            'Laporan_Presensi_PKBM_Pikat_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.xlsx'
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
        $tab = $request->get('tab', 'tutor');
        $startDateStr = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now()->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $bulan = (int) $request->get('bulan', Carbon::now('Asia/Jakarta')->month);
        $tahun = (int) $request->get('tahun', Carbon::now('Asia/Jakarta')->year);

        $filters = [
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'tutor_id' => $request->get('tutor_id'),
            'siswa_id' => $request->get('siswa_id'),
            'user_id' => $request->get('user_id'),
            'role' => $request->get('role'),
            'status' => $request->get('status'),
            'moda_pembelajaran' => $request->get('moda_pembelajaran'),
            'lokasi_presensi_id' => $request->get('lokasi_presensi_id'),
            'jenjang_paket_id' => $request->get('jenjang_paket_id'),
            'kelas_id' => $request->get('kelas_id'),
        ];

        $tutorData = $this->laporanService->getLaporanTutorData($filters);
        $siswaData = $this->laporanService->getLaporanSiswaData($filters);
        $pegawaiData = $this->laporanService->getLaporanPegawaiData($filters);
        $magangData = $this->laporanService->getLaporanMagangData($filters);
        $perizinanData = $this->laporanService->getLaporanPerizinanData($filters);
        $matriksData = $this->laporanService->getMatriksBulananSiswa($bulan, $tahun, $filters['kelas_id'], $filters['jenjang_paket_id']);

        $tanggalCetak = Carbon::now('Asia/Jakarta');

        $pdf = Pdf::loadView('admin.laporan.pdf', compact(
            'tab',
            'startDate',
            'endDate',
            'bulan',
            'tahun',
            'filters',
            'tutorData',
            'siswaData',
            'pegawaiData',
            'magangData',
            'perizinanData',
            'matriksData',
            'tanggalCetak'
        ))->setPaper('a4', 'landscape');

        $filename = 'Laporan_Presensi_PKBM_Pikat_'.strtoupper($tab).'_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.pdf';

        return $pdf->download($filename);
    }
}

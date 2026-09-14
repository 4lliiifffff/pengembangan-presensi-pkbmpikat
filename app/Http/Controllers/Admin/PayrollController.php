<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    /**
     * Tampilkan Rekapitulasi Payroll & Anggaran Penggajian Sekolah.
     */
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $summary = $this->payrollService->generatePayrollSummary($bulan, $tahun);
        $tahunOptions = range(Carbon::now()->year, Carbon::now()->year - 3);

        return view('admin.payroll.index', compact('summary', 'bulan', 'tahun', 'tahunOptions'));
    }

    /**
     * Tampilkan detail rincian Slip Gaji Tutor per siswa.
     */
    public function show(int $tutorId, Request $request)
    {
        $tutor = Tutor::findOrFail($tutorId);
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $payroll = $this->payrollService->calculateTutorPayroll($tutor, $bulan, $tahun);

        return view('admin.payroll.show', compact('payroll', 'tutor', 'bulan', 'tahun'));
    }

    /**
     * Ekspor Slip Gaji Individual Tutor ke format PDF.
     */
    public function exportSlipPdf(int $tutorId, Request $request)
    {
        $tutor = Tutor::findOrFail($tutorId);
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $payroll = $this->payrollService->calculateTutorPayroll($tutor, $bulan, $tahun);
        $tanggalCetak = Carbon::now('Asia/Jakarta');

        $pdf = Pdf::loadView('admin.payroll.slip_pdf', compact('payroll', 'tutor', 'tanggalCetak'))
            ->setPaper('a4', 'portrait');

        $filename = 'Slip_Gaji_'.str_replace(' ', '_', $tutor->nama_lengkap).'_'.$tahun.'_'.sprintf('%02d', $bulan).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Ekspor Laporan Rekapitulasi Anggaran Penggajian Sekolah ke format PDF.
     */
    public function exportRekapPdf(Request $request)
    {
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $summary = $this->payrollService->generatePayrollSummary($bulan, $tahun);
        $tanggalCetak = Carbon::now('Asia/Jakarta');

        $pdf = Pdf::loadView('admin.payroll.rekap_pdf', compact('summary', 'tanggalCetak'))
            ->setPaper('a4', 'landscape');

        $filename = 'Rekap_Anggaran_Payroll_'.$tahun.'_'.sprintf('%02d', $bulan).'.pdf';

        return $pdf->download($filename);
    }
}

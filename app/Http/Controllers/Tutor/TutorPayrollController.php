<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorPayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    /**
     * Tampilkan slip gaji digital tutor yang sedang login.
     */
    public function index(Request $request)
    {
        $tutor = Auth::user()->tutor;
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $payroll = $this->payrollService->calculateTutorPayroll($tutor, $bulan, $tahun);
        $tahunOptions = range(Carbon::now()->year, Carbon::now()->year - 3);

        return view('tutor.payroll', compact('payroll', 'bulan', 'tahun', 'tahunOptions'));
    }

    /**
     * Unduh Slip Gaji PDF mandiri oleh tutor.
     */
    public function exportPdf(Request $request)
    {
        $tutor = Auth::user()->tutor;
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $payroll = $this->payrollService->calculateTutorPayroll($tutor, $bulan, $tahun);
        $tanggalCetak = Carbon::now('Asia/Jakarta');

        $pdf = Pdf::loadView('admin.payroll.slip_pdf', compact('payroll', 'tutor', 'tanggalCetak'))
            ->setPaper('a4', 'portrait');

        $filename = 'Slip_Gaji_'.str_replace(' ', '_', $tutor->nama_lengkap).'_'.$tahun.'_'.sprintf('%02d', $bulan).'.pdf';

        return $pdf->download($filename);
    }
}

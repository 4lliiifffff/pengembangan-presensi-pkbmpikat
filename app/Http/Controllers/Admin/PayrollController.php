<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PayrollBulkTarifTemplateExport;
use App\Exports\PayrollRekapExport;
use App\Http\Controllers\Controller;
use App\Imports\PayrollBulkTarifImport;
use App\Models\Tutor;
use App\Services\PayrollService;
use App\Services\WebPushService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    /**
     * Ekspor Laporan Rekapitulasi Anggaran Penggajian Sekolah ke format Excel (.xlsx).
     */
    public function exportRekapExcel(Request $request)
    {
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $summary = $this->payrollService->generatePayrollSummary($bulan, $tahun);

        return Excel::download(
            new PayrollRekapExport($summary),
            'Rekap_Anggaran_Payroll_'.$tahun.'_'.sprintf('%02d', $bulan).'.xlsx'
        );
    }

    /**
     * Unduh template pembaruan massal tarif siswa ke format Excel (.xlsx).
     */
    public function downloadTarifTemplate()
    {
        return Excel::download(
            new PayrollBulkTarifTemplateExport,
            'Template_Pembaruan_Tarif_Honor_Siswa_PKBM_Pikat.xlsx'
        );
    }

    /**
     * Impor pembaruan massal tarif honor mengajar siswa dari spreadsheet.
     */
    public function importBulkTarif(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'Silakan pilih berkas spreadsheet Excel/CSV terlebih dahulu.',
            'file_excel.mimes' => 'Format berkas harus berekstensi .xlsx, .xls, atau .csv.',
            'file_excel.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        $import = new PayrollBulkTarifImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->back()->with(
            'success',
            "Pembaruan tarif siswa berhasil diproses: {$import->updatedCount} tarif siswa diperbarui, {$import->skippedCount} data dilewati."
        );
    }

    /**
     * Kirim broadcast notifikasi pengumuman slip gaji ke semua Tutor aktif.
     */
    public function broadcastNotifikasi(Request $request, WebPushService $webPushService)
    {
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);
        $namaBulan = Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM');

        $sentCount = $webPushService->sendToAllTutors([
            'title' => '💰 Slip Gaji & Honor Diterbitkan',
            'body' => "Slip gaji dan rekap honor mengajar periode {$namaBulan} {$tahun} telah diterbitkan. Silakan periksa di menu Profil/Payroll Anda.",
            'url' => route('tutor.payroll.index'),
        ]);

        return redirect()->back()->with(
            'success',
            "Notifikasi pengumuman payroll periode {$namaBulan} {$tahun} berhasil dikirim ke {$sentCount} perangkat tutor."
        );
    }
}

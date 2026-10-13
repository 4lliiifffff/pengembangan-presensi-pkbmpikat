<?php

namespace App\Http\Controllers\Admin;

use App\Exports\JadwalExport;
use App\Exports\JadwalTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\JadwalImport;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['tab' => 'agenda']);

        return app(JadwalSesiKbmController::class)->index($request);
    }

    public function create()
    {
        return redirect()->route('admin.jadwal.index', ['tab' => 'agenda', 'tambah' => 1]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'lokasi_presensi_id' => 'nullable|exists:lokasi_presensi,id',
        ], [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'lokasi_presensi_id.exists' => 'Titik lokasi presensi yang dipilih tidak valid.',
        ]);

        Jadwal::create($validated);

        return redirect()->route('admin.jadwal.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        return redirect()->route('admin.jadwal.index', ['tab' => 'agenda', 'edit_agenda' => $jadwal->id]);
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'lokasi_presensi_id' => 'nullable|exists:lokasi_presensi,id',
        ], [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'lokasi_presensi_id.exists' => 'Titik lokasi presensi yang dipilih tidak valid.',
        ]);

        $jadwal->update($validated);

        return redirect()->route('admin.jadwal.index', ['tanggal' => $jadwal->tanggal])
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $tanggal = $jadwal->tanggal;
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index', ['tanggal' => $tanggal])
            ->with('success', 'Agenda berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(
            new JadwalExport,
            'Agenda_Jadwal_PKBM_Pikat_'.date('Ymd').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new JadwalTemplateExport,
            'Template_Import_Jadwal_PKBM_Pikat.xlsx'
        );
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

        $import = new JadwalImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->route('admin.jadwal.index')->with(
            'success',
            "Impor jadwal/agenda berhasil: {$import->importedCount} kegiatan ditambahkan, {$import->skippedCount} data dilewati."
        );
    }
}

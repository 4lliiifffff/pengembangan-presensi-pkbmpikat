<?php

namespace App\Http\Controllers\Admin;

use App\Exports\JadwalExport;
use App\Exports\JadwalTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\JadwalImport;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($tanggal);

        // Ambil 1 bulan
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $monthDays = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $monthDays->push($date->copy());
        }

        // Agenda untuk tanggal yang dipilih
        $jadwals = Jadwal::whereDate('tanggal', $selectedDate)
            ->orderBy('created_at')
            ->get();

        // Hitung total agenda per hari dalam 1 bulan
        $monthCounts = Jadwal::whereBetween('tanggal', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])
            ->get()
            ->groupBy(function ($item) {
                return is_object($item->tanggal)
                    ? $item->tanggal->format('Y-m-d')
                    : substr((string) $item->tanggal, 0, 10);
            })
            ->map->count();

        return view('admin.jadwal.index', compact(
            'jadwals',
            'selectedDate',
            'monthDays',
            'monthCounts'));
    }

    public function create()
    {
        return view('admin.jadwal.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
        ], [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);

        Jadwal::create($request->only(['judul', 'deskripsi', 'tanggal', 'lokasi']));

        return redirect()->route('admin.jadwal.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        return view('admin.jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
        ], [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);

        $jadwal->update($request->only(['judul', 'deskripsi', 'tanggal', 'lokasi']));

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

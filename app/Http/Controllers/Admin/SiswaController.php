<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Services\TutorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function __construct(protected TutorService $tutorService) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');

        $query = Siswa::with(['relKelas.jenjangPaket', 'tutor'])->orderByDesc('id');

        if ($status !== 'semua') {
            $query->where('status_siswa', $status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('no_absen', 'like', "%{$search}%")
                    ->orWhere('nama_wali', 'like', "%{$search}%");
            });
        }

        $siswas = $query->paginate(15)->withQueryString();

        $stats = [
            'aktif' => Siswa::where('status_siswa', 'aktif')->count(),
            'alumni' => Siswa::where('status_siswa', 'alumni')->count(),
            'cuti' => Siswa::where('status_siswa', 'cuti')->count(),
            'nonaktif' => Siswa::where('status_siswa', 'nonaktif')->count(),
            'total' => Siswa::count(),
        ];

        return view('admin.siswa.index', compact('siswas', 'stats', 'status'));
    }

    public function create()
    {
        $kelas = Kelas::with('jenjangPaket')->orderBy('jenjang_paket_id')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $tutors = $this->tutorService->getAssignableTutors();

        return view('admin.siswa.create', compact('kelas', 'tutors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_absen' => ['required', 'string', 'max:50', 'unique:siswas,no_absen'],
            'nama_siswa' => ['required', 'string', 'max:120'],
            'status_siswa' => ['nullable', 'string', 'in:aktif,alumni,cuti,nonaktif'],
            'is_abk' => ['nullable', 'boolean'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_wali' => ['required', 'string', 'max:120'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tutor_id' => ['nullable', 'exists:tutors,id'],
        ]);

        $validated['is_abk'] = $request->boolean('is_abk');
        $validated['status_siswa'] = $validated['status_siswa'] ?? 'aktif';

        Siswa::create($validated);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        $siswa->load(['relKelas.jenjangPaket', 'tutor']);

        return view('admin.siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::with('jenjangPaket')->orderBy('jenjang_paket_id')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $tutors = $this->tutorService->getAssignableTutors();
        $siswa->load(['relKelas.jenjangPaket', 'tutor']);

        return view('admin.siswa.edit', compact('siswa', 'kelas', 'tutors'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'no_absen' => [
                'required',
                'string',
                'max:50',
                Rule::unique('siswas', 'no_absen')->ignore($siswa->id),
            ],
            'nama_siswa' => ['required', 'string', 'max:120'],
            'status_siswa' => ['nullable', 'string', 'in:aktif,alumni,cuti,nonaktif'],
            'is_abk' => ['nullable', 'boolean'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_wali' => ['required', 'string', 'max:120'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tutor_id' => ['nullable', 'exists:tutors,id'],
        ]);

        $validated['is_abk'] = $request->boolean('is_abk');
        $validated['status_siswa'] = $validated['status_siswa'] ?? $siswa->status_siswa;

        $siswa->update($validated);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $namaSiswa = $siswa->nama_siswa;
        $siswa->delete();

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa "'.$namaSiswa.'" berhasil diarsipkan (Soft Delete). Seluruh data riwayat presensi tetap aman.');
    }

    public function exportExcel()
    {
        return Excel::download(
            new SiswaExport,
            'Data_Siswa_PKBM_Pikat_'.date('Ymd').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new SiswaTemplateExport,
            'Template_Import_Siswa_PKBM_Pikat.xlsx'
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

        $import = new SiswaImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->route('admin.siswa.index')->with(
            'success',
            "Impor data siswa berhasil: {$import->importedCount} siswa baru ditambahkan, {$import->updatedCount} siswa diperbarui, {$import->skippedCount} data dilewati."
        );
    }
}

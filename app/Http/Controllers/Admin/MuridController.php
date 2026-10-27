<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MuridExport;
use App\Exports\MuridTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\MuridImport;
use App\Models\Kelas;
use App\Models\Murid;
use App\Services\TutorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class MuridController extends Controller
{
    public function __construct(protected TutorService $tutorService) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');
        $pembimbing = $request->query('pembimbing', 'semua');
        $tutorId = $request->query('tutor_id');
        $kelasId = $request->query('kelas_id');

        $query = Murid::with(['relKelas.jenjangPaket', 'tutor'])->orderByDesc('id');

        if ($status !== 'semua' && ! empty($status)) {
            $query->where('status_murid', $status);
        }

        if ($pembimbing === 'ada') {
            $query->whereNotNull('tutor_id');
        } elseif ($pembimbing === 'belum') {
            $query->whereNull('tutor_id');
        }

        if (! empty($tutorId)) {
            $query->where('tutor_id', $tutorId);
        }

        if (! empty($kelasId)) {
            $query->where('kelas_id', $kelasId);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama_murid', 'like', "%{$search}%")
                    ->orWhere('no_absen', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nama_wali', 'like', "%{$search}%")
                    ->orWhereHas('tutor', function ($tq) use ($search) {
                        $tq->where('nama_lengkap', 'like', "%{$search}%");
                    })
                    ->orWhereHas('relKelas', function ($kq) use ($search) {
                        $kq->where('nama_kelas', 'like', "%{$search}%");
                    });
            });
        }

        $murids = $query->paginate(15)->withQueryString();

        $rawStats = Murid::selectRaw('
            count(*) as total,
            sum(case when status_murid = "aktif" then 1 else 0 end) as aktif,
            sum(case when status_murid = "alumni" then 1 else 0 end) as alumni,
            sum(case when status_murid = "cuti" then 1 else 0 end) as cuti,
            sum(case when status_murid = "nonaktif" then 1 else 0 end) as nonaktif,
            sum(case when tutor_id is not null then 1 else 0 end) as ada_pembimbing,
            sum(case when tutor_id is null then 1 else 0 end) as tanpa_pembimbing,
            sum(case when status_murid = "aktif" and tutor_id is null then 1 else 0 end) as aktif_tanpa_pembimbing
        ')->first();

        $stats = [
            'aktif' => (int) ($rawStats->aktif ?? 0),
            'alumni' => (int) ($rawStats->alumni ?? 0),
            'cuti' => (int) ($rawStats->cuti ?? 0),
            'nonaktif' => (int) ($rawStats->nonaktif ?? 0),
            'total' => (int) ($rawStats->total ?? 0),
            'ada_pembimbing' => (int) ($rawStats->ada_pembimbing ?? 0),
            'tanpa_pembimbing' => (int) ($rawStats->tanpa_pembimbing ?? 0),
            'aktif_tanpa_pembimbing' => (int) ($rawStats->aktif_tanpa_pembimbing ?? 0),
        ];

        $tutors = $this->tutorService->getAssignableTutors();
        $kelasList = Kelas::with('jenjangPaket')->orderBy('nama_kelas')->get();

        return view('admin.murid.index', compact(
            'murids',
            'stats',
            'status',
            'pembimbing',
            'tutorId',
            'kelasId',
            'tutors',
            'kelasList'
        ));
    }

    public function create()
    {
        $kelas = Kelas::with('jenjangPaket')->orderBy('jenjang_paket_id')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $tutors = $this->tutorService->getAssignableTutors();

        return view('admin.murid.create', compact('kelas', 'tutors'));
    }

    public function store(Request $request)
    {
        if ($request->has('nama_siswa') && ! $request->has('nama_murid')) {
            $request->merge(['nama_murid' => $request->input('nama_siswa')]);
        }
        if ($request->has('status_siswa') && ! $request->has('status_murid')) {
            $request->merge(['status_murid' => $request->input('status_siswa')]);
        }

        $validated = $request->validate([
            'no_absen' => ['required', 'string', 'max:50', 'unique:murid,no_absen'],
            'nis' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'nama_murid' => ['required', 'string', 'max:120'],
            'status_murid' => ['nullable', 'string', 'in:aktif,alumni,cuti,nonaktif'],
            'is_abk' => ['nullable', 'boolean'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_wali' => ['required', 'string', 'max:120'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tutor_id' => ['nullable', 'exists:tutor,id'],
        ]);

        $validated['is_abk'] = $request->boolean('is_abk');
        $validated['status_murid'] = $validated['status_murid'] ?? 'aktif';

        Murid::create($validated);

        return redirect()
            ->route('admin.murid.index')
            ->with('success', 'Data murid berhasil ditambahkan.');
    }

    public function show(?Murid $murid = null)
    {
        $murid = ($murid && $murid->exists) ? $murid : Murid::findOrFail(request()->route('murid') ?? request()->route('siswa'));
        $murid->load(['relKelas.jenjangPaket', 'tutor', 'user']);

        return view('admin.murid.show', compact('murid'));
    }

    public function resetPassword(?Murid $murid = null)
    {
        $murid = ($murid && $murid->exists) ? $murid : Murid::findOrFail(request()->route('murid') ?? request()->route('siswa'));
        if (! $murid->user) {
            return redirect()
                ->route('admin.murid.show', ['murid' => $murid->id])
                ->with('error', 'Murid ini belum memiliki akun user.');
        }

        $murid->user->update([
            'password' => Hash::make('password123'),
        ]);

        return redirect()
            ->route('admin.murid.show', ['murid' => $murid->id])
            ->with('success', 'Password akun murid berhasil direset menjadi "password123".');
    }

    public function edit(?Murid $murid = null)
    {
        $murid = ($murid && $murid->exists) ? $murid : Murid::findOrFail(request()->route('murid') ?? request()->route('siswa'));
        $kelas = Kelas::with('jenjangPaket')->orderBy('jenjang_paket_id')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $tutors = $this->tutorService->getAssignableTutors();
        $murid->load(['relKelas.jenjangPaket', 'tutor']);

        return view('admin.murid.edit', compact('murid', 'kelas', 'tutors'));
    }

    public function update(Request $request, ?Murid $murid = null)
    {
        $murid = ($murid && $murid->exists) ? $murid : Murid::findOrFail(request()->route('murid') ?? request()->route('siswa'));
        if ($request->has('nama_siswa') && ! $request->has('nama_murid')) {
            $request->merge(['nama_murid' => $request->input('nama_siswa')]);
        }
        if ($request->has('status_siswa') && ! $request->has('status_murid')) {
            $request->merge(['status_murid' => $request->input('status_siswa')]);
        }

        $validated = $request->validate([
            'no_absen' => [
                'required',
                'string',
                'max:50',
                Rule::unique('murid', 'no_absen')->ignore($murid->id),
            ],
            'nis' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'nama_murid' => ['required', 'string', 'max:120'],
            'status_murid' => ['nullable', 'string', 'in:aktif,alumni,cuti,nonaktif'],
            'is_abk' => ['nullable', 'boolean'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_wali' => ['required', 'string', 'max:120'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tutor_id' => ['nullable', 'exists:tutor,id'],
        ]);

        $validated['is_abk'] = $request->boolean('is_abk');
        $validated['status_murid'] = $validated['status_murid'] ?? $murid->status_murid;

        $murid->update($validated);

        return redirect()
            ->route('admin.murid.index')
            ->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy(?Murid $murid = null)
    {
        $murid = ($murid && $murid->exists) ? $murid : Murid::findOrFail(request()->route('murid') ?? request()->route('siswa'));
        $namaMurid = $murid->nama_murid;
        $murid->delete();

        return redirect()
            ->route('admin.murid.index')
            ->with('success', 'Data murid "'.$namaMurid.'" berhasil diarsipkan (Soft Delete). Seluruh data riwayat presensi tetap aman.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new MuridExport($request->all()),
            'Data_Murid_PKBM_Pikat_'.date('Ymd_His').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new MuridTemplateExport,
            'Template_Import_Murid_PKBM_Pikat.xlsx'
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

        $import = new MuridImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->route('admin.murid.index')->with(
            'success',
            "Impor data murid berhasil: {$import->importedCount} murid baru ditambahkan, {$import->updatedCount} murid diperbarui, {$import->skippedCount} data dilewati."
        );
    }
}

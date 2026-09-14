<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Services\TutorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function __construct(protected TutorService $tutorService) {}

    public function index()
    {
        $siswas = Siswa::with(['relKelas', 'tutor'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.siswa.index', compact('siswas'));
    }

    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $tutors = $this->tutorService->getAssignableTutors();

        return view('admin.siswa.create', compact('kelas', 'tutors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_absen' => ['required', 'string', 'max:50', 'unique:siswas,no_absen'],
            'nama_siswa' => ['required', 'string', 'max:120'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_wali' => ['required', 'string', 'max:120'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tutor_id' => ['nullable', 'exists:tutors,id'],
            'tarif_per_jam' => ['required', 'numeric', 'min:0'],
        ]);

        Siswa::create($validated);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        $siswa->load('relKelas');

        return view('admin.siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $tutors = $this->tutorService->getAssignableTutors();
        $siswa->load('relKelas', 'tutor');

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
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_wali' => ['required', 'string', 'max:120'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tutor_id' => ['nullable', 'exists:tutors,id'],
            'tarif_per_jam' => ['required', 'numeric', 'min:0'],
        ]);

        $siswa->update($validated);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }
}

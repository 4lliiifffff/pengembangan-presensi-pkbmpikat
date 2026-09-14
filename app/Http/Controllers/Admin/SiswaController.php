<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\kelas as Kelas;
use App\Models\Kepala_Sekolah;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
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
        $tutors = $this->assignableTutors();

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
        $tutors = $this->assignableTutors();
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

    /**
     * Daftar tutor untuk dropdown siswa: semua tutor + kepala sekolah (disinkron ke tabel tutors).
     */
    private function assignableTutors()
    {
        $this->syncKepsekIntoTutors();

        return Tutor::orderBy('nama_lengkap')->get();
    }

    private function syncKepsekIntoTutors(): void
    {
        $hasJabatan = Schema::hasColumn('tutors', 'jabatan');

        $ensureTutor = function (array $attrs) use ($hasJabatan): void {
            if (! empty($attrs['user_id']) && Tutor::where('user_id', $attrs['user_id'])->exists()) {
                return;
            }
            if (! empty($attrs['nik']) && Tutor::where('nik', $attrs['nik'])->exists()) {
                return;
            }
            if (! empty($attrs['email']) && Schema::hasColumn('tutors', 'email')
                && Tutor::where('email', $attrs['email'])->exists()) {
                return;
            }

            if ($hasJabatan) {
                $attrs['jabatan'] = $attrs['jabatan'] ?? 'Kepala Sekolah';
            }

            Tutor::create($attrs);
        };

        Kepala_Sekolah::query()->orderBy('nama_lengkap')->each(function (Kepala_Sekolah $kepsek) use ($ensureTutor, $hasJabatan) {
            if (! $kepsek->user_id) {
                return;
            }

            $ensureTutor([
                'user_id' => $kepsek->user_id,
                'nik' => $kepsek->nik,
                'nama_lengkap' => $kepsek->nama_lengkap,
                'email' => $kepsek->email,
                'no_hp' => $kepsek->no_hp,
                'foto' => $kepsek->foto,
                'jabatan' => $hasJabatan ? 'Kepala Sekolah' : null,
            ]);
        });

        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        User::query()
            ->where('role', 'kepala_sekolah')
            ->each(function (User $user) use ($ensureTutor, $hasJabatan) {
                if (Tutor::where('user_id', $user->id)->exists()) {
                    return;
                }

                $nik = (string) ($user->nik ?? '');
                $email = (string) ($user->email ?? '');
                if ($email === '' && $nik !== '') {
                    $email = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
                }
                if ($nik === '') {
                    $nik = 'KEPSEK-'.$user->id;
                }

                $ensureTutor([
                    'user_id' => $user->id,
                    'nik' => $nik,
                    'nama_lengkap' => (string) (($user->nama_lengkap ?? $user->name) ?? 'Kepala Sekolah'),
                    'email' => $email,
                    'no_hp' => $user->no_hp ?? null,
                    'foto' => $user->foto ?? null,
                    'jabatan' => $hasJabatan ? 'Kepala Sekolah' : null,
                ]);
            });
    }
}

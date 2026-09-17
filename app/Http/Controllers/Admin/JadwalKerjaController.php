<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\KategoriTutorial;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalKerjaController extends Controller
{
    /**
     * Tampilkan daftar seluruh jadwal & shift kerja.
     */
    public function index(Request $request): View
    {
        $query = JadwalKerja::query()->with('kategoriTutorial')->withCount(['presensis', 'presensiKaryawans']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_shift', 'like', "%{$search}%")
                    ->orWhere('kode_shift', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_shift')) {
            $query->where('jenis_shift', $request->input('jenis_shift'));
        }

        if ($request->filled('status')) {
            $query->where('is_aktif', $request->input('status') === 'active');
        }

        $shifts = $query->orderBy('urutan')
            ->orderBy('jam_masuk')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => JadwalKerja::count(),
            'active' => JadwalKerja::where('is_aktif', true)->count(),
            'umum' => JadwalKerja::where('jenis_shift', 'umum')->count(),
            'kbm' => JadwalKerja::where('jenis_shift', 'kbm')->count(),
        ];

        return view('admin.jadwal_kerja.index', compact('shifts', 'stats'));
    }

    /**
     * Form tambah jadwal/shift kerja baru.
     */
    public function create(): View
    {
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->orderBy('nama_kategori')->get();

        return view('admin.jadwal_kerja.create', compact('kategoriTutorials'));
    }

    /**
     * Simpan jadwal shift kerja baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_shift' => ['required', 'string', 'max:100'],
            'kode_shift' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:jadwal_kerjas,kode_shift'],
            'jenis_shift' => ['required', 'string', 'in:umum,kbm'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorials,id'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'earliest_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'is_aktif' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_shift.required' => 'Nama shift kerja wajib diisi.',
            'kode_shift.required' => 'Kode shift unik wajib diisi.',
            'kode_shift.unique' => 'Kode shift sudah digunakan oleh jadwal lain.',
            'jam_masuk.required' => 'Jam masuk wajib diisi.',
            'jam_pulang.required' => 'Jam pulang wajib diisi.',
            'earliest_minutes.required' => 'Batas awal presensi wajib diisi.',
            'tolerance_minutes.required' => 'Toleransi keterlambatan wajib diisi.',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);
        $validated['urutan'] = $validated['urutan'] ?? 0;

        // Auto-calculate durasi_jam jika tidak diisi manual atau jika ada link ke kategori_tutorials
        if (! empty($validated['kategori_tutorial_id'])) {
            $kat = KategoriTutorial::find($validated['kategori_tutorial_id']);
            if ($kat) {
                $validated['durasi_jam'] = (float) $kat->durasi_jam;
            }
        } elseif (empty($validated['durasi_jam'])) {
            $tMasuk = Carbon::parse('2000-01-01 '.$validated['jam_masuk'].':00');
            $tPulang = Carbon::parse('2000-01-01 '.$validated['jam_pulang'].':00');
            if ($tPulang->lt($tMasuk)) {
                $tPulang->addDay();
            }
            $diffHours = $tMasuk->diffInMinutes($tPulang) / 60;
            $validated['durasi_jam'] = round($diffHours, 2);
        }

        JadwalKerja::create($validated);

        return redirect()->route('admin.jadwal-kerja.index')
            ->with('success', "Jadwal shift '{$validated['nama_shift']}' berhasil ditambahkan.");
    }

    /**
     * Form edit data jadwal shift kerja.
     */
    public function edit(JadwalKerja $jadwalKerja): View
    {
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->orderBy('nama_kategori')->get();

        return view('admin.jadwal_kerja.edit', compact('jadwalKerja', 'kategoriTutorials'));
    }

    /**
     * Perbarui data jadwal shift kerja.
     */
    public function update(Request $request, JadwalKerja $jadwalKerja): RedirectResponse
    {
        $validated = $request->validate([
            'nama_shift' => ['required', 'string', 'max:100'],
            'kode_shift' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:jadwal_kerjas,kode_shift,'.$jadwalKerja->id],
            'jenis_shift' => ['required', 'string', 'in:umum,kbm'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorials,id'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'earliest_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'is_aktif' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');
        $validated['urutan'] = $validated['urutan'] ?? $jadwalKerja->urutan;

        if (! empty($validated['kategori_tutorial_id'])) {
            $kat = KategoriTutorial::find($validated['kategori_tutorial_id']);
            if ($kat) {
                $validated['durasi_jam'] = (float) $kat->durasi_jam;
            }
        } elseif (empty($validated['durasi_jam'])) {
            $tMasuk = Carbon::parse('2000-01-01 '.$validated['jam_masuk'].':00');
            $tPulang = Carbon::parse('2000-01-01 '.$validated['jam_pulang'].':00');
            if ($tPulang->lt($tMasuk)) {
                $tPulang->addDay();
            }
            $diffHours = $tMasuk->diffInMinutes($tPulang) / 60;
            $validated['durasi_jam'] = round($diffHours, 2);
        }

        $jadwalKerja->update($validated);

        return redirect()->route('admin.jadwal-kerja.index')
            ->with('success', "Jadwal shift '{$jadwalKerja->nama_shift}' berhasil diperbarui.");
    }

    /**
     * Hapus jadwal shift kerja (atau nonaktifkan jika memiliki relasi presensi).
     */
    public function destroy(JadwalKerja $jadwalKerja): RedirectResponse
    {
        $hasHistory = $jadwalKerja->presensis()->exists() || $jadwalKerja->presensiKaryawans()->exists();

        if ($hasHistory) {
            $jadwalKerja->update(['is_aktif' => false]);

            return redirect()->route('admin.jadwal-kerja.index')
                ->with('warning', "Shift '{$jadwalKerja->nama_shift}' memiliki riwayat presensi. Status otomatis diubah menjadi Non-Aktif.");
        }

        $jadwalKerja->delete();

        return redirect()->route('admin.jadwal-kerja.index')
            ->with('success', "Jadwal shift '{$jadwalKerja->nama_shift}' berhasil dihapus.");
    }

    /**
     * Toggle status aktif / non-aktif via PATCH.
     */
    public function toggleStatus(JadwalKerja $jadwalKerja): RedirectResponse
    {
        $jadwalKerja->update([
            'is_aktif' => ! $jadwalKerja->is_aktif,
        ]);

        $statusStr = $jadwalKerja->is_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status shift '{$jadwalKerja->nama_shift}' berhasil {$statusStr}.");
    }
}

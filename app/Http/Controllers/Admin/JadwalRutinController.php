<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\JadwalRutin;
use App\Models\KategoriTutorial;
use App\Models\LokasiPresensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\JadwalRutinService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalRutinController extends Controller
{
    /**
     * Tampilkan master jadwal rutin mingguan siswa & tutor.
     */
    public function index(Request $request): View
    {
        $query = JadwalRutin::query()
            ->with(['siswa.relKelas', 'tutor', 'kategoriTutorial', 'jadwalKerja', 'lokasiPresensi'])
            ->withCount('jadwalSesis');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', fn ($s) => $s->where('nama_murid', 'like', "%{$search}%"))
                    ->orWhereHas('tutor', fn ($t) => $t->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $muridFilterId = $request->input('murid_id', $request->input('siswa_id'));
        if (! empty($muridFilterId)) {
            $query->where('murid_id', $muridFilterId);
        }

        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->input('tutor_id'));
        }

        if ($request->filled('hari')) {
            $query->where('hari', strtolower($request->input('hari')));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $jadwalRutins = $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->orderBy('jam_masuk')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => JadwalRutin::count(),
            'active' => JadwalRutin::where('is_active', true)->count(),
            'murid_count' => JadwalRutin::distinct('murid_id')->count('murid_id'),
            'siswa_count' => JadwalRutin::distinct('murid_id')->count('murid_id'),
            'tutor_count' => JadwalRutin::distinct('tutor_id')->count('tutor_id'),
        ];

        $siswas = Siswa::aktif()->orderBy('nama_murid')->get(['id', 'nama_murid', 'no_absen', 'kelas_id']);
        $murids = $siswas;
        $tutors = Tutor::whereHas('user', fn ($u) => $u->where('is_active', true))->orderBy('nama_lengkap')->get(['id', 'nama_lengkap']);

        return view('admin.jadwal_rutin.index', compact('jadwalRutins', 'stats', 'siswas', 'murids', 'tutors'));
    }

    /**
     * Form tambah jadwal rutin KBM baru untuk murid tertentu.
     */
    public function create(): View
    {
        $siswas = Siswa::aktif()->orderBy('nama_murid')->get();
        $murids = $siswas;
        $tutors = Tutor::whereHas('user', fn ($u) => $u->where('is_active', true))->orderBy('nama_lengkap')->get();
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->orderBy('nama_kategori')->get();
        $jadwalKerjas = JadwalKerja::active()->where('jenis_shift', 'kbm')->orderBy('urutan')->get();
        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();

        $hariList = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
            'minggu' => 'Minggu',
        ];

        return view('admin.jadwal_rutin.create', compact('siswas', 'murids', 'tutors', 'kategoriTutorials', 'jadwalKerjas', 'lokasiPresensis', 'hariList'));
    }

    /**
     * Simpan jadwal rutin KBM baru.
     */
    public function store(Request $request, JadwalRutinService $service): RedirectResponse
    {
        if (! $request->filled('siswa_id') && $request->filled('murid_id')) {
            $request->merge(['siswa_id' => $request->input('murid_id')]);
        }

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:murid,id'],
            'tutor_id' => ['required', 'exists:tutor,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorial,id'],
            'jadwal_kerja_id' => ['nullable', 'exists:jadwal_kerja,id'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
            'hari' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'berlaku_mulai' => ['nullable', 'date'],
            'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'auto_generate' => ['nullable', 'boolean'],
        ], [
            'siswa_id.required' => 'Pilih murid terlebih dahulu.',
            'tutor_id.required' => 'Pilih tutor pengampu terlebih dahulu.',
            'hari.required' => 'Pilih hari belajar rutin dalam seminggu.',
            'jam_masuk.required' => 'Jam mulai belajar wajib diisi.',
            'jam_pulang.required' => 'Jam selesai belajar wajib diisi.',
            'jam_pulang.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);

        if (empty($validated['durasi_jam'])) {
            $t1 = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
            $t2 = Carbon::createFromFormat('H:i', $validated['jam_pulang']);
            $validated['durasi_jam'] = round($t1->diffInMinutes($t2) / 60, 2);
        }

        // Auto link jadwal kerja KBM jika belum ditentukan
        if (empty($validated['jadwal_kerja_id']) && ! empty($validated['kategori_tutorial_id'])) {
            $jadwalKerja = JadwalKerja::active()
                ->where('jenis_shift', 'kbm')
                ->where('kategori_tutorial_id', $validated['kategori_tutorial_id'])
                ->first();
            $validated['jadwal_kerja_id'] = $jadwalKerja?->id;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['berlaku_mulai'] = $validated['berlaku_mulai'] ?? Carbon::now('Asia/Jakarta')->toDateString();

        $rutin = JadwalRutin::create($validated);

        // Auto-generate sesi kalender 4 minggu ke depan jika opsi aktif
        $genInfo = '';
        if ($request->boolean('auto_generate', true)) {
            $result = $service->generateForNextWeeks(4, $rutin->siswa_id, $rutin->tutor_id);
            $genInfo = " Berhasil menghasilkan {$result['created']} sesi KBM untuk 4 minggu ke depan.";
        }

        return redirect()->route('admin.jadwal-rutin.index')
            ->with('success', "Master Jadwal Rutin KBM untuk murid berhasil dibuat.{$genInfo}");
    }

    /**
     * Form edit master jadwal rutin.
     */
    public function edit(JadwalRutin $jadwalRutin): View
    {
        $siswas = Siswa::aktif()->orderBy('nama_murid')->get();
        $murids = $siswas;
        $tutors = Tutor::whereHas('user', fn ($u) => $u->where('is_active', true))->orderBy('nama_lengkap')->get();
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->orderBy('nama_kategori')->get();
        $jadwalKerjas = JadwalKerja::active()->where('jenis_shift', 'kbm')->orderBy('urutan')->get();
        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();

        $hariList = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
            'minggu' => 'Minggu',
        ];

        return view('admin.jadwal_rutin.edit', compact('jadwalRutin', 'siswas', 'murids', 'tutors', 'kategoriTutorials', 'jadwalKerjas', 'lokasiPresensis', 'hariList'));
    }

    /**
     * Perbarui master jadwal rutin.
     */
    public function update(Request $request, JadwalRutin $jadwalRutin, JadwalRutinService $service): RedirectResponse
    {
        if (! $request->filled('siswa_id') && $request->filled('murid_id')) {
            $request->merge(['siswa_id' => $request->input('murid_id')]);
        }

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:murid,id'],
            'tutor_id' => ['required', 'exists:tutor,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorial,id'],
            'jadwal_kerja_id' => ['nullable', 'exists:jadwal_kerja,id'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
            'hari' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'berlaku_mulai' => ['nullable', 'date'],
            'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sync_future' => ['nullable', 'boolean'],
        ], [
            'siswa_id.required' => 'Pilih murid terlebih dahulu.',
            'tutor_id.required' => 'Pilih tutor pengampu terlebih dahulu.',
            'hari.required' => 'Pilih hari belajar rutin dalam seminggu.',
            'jam_masuk.required' => 'Jam mulai belajar wajib diisi.',
            'jam_pulang.required' => 'Jam selesai belajar wajib diisi.',
            'jam_pulang.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);

        if (empty($validated['durasi_jam'])) {
            $t1 = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
            $t2 = Carbon::createFromFormat('H:i', $validated['jam_pulang']);
            $validated['durasi_jam'] = round($t1->diffInMinutes($t2) / 60, 2);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $jadwalRutin->update($validated);

        $syncInfo = '';
        if ($request->boolean('sync_future', true)) {
            $syncedCount = $service->syncOnMasterUpdate($jadwalRutin);
            $service->generateForNextWeeks(4, $jadwalRutin->siswa_id, $jadwalRutin->tutor_id);
            $syncInfo = " Sinkronisasi {$syncedCount} sesi mendatang berhasil diperbarui.";
        }

        return redirect()->route('admin.jadwal-rutin.index')
            ->with('success', "Master Jadwal Rutin berhasil diperbarui.{$syncInfo}");
    }

    /**
     * Toggle status aktif master jadwal rutin.
     */
    public function toggleStatus(JadwalRutin $jadwalRutin): RedirectResponse
    {
        $jadwalRutin->update(['is_active' => ! $jadwalRutin->is_active]);

        $statusText = $jadwalRutin->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Jadwal rutin berhasil {$statusText}.");
    }

    /**
     * Hapus master jadwal rutin.
     */
    public function destroy(JadwalRutin $jadwalRutin): RedirectResponse
    {
        $jadwalRutin->delete();

        return redirect()->route('admin.jadwal-rutin.index')
            ->with('success', 'Master Jadwal Rutin KBM berhasil dihapus.');
    }

    /**
     * Trigger manual generate sesi dari Master Jadwal Rutin.
     */
    public function generateManual(Request $request, JadwalRutinService $service): RedirectResponse
    {
        $weeks = (int) $request->input('weeks', 4);
        $siswaId = $request->filled('murid_id') ? (int) $request->input('murid_id') : ($request->filled('siswa_id') ? (int) $request->input('siswa_id') : null);
        $tutorId = $request->filled('tutor_id') ? (int) $request->input('tutor_id') : null;
        $skipHolidays = $request->boolean('skip_holidays');

        $result = $service->generateForNextWeeks($weeks, $siswaId, $tutorId, $skipHolidays);

        return redirect()->back()->with(
            'success',
            "Generate jadwal sesi KBM berhasil: {$result['created']} sesi baru dibuat, {$result['skipped']} sesi sudah ada dilewati, {$result['holidays']} hari libur terdeteksi."
        );
    }
}

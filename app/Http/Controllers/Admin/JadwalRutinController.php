<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\JadwalRutin;
use App\Services\JadwalRutinService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JadwalRutinController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['tab' => 'rutin']);

        return app(JadwalSesiKbmController::class)->index($request);
    }

    /**
     * Form tambah jadwal rutin KBM baru untuk murid tertentu.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.jadwal.index', ['tab' => 'rutin', 'tambah' => 1]);
    }

    /**
     * Simpan jadwal rutin KBM baru (Mendukung Multi-Murid Rombel & Single Murid).
     */
    public function store(Request $request, JadwalRutinService $service): RedirectResponse
    {
        // Normalisasi murid (dukung array siswa_ids[] atau murid_id / siswa_id tunggal)
        $siswaIds = $request->input('siswa_ids');
        if (empty($siswaIds) && $request->filled('murid_id')) {
            $siswaIds = [$request->input('murid_id')];
        } elseif (empty($siswaIds) && $request->filled('siswa_id')) {
            $siswaIds = [$request->input('siswa_id')];
        }
        if (! is_array($siswaIds)) {
            $siswaIds = $siswaIds ? [$siswaIds] : [];
        }
        $siswaIds = array_values(array_filter(array_unique(array_map('intval', $siswaIds))));
        $request->merge(['siswa_ids' => $siswaIds]);

        $validated = $request->validate([
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'integer', 'exists:murid,id'],
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
            'siswa_ids.required' => 'Pilih minimal satu murid bimbingan / rombel.',
            'siswa_ids.min' => 'Pilih minimal satu murid bimbingan / rombel.',
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

        $totalSesi = 0;
        $totalMurid = count($validated['siswa_ids']);

        foreach ($validated['siswa_ids'] as $sid) {
            $itemData = $validated;
            unset($itemData['siswa_ids']);
            $itemData['siswa_id'] = $sid;
            $itemData['murid_id'] = $sid;

            $rutin = JadwalRutin::create($itemData);

            // Auto-generate sesi kalender 4 minggu ke depan jika opsi aktif
            if ($request->boolean('auto_generate', true)) {
                $result = $service->generateForNextWeeks(4, $rutin->siswa_id, $rutin->tutor_id);
                $totalSesi += $result['created'] ?? 0;
            }
        }

        $genInfo = $totalSesi > 0 ? " dan berhasil menghasilkan {$totalSesi} sesi kalender KBM untuk 4 minggu ke depan." : '.';

        return redirect()->route('admin.jadwal.index', ['tab' => 'rutin'])
            ->with('success', "Jadwal belajar rutin berhasil disimpan untuk {$totalMurid} murid{$genInfo}");
    }

    /**
     * Form edit master jadwal rutin.
     */
    public function edit(JadwalRutin $jadwalRutin): RedirectResponse
    {
        return redirect()->route('admin.jadwal.index', ['tab' => 'rutin', 'edit_rutin' => $jadwalRutin->id]);
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

        return redirect()->route('admin.jadwal.index', ['tab' => 'rutin'])
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

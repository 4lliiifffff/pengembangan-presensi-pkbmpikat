<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tutor\Concerns\ResolvesTutor;
use App\Models\JadwalKerja;
use App\Models\JadwalSesi;
use App\Models\KategoriTutorial;
use App\Models\Siswa;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class JadwalSesiController extends Controller
{
    use ResolvesTutor;

    public function __construct(protected WebPushService $webPushService) {}

    /**
     * Tampilkan kalender dan daftar sesi rencana / sesi pengganti tutor.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor) {
            return redirect()->route('tutor.dashboard')->with('warning', 'Data profil tutor belum terhubung.');
        }

        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();
        $selectedDate = Carbon::parse($request->get('tanggal', $today))->startOfDay();

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $monthDays = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $monthDays->push($date->copy());
        }

        // Sesi belajar tutor pada tanggal terpilih
        $sesiHariIni = JadwalSesi::with(['siswa', 'kategoriTutorial', 'presensi'])
            ->where('tutor_id', $tutor->id)
            ->whereDate('tanggal_rencana', $selectedDate)
            ->orderBy('jam_masuk_rencana')
            ->get();

        // Riwayat sesi mendatang dan sesi pengganti
        $upcomingSesi = JadwalSesi::with(['siswa', 'kategoriTutorial'])
            ->where('tutor_id', $tutor->id)
            ->where('status', 'terjadwal')
            ->whereDate('tanggal_rencana', '>=', $today)
            ->orderBy('tanggal_rencana')
            ->orderBy('jam_masuk_rencana')
            ->limit(10)
            ->get();

        // Hitung sesi per hari dalam bulan terpilih
        $monthCounts = JadwalSesi::where('tutor_id', $tutor->id)
            ->whereBetween('tanggal_rencana', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->groupBy(fn ($item) => $item->tanggal_rencana->format('Y-m-d'))
            ->map->count();

        $siswas = Siswa::where('tutor_id', $tutor->id)->orWhereNull('tutor_id')->orderBy('nama_siswa')->get();
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->get();

        return view('tutor.jadwal_sesi.index', compact(
            'tutor',
            'sesiHariIni',
            'upcomingSesi',
            'today',
            'selectedDate',
            'monthDays',
            'monthCounts',
            'siswas',
            'kategoriTutorials'
        ));
    }

    /**
     * Simpan jadwal sesi baru / jadwal pengganti (make-up class).
     */
    public function store(Request $request): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor) {
            return redirect()->route('tutor.dashboard')->with('warning', 'Data profil tutor belum terhubung.');
        }

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorials,id'],
            'tanggal_rencana' => ['required', 'date'],
            'jam_masuk_rencana' => ['required', 'date_format:H:i'],
            'jam_pulang_rencana' => ['required', 'date_format:H:i', 'after:jam_masuk_rencana'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'jenis_sesi' => ['required', 'string', 'in:reguler,pengganti,tambahan'],
            'tanggal_asli' => ['nullable', 'date'],
            'alasan_penggantian' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'siswa_id.required' => 'Pilih siswa bimbingan terlebih dahulu.',
            'tanggal_rencana.required' => 'Tanggal rencana sesi wajib diisi.',
            'jam_masuk_rencana.required' => 'Jam mulai belajar wajib diisi.',
            'jam_pulang_rencana.required' => 'Jam selesai belajar wajib diisi.',
            'jam_pulang_rencana.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);

        // Auto-calculate durasi_jam jika tidak diisi atau mengikuti kategori
        if (! empty($validated['kategori_tutorial_id'])) {
            $kat = KategoriTutorial::find($validated['kategori_tutorial_id']);
            if ($kat) {
                $validated['durasi_jam'] = (float) $kat->durasi_jam;
            }
        }

        if (empty($validated['durasi_jam'])) {
            $t1 = Carbon::createFromFormat('H:i', $validated['jam_masuk_rencana']);
            $t2 = Carbon::createFromFormat('H:i', $validated['jam_pulang_rencana']);
            $validated['durasi_jam'] = round($t1->diffInMinutes($t2) / 60, 2);
        }

        // Cari jadwal kerja / shift KBM yang cocok jika ada
        $jadwalKerja = JadwalKerja::active()
            ->where('jenis_shift', 'kbm')
            ->when(! empty($validated['kategori_tutorial_id']), fn ($q) => $q->where('kategori_tutorial_id', $validated['kategori_tutorial_id']))
            ->first();

        $validated['tutor_id'] = $tutor->id;
        $validated['jadwal_kerja_id'] = $jadwalKerja?->id;
        $validated['status'] = 'terjadwal';
        $validated['status_kehadiran_siswa'] = 'belum_presensi';

        $sesi = JadwalSesi::create($validated);

        // Web Push Notifikasi ke Siswa jika terhubung akun
        try {
            $siswa = Siswa::with('user')->find($validated['siswa_id']);
            if ($siswa?->user) {
                $katNama = isset($kat) && $kat ? $kat->nama_kategori : 'Tutorial KBM';
                $tglFormatted = Carbon::parse($validated['tanggal_rencana'])->translatedFormat('d F Y');
                $jamMulai = substr((string) $validated['jam_masuk_rencana'], 0, 5);

                $this->webPushService->sendToUser($siswa->user, [
                    'title' => '📅 Jadwal Belajar Baru Terdaftar',
                    'body' => "Tutor {$tutor->nama_lengkap} telah menjadwalkan sesi belajar {$katNama} untuk Anda pada {$tglFormatted} pukul {$jamMulai} WIB.",
                    'url' => route('siswa.jadwal', ['tanggal' => $validated['tanggal_rencana']]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim webpush jadwal baru ke siswa: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Jadwal sesi mengajar berhasil disimpan.');
    }

    /**
     * Perbarui jadwal sesi.
     */
    public function update(Request $request, JadwalSesi $jadwalSesi): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor || $jadwalSesi->tutor_id !== $tutor->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah sesi ini.');
        }

        if ($jadwalSesi->status === 'selesai') {
            return redirect()->back()->with('warning', 'Sesi yang sudah selesai presensi tidak dapat diubah.');
        }

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorials,id'],
            'tanggal_rencana' => ['required', 'date'],
            'jam_masuk_rencana' => ['required', 'date_format:H:i'],
            'jam_pulang_rencana' => ['required', 'date_format:H:i', 'after:jam_masuk_rencana'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'jenis_sesi' => ['required', 'string', 'in:reguler,pengganti,tambahan'],
            'tanggal_asli' => ['nullable', 'date'],
            'alasan_penggantian' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($validated['kategori_tutorial_id'])) {
            $kat = KategoriTutorial::find($validated['kategori_tutorial_id']);
            if ($kat) {
                $validated['durasi_jam'] = (float) $kat->durasi_jam;
            }
        }

        $jadwalSesi->update($validated);

        // Web Push Notifikasi ke Siswa jika ada perubahan
        try {
            $siswa = Siswa::with('user')->find($validated['siswa_id']);
            if ($siswa?->user) {
                $katNama = isset($kat) && $kat ? $kat->nama_kategori : 'Tutorial KBM';
                $tglFormatted = Carbon::parse($validated['tanggal_rencana'])->translatedFormat('d F Y');
                $jamMulai = substr((string) $validated['jam_masuk_rencana'], 0, 5);

                $this->webPushService->sendToUser($siswa->user, [
                    'title' => '📅 Pembaruan Jadwal Belajar',
                    'body' => "Sesi belajar {$katNama} bersama Tutor {$tutor->nama_lengkap} telah diperbarui ke tanggal {$tglFormatted} pukul {$jamMulai} WIB.",
                    'url' => route('siswa.jadwal', ['tanggal' => $validated['tanggal_rencana']]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim webpush update jadwal ke siswa: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Jadwal sesi berhasil diperbarui.');
    }

    /**
     * Batalkan / hapus jadwal sesi.
     */
    public function destroy(JadwalSesi $jadwalSesi): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor || $jadwalSesi->tutor_id !== $tutor->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus sesi ini.');
        }

        if ($jadwalSesi->status === 'selesai' || $jadwalSesi->presensi_id) {
            return redirect()->back()->with('warning', 'Sesi yang sudah selesai presensi tidak dapat dihapus.');
        }

        $jadwalSesi->delete();

        return redirect()->back()->with('success', 'Jadwal sesi berhasil dibatalkan.');
    }
}

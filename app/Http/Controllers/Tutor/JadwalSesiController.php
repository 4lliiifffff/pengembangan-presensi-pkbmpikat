<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tutor\Concerns\ResolvesTutor;
use App\Models\Jadwal;
use App\Models\JadwalKerja;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\KategoriTutorial;
use App\Models\Siswa;
use App\Services\JadwalRutinService;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class JadwalSesiController extends Controller
{
    use ResolvesTutor;

    public function __construct(
        protected WebPushService $webPushService,
        protected JadwalRutinService $jadwalRutinService
    ) {}

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

        // Master Pola Rutin Mingguan milik Tutor ini
        $jadwalRutins = JadwalRutin::with(['siswa', 'kategoriTutorial'])
            ->where('tutor_id', $tutor->id)
            ->withCount('jadwalSesis')
            ->orderBy('hari')
            ->orderBy('jam_masuk')
            ->get();

        // INTEGRASI: Agenda & Pengumuman Resmi PKBM pada tanggal terpilih
        $agendasHariIni = Jadwal::whereDate('tanggal', $selectedDate)
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

        // INTEGRASI: Hitung total agenda PKBM per hari dalam 1 bulan
        $agendaMonthCounts = Jadwal::whereBetween('tanggal', [
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

        $totalAgendaCount = Jadwal::whereBetween('tanggal', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])->count();

        $todaySesiCount = JadwalSesi::where('tutor_id', $tutor->id)
            ->whereDate('tanggal_rencana', $today)
            ->count();

        $siswas = Siswa::where('tutor_id', $tutor->id)->orWhereNull('tutor_id')->orderBy('nama_siswa')->get();
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->get();

        return view('tutor.jadwal_sesi.index', compact(
            'tutor',
            'sesiHariIni',
            'todaySesiCount',
            'upcomingSesi',
            'jadwalRutins',
            'agendasHariIni',
            'agendaMonthCounts',
            'totalAgendaCount',
            'today',
            'selectedDate',
            'monthDays',
            'monthCounts',
            'siswas',
            'kategoriTutorials'
        ));
    }

    /**
     * Simpan jadwal baru: mendukung Pola Rutin Mingguan atau Sesi Tunggal / Pengganti.
     */
    public function store(Request $request): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor) {
            return redirect()->route('tutor.dashboard')->with('warning', 'Data profil tutor belum terhubung.');
        }

        $isRecurring = $request->boolean('is_recurring');

        if ($isRecurring) {
            // ── Opsi A: Jadwal Rutin Berulang Mingguan ──
            $validated = $request->validate([
                'siswa_id' => ['required', 'exists:siswas,id'],
                'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorials,id'],
                'hari' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
                'jam_masuk' => ['required', 'date_format:H:i'],
                'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
                'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
                'berlaku_mulai' => ['nullable', 'date'],
                'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
                'keterangan' => ['nullable', 'string', 'max:1000'],
                'auto_generate' => ['nullable', 'boolean'],
            ], [
                'siswa_id.required' => 'Pilih siswa bimbingan terlebih dahulu.',
                'hari.required' => 'Hari belajar mingguan wajib dipilih.',
                'jam_masuk.required' => 'Jam mulai belajar wajib diisi.',
                'jam_pulang.required' => 'Jam selesai belajar wajib diisi.',
                'jam_pulang.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
                'berlaku_sampai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]);

            // Hitung durasi jam aktual dari selisih jam
            $t1 = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
            $t2 = Carbon::createFromFormat('H:i', $validated['jam_pulang']);
            $diffMenit = $t1->diffInMinutes($t2);
            $durasiHitung = round($diffMenit / 60, 2);

            // Cek kesesuaian dengan SK resmi
            $katSk = null;
            if (! empty($validated['kategori_tutorial_id'])) {
                $katSk = KategoriTutorial::find($validated['kategori_tutorial_id']);
            }

            if ($katSk && abs((float) $katSk->durasi_jam - $durasiHitung) < 0.05) {
                $validated['durasi_jam'] = (float) $katSk->durasi_jam;
                $validated['kategori_tutorial_id'] = $katSk->id;
            } else {
                // Cari apakah ada SK lain yang pas dengan durasi ini
                $katCocok = KategoriTutorial::active()->where('durasi_jam', $durasiHitung)->first();
                if ($katCocok) {
                    $validated['durasi_jam'] = (float) $katCocok->durasi_jam;
                    $validated['kategori_tutorial_id'] = $katCocok->id;
                } else {
                    // Durasi di luar SK
                    $validated['durasi_jam'] = $durasiHitung;
                    $validated['kategori_tutorial_id'] = null;
                    $tagKhusus = "[Jadwal Khusus: Durasi {$durasiHitung} Jam di luar SK]";
                    $validated['keterangan'] = ! empty($validated['keterangan'])
                        ? $tagKhusus.' '.$validated['keterangan']
                        : $tagKhusus;
                }
            }

            $jadwalKerja = JadwalKerja::active()
                ->where('jenis_shift', 'kbm')
                ->when(! empty($validated['kategori_tutorial_id']), fn ($q) => $q->where('kategori_tutorial_id', $validated['kategori_tutorial_id']))
                ->first();

            $validated['jadwal_kerja_id'] = $jadwalKerja?->id;

            $result = $this->jadwalRutinService->createRutinFromTutor($validated, $tutor->id);

            // Web Push Notifikasi ke Siswa
            try {
                $siswa = Siswa::with('user')->find($validated['siswa_id']);
                if ($siswa?->user) {
                    $hariLabel = JadwalRutin::HARI_LABELS[$validated['hari']] ?? ucfirst($validated['hari']);
                    $this->webPushService->sendToUser($siswa->user, [
                        'title' => '📅 Jadwal Belajar Mingguan Ditetapkan',
                        'body' => "Tutor {$tutor->nama_lengkap} telah menetapkan jadwal KBM rutin setiap hari {$hariLabel} pukul {$validated['jam_masuk']} - {$validated['jam_pulang']} WIB.",
                        'url' => route('siswa.jadwal'),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim webpush jadwal rutin ke siswa: '.$e->getMessage());
            }

            $sesiCount = $result['generated']['created'];

            return redirect()->back()->with('success', "Jadwal rutin mingguan berhasil disimpan dan {$sesiCount} sesi kalender telah digenerate.");
        }

        // ── Opsi B: Sesi Tunggal / Sesi Pengganti Sekali ──
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

        // Hitung durasi jam aktual dari selisih jam
        $t1 = Carbon::createFromFormat('H:i', $validated['jam_masuk_rencana']);
        $t2 = Carbon::createFromFormat('H:i', $validated['jam_pulang_rencana']);
        $diffMenit = $t1->diffInMinutes($t2);
        $durasiHitung = round($diffMenit / 60, 2);

        // Cek kesesuaian dengan SK resmi
        $katSk = null;
        if (! empty($validated['kategori_tutorial_id'])) {
            $katSk = KategoriTutorial::find($validated['kategori_tutorial_id']);
        }

        if ($katSk && abs((float) $katSk->durasi_jam - $durasiHitung) < 0.05) {
            $validated['durasi_jam'] = (float) $katSk->durasi_jam;
            $validated['kategori_tutorial_id'] = $katSk->id;
        } else {
            // Cari apakah ada SK lain yang pas dengan durasi ini
            $katCocok = KategoriTutorial::active()->where('durasi_jam', $durasiHitung)->first();
            if ($katCocok) {
                $validated['durasi_jam'] = (float) $katCocok->durasi_jam;
                $validated['kategori_tutorial_id'] = $katCocok->id;
            } else {
                // Durasi di luar SK
                $validated['durasi_jam'] = $durasiHitung;
                $validated['kategori_tutorial_id'] = null;
                $tagKhusus = "[Jadwal Khusus: Durasi {$durasiHitung} Jam di luar SK]";
                $validated['catatan'] = ! empty($validated['catatan'])
                    ? $tagKhusus.' '.$validated['catatan']
                    : $tagKhusus;
            }
        }

        $jadwalKerja = JadwalKerja::active()
            ->where('jenis_shift', 'kbm')
            ->when(! empty($validated['kategori_tutorial_id']), fn ($q) => $q->where('kategori_tutorial_id', $validated['kategori_tutorial_id']))
            ->first();

        $validated['tutor_id'] = $tutor->id;
        $validated['jadwal_kerja_id'] = $jadwalKerja?->id;
        $validated['status'] = 'terjadwal';
        $validated['status_kehadiran_siswa'] = 'belum_presensi';

        $sesi = JadwalSesi::create($validated);

        // Web Push Notifikasi ke Siswa
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
     * Reschedule sesi mengajar (Opsi 2: Sesi lama dibatalkan dengan riwayat alasan, sesi pengganti dibuat).
     */
    public function reschedule(Request $request, JadwalSesi $jadwalSesi): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor || $jadwalSesi->tutor_id !== $tutor->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk me-reschedule sesi ini.');
        }

        if ($jadwalSesi->status === 'selesai' || $jadwalSesi->presensi_id) {
            return redirect()->back()->with('warning', 'Sesi yang sudah selesai presensi tidak dapat di-reschedule.');
        }

        $validated = $request->validate([
            'tanggal_baru' => ['required', 'date'],
            'jam_masuk_baru' => ['required', 'date_format:H:i'],
            'jam_pulang_baru' => ['required', 'date_format:H:i', 'after:jam_masuk_baru'],
            'alasan_penggantian' => ['required', 'string', 'max:1000'],
        ], [
            'tanggal_baru.required' => 'Tanggal baru hasil reschedule wajib diisi.',
            'jam_masuk_baru.required' => 'Jam mulai belajar baru wajib diisi.',
            'jam_pulang_baru.required' => 'Jam selesai belajar baru wajib diisi.',
            'jam_pulang_baru.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
            'alasan_penggantian.required' => 'Alasan reschedule / pemindahan jadwal wajib diisi.',
        ]);

        $newSesi = $this->jadwalRutinService->rescheduleSesi(
            $jadwalSesi,
            $validated['tanggal_baru'],
            $validated['jam_masuk_baru'],
            $validated['jam_pulang_baru'],
            $validated['alasan_penggantian']
        );

        // Web Push Notifikasi ke Siswa
        try {
            $siswa = Siswa::with('user')->find($jadwalSesi->siswa_id);
            if ($siswa?->user) {
                $tglFormatted = Carbon::parse($validated['tanggal_baru'])->translatedFormat('d F Y');
                $jamMulai = substr((string) $validated['jam_masuk_baru'], 0, 5);

                $this->webPushService->sendToUser($siswa->user, [
                    'title' => '🔄 Sesi Belajar Direschedule',
                    'body' => "Sesi KBM Anda bersama Tutor {$tutor->nama_lengkap} telah dipindahkan ke tanggal {$tglFormatted} pukul {$jamMulai} WIB. Alasan: {$validated['alasan_penggantian']}.",
                    'url' => route('siswa.jadwal', ['tanggal' => $validated['tanggal_baru']]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim webpush reschedule ke siswa: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Sesi berhasil di-reschedule. Sesi lama telah dibatalkan dengan riwayat alasan, dan sesi pengganti baru telah tercatat.');
    }

    /**
     * Perbarui jadwal sesi langsung (misal pergeseran jam pada hari yang sama).
     */
    public function update(Request $request, JadwalSesi $jadwalSesi): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor || $jadwalSesi->tutor_id !== $tutor->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah sesi ini.');
        }

        if ($jadwalSesi->status === 'selesai' || $jadwalSesi->presensi_id) {
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
     * Batalkan / hapus jadwal sesi tunggal.
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

        return redirect()->back()->with('success', 'Jadwal sesi berhasil dihapus.');
    }

    /**
     * Toggle status aktif master jadwal rutin mingguan milik tutor.
     */
    public function toggleStatusRutin(JadwalRutin $jadwalRutin): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor || $jadwalRutin->tutor_id !== $tutor->id) {
            abort(403, 'Akses ditolak.');
        }

        $jadwalRutin->update(['is_active' => ! $jadwalRutin->is_active]);

        $statusText = $jadwalRutin->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Master jadwal rutin siswa berhasil {$statusText}.");
    }

    /**
     * Hapus master jadwal rutin mingguan milik tutor.
     */
    public function destroyRutin(JadwalRutin $jadwalRutin): RedirectResponse
    {
        $tutor = $this->resolveTutor();
        if (! $tutor || $jadwalRutin->tutor_id !== $tutor->id) {
            abort(403, 'Akses ditolak.');
        }

        $jadwalRutin->delete();

        return redirect()->back()->with('success', 'Master jadwal rutin mingguan berhasil dihapus.');
    }
}

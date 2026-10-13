<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalKerja;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\KategoriTutorial;
use App\Models\Kelas;
use App\Models\LokasiPresensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\JadwalRutinService;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class JadwalSesiKbmController extends Controller
{
    public function __construct(
        protected JadwalRutinService $jadwalRutinService,
        protected WebPushService $webPushService
    ) {}

    /**
     * Tampilkan Unified Schedule Hub: Kalender Sesi KBM Harian, Pola Rutin KBM, dan Agenda Acara PKBM.
     */
    public function index(Request $request): View
    {
        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();
        $selectedDate = Carbon::parse($request->get('tanggal', $today))->startOfDay();
        $activeTab = $request->get('tab', 'sesi');

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $monthDays = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $monthDays->push($date->copy());
        }

        // ── TAB 1: Query Sesi KBM Harian seluruh tutor pada tanggal terpilih ──
        $query = JadwalSesi::with(['siswa.relKelas.jenjangPaket', 'tutor.user', 'kategoriTutorial', 'presensi', 'lokasiPresensi'])
            ->whereDate('tanggal_rencana', $selectedDate);

        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->input('tutor_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', fn ($s) => $s->where('nama_murid', 'like', "%{$search}%"))
                    ->orWhereHas('tutor', fn ($t) => $t->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $sesiHariIni = $query->orderBy('jam_masuk_rencana')->get();

        // Hitung sesi per hari dalam 1 bulan terpilih
        $monthCounts = JadwalSesi::whereBetween('tanggal_rencana', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])
            ->get()
            ->groupBy(fn ($item) => $item->tanggal_rencana->format('Y-m-d'))
            ->map->count();

        // KPI Ringkasan
        $stats = [
            'total' => $sesiHariIni->count(),
            'selesai' => $sesiHariIni->where('status', 'selesai')->count(),
            'terjadwal' => $sesiHariIni->where('status', 'terjadwal')->count(),
            'dibatalkan' => $sesiHariIni->where('status', 'dibatalkan')->count(),
        ];

        // ── TAB 2: Query Master Pola Rutin KBM ──
        $rutinQuery = JadwalRutin::with(['siswa.relKelas.jenjangPaket', 'tutor.user', 'kategoriTutorial', 'lokasiPresensi'])
            ->withCount('jadwalSesis');

        if ($request->filled('tutor_id')) {
            $rutinQuery->where('tutor_id', $request->input('tutor_id'));
        }

        if ($request->filled('hari')) {
            $rutinQuery->where('hari', $request->input('hari'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $rutinQuery->where(function ($q) use ($search) {
                $q->whereHas('siswa', fn ($s) => $s->where('nama_murid', 'like', "%{$search}%"))
                    ->orWhereHas('tutor', fn ($t) => $t->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $jadwalRutins = $rutinQuery->orderBy('hari')->orderBy('jam_masuk')->get();

        // ── TAB 3: Query Agenda Acara & Libur Resmi PKBM ──
        $agendasHariIni = Jadwal::with('lokasiPresensi')
            ->whereDate('tanggal', $selectedDate)
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

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

        // ── Data Pendukung untuk Form / Modal Terpadu ──
        $tutors = Tutor::whereHas('user', fn ($u) => $u->where('is_active', true))
            ->orderBy('nama_lengkap')
            ->get();

        $siswas = Siswa::with(['relKelas.jenjangPaket', 'tutor'])
            ->where(function ($q) {
                $q->where('status_murid', 'aktif')
                    ->orWhereNull('status_murid');
            })
            ->orderBy('kelas_id')
            ->orderBy('nama_murid')
            ->get();

        $kelasList = Kelas::with('jenjangPaket')->orderBy('nama_kelas')->get();
        $kategoriTutorials = KategoriTutorial::active()->orderBy('urutan')->get();
        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();

        return view('admin.jadwal.index', compact(
            'sesiHariIni',
            'stats',
            'today',
            'selectedDate',
            'monthDays',
            'monthCounts',
            'tutors',
            'lokasiPresensis',
            'jadwalRutins',
            'agendasHariIni',
            'agendaMonthCounts',
            'totalAgendaCount',
            'siswas',
            'kelasList',
            'kategoriTutorials',
            'activeTab'
        ));
    }

    /**
     * Simpan jadwal baru dari Admin (mendukung Pola Rutin Mingguan atau Sesi Tunggal Sekali).
     */
    public function store(Request $request): RedirectResponse
    {
        // Deteksi cerdas: Jika request berisi agenda kegiatan PKBM (judul tanpa tutor_id)
        if ($request->filled('judul') && ! $request->filled('tutor_id') && ! $request->filled('siswa_ids') && ! $request->filled('siswa_id')) {
            return app(JadwalController::class)->store($request);
        }

        // Normalisasi murid terpilih (bisa multi-select siswa_ids[] atau single siswa_id)
        $siswaIds = $request->input('siswa_ids');
        if (empty($siswaIds) && $request->filled('siswa_id')) {
            $siswaIds = [$request->input('siswa_id')];
        }
        if (! is_array($siswaIds)) {
            $siswaIds = $siswaIds ? [$siswaIds] : [];
        }
        $siswaIds = array_values(array_filter(array_unique(array_map('intval', $siswaIds))));
        $request->merge(['siswa_ids' => $siswaIds]);

        // Sanitasi input awal: 'custom' atau kosong dijadikan null
        $rawKategori = $request->input('kategori_tutorial_id');
        if ($rawKategori === 'custom' || $rawKategori === '' || $rawKategori === '0') {
            $request->merge(['kategori_tutorial_id' => null]);
        }

        // Sanitasi format jam: potong H:i:s menjadi H:i jika ada detik dari browser
        $jamFields = ['jam_masuk', 'jam_pulang', 'jam_masuk_rencana', 'jam_pulang_rencana'];
        $normalizedTimes = [];
        foreach ($jamFields as $field) {
            if ($request->filled($field)) {
                $timeVal = trim((string) $request->input($field));
                $normalizedTimes[$field] = strlen($timeVal) > 5 ? substr($timeVal, 0, 5) : $timeVal;
            }
        }
        if (! empty($normalizedTimes)) {
            $request->merge($normalizedTimes);
        }

        $isRecurring = $request->boolean('is_recurring');

        if ($isRecurring) {
            // ── Opsi A: Jadwal Rutin Berulang Mingguan ──
            $validated = $request->validate([
                'tutor_id' => ['required', 'exists:tutor,id'],
                'siswa_ids' => ['required', 'array', 'min:1'],
                'siswa_ids.*' => ['required', 'integer', 'exists:murid,id'],
                'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorial,id'],
                'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
                'hari' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
                'jam_masuk' => ['required', 'date_format:H:i'],
                'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
                'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
                'berlaku_mulai' => ['nullable', 'date'],
                'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
                'keterangan' => ['nullable', 'string', 'max:1000'],
                'auto_generate' => ['nullable', 'boolean'],
            ], [
                'tutor_id.required' => 'Pilih tutor pengampu KBM terlebih dahulu.',
                'siswa_ids.required' => 'Pilih minimal satu murid bimbingan / rombel.',
                'siswa_ids.min' => 'Pilih minimal satu murid bimbingan / rombel.',
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
            } elseif ($rawKategori !== 'custom' && ($katCocok = KategoriTutorial::active()->where('durasi_jam', $durasiHitung)->first())) {
                $validated['durasi_jam'] = (float) $katCocok->durasi_jam;
                $validated['kategori_tutorial_id'] = $katCocok->id;
            } else {
                $validated['durasi_jam'] = $durasiHitung;
                $validated['kategori_tutorial_id'] = null;
                if ($rawKategori !== 'custom') {
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

            $tutor = Tutor::with('user')->find($validated['tutor_id']);
            $totalCreatedSesi = 0;
            $totalMurid = count($validated['siswa_ids']);
            $hariLabel = JadwalRutin::HARI_LABELS[$validated['hari']] ?? ucfirst($validated['hari']);

            foreach ($validated['siswa_ids'] as $sid) {
                $itemData = $validated;
                unset($itemData['siswa_ids']);
                $itemData['siswa_id'] = $sid;
                $itemData['murid_id'] = $sid;
                $itemData['jadwal_kerja_id'] = $jadwalKerja?->id;

                $result = $this->jadwalRutinService->createRutinFromTutor($itemData, $tutor->id);
                $totalCreatedSesi += $result['generated']['created'] ?? 0;

                // Web Push Notifikasi ke Siswa
                try {
                    $siswa = Siswa::with('user')->find($sid);
                    if ($siswa?->user) {
                        $this->webPushService->sendToUser($siswa->user, [
                            'title' => '📅 Jadwal Belajar Mingguan Ditetapkan',
                            'body' => "Admin telah menetapkan jadwal KBM rutin Anda bersama tutor {$tutor?->nama_lengkap} setiap hari {$hariLabel} pukul {$validated['jam_masuk']} - {$validated['jam_pulang']} WIB.",
                            'url' => route('siswa.jadwal'),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Gagal kirim webpush jadwal rutin ke siswa {$sid}: ".$e->getMessage());
                }
            }

            // Web Push Notifikasi ke Tutor
            try {
                if ($tutor?->user) {
                    $this->webPushService->sendToUser($tutor->user, [
                        'title' => '📅 Jadwal Rutin Ditetapkan oleh Admin',
                        'body' => "Admin telah menetapkan jadwal KBM rutin untuk {$totalMurid} murid setiap hari {$hariLabel} pukul {$validated['jam_masuk']} - {$validated['jam_pulang']} WIB.",
                        'url' => route('tutor.jadwal', ['tab' => 'rutin']),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim webpush jadwal rutin ke tutor: '.$e->getMessage());
            }

            return redirect()->route('admin.jadwal.index', ['tab' => 'rutin'])
                ->with('success', "Pola rutin KBM berhasil disimpan untuk {$totalMurid} murid dan total {$totalCreatedSesi} sesi kalender telah digenerate.");
        }

        // ── Opsi B: Sesi Tunggal / Pengganti Sekali ──
        $validated = $request->validate([
            'tutor_id' => ['required', 'exists:tutor,id'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'integer', 'exists:murid,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorial,id'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
            'tanggal_rencana' => ['required', 'date'],
            'jam_masuk_rencana' => ['required', 'date_format:H:i'],
            'jam_pulang_rencana' => ['required', 'date_format:H:i', 'after:jam_masuk_rencana'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'jenis_sesi' => ['required', 'string', 'in:reguler,pengganti,tambahan'],
            'tanggal_asli' => ['nullable', 'date'],
            'alasan_penggantian' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'tutor_id.required' => 'Pilih tutor pengampu KBM terlebih dahulu.',
            'siswa_ids.required' => 'Pilih minimal satu murid bimbingan / rombel.',
            'siswa_ids.min' => 'Pilih minimal satu murid bimbingan / rombel.',
            'tanggal_rencana.required' => 'Tanggal rencana sesi wajib diisi.',
            'jam_masuk_rencana.required' => 'Jam mulai belajar wajib diisi.',
            'jam_pulang_rencana.required' => 'Jam selesai belajar wajib diisi.',
            'jam_pulang_rencana.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);

        $t1 = Carbon::createFromFormat('H:i', $validated['jam_masuk_rencana']);
        $t2 = Carbon::createFromFormat('H:i', $validated['jam_pulang_rencana']);
        $diffMenit = $t1->diffInMinutes($t2);
        $durasiHitung = round($diffMenit / 60, 2);

        $katSk = null;
        if (! empty($validated['kategori_tutorial_id'])) {
            $katSk = KategoriTutorial::find($validated['kategori_tutorial_id']);
        }

        if ($katSk && abs((float) $katSk->durasi_jam - $durasiHitung) < 0.05) {
            $validated['durasi_jam'] = (float) $katSk->durasi_jam;
            $validated['kategori_tutorial_id'] = $katSk->id;
        } elseif ($rawKategori !== 'custom' && ($katCocok = KategoriTutorial::active()->where('durasi_jam', $durasiHitung)->first())) {
            $validated['durasi_jam'] = (float) $katCocok->durasi_jam;
            $validated['kategori_tutorial_id'] = $katCocok->id;
        } else {
            $validated['durasi_jam'] = $durasiHitung;
            $validated['kategori_tutorial_id'] = null;
            if ($rawKategori !== 'custom') {
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

        $tutor = Tutor::with('user')->find($validated['tutor_id']);
        $totalMurid = count($validated['siswa_ids']);
        $katNama = ($katSk ?? $katCocok)?->nama_kategori ?? 'Tutorial KBM';
        $tglFormatted = Carbon::parse($validated['tanggal_rencana'])->translatedFormat('d F Y');
        $jamMulai = substr((string) $validated['jam_masuk_rencana'], 0, 5);

        foreach ($validated['siswa_ids'] as $sid) {
            $itemData = $validated;
            unset($itemData['siswa_ids']);
            $itemData['siswa_id'] = $sid;
            $itemData['tutor_id'] = $tutor->id;
            $itemData['jadwal_kerja_id'] = $jadwalKerja?->id;
            $itemData['status'] = 'terjadwal';
            $itemData['status_kehadiran_siswa'] = 'belum_presensi';

            JadwalSesi::create($itemData);

            // Web Push Notifikasi ke Siswa
            try {
                $siswa = Siswa::with('user')->find($sid);
                if ($siswa?->user) {
                    $this->webPushService->sendToUser($siswa->user, [
                        'title' => '📅 Jadwal Belajar Baru Terdaftar',
                        'body' => "Admin telah menjadwalkan sesi belajar {$katNama} untuk Anda bersama tutor {$tutor?->nama_lengkap} pada {$tglFormatted} pukul {$jamMulai} WIB.",
                        'url' => route('siswa.jadwal', ['tanggal' => $validated['tanggal_rencana']]),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal kirim webpush jadwal baru ke siswa {$sid}: ".$e->getMessage());
            }
        }

        // Web Push Notifikasi ke Tutor
        try {
            if ($tutor?->user) {
                $this->webPushService->sendToUser($tutor->user, [
                    'title' => '📅 Sesi KBM Ditambahkan oleh Admin',
                    'body' => "Admin telah menjadwalkan sesi KBM untuk {$totalMurid} murid Anda pada {$tglFormatted} pukul {$jamMulai} WIB.",
                    'url' => route('tutor.jadwal', ['tanggal' => $validated['tanggal_rencana']]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim webpush jadwal baru ke tutor: '.$e->getMessage());
        }

        return redirect()->route('admin.jadwal.index', [
            'tab' => 'sesi',
            'tanggal' => $validated['tanggal_rencana'],
        ])->with('success', "Jadwal sesi mengajar berhasil disimpan untuk {$totalMurid} murid.");
    }

    /**
     * Admin membantu me-reschedule sesi KBM tutor jika ada kendala lapangan.
     */
    public function reschedule(Request $request, JadwalSesi $jadwalSesi): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_baru' => ['required', 'date', 'after_or_equal:today'],
            'jam_masuk_baru' => ['required', 'date_format:H:i'],
            'jam_pulang_baru' => ['required', 'date_format:H:i', 'after:jam_masuk_baru'],
            'alasan_penggantian' => ['required', 'string', 'max:1000'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
        ], [
            'tanggal_baru.required' => 'Tanggal baru wajib dipilih.',
            'tanggal_baru.after_or_equal' => 'Tanggal baru tidak boleh tanggal lampau.',
            'jam_masuk_baru.required' => 'Jam mulai baru wajib diisi.',
            'jam_pulang_baru.required' => 'Jam selesai baru wajib diisi.',
            'jam_pulang_baru.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
            'alasan_penggantian.required' => 'Alasan reschedule / penyesuaian jadwal wajib diisi.',
        ]);

        try {
            $newSesi = $this->jadwalRutinService->rescheduleSesi(
                $jadwalSesi,
                $validated['tanggal_baru'],
                $validated['jam_masuk_baru'],
                $validated['jam_pulang_baru'],
                $validated['alasan_penggantian'],
                null,
                $validated['lokasi_presensi_id'] ?? null
            );

            // Web Push Notifikasi ke Tutor & Siswa
            try {
                $tutorUser = $jadwalSesi->tutor?->user;
                $siswaUser = $jadwalSesi->siswa?->user;
                $tglBaruFmt = Carbon::parse($validated['tanggal_baru'])->translatedFormat('d F Y');

                if ($tutorUser) {
                    $this->webPushService->sendToUser($tutorUser, [
                        'title' => '📅 Jadwal Sesi Di-reschedule oleh Admin',
                        'body' => "Sesi KBM murid {$jadwalSesi->siswa?->nama_murid} telah disesuaikan ke tanggal {$tglBaruFmt} pukul {$validated['jam_masuk_baru']} WIB oleh Admin.",
                        'url' => route('tutor.jadwal-sesi.index', ['tanggal' => $validated['tanggal_baru']]),
                    ]);
                }

                if ($siswaUser) {
                    $this->webPushService->sendToUser($siswaUser, [
                        'title' => '📅 Jadwal Belajar Di-reschedule oleh Admin',
                        'body' => "Sesi KBM bersama tutor {$jadwalSesi->tutor?->nama_lengkap} telah dijadwalkan ulang ke tanggal {$tglBaruFmt} pukul {$validated['jam_masuk_baru']} WIB.",
                        'url' => route('siswa.jadwal', ['tanggal' => $validated['tanggal_baru']]),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim webpush reschedule admin: '.$e->getMessage());
            }

            return redirect()->back()->with('success', 'Sesi KBM berhasil di-reschedule oleh Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', 'Gagal me-reschedule sesi: '.$e->getMessage());
        }
    }

    /**
     * Hapus jadwal sesi KBM harian (hanya jika belum presensi).
     */
    public function destroy(JadwalSesi $jadwalSesi): RedirectResponse
    {
        if ($jadwalSesi->status === 'selesai' || $jadwalSesi->presensi_id) {
            return redirect()->back()->with('warning', 'Sesi yang sudah selesai presensi tidak dapat dihapus.');
        }

        $jadwalSesi->delete();

        return redirect()->back()->with('success', 'Jadwal sesi berhasil dihapus.');
    }

    /**
     * Toggle status aktif master jadwal rutin KBM.
     */
    public function toggleStatusRutin(JadwalRutin $jadwalRutin): RedirectResponse
    {
        $jadwalRutin->update(['is_active' => ! $jadwalRutin->is_active]);

        $statusText = $jadwalRutin->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Pola rutin KBM murid berhasil {$statusText}.");
    }

    /**
     * Hapus master jadwal rutin KBM.
     */
    public function destroyRutin(JadwalRutin $jadwalRutin): RedirectResponse
    {
        $jadwalRutin->delete();

        return redirect()->back()->with('success', 'Master pola rutin KBM berhasil dihapus.');
    }

    /**
     * Perbarui master pola rutin KBM dari modal in-page.
     */
    public function updateRutin(Request $request, JadwalRutin $jadwalRutin): RedirectResponse
    {
        $validated = $request->validate([
            'murid_id' => ['required', 'exists:murid,id'],
            'tutor_id' => ['required', 'exists:tutor,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorial,id'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
            'hari' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
            'durasi_jam' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'berlaku_mulai' => ['nullable', 'date'],
            'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['kategori_tutorial_id'])) {
            $kat = KategoriTutorial::find($validated['kategori_tutorial_id']);
            if ($kat) {
                $validated['durasi_jam'] = (float) $kat->durasi_jam;
            }
        }

        $validated['siswa_id'] = $validated['murid_id'];
        $jadwalRutin->update($validated);

        return redirect()->route('admin.jadwal.index', ['tab' => 'rutin'])
            ->with('success', 'Master pola rutin KBM berhasil diperbarui.');
    }

    /**
     * Generate manual sesi KBM untuk 4 minggu ke depan dari seluruh pola rutin aktif.
     */
    public function generateManualRutin(Request $request): RedirectResponse
    {
        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();
        $endDate = Carbon::now($tz)->addWeeks(4)->toDateString();

        $stats = $this->jadwalRutinService->generateSesiForPeriod(
            $today,
            $endDate,
            $request->filled('murid_id') ? (int) $request->input('murid_id') : null,
            $request->filled('tutor_id') ? (int) $request->input('tutor_id') : null
        );

        return redirect()->back()->with(
            'success',
            "Sinkronisasi selesai! {$stats['created']} sesi berhasil digenerate, {$stats['skipped']} sesi dilewati (sudah ada), {$stats['holidays']} sesi disesuaikan dengan hari libur."
        );
    }

    /**
     * Perbarui langsung sesi KBM dari modal in-page.
     */
    public function update(Request $request, JadwalSesi $jadwalSesi): RedirectResponse
    {
        if ($jadwalSesi->status === 'selesai' || $jadwalSesi->presensi_id) {
            return redirect()->back()->with('warning', 'Sesi yang sudah selesai presensi tidak dapat diubah.');
        }

        $validated = $request->validate([
            'tutor_id' => ['required', 'exists:tutor,id'],
            'murid_id' => ['required', 'exists:murid,id'],
            'kategori_tutorial_id' => ['nullable', 'exists:kategori_tutorial,id'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
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

        $validated['siswa_id'] = $validated['murid_id'];
        $jadwalSesi->update($validated);

        return redirect()->back()->with('success', 'Jadwal sesi belajar berhasil diperbarui.');
    }

    /**
     * Simpan agenda kegiatan resmi / hari libur PKBM dari modal.
     */
    public function storeAgenda(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
        ], [
            'judul.required' => 'Judul agenda atau nama kegiatan wajib diisi.',
            'tanggal.required' => 'Tanggal pelaksanaan agenda wajib diisi.',
            'lokasi_presensi_id.exists' => 'Titik lokasi presensi yang dipilih tidak valid.',
        ]);

        Jadwal::create($validated);

        return redirect()->route('admin.jadwal.index', [
            'tab' => 'agenda',
            'tanggal' => $validated['tanggal'],
        ])->with('success', 'Agenda kegiatan resmi PKBM berhasil disimpan.');
    }

    /**
     * Perbarui agenda kegiatan resmi / hari libur PKBM dari modal in-page.
     */
    public function updateAgenda(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
        ], [
            'judul.required' => 'Judul agenda atau nama kegiatan wajib diisi.',
            'tanggal.required' => 'Tanggal pelaksanaan agenda wajib diisi.',
            'lokasi_presensi_id.exists' => 'Titik lokasi presensi yang dipilih tidak valid.',
        ]);

        $jadwal->update($validated);

        return redirect()->route('admin.jadwal.index', [
            'tab' => 'agenda',
            'tanggal' => $validated['tanggal'],
        ])->with('success', 'Agenda kegiatan resmi PKBM berhasil diperbarui.');
    }

    /**
     * Hapus agenda kegiatan resmi PKBM.
     */
    public function destroyAgenda(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->back()->with('success', 'Agenda resmi PKBM berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\LokasiPresensi;
use App\Models\Murid;
use App\Models\PresensiMandiriMurid;
use App\Services\GeofencingService;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MuridPresensiController extends Controller
{
    public function __construct(protected WebPushService $webPushService) {}

    /**
     * Tampilkan form presensi foto kamera & lokasi GPS untuk Murid.
     */
    public function index(): View|RedirectResponse
    {
        return $this->foto();
    }

    public function foto(): View|RedirectResponse
    {
        $user = Auth::user();
        $murid = $user->murid;

        if (! $murid) {
            return redirect()->route('logout')->with('warning', 'Profil data murid belum terhubung.');
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $todayPresensi = PresensiMandiriMurid::with('lokasiPresensi')
            ->where('murid_id', $murid->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        $requestedSesiId = request()->query('jadwal_sesi_id') ?? request()->query('sesi_id');

        // Cek Jadwal Sesi KBM Hari Ini untuk Smart Time-Gating & Lokasi Target
        $todaySesiQuery = JadwalSesi::with(['tutor', 'kategoriTutorial', 'lokasiPresensi', 'jadwalRutin.lokasiPresensi'])
            ->where('murid_id', $murid->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan');

        if ($requestedSesiId) {
            $todaySesi = (clone $todaySesiQuery)->where('id', $requestedSesiId)->first();
        }

        if (! isset($todaySesi) || ! $todaySesi) {
            $todaySesi = (clone $todaySesiQuery)
                ->where(function ($q) {
                    $q->whereNull('presensi_murid_id')
                        ->orWhere('status_kehadiran_murid', '!=', 'hadir');
                })
                ->orderBy('jam_masuk_rencana')
                ->first() ?? (clone $todaySesiQuery)->orderBy('jam_masuk_rencana')->first();
        }

        $strictGating = (bool) config('presensi_sk.strict_schedule_gating', true);
        $hasConfiguredSchedule = JadwalRutin::where('murid_id', $murid->id)->exists()
            || JadwalSesi::where('murid_id', $murid->id)->exists();

        $canCheckIn = true;
        $gatingReason = 'open';
        $gatingMessage = null;
        $waktuBukaStr = null;

        $sesiEval = null;
        if (! $todayPresensi) {
            if (! $todaySesi && ($strictGating || $hasConfiguredSchedule)) {
                $canCheckIn = false;
                $gatingReason = 'no_schedule';
                $gatingMessage = 'Anda tidak memiliki jadwal belajar (KBM) yang terdaftar hari ini.';
            } elseif ($todaySesi) {
                $jamMasukStr = substr((string) $todaySesi->jam_masuk_rencana, 0, 5);
                $jamMasukCarbon = Carbon::createFromFormat('Y-m-d H:i', $today.' '.$jamMasukStr, 'Asia/Jakarta');
                $waktuBuka = $jamMasukCarbon->copy()->subMinutes(30);
                $waktuBukaStr = $waktuBuka->format('H:i');

                $now = Carbon::now('Asia/Jakarta');
                if ($now->lt($waktuBuka)) {
                    $canCheckIn = false;
                    $gatingReason = 'too_early';
                    $gatingMessage = "Presensi belum dibuka. Absen dibuka mulai pukul {$waktuBukaStr} WIB (30 menit sebelum sesi KBM dimulai).";
                }
            }
        }

        if ($todaySesi) {
            $jamMasukStr = substr((string) $todaySesi->jam_masuk_rencana, 0, 5);
            $jamMasukCarbon = Carbon::createFromFormat('Y-m-d H:i', $today.' '.$jamMasukStr, 'Asia/Jakarta');
            $waktuBuka = $jamMasukCarbon->copy()->subMinutes(30);
            $waktuBukaStr = $waktuBuka->format('H:i');
            $now = Carbon::now('Asia/Jakarta');

            $defaultTolerance = (int) ($todaySesi->jadwalKerja->tolerance_minutes ?? config('presensi_sk.tolerance_minutes', 30));
            $durasiSesiMenit = (float) $todaySesi->durasi_jam * 60;
            $toleranceMinutes = ($durasiSesiMenit < 90)
                ? min($defaultTolerance, max(10, (int) round($durasiSesiMenit * 0.3)))
                : $defaultTolerance;

            $batasToleransi = $jamMasukCarbon->copy()->addMinutes($toleranceMinutes);

            $waktuCek = $todayPresensi ? Carbon::parse($today.' '.$todayPresensi->jam_masuk, 'Asia/Jakarta') : $now;
            $menitKeterlambatan = 0;
            $statusKehadiran = 'tepat_waktu';
            $pesanStatus = "Presensi masuk tepat waktu ({$jamMasukStr} WIB).";

            if ($waktuCek->lt($jamMasukCarbon)) {
                $statusKehadiran = 'lebih_awal';
                $pesanStatus = "Presensi masuk lebih awal dari jadwal KBM ({$jamMasukStr} WIB).";
            } elseif ($waktuCek->gt($batasToleransi)) {
                $statusKehadiran = 'terlambat';
                $menitKeterlambatan = (int) $jamMasukCarbon->diffInMinutes($waktuCek, false);
                if ($menitKeterlambatan < 0) {
                    $menitKeterlambatan = 0;
                }
                $pesanStatus = "Presensi masuk tercatat terlambat {$menitKeterlambatan} menit dari jadwal KBM ({$jamMasukStr} WIB).";
            }

            $sesiEval = [
                'jam_masuk_target' => $jamMasukStr,
                'jam_pulang_target' => substr((string) $todaySesi->jam_pulang_rencana, 0, 5),
                'batas_awal' => $waktuBukaStr,
                'batas_toleransi' => $batasToleransi->format('H:i'),
                'tolerance_minutes' => $toleranceMinutes,
                'status_kehadiran' => $statusKehadiran,
                'menit_keterlambatan' => $menitKeterlambatan,
                'is_terlambat' => ($statusKehadiran === 'terlambat'),
                'pesan' => $pesanStatus,
            ];
        }

        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();
        $targetLokasi = $todaySesi?->lokasiPresensi ?? $todaySesi?->jadwalRutin?->lokasiPresensi;

        if ($targetLokasi && $targetLokasi->latitude && $targetLokasi->longitude) {
            $kantorLat = (float) $targetLokasi->latitude;
            $kantorLng = (float) $targetLokasi->longitude;
            $radius = (int) $targetLokasi->radius_meter;
        } else {
            $kantorLat = (float) config('lokasi.sekolah_lat', -7.8011945);
            $kantorLng = (float) config('lokasi.sekolah_lng', 110.364917);
            $radius = (int) config('lokasi.radius_meter', 100);
        }

        return view('murid.presensi_foto', [
            'user' => $user,
            'murid' => $murid,
            'today' => $today,
            'todayPresensi' => $todayPresensi,
            'alreadyCheckedIn' => (bool) $todayPresensi,
            'todaySesi' => $todaySesi,
            'targetLokasi' => $targetLokasi,
            'sesiEval' => $sesiEval,
            'canCheckIn' => $canCheckIn,
            'gatingReason' => $gatingReason,
            'gatingMessage' => $gatingMessage,
            'waktuBukaStr' => $waktuBukaStr,
            'lokasiPresensis' => $lokasiPresensis,
            'kantorLat' => $kantorLat,
            'kantorLng' => $kantorLng,
            'radius' => $radius,
        ]);
    }

    /**
     * Simpan Presensi Masuk Murid (Single Check-in / Sekali Absen Masuk Per Hari).
     */
    public function store(Request $request, GeofencingService $geofencingService): RedirectResponse
    {
        $user = Auth::user();
        $murid = $user->murid;

        if (! $murid) {
            return redirect()->route('logout')->with('warning', 'Profil data murid belum terhubung.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $waktuServer = $now->format('H:i:s');

        // Cek apakah murid sudah melakukan presensi masuk hari ini (Anti-Duplikasi)
        $existingPresensi = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        if ($existingPresensi) {
            $jamTercatat = substr((string) $existingPresensi->jam_masuk, 0, 5);

            return redirect()
                ->route('murid.dashboard')
                ->with('warning', "Anda sudah melakukan presensi masuk hari ini pada pukul {$jamTercatat} WIB.");
        }

        // ── Smart Time-Gating Check ──────────────────────────────────────
        $requestedSesiId = $request->input('jadwal_sesi_id');
        $todaySesiQuery = JadwalSesi::with(['lokasiPresensi', 'jadwalRutin.lokasiPresensi'])
            ->where('murid_id', $murid->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan');

        if ($requestedSesiId) {
            $todaySesi = (clone $todaySesiQuery)->where('id', $requestedSesiId)->first();
        }

        if (! isset($todaySesi) || ! $todaySesi) {
            $todaySesi = (clone $todaySesiQuery)
                ->where(function ($q) {
                    $q->whereNull('presensi_murid_id')
                        ->orWhere('status_kehadiran_murid', '!=', 'hadir');
                })
                ->orderBy('jam_masuk_rencana')
                ->first() ?? (clone $todaySesiQuery)->orderBy('jam_masuk_rencana')->first();
        }

        $strictGating = (bool) config('presensi_sk.strict_schedule_gating', true);
        $hasConfiguredSchedule = JadwalRutin::where('murid_id', $murid->id)->exists()
            || JadwalSesi::where('murid_id', $murid->id)->exists();

        if (! $todaySesi && ($strictGating || $hasConfiguredSchedule)) {
            return redirect()
                ->route('murid.dashboard')
                ->with('warning', 'Presensi tidak dapat dilakukan karena Anda tidak memiliki jadwal KBM yang aktif hari ini.');
        }

        if ($todaySesi) {
            $jamMasukStr = substr((string) $todaySesi->jam_masuk_rencana, 0, 5);
            $jamMasukCarbon = Carbon::createFromFormat('Y-m-d H:i', $today.' '.$jamMasukStr, 'Asia/Jakarta');
            $waktuBuka = $jamMasukCarbon->copy()->subMinutes(30);

            if ($now->lt($waktuBuka)) {
                $waktuBukaStr = $waktuBuka->format('H:i');

                return redirect()
                    ->route('murid.presensi')
                    ->with('warning', "Presensi belum dibuka. Sesi Anda dijadwalkan pukul {$jamMasukStr} WIB, absensi baru dibuka mulai pukul {$waktuBukaStr} WIB.");
            }
        }

        $validated = $request->validate([
            'mode' => ['nullable', Rule::in(['mulai', 'masuk'])],
            'jadwal_sesi_id' => ['nullable', 'exists:jadwal_sesi,id'],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensi,id'],
            'foto' => ['required', 'image', 'max:5120'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'lokasi_akurasi' => ['nullable', 'numeric'],
            'is_mocked' => ['nullable', 'boolean'],
        ], [
            'foto.required' => 'Silahkan ambil foto kehadiran terlebih dahulu.',
            'foto.image' => 'Berkas foto tidak valid.',
            'foto.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $accuracy = isset($validated['lokasi_akurasi']) ? (float) $validated['lokasi_akurasi'] : null;
        $isMocked = $request->boolean('is_mocked');

        // Validasi Anti-Fake GPS & Integritas Sinyal Lokasi
        $antiMockCheck = $geofencingService->validateGpsIntegrity($validated['lokasi'] ?? null, $accuracy, $isMocked);
        if (! $antiMockCheck['is_valid']) {
            return back()->with('warning', $antiMockCheck['message']);
        }

        // Validasi Geofencing berdasarkan titik lokasi yang dipilih (atau dari sesi aktif / jadwal rutin)
        $lokasiPresensiId = isset($validated['lokasi_presensi_id']) && $validated['lokasi_presensi_id']
            ? (int) $validated['lokasi_presensi_id']
            : ($todaySesi?->lokasi_presensi_id ?? ($todaySesi?->jadwalRutin?->lokasi_presensi_id ?? null));
        $geofenceCheck = $geofencingService->checkSelectedLokasiRadius($validated['lokasi'] ?? null, $lokasiPresensiId);
        if (! $geofenceCheck['is_valid']) {
            return back()->with('warning', $geofenceCheck['message']);
        }

        $dir = 'presensi_murid/'.$murid->id.'/'.$today;
        $file = $request->file('foto');
        $filename = 'masuk_'.time().'_'.$file->getClientOriginalName();
        $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

        // Hitung evaluasi keterlambatan berdasarkan sesi aktif hari ini
        $statusKehadiran = 'tepat_waktu';
        $menitKeterlambatan = 0;
        $pesanKehadiran = 'tepat waktu';

        if ($todaySesi) {
            $jamMasukStr = substr((string) $todaySesi->jam_masuk_rencana, 0, 5);
            $defaultTolerance = (int) ($todaySesi->jadwalKerja->tolerance_minutes ?? config('presensi_sk.tolerance_minutes', 30));
            $durasiSesiMenit = (float) $todaySesi->durasi_jam * 60;
            $toleranceMinutes = ($durasiSesiMenit < 90)
                ? min($defaultTolerance, max(10, (int) round($durasiSesiMenit * 0.3)))
                : $defaultTolerance;

            $batasToleransi = $jamMasukCarbon->copy()->addMinutes($toleranceMinutes);

            if ($now->lt($jamMasukCarbon)) {
                $statusKehadiran = 'lebih_awal';
                $pesanKehadiran = 'lebih awal dari jadwal sesi ('.$jamMasukStr.' WIB)';
            } elseif ($now->gt($batasToleransi)) {
                $statusKehadiran = 'terlambat';
                $menitKeterlambatan = (int) $jamMasukCarbon->diffInMinutes($now, false);
                if ($menitKeterlambatan < 0) {
                    $menitKeterlambatan = 0;
                }
                $pesanKehadiran = "terlambat {$menitKeterlambatan} menit dari jadwal KBM ({$jamMasukStr} WIB)";
            } else {
                $pesanKehadiran = "tepat waktu ({$jamMasukStr} WIB)";
            }
        }

        $presensi = PresensiMandiriMurid::create([
            'murid_id' => $murid->id,
            'lokasi_presensi_id' => $lokasiPresensiId,
            'tgl_presensi' => $today,
            'jam_masuk' => $waktuServer,
            'foto_masuk' => $path,
            'lokasi_masuk' => $validated['lokasi'] ?? null,
            'lokasi_akurasi' => $accuracy,
            'is_mocked' => $isMocked,
            'status' => 'hadir',
            'status_kehadiran' => $statusKehadiran,
            'menit_keterlambatan' => $menitKeterlambatan,
        ]);

        // Auto-link ke Jadwal Sesi hari ini jika ada
        JadwalSesi::where('murid_id', $murid->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan')
            ->update([
                'status_kehadiran_murid' => 'hadir',
                'presensi_murid_id' => $presensi->id,
            ]);

        // Web Push Notification Konfirmasi ke HP Murid jika ada subscription
        $pushKet = ($statusKehadiran === 'terlambat') ? ' (Terlambat '.$menitKeterlambatan.' menit)' : '';
        $this->webPushService->sendToUser($user, [
            'title' => '📸 Presensi Masuk Berhasil',
            'body' => 'Kehadiran murid berhasil dicatat pada pukul '.$waktuServer.$pushKet.'. Selamat belajar di PKBM Pikat!',
            'url' => route('murid.presensi'),
        ]);

        // Web Push Notification ke Tutor yang memiliki sesi belajar dengan murid hari ini
        try {
            $todaySesiTutors = JadwalSesi::with('tutor.user')
                ->where('murid_id', $murid->id)
                ->whereDate('tanggal_rencana', $today)
                ->get()
                ->pluck('tutor')
                ->filter()
                ->unique('id');

            foreach ($todaySesiTutors as $tutor) {
                if ($tutor->user) {
                    $this->webPushService->sendToUser($tutor->user, [
                        'title' => '🎓 Murid Bimbingan Hadir',
                        'body' => "Murid bimbingan Anda, {$murid->nama_murid}, telah tiba dan melakukan presensi masuk di sekolah pada pukul {$waktuServer} WIB.",
                        'url' => route('tutor.dashboard'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim webpush kehadiran murid ke tutor: '.$e->getMessage());
        }

        $msgType = ($statusKehadiran === 'terlambat') ? 'warning' : 'success';
        $msgText = ($statusKehadiran === 'terlambat')
            ? "Presensi masuk berhasil dicatat pada pukul {$waktuServer}. Anda tercatat {$pesanKehadiran}."
            : "Presensi masuk berhasil dicatat {$pesanKehadiran} pada pukul {$waktuServer}. Selamat belajar!";

        return redirect()
            ->route('murid.dashboard')
            ->with($msgType, $msgText);
    }
}

<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\LokasiPresensi;
use App\Models\PresensiMandiriSiswa;
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

class SiswaPresensiController extends Controller
{
    public function __construct(protected WebPushService $webPushService) {}

    /**
     * Tampilkan form presensi foto kamera & lokasi GPS untuk Siswa.
     */
    public function index(): View|RedirectResponse
    {
        return $this->foto();
    }

    public function foto(): View|RedirectResponse
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung.');
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $todayPresensi = PresensiMandiriSiswa::with('lokasiPresensi')
            ->where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        // Cek Jadwal Sesi KBM Hari Ini untuk Smart Time-Gating
        $todaySesiQuery = JadwalSesi::with(['tutor', 'kategoriTutorial'])
            ->where('siswa_id', $siswa->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan');

        $todaySesi = (clone $todaySesiQuery)
            ->where(function ($q) {
                $q->whereNull('presensi_siswa_id')
                    ->orWhere('status_kehadiran_siswa', '!=', 'hadir');
            })
            ->orderBy('jam_masuk_rencana')
            ->first() ?? (clone $todaySesiQuery)->orderBy('jam_masuk_rencana')->first();

        $hasConfiguredSchedule = JadwalRutin::where('siswa_id', $siswa->id)->exists()
            || JadwalSesi::where('siswa_id', $siswa->id)->exists();

        $canCheckIn = true;
        $gatingReason = 'open';
        $gatingMessage = null;
        $waktuBukaStr = null;

        $sesiEval = null;
        if (! $todayPresensi) {
            if ($hasConfiguredSchedule && ! $todaySesi) {
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
            // Jika durasi sesi < 90 menit, gunakan toleransi proporsional 30% (lantai min. 10 menit)
            $toleranceMinutes = ($durasiSesiMenit < 90)
                ? min($defaultTolerance, max(10, (int) round($durasiSesiMenit * 0.3)))
                : $defaultTolerance;

            $batasToleransi = $jamMasukCarbon->copy()->addMinutes($toleranceMinutes);

            // Evaluasi keterlambatan (jika sudah presensi gunakan jam masuk, jika belum gunakan waktu saat ini)
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
        $kantorLat = (float) config('lokasi.sekolah_lat', -7.8011945);
        $kantorLng = (float) config('lokasi.sekolah_lng', 110.364917);
        $radius = (int) config('lokasi.radius_meter', 100);

        return view('siswa.presensi_foto', [
            'user' => $user,
            'siswa' => $siswa,
            'today' => $today,
            'todayPresensi' => $todayPresensi,
            'alreadyCheckedIn' => (bool) $todayPresensi,
            'todaySesi' => $todaySesi,
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
     * Simpan Presensi Masuk Siswa (Single Check-in / Sekali Absen Masuk Per Hari).
     */
    public function store(Request $request, GeofencingService $geofencingService): RedirectResponse
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $waktuServer = $now->format('H:i:s');

        // Cek apakah siswa sudah melakukan presensi masuk hari ini (Anti-Duplikasi)
        $existingPresensi = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        if ($existingPresensi) {
            $jamTercatat = substr((string) $existingPresensi->jam_masuk, 0, 5);

            return redirect()
                ->route('siswa.dashboard')
                ->with('warning', "Anda sudah melakukan presensi masuk hari ini pada pukul {$jamTercatat} WIB.");
        }

        // ── Smart Time-Gating Check ──────────────────────────────────────
        $todaySesiQuery = JadwalSesi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan');

        $todaySesi = (clone $todaySesiQuery)
            ->where(function ($q) {
                $q->whereNull('presensi_siswa_id')
                    ->orWhere('status_kehadiran_siswa', '!=', 'hadir');
            })
            ->orderBy('jam_masuk_rencana')
            ->first() ?? (clone $todaySesiQuery)->orderBy('jam_masuk_rencana')->first();

        $hasConfiguredSchedule = JadwalRutin::where('siswa_id', $siswa->id)->exists()
            || JadwalSesi::where('siswa_id', $siswa->id)->exists();

        if ($hasConfiguredSchedule && ! $todaySesi) {
            return redirect()
                ->route('siswa.dashboard')
                ->with('warning', 'Presensi tidak dapat dilakukan karena Anda tidak memiliki jadwal KBM yang aktif hari ini.');
        }

        if ($todaySesi) {
            $jamMasukStr = substr((string) $todaySesi->jam_masuk_rencana, 0, 5);
            $jamMasukCarbon = Carbon::createFromFormat('Y-m-d H:i', $today.' '.$jamMasukStr, 'Asia/Jakarta');
            $waktuBuka = $jamMasukCarbon->copy()->subMinutes(30);

            if ($now->lt($waktuBuka)) {
                $waktuBukaStr = $waktuBuka->format('H:i');

                return redirect()
                    ->route('siswa.presensi')
                    ->with('warning', "Presensi belum dibuka. Sesi Anda dijadwalkan pukul {$jamMasukStr} WIB, absensi baru dibuka mulai pukul {$waktuBukaStr} WIB.");
            }
        }

        $validated = $request->validate([
            'mode' => ['nullable', Rule::in(['mulai', 'masuk'])],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensis,id'],
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

        // Validasi Geofencing berdasarkan titik lokasi yang dipilih
        $lokasiPresensiId = isset($validated['lokasi_presensi_id']) ? (int) $validated['lokasi_presensi_id'] : null;
        $geofenceCheck = $geofencingService->checkSelectedLokasiRadius($validated['lokasi'] ?? null, $lokasiPresensiId);
        if (! $geofenceCheck['is_valid']) {
            return back()->with('warning', $geofenceCheck['message']);
        }

        $dir = 'uploads/presensi_siswa/'.$siswa->id.'/'.$today;
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

        $presensi = PresensiMandiriSiswa::create([
            'siswa_id' => $siswa->id,
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
        JadwalSesi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan')
            ->update([
                'status_kehadiran_siswa' => 'hadir',
                'presensi_siswa_id' => $presensi->id,
            ]);

        // Web Push Notification Konfirmasi ke HP Siswa jika ada subscription
        $pushKet = ($statusKehadiran === 'terlambat') ? ' (Terlambat '.$menitKeterlambatan.' menit)' : '';
        $this->webPushService->sendToUser($user, [
            'title' => '📸 Presensi Masuk Berhasil',
            'body' => 'Kehadiran siswa berhasil dicatat pada pukul '.$waktuServer.$pushKet.'. Selamat belajar di PKBM Pikat!',
            'url' => route('siswa.presensi'),
        ]);

        // Web Push Notification ke Tutor yang memiliki sesi belajar dengan siswa hari ini
        try {
            $todaySesiTutors = JadwalSesi::with('tutor.user')
                ->where('siswa_id', $siswa->id)
                ->whereDate('tanggal_rencana', $today)
                ->get()
                ->pluck('tutor')
                ->filter()
                ->unique('id');

            foreach ($todaySesiTutors as $tutor) {
                if ($tutor->user) {
                    $this->webPushService->sendToUser($tutor->user, [
                        'title' => '🎓 Siswa Bimbingan Hadir',
                        'body' => "Siswa bimbingan Anda, {$siswa->nama_siswa}, telah tiba dan melakukan presensi masuk di sekolah pada pukul {$waktuServer} WIB.",
                        'url' => route('tutor.dashboard'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim webpush kehadiran siswa ke tutor: '.$e->getMessage());
        }

        $msgType = ($statusKehadiran === 'terlambat') ? 'warning' : 'success';
        $msgText = ($statusKehadiran === 'terlambat')
            ? "Presensi masuk berhasil dicatat pada pukul {$waktuServer}. Anda tercatat {$pesanKehadiran}."
            : "Presensi masuk berhasil dicatat {$pesanKehadiran} pada pukul {$waktuServer}. Selamat belajar!";

        return redirect()
            ->route('siswa.dashboard')
            ->with($msgType, $msgText);
    }
}

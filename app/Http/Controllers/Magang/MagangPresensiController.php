<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Models\PresensiKaryawan;
use App\Services\GeofencingService;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MagangPresensiController extends Controller
{
    public function __construct(protected WebPushService $webPushService) {}

    /**
     * Tampilkan form presensi foto kamera & lokasi GPS untuk mahasiswa magang.
     */
    public function index(): View
    {
        return $this->foto();
    }

    /**
     * Halaman kamera presensi foto magang.
     */
    public function foto(): View
    {
        $user = Auth::user();
        $magang = $user->magang;
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $activeSesi = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_mulai')
            ->whereNull('foto_selesai')
            ->first();

        $todayPresensi = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        $kantorLat = (float) config('lokasi.sekolah_lat', -7.8011945);
        $kantorLng = (float) config('lokasi.sekolah_lng', 110.364917);
        $radius = (int) config('lokasi.radius_meter', 100);

        return view('magang.presensi_foto', [
            'user' => $user,
            'magang' => $magang,
            'today' => $today,
            'activeSesi' => $activeSesi,
            'globalActiveSesi' => $activeSesi,
            'todayPresensi' => $todayPresensi,
            'kantorLat' => $kantorLat,
            'kantorLng' => $kantorLng,
            'radius' => $radius,
        ]);
    }

    /**
     * Proses Clock-In (Absen Masuk) dan Clock-Out (Absen Pulang) Magang.
     */
    public function store(Request $request, GeofencingService $geofencingService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['mulai', 'selesai'])],
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

        // Validasi Radius Geofencing (100m dari PKBM Pikat)
        $geofenceCheck = $geofencingService->checkSekolahRadius($validated['lokasi'] ?? null);
        if (! $geofenceCheck['is_valid']) {
            return back()->with('warning', $geofenceCheck['message']);
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $waktuServer = $now->format('H:i:s');
        $dir = 'uploads/presensi_magang/'.$user->id.'/'.$today;

        $existingSesi = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->orderByDesc('id')
            ->first();

        // ─────────────────────────────────────────────
        //  MODE: MULAI (Absen Masuk)
        // ─────────────────────────────────────────────
        if ($validated['mode'] === 'mulai') {
            if ($existingSesi && ! $existingSesi->foto_selesai) {
                return back()->with('warning', 'Presensi masuk hari ini masih berjalan. Silahkan lakukan absen pulang.');
            }

            if ($existingSesi && $existingSesi->foto_selesai) {
                return back()->with('warning', 'Anda sudah menyelesaikan presensi masuk dan pulang hari ini.');
            }

            $file = $request->file('foto');
            $filename = 'masuk_'.time().'_'.$file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

            PresensiKaryawan::create([
                'user_id' => $user->id,
                'tgl_presensi' => $today,
                'jam_mulai' => $waktuServer,
                'foto_mulai' => $path,
                'lokasi_mulai' => $validated['lokasi'] ?? null,
                'status' => 'hadir',
            ]);

            // Web Push Notification Konfirmasi ke HP Magang
            $this->webPushService->sendToUser($user, [
                'title' => '📸 Presensi Masuk Berhasil',
                'body' => 'Presensi masuk magang/PKL berhasil dicatat pada pukul '.$waktuServer.'. Selamat beraktivitas!',
                'url' => route('magang.presensi'),
            ]);

            return redirect()
                ->route('magang.dashboard')
                ->with('success', 'Presensi masuk berhasil dicatat pada pukul '.$waktuServer.'.');
        }

        // ─────────────────────────────────────────────
        //  MODE: SELESAI (Absen Pulang)
        // ─────────────────────────────────────────────
        if (! $existingSesi || ! $existingSesi->foto_mulai) {
            return back()->with('warning', 'Presensi pulang harus setelah melakukan presensi masuk.');
        }

        if ($existingSesi->foto_selesai) {
            return back()->with('warning', 'Presensi pulang hari ini sudah tercatat.');
        }

        // Validasi jeda minimal 1 jam antara masuk dan pulang
        $jamMulai = Carbon::parse($today.' '.$existingSesi->jam_mulai, 'Asia/Jakarta');
        if ($jamMulai->greaterThan($now)) {
            $jamMulai->subDay();
        }
        $detikJalan = (int) $jamMulai->diffInSeconds($now, false);

        if ($detikJalan < 3600) {
            $sisaDetik = max(0, 3600 - $detikJalan);
            $sisaMenit = ceil($sisaDetik / 60);

            return back()->with('warning', "Tunggu {$sisaMenit} menit lagi. Presensi pulang harus berjarak minimal 1 jam setelah presensi masuk.");
        }

        $file = $request->file('foto');
        $filename = 'keluar_'.time().'_'.$file->getClientOriginalName();
        $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

        $existingSesi->update([
            'jam_selesai' => $waktuServer,
            'foto_selesai' => $path,
            'lokasi_selesai' => $validated['lokasi'] ?? null,
        ]);

        // Web Push Notification Konfirmasi Pulang
        $this->webPushService->sendToUser($user, [
            'title' => '📸 Presensi Pulang Berhasil',
            'body' => 'Presensi pulang magang/PKL berhasil dicatat pada pukul '.$waktuServer.'. Terima kasih atas kontribusi Anda hari ini!',
            'url' => route('magang.riwayat'),
        ]);

        return redirect()
            ->route('magang.dashboard')
            ->with('success', 'Presensi pulang berhasil dicatat pada pukul '.$waktuServer.'.');
    }
}

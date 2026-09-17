<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\LokasiPresensi;
use App\Models\PresensiMandiriSiswa;
use App\Services\GeofencingService;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $activeSesi = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_masuk')
            ->whereNull('foto_pulang')
            ->first();

        $todayPresensi = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();
        $kantorLat = (float) config('lokasi.sekolah_lat', -7.8011945);
        $kantorLng = (float) config('lokasi.sekolah_lng', 110.364917);
        $radius = (int) config('lokasi.radius_meter', 100);

        return view('siswa.presensi_foto', [
            'user' => $user,
            'siswa' => $siswa,
            'today' => $today,
            'activeSesi' => $activeSesi,
            'globalActiveSesi' => $activeSesi,
            'todayPresensi' => $todayPresensi,
            'lokasiPresensis' => $lokasiPresensis,
            'kantorLat' => $kantorLat,
            'kantorLng' => $kantorLng,
            'radius' => $radius,
        ]);
    }

    /**
     * Simpan Presensi Masuk (Clock-In) atau Pulang (Clock-Out) Siswa.
     */
    public function store(Request $request, GeofencingService $geofencingService): RedirectResponse
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung.');
        }

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['mulai', 'selesai'])],
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

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $waktuServer = $now->format('H:i:s');
        $dir = 'uploads/presensi_siswa/'.$siswa->id.'/'.$today;

        $existingSesi = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->orderByDesc('id')
            ->first();

        // ─────────────────────────────────────────────
        //  MODE: MULAI (Absen Masuk Siswa)
        // ─────────────────────────────────────────────
        if ($validated['mode'] === 'mulai') {
            if ($existingSesi && ! $existingSesi->foto_pulang) {
                return back()->with('warning', 'Presensi masuk hari ini masih berjalan. Silahkan lakukan presensi pulang.');
            }

            if ($existingSesi && $existingSesi->foto_pulang) {
                return back()->with('warning', 'Anda sudah menyelesaikan presensi masuk dan pulang hari ini.');
            }

            // Validasi Geofencing berdasarkan titik lokasi yang dipilih
            $lokasiPresensiId = isset($validated['lokasi_presensi_id']) ? (int) $validated['lokasi_presensi_id'] : null;
            $geofenceCheck = $geofencingService->checkSelectedLokasiRadius($validated['lokasi'] ?? null, $lokasiPresensiId);
            if (! $geofenceCheck['is_valid']) {
                return back()->with('warning', $geofenceCheck['message']);
            }

            $file = $request->file('foto');
            $filename = 'masuk_'.time().'_'.$file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

            PresensiMandiriSiswa::create([
                'siswa_id' => $siswa->id,
                'lokasi_presensi_id' => $lokasiPresensiId,
                'tgl_presensi' => $today,
                'jam_masuk' => $waktuServer,
                'foto_masuk' => $path,
                'lokasi_masuk' => $validated['lokasi'] ?? null,
                'lokasi_akurasi' => $accuracy,
                'is_mocked' => $isMocked,
                'status' => 'hadir',
            ]);

            // Web Push Notification Konfirmasi ke HP Siswa jika ada subscription
            $this->webPushService->sendToUser($user, [
                'title' => '📸 Presensi Masuk Berhasil',
                'body' => 'Presensi masuk siswa berhasil dicatat pada pukul '.$waktuServer.'. Selamat belajar!',
                'url' => route('siswa.presensi'),
            ]);

            return redirect()
                ->route('siswa.dashboard')
                ->with('success', 'Presensi masuk berhasil dicatat pada pukul '.$waktuServer.'. Selamat belajar!');
        }

        // ─────────────────────────────────────────────
        //  MODE: SELESAI (Absen Pulang Siswa)
        // ─────────────────────────────────────────────
        if (! $existingSesi || ! $existingSesi->foto_masuk) {
            return back()->with('warning', 'Presensi pulang harus setelah melakukan presensi masuk.');
        }

        if ($existingSesi->foto_pulang) {
            return back()->with('warning', 'Presensi pulang hari ini sudah tercatat.');
        }

        // Validasi jeda minimal 15 menit antara masuk dan pulang untuk siswa
        $jamMasuk = Carbon::parse($today.' '.$existingSesi->jam_masuk, 'Asia/Jakarta');
        if ($jamMasuk->greaterThan($now)) {
            $jamMasuk->subDay();
        }
        $detikJalan = (int) $jamMasuk->diffInSeconds($now, false);

        if ($detikJalan < 900) { // 15 menit untuk siswa PKBM
            $sisaDetik = max(0, 900 - $detikJalan);
            $sisaMenit = ceil($sisaDetik / 60);

            return back()->with('warning', "Tunggu {$sisaMenit} menit lagi sebelum melakukan presensi pulang.");
        }

        $file = $request->file('foto');
        $filename = 'pulang_'.time().'_'.$file->getClientOriginalName();
        $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

        $existingSesi->update([
            'jam_pulang' => $waktuServer,
            'foto_pulang' => $path,
            'lokasi_pulang' => $validated['lokasi'] ?? null,
        ]);

        $this->webPushService->sendToUser($user, [
            'title' => '📸 Presensi Pulang Berhasil',
            'body' => 'Presensi pulang berhasil dicatat pada pukul '.$waktuServer.'. Terima kasih dan hati-hati di jalan!',
            'url' => route('siswa.riwayat'),
        ]);

        return redirect()
            ->route('siswa.dashboard')
            ->with('success', 'Presensi pulang berhasil dicatat pada pukul '.$waktuServer.'.');
    }
}

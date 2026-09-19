<?php

namespace App\Http\Controllers;

use App\Models\LokasiPresensi;
use App\Models\PresensiKaryawan;
use App\Services\GeofencingService;
use App\Services\ShiftPresensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KaryawanPresensiController extends Controller
{
    public function index(ShiftPresensiService $shiftService, GeofencingService $geofencingService)
    {
        $user = auth()->user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $activeSesi = PresensiKaryawan::with('lokasiPresensi')
            ->where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_mulai')
            ->whereNull('foto_selesai')
            ->first();

        $completedSessions = PresensiKaryawan::with('lokasiPresensi')
            ->where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_mulai')
            ->whereNotNull('foto_selesai')
            ->get();

        $lokasiPresensis = LokasiPresensi::active()->orderBy('nama_lokasi')->get();
        $shiftEval = $shiftService->evaluateCheckIn();
        $isBypassRadius = $geofencingService->isExemptFromRadius($user);
        $isBypassWaktuTunggu = in_array($user->role, ['admin', 'kepala_sekolah'], true);

        return view('karyawan.presensi_foto', [
            'user' => $user,
            'today' => $today,
            'activeSesi' => $activeSesi,
            'completedSessions' => $completedSessions,
            'lokasiPresensis' => $lokasiPresensis,
            'shiftEval' => $shiftEval,
            'isBypassRadius' => $isBypassRadius,
            'isBypassWaktuTunggu' => $isBypassWaktuTunggu,
        ]);
    }

    public function store(Request $request, GeofencingService $geofencingService, ShiftPresensiService $shiftService)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['mulai', 'selesai'])],
            'lokasi_presensi_id' => ['nullable', 'exists:lokasi_presensis,id'],
            'foto' => ['required', 'image', 'max:5120'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'lokasi_akurasi' => ['nullable', 'numeric'],
            'is_mock_location' => ['nullable', 'boolean'],
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $waktuServer = $now->format('H:i:s');
        $dir = 'presensi_karyawan/'.$user->id.'/'.$today;

        $activeSesi = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_mulai')
            ->whereNull('foto_selesai')
            ->orderByDesc('id')
            ->first();

        if ($validated['mode'] === 'mulai') {
            if ($activeSesi) {
                return back()->with('warning', 'Presensi masuk masih berjalan. Silahkan absen pulang dulu.');
            }

            $isBypassRadius = $geofencingService->isExemptFromRadius($user);

            // Validasi Geofencing berdasarkan titik lokasi yang dipilih (Admin & Kepsek bebas radius untuk rapat/keperluan dinas)
            $lokasiPresensiId = isset($validated['lokasi_presensi_id']) ? (int) $validated['lokasi_presensi_id'] : null;
            $geofenceCheck = $geofencingService->checkSelectedLokasiRadius(
                $validated['lokasi'] ?? null,
                $lokasiPresensiId,
                null,
                null,
                null,
                $isBypassRadius
            );

            if (! $geofenceCheck['is_valid']) {
                return back()->with('warning', $geofenceCheck['message']);
            }

            $shiftEval = $shiftService->evaluateCheckIn($now);

            $file = $request->file('foto');
            $filename = 'masuk_'.time().'_'.$file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

            PresensiKaryawan::create([
                'user_id' => $user->id,
                'lokasi_presensi_id' => $lokasiPresensiId,
                'tgl_presensi' => $today,
                'jam_mulai' => $waktuServer,
                'foto_mulai' => $path,
                'lokasi_mulai' => $validated['lokasi'] ?? null,
                'status' => 'hadir',
                'shift_nama' => $shiftEval['shift_nama'],
                'status_kehadiran' => $shiftEval['status_kehadiran'],
                'menit_keterlambatan' => $shiftEval['menit_keterlambatan'],
            ]);

            $catatanBypass = ($isBypassRadius && ($geofenceCheck['distance'] > $geofenceCheck['max_radius'] || empty($validated['lokasi'])))
                ? ' (Mode Bebas Radius / Rapat Dinas Luar)'
                : '';
            $successMsg = 'Presensi masuk berhasil disimpan'.$catatanBypass.'. '.$shiftEval['pesan'];

            return redirect()
                ->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'kepsek.dashboard')
                ->with('success', $successMsg);
        }

        // Mode selesai
        if (! $activeSesi) {
            return back()->with('warning', 'Tidak ada presensi masuk yang berjalan.');
        }

        $jamMulai = Carbon::parse($today.' '.$activeSesi->jam_mulai, 'Asia/Jakarta');
        if ($jamMulai->greaterThan($now)) {
            $jamMulai->subDay();
        }
        $detikJalan = (int) $jamMulai->diffInSeconds($now, false);

        $isBypassWaktuTunggu = in_array($user->role, ['admin', 'kepala_sekolah'], true);
        if (! $isBypassWaktuTunggu && $detikJalan < 3600) {
            $sisaDetik = max(0, 3600 - $detikJalan);
            $sisaMenit = (int) ceil($sisaDetik / 60);

            return back()->with('warning', "Tunggu {$sisaMenit} menit lagi. Presensi pulang harus berjarak minimal 1 jam setelah masuk.");
        }

        $file = $request->file('foto');
        $filename = 'keluar_'.time().'_'.$file->getClientOriginalName();
        $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

        $updateData = [
            'jam_selesai' => $waktuServer,
            'foto_selesai' => $path,
            'lokasi_selesai' => $validated['lokasi'] ?? null,
        ];
        if (! $activeSesi->lokasi_presensi_id && ! empty($validated['lokasi_presensi_id'])) {
            $updateData['lokasi_presensi_id'] = (int) $validated['lokasi_presensi_id'];
        }

        $activeSesi->update($updateData);

        return redirect()
            ->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'kepsek.dashboard')
            ->with('success', 'Presensi pulang berhasil disimpan.');
    }
}

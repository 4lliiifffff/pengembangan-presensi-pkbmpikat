<?php

namespace App\Http\Controllers;

use App\Models\PresensiKaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KaryawanPresensiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $activeSesi = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_mulai')
            ->whereNull('foto_selesai')
            ->first();

        $completedSessions = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->whereNotNull('foto_mulai')
            ->whereNotNull('foto_selesai')
            ->get();

        return view('karyawan.presensi_foto', [
            'user' => $user,
            'today' => $today,
            'activeSesi' => $activeSesi,
            'completedSessions' => $completedSessions,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['mulai', 'selesai'])],
            'foto' => ['required', 'image', 'max:5120'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $waktuServer = $now->format('H:i:s');
        $dir = 'uploads/presensi_karyawan/'.$user->id.'/'.$today;

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

            return redirect()
                ->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'kepsek.dashboard')
                ->with('success', 'Presensi masuk berhasil disimpan.');
        }

        // Mode selesai
        if (! $activeSesi) {
            return back()->with('warning', 'Tidak ada presensi masuk yang berjalan.');
        }

        $jamMulai = Carbon::parse($today.' '.$activeSesi->jam_mulai, 'Asia/Jakarta');
        $detikJalan = (int) $jamMulai->diffInSeconds($now, false);

        if ($detikJalan < 3600) {
            $sisaDetik = max(0, 3600 - $detikJalan);
            $sisaMenit = $sisaDetik / 60;
            $sisaLabel = number_format($sisaMenit, 2, ':', '');

            return back()->with('warning', "Tunggu {$sisaLabel} menit lagi. Presensi pulang harus berjarak minimal 1 jam setelah masuk.");
        }

        $file = $request->file('foto');
        $filename = 'keluar_'.time().'_'.$file->getClientOriginalName();
        $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

        $activeSesi->update([
            'jam_selesai' => $waktuServer,
            'foto_selesai' => $path,
            'lokasi_selesai' => $validated['lokasi'] ?? null,
        ]);

        return redirect()
            ->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'kepsek.dashboard')
            ->with('success', 'Presensi pulang berhasil disimpan.');
    }
}

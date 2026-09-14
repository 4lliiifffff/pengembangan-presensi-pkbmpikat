<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IzinController extends Controller
{
    /**
     * Tampilkan halaman Kelola Izin.
     */
    public function index(Request $request)
    {
        $tutors = Tutor::orderBy('nama_lengkap')->get();

        // Riwayat izin: semua presensi dengan status izin, terbaru di atas
        $riwayatIzin = Presensi::with(['tutor', 'siswa'])
            ->where('status', 'izin')
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.izin.index', compact('tutors', 'riwayatIzin'));
    }

    /**
     * AJAX: ambil daftar siswa yang pernah diajar oleh tutor tertentu.
     */
    public function getSiswaByTutor($tutorId)
    {
        // Ambil siswa_id unik dari riwayat presensi tutor ini
        $siswaIds = Presensi::where('tutor_id', $tutorId)
            ->distinct()
            ->pluck('siswa_id');

        $siswas = Siswa::whereIn('id', $siswaIds)
            ->orderBy('nama_siswa')
            ->get(['id', 'nama_siswa']);

        return response()->json($siswas);
    }

    /**
     * Beri izin: buat atau update record presensi untuk tutor + siswa[] + tanggal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|exists:tutors,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'tanggal' => 'required|date',
        ], [
            'tutor_id.required' => 'Pilih tutor terlebih dahulu.',
            'siswa_ids.required' => 'Pilih minimal 1 siswa.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);

        $tutorId = $request->tutor_id;
        $tanggal = Carbon::parse($request->tanggal)->toDateString();
        $count = 0;

        foreach ($request->siswa_ids as $siswaId) {
            // Cek apakah sudah ada record untuk tutor + siswa + tanggal ini
            $presensi = Presensi::where('tutor_id', $tutorId)
                ->where('siswa_id', $siswaId)
                ->whereDate('tgl_presensi', $tanggal)
                ->first();

            if ($presensi) {
                // Update status saja
                $presensi->update(['status' => 'izin']);
            } else {
                // Buat record baru
                Presensi::create([
                    'tutor_id' => $tutorId,
                    'siswa_id' => $siswaId,
                    'tgl_presensi' => $tanggal,
                    'jam_mulai' => null,
                    'jam_selesai' => null,
                    'foto_mulai' => null,
                    'foto_selesai' => null,
                    'lokasi_mulai' => null,
                    'lokasi_selesai' => null,
                    'status' => 'izin',
                ]);
            }
            $count++;
        }

        return redirect()->route('admin.izin.index')
            ->with('success', "Berhasil memberikan Izin untuk {$count} siswa pada tanggal {$tanggal}.");
    }

    /**
     * Batalkan izin: hapus record presensi yang statusnya izin.
     */
    public function destroy($id)
    {
        $presensi = Presensi::where('status', 'izin')->findOrFail($id);
        $presensi->delete();

        return redirect()->route('admin.izin.index')
            ->with('success', 'Izin berhasil dibatalkan.');
    }
}

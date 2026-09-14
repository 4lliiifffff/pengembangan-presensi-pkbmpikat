<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\PresensiService;
use App\Services\TutorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IzinController extends Controller
{
    public function __construct(
        protected TutorService $tutorService,
        protected PresensiService $presensiService
    ) {}

    /**
     * Tampilkan halaman Kelola Izin.
     */
    public function index(Request $request)
    {
        $tutors = $this->tutorService->getAssignableTutors();

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
        $siswaIds = Presensi::where('tutor_id', $tutorId)
            ->distinct()
            ->pluck('siswa_id');

        $siswas = Siswa::whereIn('id', $siswaIds)
            ->orderBy('nama_siswa')
            ->get(['id', 'nama_siswa']);

        return response()->json($siswas);
    }

    /**
     * Beri izin: buat atau update record presensi untuk tutor + siswa[] + tanggal via PresensiService.
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

        $tutorId = (int) $request->tutor_id;
        $tanggal = Carbon::parse($request->tanggal)->toDateString();

        $count = $this->presensiService->syncApprovedAttendance(
            tutorId: $tutorId,
            siswaIds: $request->siswa_ids,
            startDateStr: $tanggal,
            status: 'izin'
        );

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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Murid;
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

        $riwayatIzin = Presensi::with(['tutor', 'murid'])
            ->where('status', 'izin')
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.izin.index', compact('tutors', 'riwayatIzin'));
    }

    /**
     * AJAX: ambil daftar murid yang pernah diajar oleh tutor tertentu.
     */
    public function getMuridByTutor($tutorId)
    {
        $muridIds = Presensi::where('tutor_id', $tutorId)
            ->distinct()
            ->pluck('murid_id');

        if ($muridIds->filter()->isEmpty()) {
            $muridIds = Presensi::where('tutor_id', $tutorId)
                ->distinct()
                ->pluck('siswa_id');
        }

        $murids = Murid::whereIn('id', $muridIds)
            ->orderBy('nama_murid')
            ->get(['id', 'nama_murid']);

        return response()->json($murids);
    }

    public function getSiswaByTutor($tutorId)
    {
        return $this->getMuridByTutor($tutorId);
    }

    /**
     * Beri izin: buat atau update record presensi untuk tutor + siswa[] + tanggal via PresensiService.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|exists:tutor,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:murid,id',
            'tanggal' => 'required|date',
        ], [
            'tutor_id.required' => 'Pilih tutor terlebih dahulu.',
            'siswa_ids.required' => 'Pilih minimal 1 murid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);

        $tutorId = (int) $request->tutor_id;
        $tanggal = Carbon::parse($request->tanggal)->toDateString();

        $count = $this->presensiService->syncApprovedAttendance(
            tutorId: $tutorId,
            muridIds: $request->siswa_ids,
            startDateStr: $tanggal,
            status: 'izin'
        );

        return redirect()->route('admin.izin.index')
            ->with('success', "Berhasil memberikan Izin untuk {$count} murid pada tanggal {$tanggal}.");
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

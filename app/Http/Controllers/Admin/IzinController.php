<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalSesi;
use App\Models\Murid;
use App\Models\PengajuanIzinSakit;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\PresensiService;
use App\Services\TutorService;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    public function __construct(
        protected TutorService $tutorService,
        protected PresensiService $presensiService,
        protected WebPushService $webPushService
    ) {}

    /**
     * Tampilkan halaman Kelola Izin.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'pengajuan');

        // Query Pengajuan Izin & Sakit Tutor
        $pengajuanQuery = PengajuanIzinSakit::with(['tutor.user', 'verifikator'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $pengajuanQuery->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $pengajuanQuery->where('jenis', $request->jenis);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $pengajuanQuery->whereHas('tutor', fn ($t) => $t->where('nama_lengkap', 'like', "%{$cari}%"));
        }

        $totalPengajuan = (clone $pengajuanQuery)->count();
        $totalPending = PengajuanIzinSakit::where('status', 'pending')->count();
        $totalDisetujui = PengajuanIzinSakit::where('status', 'disetujui')->count();
        $totalDitolak = PengajuanIzinSakit::where('status', 'ditolak')->count();
        $pengajuanItems = $pengajuanQuery->paginate(15)->withQueryString();

        // Data untuk Input Izin Manual
        $tutors = $this->tutorService->getAssignableTutors();

        $riwayatIzin = Presensi::with(['tutor', 'murid'])
            ->where('status', 'izin')
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.izin.index', compact(
            'activeTab',
            'pengajuanItems',
            'totalPengajuan',
            'totalPending',
            'totalDisetujui',
            'totalDitolak',
            'tutors',
            'riwayatIzin'
        ));
    }

    /**
     * AJAX: ambil daftar murid yang pernah diajar oleh tutor tertentu.
     */
    public function getMuridByTutor($tutorId)
    {
        $muridIds = Presensi::where('tutor_id', $tutorId)
            ->distinct()
            ->pluck('murid_id')
            ->filter();

        if ($muridIds->isEmpty()) {
            $muridIds = Murid::where('tutor_id', $tutorId)
                ->pluck('id')
                ->filter();
        }

        if ($muridIds->isEmpty()) {
            $muridIds = JadwalSesi::where('tutor_id', $tutorId)
                ->distinct()
                ->pluck('murid_id')
                ->filter();
        }

        // Jika tutor belum memiliki riwayat presensi atau murid spesifik,
        // fallback ke murid berstatus aktif agar admin tetap bisa mencatatkan izin
        $query = Murid::query();
        if ($muridIds->isNotEmpty()) {
            $query->whereIn('id', $muridIds);
        } else {
            $query->where('status_murid', 'aktif');
        }

        $murids = $query->orderBy('nama_murid')
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

    /**
     * Setujui permohonan izin/sakit yang diajukan oleh tutor.
     */
    public function setujuiPengajuanIzin(Request $request, int $id)
    {
        $item = PengajuanIzinSakit::findOrFail($id);
        $item->status = 'disetujui';
        $item->disetujui_oleh = Auth::id();
        $item->catatan_verifikasi = $request->input('catatan_verifikasi', 'Pengajuan disetujui oleh Administrator');
        $item->save();

        $this->presensiService->syncApprovedAttendance(
            tutorId: $item->tutor_id,
            muridIds: [],
            startDateStr: $item->tgl_mulai,
            endDateStr: $item->tgl_selesai,
            status: $item->jenis
        );

        // Kirim Web Push Notification ke Tutor
        $this->webPushService->sendToUser($item->tutor_id, [
            'title' => '✅ Pengajuan '.ucfirst($item->jenis).' Disetujui',
            'body' => 'Pengajuan '.$item->jenis.' Anda untuk tanggal '.$item->tgl_mulai.' telah disetujui oleh Administrator.',
            'url' => route('tutor.pengajuan-izin'),
        ]);

        return back()->with('success', 'Pengajuan '.ucfirst($item->jenis).' berhasil disetujui & presensi disinkronkan.');
    }

    /**
     * Tolak permohonan izin/sakit yang diajukan oleh tutor.
     */
    public function tolakPengajuanIzin(Request $request, int $id)
    {
        $item = PengajuanIzinSakit::findOrFail($id);
        $item->status = 'ditolak';
        $item->disetujui_oleh = Auth::id();
        $item->catatan_verifikasi = $request->input('catatan_verifikasi', 'Pengajuan ditolak oleh Administrator');
        $item->save();

        // Kirim Web Push Notification ke Tutor
        $this->webPushService->sendToUser($item->tutor_id, [
            'title' => '❌ Pengajuan '.ucfirst($item->jenis).' Ditolak',
            'body' => 'Pengajuan '.$item->jenis.' Anda telah ditolak. Catatan: '.$item->catatan_verifikasi,
            'url' => route('tutor.pengajuan-izin'),
        ]);

        return back()->with('warning', 'Pengajuan '.ucfirst($item->jenis).' ditolak.');
    }

    /**
     * Hapus arsip permohonan izin/sakit tutor.
     */
    public function destroyPengajuanIzin(int $id)
    {
        PengajuanIzinSakit::findOrFail($id)->delete();

        return back()->with('success', 'Data pengajuan izin berhasil dihapus.');
    }
}

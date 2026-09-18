<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang;
use App\Models\PresensiKaryawan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MagangController extends Controller
{
    /**
     * Tampilkan daftar seluruh peserta magang/PKL.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = User::where('role', 'magang')->with('magang');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('magang', function ($mq) use ($search) {
                        $mq->where('asal_instansi', 'like', "%{$search}%")
                            ->orWhere('jurusan', 'like', "%{$search}%")
                            ->orWhere('nim_nisn', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (int) $status);
        }

        $magangs = $query->latest()->paginate(15)->withQueryString();

        $total = User::where('role', 'magang')->count();
        $aktif = User::where('role', 'magang')->where('is_active', 1)->count();
        $nonaktif = User::where('role', 'magang')->where('is_active', 0)->count();

        return view('admin.magang.index', compact('magangs', 'total', 'aktif', 'nonaktif', 'search', 'status'));
    }

    /**
     * Form tambah peserta magang.
     */
    public function create()
    {
        return view('admin.magang.create');
    }

    /**
     * Simpan data peserta magang dan buatkan akun login.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|max:50|unique:users,nik',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'asal_instansi' => 'required|string|max:255',
            'nim_nisn' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:255',
            'pembimbing_lapangan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:25',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = 'foto_magang_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $fotoPath = Storage::disk('public')->putFileAs('foto_karyawan', $file, $filename);
            }

            $user = User::create([
                'nama_lengkap' => $request->nama_lengkap,
                'name' => $request->nama_lengkap,
                'nik' => $request->nik,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'magang',
                'is_active' => 1,
                'no_hp' => $request->no_hp,
                'foto' => $fotoPath,
            ]);

            Magang::create([
                'user_id' => $user->id,
                'asal_instansi' => $request->asal_instansi,
                'nim_nisn' => $request->nim_nisn ?: $request->nik,
                'jurusan' => $request->jurusan,
                'pembimbing_lapangan' => $request->pembimbing_lapangan,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'status' => 'aktif',
            ]);

            DB::commit();

            return redirect()->route('admin.magang.index')->with('success', 'Peserta magang berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('warning', 'Gagal menambahkan peserta magang: '.$e->getMessage());
        }
    }

    /**
     * Form edit peserta magang.
     */
    public function edit(User $magang)
    {
        $magang->load('magang');

        return view('admin.magang.edit', compact('magang'));
    }

    /**
     * Perbarui data peserta magang.
     */
    public function update(Request $request, User $magang)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|max:50|unique:users,nik,'.$magang->id,
            'email' => 'required|email|max:255|unique:users,email,'.$magang->id,
            'password' => 'nullable|string|min:6',
            'asal_instansi' => 'required|string|max:255',
            'nim_nisn' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:255',
            'pembimbing_lapangan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:25',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'is_active' => 'required|boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'nama_lengkap' => $request->nama_lengkap,
                'name' => $request->nama_lengkap,
                'nik' => $request->nik,
                'email' => $request->email,
                'is_active' => $request->is_active,
                'no_hp' => $request->no_hp,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = 'foto_magang_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $userData['foto'] = Storage::disk('public')->putFileAs('foto_karyawan', $file, $filename);
            }

            $magang->update($userData);

            $magang->magang()->updateOrCreate(
                ['user_id' => $magang->id],
                [
                    'asal_instansi' => $request->asal_instansi,
                    'nim_nisn' => $request->nim_nisn ?: $request->nik,
                    'jurusan' => $request->jurusan,
                    'pembimbing_lapangan' => $request->pembimbing_lapangan,
                    'tgl_mulai' => $request->tgl_mulai,
                    'tgl_selesai' => $request->tgl_selesai,
                    'status' => $request->is_active ? 'aktif' : 'selesai',
                ]
            );

            DB::commit();

            return redirect()->route('admin.magang.index')->with('success', 'Data peserta magang berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('warning', 'Gagal memperbarui data: '.$e->getMessage());
        }
    }

    /**
     * Hapus peserta magang.
     */
    public function destroy(User $magang)
    {
        try {
            DB::beginTransaction();
            $magang->magang()->delete();
            $magang->presensiKaryawans()->delete();
            $magang->delete();
            DB::commit();

            return redirect()->route('admin.magang.index')->with('success', 'Peserta magang dan riwayat presensinya berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('warning', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    /**
     * Monitoring & Laporan Presensi Magang.
     */
    public function presensi(Request $request)
    {
        $startDateStr = $request->get('start_date', Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now('Asia/Jakarta')->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $magangUserId = $request->get('user_id');
        $statusFilter = $request->get('status');

        $query = PresensiKaryawan::with(['user.magang', 'lokasiPresensi'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'magang');
            })
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($magangUserId) {
            $query->where('user_id', $magangUserId);
        }

        if ($statusFilter) {
            if ($statusFilter === 'hadir') {
                $query->whereNotNull('jam_selesai');
            } elseif ($statusFilter === 'proses') {
                $query->whereNotNull('jam_mulai')->whereNull('jam_selesai');
            }
        }

        $presensis = $query->orderByDesc('tgl_presensi')->orderByDesc('jam_mulai')->paginate(20)->withQueryString();

        $allMagangUsers = User::where('role', 'magang')->orderBy('nama_lengkap')->get();

        $totalPresensi = (clone $query)->count();
        $totalHadirLengkap = (clone $query)->whereNotNull('jam_selesai')->count();
        $totalSedangProses = (clone $query)->whereNotNull('jam_mulai')->whereNull('jam_selesai')->count();

        return view('admin.magang.presensi', compact(
            'presensis',
            'allMagangUsers',
            'startDateStr',
            'endDateStr',
            'magangUserId',
            'statusFilter',
            'totalPresensi',
            'totalHadirLengkap',
            'totalSedangProses'
        ));
    }

    /**
     * Export Rekap Presensi Magang ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $startDateStr = $request->get('start_date', Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDateStr = $request->get('end_date', Carbon::now('Asia/Jakarta')->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $magangUserId = $request->get('user_id');
        $selectedUser = $magangUserId ? User::with('magang')->find($magangUserId) : null;

        $query = PresensiKaryawan::with('user.magang')
            ->whereHas('user', function ($q) {
                $q->where('role', 'magang');
            })
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($magangUserId) {
            $query->where('user_id', $magangUserId);
        }

        $items = $query->orderBy('tgl_presensi')->orderBy('jam_mulai')->get();

        $pdf = Pdf::loadView('admin.magang.pdf_presensi', compact(
            'items',
            'startDateStr',
            'endDateStr',
            'selectedUser'
        ))->setPaper('a4', 'portrait');

        $filename = 'Laporan_Presensi_Magang_'.Carbon::now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }
}

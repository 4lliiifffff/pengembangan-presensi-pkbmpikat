<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\PresensiMandiriSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SiswaDashboardController extends Controller
{
    /**
     * Dashboard Siswa PKBM.
     */
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa?->load(['kelas', 'masterJenjang']);

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung dengan akun ini. Silakan hubungi admin.');
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Status presensi mandiri hari ini (Single Check-in)
        $todayPresensi = PresensiMandiriSiswa::with('lokasiPresensi')
            ->where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        $todayStatus = $todayPresensi ? 'selesai' : 'belum';
        $activeSesi = null;

        // Hitung statistik presensi mandiri bulan berjalan
        $currentMonth = Carbon::now('Asia/Jakarta')->month;
        $currentYear = Carbon::now('Asia/Jakarta')->year;

        $hadirBulanIni = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->where('status', 'hadir')
            ->count();

        // Total presensi dari tutorial/sesi kelas jika ada
        $hadirSesiKelas = Presensi::where('siswa_id', $siswa->id)
            ->where('status', 'hadir')
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->count();

        $totalHadirBulanIni = $hadirBulanIni + $hadirSesiKelas;

        // Riwayat 5 presensi mandiri terakhir
        $recentPresensi = PresensiMandiriSiswa::with('lokasiPresensi')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('siswa.dashboard', compact(
            'user',
            'siswa',
            'today',
            'todayPresensi',
            'todayStatus',
            'activeSesi',
            'hadirBulanIni',
            'hadirSesiKelas',
            'totalHadirBulanIni',
            'recentPresensi'
        ));
    }

    /**
     * Halaman Riwayat Presensi Lengkap Siswa.
     */
    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung.');
        }

        $selectedDate = $request->get('tanggal', Carbon::now('Asia/Jakarta')->format('Y-m'));
        $statusFilter = $request->get('status');

        $dt = Carbon::parse($selectedDate.'-01');
        $bulan = $dt->month;
        $tahun = $dt->year;

        $query = PresensiMandiriSiswa::with('lokasiPresensi')
            ->where('siswa_id', $siswa->id)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun);

        if ($statusFilter && in_array($statusFilter, ['hadir', 'izin', 'sakit', 'alpha'])) {
            $query->where('status', $statusFilter);
        }

        $items = $query->orderByDesc('tgl_presensi')->orderByDesc('id')->paginate(15)->withQueryString();

        $hadir = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
            ->where('status', 'hadir')
            ->count();

        return view('siswa.riwayat', compact(
            'user',
            'siswa',
            'selectedDate',
            'statusFilter',
            'hadir',
            'items'
        ));
    }

    /**
     * Halaman Profil & Informasi Siswa.
     */
    public function profil()
    {
        $user = Auth::user();
        $siswa = $user->siswa?->load(['kelas', 'masterJenjang']);

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung.');
        }

        $totalHadirMandiri = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->where('status', 'hadir')
            ->count();

        $totalHadirKelas = Presensi::where('siswa_id', $siswa->id)
            ->where('status', 'hadir')
            ->count();

        return view('siswa.profil', compact('user', 'siswa', 'totalHadirMandiri', 'totalHadirKelas'));
    }

    /**
     * Ganti Password Akun Siswa.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->old_password, $user->password)) {
            return back()->with('warning', 'Password lama tidak sesuai.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}

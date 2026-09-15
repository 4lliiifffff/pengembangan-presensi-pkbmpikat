<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Models\PresensiKaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MagangDashboardController extends Controller
{
    /**
     * Dashboard Mahasiswa Magang.
     */
    public function index()
    {
        $user = Auth::user();
        $magang = $user->magang;
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Status presensi hari ini
        $todayPresensi = PresensiKaryawan::where('user_id', $user->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        $todayStatus = 'belum';
        if ($todayPresensi) {
            if ($todayPresensi->foto_mulai && $todayPresensi->foto_selesai) {
                $todayStatus = 'selesai';
            } elseif ($todayPresensi->foto_mulai) {
                $todayStatus = 'proses';
            }
        }

        $activeSesi = ($todayStatus === 'proses') ? $todayPresensi : null;

        // Hitung statistik bulan berjalan
        $currentMonth = Carbon::now('Asia/Jakarta')->month;
        $currentYear = Carbon::now('Asia/Jakarta')->year;

        $hadirBulanIni = PresensiKaryawan::where('user_id', $user->id)
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->whereNotNull('foto_selesai')
            ->count();

        $totalHariKerja = 22; // Standar hari kerja bulanan
        $persentaseKehadiran = $totalHariKerja > 0 ? min(100, (int) round(($hadirBulanIni / $totalHariKerja) * 100)) : 0;

        // Riwayat 5 presensi terakhir
        $recentPresensi = PresensiKaryawan::where('user_id', $user->id)
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('magang.dashboard', compact(
            'user',
            'magang',
            'today',
            'todayPresensi',
            'todayStatus',
            'activeSesi',
            'hadirBulanIni',
            'totalHariKerja',
            'persentaseKehadiran',
            'recentPresensi'
        ));
    }

    /**
     * Halaman Riwayat Presensi Lengkap.
     */
    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $selectedDate = $request->get('tanggal', Carbon::now('Asia/Jakarta')->format('Y-m'));
        $statusFilter = $request->get('status');

        $dt = Carbon::parse($selectedDate.'-01');
        $bulan = $dt->month;
        $tahun = $dt->year;

        $query = PresensiKaryawan::where('user_id', $user->id)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun);

        if ($statusFilter === 'hadir') {
            $query->whereNotNull('foto_selesai');
        } elseif ($statusFilter === 'proses') {
            $query->whereNotNull('foto_mulai')->whereNull('foto_selesai');
        }

        $items = $query->orderByDesc('tgl_presensi')->orderByDesc('id')->paginate(15)->withQueryString();

        $hadir = PresensiKaryawan::where('user_id', $user->id)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
            ->whereNotNull('foto_selesai')
            ->count();

        $totalHariKerja = 22;
        $persentase = $totalHariKerja > 0 ? min(100, (int) round(($hadir / $totalHariKerja) * 100)) : 0;

        return view('magang.riwayat', compact(
            'user',
            'selectedDate',
            'statusFilter',
            'hadir',
            'persentase',
            'items'
        ));
    }

    /**
     * Halaman Profil & Pengaturan Akun Magang.
     */
    public function profil()
    {
        $user = Auth::user();
        $magang = $user->magang;

        $hadirCount = PresensiKaryawan::where('user_id', $user->id)
            ->whereNotNull('foto_selesai')
            ->count();

        return view('magang.profil', compact('user', 'magang', 'hadirCount'));
    }

    /**
     * Ganti Password.
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

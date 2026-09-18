<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalSesi;
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

        // Jadwal Sesi Belajar / Tutorial bersama Tutor Hari Ini
        $jadwalSesiHariIni = JadwalSesi::with(['tutor', 'kategoriTutorial'])
            ->where('siswa_id', $siswa->id)
            ->whereDate('tanggal_rencana', $today)
            ->orderBy('jam_masuk_rencana')
            ->get();

        // Agenda / Jadwal Kegiatan Umum PKBM Mendatang
        $agendaMendatang = Jadwal::whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->limit(3)
            ->get();

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

        // Hitung hari unik kehadiran (mencegah double-counting jika siswa absen mandiri dan tutor mengabsen di hari yang sama)
        $datesMandiri = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->where('status', 'hadir')
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $datesKelas = Presensi::where('siswa_id', $siswa->id)
            ->where('status', 'hadir')
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $totalHadirBulanIni = $datesMandiri->merge($datesKelas)->unique()->count();

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
            'jadwalSesiHariIni',
            'agendaMendatang',
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
     * Halaman Jadwal Sesi Belajar & Agenda Kegiatan Siswa.
     */
    public function jadwal(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa?->load(['kelas', 'masterJenjang']);

        if (! $siswa) {
            return redirect()->route('logout')->with('warning', 'Profil data siswa belum terhubung.');
        }

        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();
        $selectedDate = Carbon::parse($request->get('tanggal', $today))->startOfDay();

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $monthDays = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $monthDays->push($date->copy());
        }

        // 1. Jadwal Sesi Belajar / Tutorial Siswa pada tanggal terpilih
        $jadwalSesis = JadwalSesi::with(['tutor', 'kategoriTutorial', 'jadwalKerja'])
            ->where('siswa_id', $siswa->id)
            ->whereDate('tanggal_rencana', $selectedDate)
            ->orderBy('jam_masuk_rencana')
            ->get();

        // 2. Agenda Kegiatan Umum PKBM pada tanggal terpilih
        $agendas = Jadwal::whereDate('tanggal', $selectedDate)
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

        // 3. Hitung indikator event kalender per tanggal di bulan aktif (gabungan Sesi + Agenda PKBM)
        $sesiPerHari = JadwalSesi::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal_rencana', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(fn ($item) => is_object($item->tanggal_rencana) ? $item->tanggal_rencana->format('Y-m-d') : substr((string) $item->tanggal_rencana, 0, 10))
            ->map->count();

        $agendaPerHari = Jadwal::whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(fn ($item) => is_object($item->tanggal) ? $item->tanggal->format('Y-m-d') : substr((string) $item->tanggal, 0, 10))
            ->map->count();

        // Gabungkan keduanya
        $monthCounts = collect();
        foreach ($monthDays as $d) {
            $dStr = $d->format('Y-m-d');
            $count = ($sesiPerHari[$dStr] ?? 0) + ($agendaPerHari[$dStr] ?? 0);
            if ($count > 0) {
                $monthCounts[$dStr] = $count;
            }
        }

        // Status presensi mandiri hari ini (jika user melihat tanggal hari ini)
        $todayPresensi = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        return view('siswa.jadwal', compact(
            'user',
            'siswa',
            'today',
            'selectedDate',
            'monthDays',
            'monthCounts',
            'jadwalSesis',
            'agendas',
            'todayPresensi'
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

        $datesMandiri = PresensiMandiriSiswa::where('siswa_id', $siswa->id)
            ->where('status', 'hadir')
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $datesKelas = Presensi::where('siswa_id', $siswa->id)
            ->where('status', 'hadir')
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $totalHariHadir = $datesMandiri->merge($datesKelas)->unique()->count();

        return view('siswa.profil', compact('user', 'siswa', 'totalHadirMandiri', 'totalHadirKelas', 'totalHariHadir'));
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

<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalSesi;
use App\Models\Murid;
use App\Models\Presensi;
use App\Models\PresensiMandiriMurid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MuridDashboardController extends Controller
{
    /**
     * Dashboard Murid PKBM.
     */
    public function index()
    {
        $user = Auth::user();
        $murid = $user->murid?->load(['kelas', 'masterJenjang']);

        if (! $murid) {
            return redirect()->route('logout')->with('warning', 'Profil data murid belum terhubung dengan akun ini. Silakan hubungi admin.');
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Status presensi mandiri hari ini (Single Check-in)
        $todayPresensi = PresensiMandiriMurid::with('lokasiPresensi')
            ->where('murid_id', $murid->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        $todayStatus = $todayPresensi ? 'selesai' : 'belum';
        $activeSesi = null;

        // Jadwal Sesi Belajar / Tutorial bersama Tutor Hari Ini (Hanya sesi aktif, sesi dibatalkan di-exclude)
        $jadwalSesiHariIni = JadwalSesi::with(['tutor', 'kategoriTutorial', 'lokasiPresensi'])
            ->where('murid_id', $murid->id)
            ->whereDate('tanggal_rencana', $today)
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('jam_masuk_rencana')
            ->get();

        // Agenda / Jadwal Kegiatan Umum PKBM Mendatang
        $agendaMendatang = Jadwal::with('lokasiPresensi')
            ->whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->limit(3)
            ->get();

        // Hitung statistik presensi mandiri bulan berjalan
        $currentMonth = Carbon::now('Asia/Jakarta')->month;
        $currentYear = Carbon::now('Asia/Jakarta')->year;

        $hadirBulanIni = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->where('status', 'hadir')
            ->count();

        // Total presensi dari tutorial/sesi kelas jika ada
        $hadirSesiKelas = Presensi::where('murid_id', $murid->id)
            ->where('status', 'hadir')
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->count();

        // Hitung hari unik kehadiran (mencegah double-counting jika murid absen mandiri dan tutor mengabsen di hari yang sama)
        $datesMandiri = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->where('status', 'hadir')
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $datesKelas = Presensi::where('murid_id', $murid->id)
            ->where('status', 'hadir')
            ->whereMonth('tgl_presensi', $currentMonth)
            ->whereYear('tgl_presensi', $currentYear)
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $totalHadirBulanIni = $datesMandiri->merge($datesKelas)->unique()->count();

        // Riwayat 5 presensi mandiri terakhir
        $recentPresensi = PresensiMandiriMurid::with('lokasiPresensi')
            ->where('murid_id', $murid->id)
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('murid.dashboard', compact(
            'user',
            'murid',
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
     * Halaman Riwayat Presensi Lengkap Murid.
     */
    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $murid = $user->murid;

        if (! $murid) {
            return redirect()->route('logout')->with('warning', 'Profil data murid belum terhubung.');
        }

        $selectedDate = $request->get('tanggal', Carbon::now('Asia/Jakarta')->format('Y-m'));
        $statusFilter = $request->get('status');

        $dt = Carbon::parse($selectedDate.'-01');
        $bulan = $dt->month;
        $tahun = $dt->year;

        $query = PresensiMandiriMurid::with('lokasiPresensi')
            ->where('murid_id', $murid->id)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun);

        if ($statusFilter && in_array($statusFilter, ['hadir', 'izin', 'sakit', 'alpha'])) {
            $query->where('status', $statusFilter);
        }

        $items = $query->orderByDesc('tgl_presensi')->orderByDesc('id')->paginate(15)->withQueryString();

        $hadir = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
            ->where('status', 'hadir')
            ->count();

        return view('murid.riwayat', compact(
            'user',
            'murid',
            'selectedDate',
            'statusFilter',
            'hadir',
            'items'
        ));
    }

    /**
     * Halaman Jadwal Sesi Belajar & Agenda Kegiatan Murid.
     */
    public function jadwal(Request $request)
    {
        $user = Auth::user();
        $murid = $user->murid?->load(['kelas', 'masterJenjang']);

        if (! $murid) {
            return redirect()->route('logout')->with('warning', 'Profil data murid belum terhubung.');
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

        // 1. Jadwal Sesi Belajar / Tutorial Murid pada tanggal terpilih
        $allSesis = JadwalSesi::with(['tutor', 'kategoriTutorial', 'jadwalKerja', 'lokasiPresensi'])
            ->where('murid_id', $murid->id)
            ->whereDate('tanggal_rencana', $selectedDate)
            ->orderBy('jam_masuk_rencana')
            ->get();

        // Jika terdapat sesi aktif pada tanggal ini, singkirkan sesi lama yang berstatus dibatalkan agar jadwal lama tertimpa rapi
        $hasActiveSesi = $allSesis->contains(fn ($s) => $s->status !== 'dibatalkan');
        $jadwalSesis = $hasActiveSesi
            ? $allSesis->reject(fn ($s) => $s->status === 'dibatalkan' && (str_contains((string) $s->catatan, 'Direschedule ke') || $s->jenis_sesi === 'pengganti'))
            : $allSesis;

        // 2. Agenda Kegiatan Umum PKBM pada tanggal terpilih
        $agendas = Jadwal::with('lokasiPresensi')
            ->whereDate('tanggal', $selectedDate)
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

        // 3. Hitung indikator event kalender per tanggal di bulan aktif (hanya sesi aktif)
        $sesiPerHari = JadwalSesi::where('murid_id', $murid->id)
            ->where('status', '!=', 'dibatalkan')
            ->whereBetween('tanggal_rencana', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(fn ($item) => is_object($item->tanggal_rencana) ? $item->tanggal_rencana->format('Y-m-d') : substr((string) $item->tanggal_rencana, 0, 10))
            ->map->count();

        $agendaPerHari = Jadwal::whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(fn ($item) => is_object($item->tanggal) ? $item->tanggal->format('Y-m-d') : substr((string) $item->tanggal, 0, 10))
            ->map->count();

        $monthCounts = collect();
        foreach ($monthDays as $d) {
            $dStr = $d->format('Y-m-d');
            $count = ($sesiPerHari[$dStr] ?? 0) + ($agendaPerHari[$dStr] ?? 0);
            if ($count > 0) {
                $monthCounts[$dStr] = $count;
            }
        }

        // Status presensi mandiri hari ini
        $todayPresensi = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->whereDate('tgl_presensi', $today)
            ->first();

        return view('murid.jadwal', compact(
            'user',
            'murid',
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
     * Halaman Profil & Informasi Murid.
     */
    public function profil()
    {
        $user = Auth::user();
        $murid = $user->murid?->load(['kelas', 'masterJenjang']);

        if (! $murid) {
            return redirect()->route('logout')->with('warning', 'Profil data murid belum terhubung.');
        }

        $totalHadirMandiri = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->where('status', 'hadir')
            ->count();

        $totalHadirKelas = Presensi::where('murid_id', $murid->id)
            ->where('status', 'hadir')
            ->count();

        $datesMandiri = PresensiMandiriMurid::where('murid_id', $murid->id)
            ->where('status', 'hadir')
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $datesKelas = Presensi::where('murid_id', $murid->id)
            ->where('status', 'hadir')
            ->pluck('tgl_presensi')
            ->map(fn ($d) => is_object($d) ? $d->format('Y-m-d') : substr((string) $d, 0, 10));

        $totalHariHadir = $datesMandiri->merge($datesKelas)->unique()->count();

        return view('murid.profil', compact('user', 'murid', 'totalHadirMandiri', 'totalHadirKelas', 'totalHariHadir'));
    }

    /**
     * Ganti Password Akun Murid.
     */
    public function updatePassword(Request $request)
    {
        if (! $request->has('current_password') && $request->has('old_password')) {
            $request->merge(['current_password' => $request->old_password]);
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}

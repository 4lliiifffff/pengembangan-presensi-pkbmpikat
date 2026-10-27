<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\Concerns\ResolvesAdmin;
use App\Models\JadwalSesi;
use App\Models\PengajuanIzinSakit;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ResolvesAdmin;

    public function index()
    {
        $this->resolveAdmin();
        $user = auth()->user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Presensi personal admin hari ini
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

        // Pengajuan Menunggu Tindakan (Pending Approval)
        $pendingIzin = PengajuanIzinSakit::where('status', 'pending')->count();
        $pendingLupaLapor = PengajuanLupaLapor::where('status', 'pending')->count();
        $pendingTotal = $pendingIzin + $pendingLupaLapor;

        // Snapshot KBM Hari Ini
        $totalSesiHariIni = JadwalSesi::whereDate('tanggal_rencana', $today)->count();
        $sesiSelesaiHariIni = JadwalSesi::whereDate('tanggal_rencana', $today)
            ->where('status', 'selesai')
            ->count();

        // Hitung berdasarkan jam_selesai
        $counts = [
            'total_sesi' => $totalSesiHariIni,
            'hadir' => $this->countHadir($today),
            'izin' => Presensi::where('status', 'izin')
                ->whereDate('tgl_presensi', $today)
                ->count(),
            'pending_total' => $pendingTotal,
            'pending_izin' => $pendingIzin,
            'pending_lupa_lapor' => $pendingLupaLapor,
        ];

        // Statistik mingguan (ambil total "hadir" per hari dengan 1 single query agregasi).
        $weekStart = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $weeklyCounts = Presensi::whereBetween('tgl_presensi', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->whereNotNull('jam_selesai')
            ->selectRaw('DATE(tgl_presensi) as tanggal, count(*) as total')
            ->groupByRaw('DATE(tgl_presensi)')
            ->pluck('total', 'tanggal');

        $days = [];
        $max = 0;
        $dayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i)->toDateString();
            $count = (int) ($weeklyCounts[$date] ?? 0);

            $days[] = [
                'label' => $dayLabels[$i],
                'isoDay' => $date,
                'count' => $count,
            ];
            $max = max($max, $count);
        }

        // Data Baru
        $latest = Presensi::with(['siswa', 'tutor'])
            ->orderByDesc('tgl_presensi')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(function (Presensi $p) {

                if ($p->status === 'alpha') {
                    $p->status_label = 'ALPHA';
                    $p->status_class = 'alpha';
                } elseif ($p->foto_mulai && $p->foto_selesai) {
                    $p->status_label = 'SELESAI';
                    $p->status_class = 'hadir';
                } elseif ($p->foto_mulai) {
                    $p->status_label = 'SEDANG BERJALAN';
                    $p->status_class = 'pending';
                } elseif ($p->status === 'izin') {
                    $p->status_label = 'IZIN';
                    $p->status_class = 'izin';
                } else {
                    $p->status_label = 'BELUM ABSEN';
                    $p->status_class = 'pending';
                }

                return $p;
            });

        return view('admin.dashboard', [
            'today' => $today,
            'todayPresensi' => $todayPresensi,
            'todayStatus' => $todayStatus,
            'counts' => $counts,
            'weekly' => [
                'days' => $days,
                'max' => $max ?: 1,
            ],
            'latest' => $latest,
        ]);
    }

    // Hitung "hadir" berdasarkan jam_selesai (bukan status).
    private function countHadir(string $date): int
    {
        return Presensi::whereDate('tgl_presensi', $date)
            ->whereNotNull('jam_selesai')
            ->count();
    }
}

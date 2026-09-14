<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    /**
     * Dapatkan statistik tren kehadiran dan total jam mengajar bulanan selama N bulan terakhir.
     */
    public function getMonthlyAttendanceTrend(int $months = 6): array
    {
        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');
        $trend = [];

        $now = Carbon::now('Asia/Jakarta');

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth()->startOfDay();
            $endOfMonth = $date->copy()->endOfMonth()->endOfDay();

            $monthLabel = $date->translatedFormat('M Y');
            $shortLabel = $date->translatedFormat('M');

            $query = Presensi::whereBetween('tgl_presensi', [$startOfMonth, $endOfMonth]);

            $totalSesi = (clone $query)->count();
            $totalHadir = (clone $query)->whereNotNull('jam_selesai')->count();
            $totalIzinSakit = (clone $query)->whereIn('status', ['izin', 'sakit'])->count();

            $totalMenit = 0;
            if ($hasJamMulai) {
                $rows = (clone $query)
                    ->whereNotNull('jam_mulai')
                    ->whereNotNull('jam_selesai')
                    ->get(['jam_mulai', 'jam_selesai']);

                foreach ($rows as $row) {
                    try {
                        $m = Carbon::parse($row->jam_mulai);
                        $s = Carbon::parse($row->jam_selesai);
                        if ($s->gt($m)) {
                            $totalMenit += $m->diffInMinutes($s);
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            $totalJam = round($totalMenit / 60, 1);
            $pctHadir = $totalSesi > 0 ? round(($totalHadir / $totalSesi) * 100) : 0;

            $trend[] = [
                'month' => $monthLabel,
                'short_month' => $shortLabel,
                'year' => $date->year,
                'month_num' => $date->month,
                'total_sesi' => $totalSesi,
                'total_hadir' => $totalHadir,
                'total_izin_sakit' => $totalIzinSakit,
                'total_jam_mengajar' => $totalJam,
                'pct_hadir' => $pctHadir,
            ];
        }

        return [
            'months' => array_column($trend, 'short_month'),
            'labels' => array_column($trend, 'month'),
            'total_sesi' => array_column($trend, 'total_sesi'),
            'total_hadir' => array_column($trend, 'total_hadir'),
            'total_izin_sakit' => array_column($trend, 'total_izin_sakit'),
            'total_jam' => array_column($trend, 'total_jam_mengajar'),
            'pct_hadir' => array_column($trend, 'pct_hadir'),
            'raw' => $trend,
        ];
    }

    /**
     * Dapatkan pemeringkatan Indikator Kinerja Utama (KPI) Tutor per periode bulan/tahun.
     */
    public function getTutorKpiRanking(?int $bulan = null, ?int $tahun = null): Collection
    {
        $targetBulan = $bulan ?? Carbon::now('Asia/Jakarta')->month;
        $targetTahun = $tahun ?? Carbon::now('Asia/Jakarta')->year;

        $startDate = Carbon::createFromDate($targetTahun, $targetBulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');
        $tutors = Tutor::orderBy('nama_lengkap')->get();

        $ranked = $tutors->map(function (Tutor $tutor) use ($startDate, $endDate, $hasJamMulai) {
            $tutorPresensi = Presensi::where('tutor_id', $tutor->id)
                ->whereBetween('tgl_presensi', [$startDate, $endDate]);

            $totalSesi = (clone $tutorPresensi)->count();
            $totalHadir = (clone $tutorPresensi)->whereNotNull('jam_selesai')->count();
            $totalIzinSakit = (clone $tutorPresensi)->whereIn('status', ['izin', 'sakit'])->count();

            $totalMenit = 0;
            if ($hasJamMulai) {
                $rows = (clone $tutorPresensi)
                    ->whereNotNull('jam_mulai')
                    ->whereNotNull('jam_selesai')
                    ->get(['jam_mulai', 'jam_selesai']);

                foreach ($rows as $row) {
                    try {
                        $m = Carbon::parse($row->jam_mulai);
                        $s = Carbon::parse($row->jam_selesai);
                        if ($s->gt($m)) {
                            $totalMenit += $m->diffInMinutes($s);
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            $totalJam = round($totalMenit / 60, 1);
            $pctDisiplin = $totalSesi > 0 ? round(($totalHadir / $totalSesi) * 100) : 0;

            // Composite KPI score: 60% Kehadiran + 40% Volume Jam (Cap @ 40 Jam per bulan = 100%)
            $volumeScore = min(100, round(($totalJam / 40) * 100));
            $kpiScore = round(($pctDisiplin * 0.6) + ($volumeScore * 0.4), 1);

            $kategoriKinerja = match (true) {
                $kpiScore >= 85 => 'Sangat Baik',
                $kpiScore >= 70 => 'Baik',
                $kpiScore >= 50 => 'Cukup',
                default => 'Perlu Perhatian',
            };

            $badgeColor = match (true) {
                $kpiScore >= 85 => 'success',
                $kpiScore >= 70 => 'info',
                $kpiScore >= 50 => 'warning',
                default => 'danger',
            };

            return [
                'tutor' => $tutor,
                'total_sesi' => $totalSesi,
                'total_hadir' => $totalHadir,
                'total_izin_sakit' => $totalIzinSakit,
                'total_jam' => $totalJam,
                'pct_disiplin' => $pctDisiplin,
                'kpi_score' => $kpiScore,
                'kategori_kinerja' => $kategoriKinerja,
                'badge_color' => $badgeColor,
            ];
        });

        // Urutkan berdasarkan KPI score tertinggi
        $sorted = $ranked->sortByDesc('kpi_score')->values();

        // Tambahkan peringkat & rank badge
        return $sorted->map(function ($item, $index) {
            $rank = $index + 1;
            $rankBadge = match ($rank) {
                1 => '🥇 #1 Top Performer',
                2 => '🥈 #2 Runner Up',
                3 => '🥉 #3 Top 3',
                default => '#'.$rank,
            };

            $item['rank'] = $rank;
            $item['rank_badge'] = $rankBadge;

            return $item;
        });
    }
}

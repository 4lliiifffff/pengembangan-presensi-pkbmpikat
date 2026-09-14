<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class LaporanPresensiService
{
    /**
     * Dapatkan rekapitulasi presensi tutor per periode bulan dan tahun.
     */
    public function getRekapTutorBulanan(int $bulan, int $tahun, ?int $tutorId = null, ?int $siswaId = null): array
    {
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $hasJamMulai = Schema::hasColumn('presensis', 'jam_mulai');

        $baseQuery = Presensi::whereBetween('tgl_presensi', [$startDate, $endDate]);
        if ($tutorId) {
            $baseQuery->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $baseQuery->where('siswa_id', $siswaId);
        }

        $totalHadir = (clone $baseQuery)->whereNotNull('jam_selesai')->count();
        $totalSesi = (clone $baseQuery)->count();

        $hariKerja = 0;
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $hariKerja++;
            }
            $cursor->addDay();
        }

        $tutorsQuery = Tutor::orderBy('nama_lengkap');
        if ($tutorId) {
            $tutorsQuery->where('id', $tutorId);
        }
        $tutors = $tutorsQuery->get();

        $rekapTutor = $tutors->map(function (Tutor $tutor) use ($startDate, $endDate, $hariKerja, $hasJamMulai, $siswaId) {
            $tutorPresensi = Presensi::where('tutor_id', $tutor->id)
                ->whereBetween('tgl_presensi', [$startDate, $endDate]);

            if ($siswaId) {
                $tutorPresensi->where('siswa_id', $siswaId);
            }

            $totalSesiTutor = (clone $tutorPresensi)->count();
            $hadirCount = (clone $tutorPresensi)->whereNotNull('jam_selesai')->count();

            $jamMengajar = 0;
            if ($hasJamMulai) {
                $rows = (clone $tutorPresensi)
                    ->whereNotNull('jam_mulai')
                    ->whereNotNull('jam_selesai')
                    ->get(['jam_mulai', 'jam_selesai']);

                foreach ($rows as $row) {
                    try {
                        $mulai = Carbon::parse($row->jam_mulai);
                        $selesai = Carbon::parse($row->jam_selesai);
                        if ($selesai->gt($mulai)) {
                            $jamMengajar += $mulai->diffInMinutes($selesai);
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            $jamMengajarJam = round($jamMengajar / 60, 1);
            $pctHadir = $totalSesiTutor > 0 ? round($hadirCount / $totalSesiTutor * 100) : 0;

            return [
                'tutor' => $tutor,
                'total_sesi' => $totalSesiTutor,
                'hadir' => $hadirCount,
                'pct_hadir' => $pctHadir,
                'jam_mengajar' => $jamMengajarJam,
                'hari_kerja' => $hariKerja,
            ];
        })->filter(fn ($r) => $r['total_sesi'] > 0)
            ->sortByDesc('hadir')
            ->values();

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalHadir' => $totalHadir,
            'totalSesi' => $totalSesi,
            'totalTutorAktif' => $rekapTutor->count(),
            'rekapTutor' => $rekapTutor,
        ];
    }
}

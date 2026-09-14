<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Tutor;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Kalkulasi honorarium bulanan untuk seorang tutor spesifik berbasis tarif siswa.
     */
    public function calculateTutorPayroll(Tutor $tutor, int $bulan, int $tahun): array
    {
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        // Ambil presensi valid tutor ini (hadir + punya jam_selesai)
        $presensis = Presensi::with('siswa')
            ->where('tutor_id', $tutor->id)
            ->whereBetween('tgl_presensi', [$startDate, $endDate])
            ->where('status', 'hadir')
            ->whereNotNull('jam_selesai')
            ->orderBy('tgl_presensi')
            ->get();

        $siswaSummary = [];
        $totalJamMengajar = 0.0;
        $totalHonorarium = 0.0;
        $sessionRows = [];

        foreach ($presensis as $p) {
            $mulai = Carbon::parse($p->jam_mulai);
            $selesai = Carbon::parse($p->jam_selesai);

            $durasiMenit = max(0, $mulai->diffInMinutes($selesai));
            $durasiJam = round($durasiMenit / 60, 2);
            if ($durasiJam <= 0) {
                $durasiJam = 1.0;
            }

            $siswa = $p->siswa;
            $siswaId = $siswa ? $siswa->id : 0;
            $namaSiswa = $siswa ? $siswa->nama_siswa : 'Siswa Umum';
            $tarifPerJam = $siswa ? (float) ($siswa->tarif_per_jam ?? 50000) : 50000.0;

            $subtotalHonor = round($durasiJam * $tarifPerJam, 2);

            $totalJamMengajar += $durasiJam;
            $totalHonorarium += $subtotalHonor;

            if (! isset($siswaSummary[$siswaId])) {
                $siswaSummary[$siswaId] = [
                    'siswa_id' => $siswaId,
                    'nama_siswa' => $namaSiswa,
                    'tarif_per_jam' => $tarifPerJam,
                    'formatted_tarif' => 'Rp '.number_format($tarifPerJam, 0, ',', '.'),
                    'total_sesi' => 0,
                    'total_jam' => 0.0,
                    'subtotal_honor' => 0.0,
                    'formatted_subtotal' => 'Rp 0',
                ];
            }

            $siswaSummary[$siswaId]['total_sesi'] += 1;
            $siswaSummary[$siswaId]['total_jam'] += $durasiJam;
            $siswaSummary[$siswaId]['subtotal_honor'] += $subtotalHonor;
            $siswaSummary[$siswaId]['formatted_subtotal'] = 'Rp '.number_format($siswaSummary[$siswaId]['subtotal_honor'], 0, ',', '.');

            $sessionRows[] = [
                'id' => $p->id,
                'tgl_presensi' => $p->tgl_presensi,
                'jam_mulai' => $p->jam_mulai,
                'jam_selesai' => $p->jam_selesai,
                'durasi_jam' => $durasiJam,
                'nama_siswa' => $namaSiswa,
                'tarif_per_jam' => $tarifPerJam,
                'formatted_tarif' => 'Rp '.number_format($tarifPerJam, 0, ',', '.'),
                'subtotal' => $subtotalHonor,
                'formatted_subtotal' => 'Rp '.number_format($subtotalHonor, 0, ',', '.'),
                'moda_label' => $p->moda_label,
            ];
        }

        $totalIzinSakit = Presensi::where('tutor_id', $tutor->id)
            ->whereBetween('tgl_presensi', [$startDate, $endDate])
            ->whereIn('status', ['izin', 'sakit'])
            ->count();

        return [
            'tutor' => $tutor,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode_label' => $startDate->translatedFormat('F Y'),
            'total_sesi_hadir' => count($presensis),
            'total_izin_sakit' => $totalIzinSakit,
            'total_jam' => round($totalJamMengajar, 2),
            'total_honor' => round($totalHonorarium, 2),
            'formatted_total_honor' => 'Rp '.number_format($totalHonorarium, 0, ',', '.'),
            'siswa_summary' => array_values($siswaSummary),
            'session_rows' => $sessionRows,
        ];
    }

    /**
     * Rekapitulasi payroll global seluruh tutor per bulan.
     */
    public function generatePayrollSummary(int $bulan, int $tahun): array
    {
        $tutors = Tutor::orderBy('nama_lengkap')->get();

        $payrollList = [];
        $totalAnggaranGlobal = 0.0;
        $totalJamGlobal = 0.0;
        $totalSesiGlobal = 0;

        foreach ($tutors as $tutor) {
            $payroll = $this->calculateTutorPayroll($tutor, $bulan, $tahun);
            $payrollList[] = $payroll;

            $totalAnggaranGlobal += $payroll['total_honor'];
            $totalJamGlobal += $payroll['total_jam'];
            $totalSesiGlobal += $payroll['total_sesi_hadir'];
        }

        $startDate = Carbon::createFromDate($tahun, $bulan, 1);

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode_label' => $startDate->translatedFormat('F Y'),
            'total_tutor' => count($tutors),
            'total_sesi_global' => $totalSesiGlobal,
            'total_jam_global' => round($totalJamGlobal, 2),
            'total_anggaran_global' => round($totalAnggaranGlobal, 2),
            'formatted_total_anggaran' => 'Rp '.number_format($totalAnggaranGlobal, 0, ',', '.'),
            'payrolls' => $payrollList,
        ];
    }
}

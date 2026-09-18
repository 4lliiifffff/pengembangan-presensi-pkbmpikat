<?php

namespace App\Services;

use App\Models\KategoriTutorial;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Resolusi kategori tutorial dan nominal honor berdasarkan data sesi dan profil siswa.
     */
    public function resolveHonorSesi(string $moda, float $durasiJam, Siswa $siswa, bool $isGabungan = false): array
    {
        $jenisLayanan = ($moda === 'online') ? 'dl' : 'komunitas';
        $isAbk = (bool) ($siswa->is_abk ?? false);

        $kategori = KategoriTutorial::resolveKategori($jenisLayanan, $durasiJam, $isAbk, $isGabungan);

        if (! $kategori) {
            // Fallback cari kategori terdekat atau default
            $kategori = KategoriTutorial::active()
                ->where('jenis_layanan', $jenisLayanan)
                ->where('is_abk', $isAbk)
                ->orderBy('urutan')
                ->first();
        }

        $nominal = $kategori ? (float) $kategori->nominal_honor : ($isAbk ? 100000.0 : 75000.0);

        return [
            'kategori_tutorial_id' => $kategori?->id,
            'kategori' => $kategori,
            'nominal_honor' => $nominal,
        ];
    }

    /**
     * Hitung honor untuk satu sesi presensi spesifik (dengan snapshot immutability).
     */
    public function hitungHonorSesi(Presensi $presensi, float $durasiJam = 1.0): float
    {
        // 1. Prioritas utama: snapshot nominal saat presensi dibuat
        if ($presensi->nominal_honor_snapshot !== null) {
            return (float) $presensi->nominal_honor_snapshot;
        }

        // 2. Prioritas kedua: relasi master kategori tutorial
        if ($presensi->kategori_tutorial_id && $presensi->kategoriTutorial) {
            return (float) $presensi->kategoriTutorial->nominal_honor;
        }

        // 3. Fallback: Standar SK sesuai status ABK siswa
        return ($presensi->siswa?->is_abk) ? 100000.0 : 75000.0;
    }

    /**
     * Kalkulasi honorarium bulanan untuk seorang tutor spesifik berbasis tarif sesi SK.
     */
    public function calculateTutorPayroll(Tutor $tutor, int $bulan, int $tahun): array
    {
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        // Ambil presensi valid tutor ini (hadir + punya jam_selesai)
        $presensis = Presensi::with(['siswa', 'kategoriTutorial'])
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
            $isAbk = $siswa ? (bool) $siswa->is_abk : false;

            // Hitung honor sesi menggunakan engine baru (snapshot / flat per sesi)
            $subtotalHonor = $this->hitungHonorSesi($p, $durasiJam);

            $totalJamMengajar += $durasiJam;
            $totalHonorarium += $subtotalHonor;

            // Tentukan label kategori transparan untuk rincian slip honor
            $durasiSk = $p->kategoriTutorial ? (float) $p->kategoriTutorial->durasi_jam : null;
            $isDurasiSesuaiSk = $durasiSk !== null && abs($durasiSk - $durasiJam) < 0.1;

            if ($p->kategoriTutorial) {
                if ($isDurasiSesuaiSk) {
                    $kategoriNama = $p->kategoriTutorial->nama_kategori." ({$durasiSk} Jam)";
                } else {
                    $kategoriNama = $p->kategoriTutorial->nama_kategori." (Aktual {$durasiJam}j - Flat SK {$durasiSk}j)";
                }
            } else {
                $kategoriNama = "Tutorial Non-SK (Aktual {$durasiJam}j - Flat Default SK)";
            }

            if (! isset($siswaSummary[$siswaId])) {
                $siswaSummary[$siswaId] = [
                    'siswa_id' => $siswaId,
                    'nama_siswa' => $namaSiswa,
                    'is_abk' => $isAbk,
                    'is_abk_label' => $isAbk ? 'ABK' : 'Reguler',
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
                'durasi_pilihan' => $p->durasi_pilihan ?? $durasiJam,
                'durasi_sk' => $durasiSk,
                'is_sesuai_sk' => $isDurasiSesuaiSk,
                'nama_siswa' => $namaSiswa,
                'is_abk' => $isAbk,
                'kategori_nama' => $kategoriNama,
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

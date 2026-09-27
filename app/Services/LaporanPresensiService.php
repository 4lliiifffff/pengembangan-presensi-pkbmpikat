<?php

namespace App\Services;

use App\Models\JenjangPaket;
use App\Models\Magang;
use App\Models\PengajuanIzinSakit;
use App\Models\PengajuanLupaLapor;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\PresensiMandiriSiswa;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class LaporanPresensiService
{
    /**
     * Dapatkan data lengkap laporan presensi Tutor & KBM.
     */
    public function getLaporanTutorData(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($filters['end_date'] ?? Carbon::now('Asia/Jakarta')->endOfDay()->toDateString())->endOfDay();

        $tutorId = $filters['tutor_id'] ?? null;
        $siswaId = $filters['siswa_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;
        $modaFilter = $filters['moda_pembelajaran'] ?? null;
        $lokasiId = $filters['lokasi_presensi_id'] ?? null;
        $jenjangId = $filters['jenjang_paket_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        $query = Presensi::with(['tutor.user', 'siswa.relKelas.masterJenjang', 'lokasiPresensi', 'kategoriTutorial'])
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($tutorId) {
            $query->where('tutor_id', $tutorId);
        }
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }
        if ($modaFilter) {
            $query->where('moda_pembelajaran', $modaFilter);
        }
        if ($lokasiId) {
            $query->where('lokasi_presensi_id', $lokasiId);
        }
        if ($kelasId) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId));
        }
        if ($jenjangId) {
            $query->whereHas('siswa.relKelas', fn ($q) => $q->where('jenjang_paket_id', $jenjangId));
        }

        if ($statusFilter) {
            if ($statusFilter === 'hadir') {
                $query->where('status', 'hadir')->whereNotNull('foto_selesai');
            } elseif ($statusFilter === 'proses') {
                $query->whereNotNull('foto_mulai')->whereNull('foto_selesai');
            } elseif ($statusFilter === 'izin') {
                $query->whereIn('status', ['izin', 'sakit']);
            } elseif ($statusFilter === 'alpha') {
                $query->where('status', 'alpha');
            }
        }

        $allRecords = (clone $query)->orderByDesc('tgl_presensi')->orderByDesc('jam_mulai')->get();

        $totalSesi = $allRecords->count();
        $totalHadir = $allRecords->where('status', 'hadir')->whereNotNull('jam_selesai')->count();
        $totalProses = $allRecords->whereNotNull('jam_mulai')->whereNull('jam_selesai')->count();
        $totalIzin = $allRecords->whereIn('status', ['izin', 'sakit'])->count();
        $totalAlpha = $allRecords->where('status', 'alpha')->count();

        $totalJamKbmMenit = 0;
        $totalHonorEstimasi = 0;

        foreach ($allRecords as $p) {
            if ($p->jam_mulai && $p->jam_selesai) {
                try {
                    $m = Carbon::parse($p->jam_mulai);
                    $s = Carbon::parse($p->jam_selesai);
                    if ($s->gt($m)) {
                        $totalJamKbmMenit += $m->diffInMinutes($s);
                    }
                } catch (\Throwable) {
                }
            }

            if ($p->nominal_honor_snapshot) {
                $totalHonorEstimasi += (float) $p->nominal_honor_snapshot;
            }
        }

        $totalJamKbm = round($totalJamKbmMenit / 60, 1);

        return [
            'records' => $allRecords,
            'totalSesi' => $totalSesi,
            'totalHadir' => $totalHadir,
            'totalProses' => $totalProses,
            'totalIzin' => $totalIzin,
            'totalAlpha' => $totalAlpha,
            'totalJamKbm' => $totalJamKbm,
            'totalHonorEstimasi' => $totalHonorEstimasi,
        ];
    }

    /**
     * Dapatkan data lengkap laporan presensi mandiri Siswa.
     */
    public function getLaporanSiswaData(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($filters['end_date'] ?? Carbon::now('Asia/Jakarta')->endOfDay()->toDateString())->endOfDay();

        $siswaId = $filters['siswa_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;
        $jenjangId = $filters['jenjang_paket_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;
        $lokasiId = $filters['lokasi_presensi_id'] ?? null;

        $query = PresensiMandiriSiswa::with(['siswa.relKelas.masterJenjang', 'lokasiPresensi'])
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }
        if ($kelasId) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId));
        }
        if ($jenjangId) {
            $query->whereHas('siswa.relKelas', fn ($q) => $q->where('jenjang_paket_id', $jenjangId));
        }
        if ($lokasiId) {
            $query->where('lokasi_presensi_id', $lokasiId);
        }
        if ($statusFilter) {
            if ($statusFilter === 'hadir') {
                $query->where('status', 'hadir');
            } elseif ($statusFilter === 'terlambat') {
                $query->where('status_kehadiran', 'terlambat');
            } elseif (in_array($statusFilter, ['izin', 'sakit', 'alpha'])) {
                $query->where('status', $statusFilter);
            }
        }

        $allRecords = (clone $query)->orderByDesc('tgl_presensi')->orderByDesc('jam_masuk')->get();

        $totalPresensi = $allRecords->count();
        $totalHadir = $allRecords->where('status', 'hadir')->count();
        $totalTerlambat = $allRecords->where('status_kehadiran', 'terlambat')->count();
        $totalIzin = $allRecords->where('status', 'izin')->count();
        $totalSakit = $allRecords->where('status', 'sakit')->count();
        $totalAlpha = $allRecords->where('status', 'alpha')->count();

        return [
            'records' => $allRecords,
            'totalPresensi' => $totalPresensi,
            'totalHadir' => $totalHadir,
            'totalTerlambat' => $totalTerlambat,
            'totalIzin' => $totalIzin,
            'totalSakit' => $totalSakit,
            'totalAlpha' => $totalAlpha,
        ];
    }

    /**
     * Dapatkan data presensi Karyawan, Admin & Kepala Sekolah.
     */
    public function getLaporanPegawaiData(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($filters['end_date'] ?? Carbon::now('Asia/Jakarta')->endOfDay()->toDateString())->endOfDay();

        $userId = $filters['user_id'] ?? null;
        $roleFilter = $filters['role'] ?? null;
        $lokasiId = $filters['lokasi_presensi_id'] ?? null;

        $query = PresensiKaryawan::with(['user', 'lokasiPresensi'])
            ->whereHas('user', function ($q) use ($roleFilter) {
                if ($roleFilter) {
                    $q->where('role', $roleFilter);
                } else {
                    $q->whereIn('role', ['admin', 'kepala_sekolah', 'karyawan']);
                }
            })
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($lokasiId) {
            $query->where('lokasi_presensi_id', $lokasiId);
        }

        $allRecords = (clone $query)->orderByDesc('tgl_presensi')->orderByDesc('jam_mulai')->get();

        $totalPresensi = $allRecords->count();
        $totalHadirLengkap = $allRecords->whereNotNull('jam_selesai')->count();
        $totalSedangProses = $allRecords->whereNotNull('jam_mulai')->whereNull('jam_selesai')->count();

        return [
            'records' => $allRecords,
            'totalPresensi' => $totalPresensi,
            'totalHadir' => $totalHadirLengkap,
            'totalProses' => $totalSedangProses,
        ];
    }

    /**
     * Dapatkan data presensi Mahasiswa Magang / Siswa PKL.
     */
    public function getLaporanMagangData(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($filters['end_date'] ?? Carbon::now('Asia/Jakarta')->endOfDay()->toDateString())->endOfDay();

        $userId = $filters['user_id'] ?? null;
        $lokasiId = $filters['lokasi_presensi_id'] ?? null;

        $query = PresensiKaryawan::with(['user.magang', 'lokasiPresensi'])
            ->whereHas('user', fn ($q) => $q->where('role', 'magang'))
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($lokasiId) {
            $query->where('lokasi_presensi_id', $lokasiId);
        }

        $allRecords = (clone $query)->orderByDesc('tgl_presensi')->orderByDesc('jam_mulai')->get();

        $totalPresensi = $allRecords->count();
        $totalHadirLengkap = $allRecords->whereNotNull('jam_selesai')->count();
        $totalSedangProses = $allRecords->whereNotNull('jam_mulai')->whereNull('jam_selesai')->count();

        $totalMenitKerja = 0;
        foreach ($allRecords as $r) {
            if ($r->jam_mulai && $r->jam_selesai) {
                try {
                    $m = Carbon::parse($r->jam_mulai);
                    $s = Carbon::parse($r->jam_selesai);
                    if ($s->gt($m)) {
                        $totalMenitKerja += $m->diffInMinutes($s);
                    }
                } catch (\Throwable) {
                }
            }
        }

        return [
            'records' => $allRecords,
            'totalPresensi' => $totalPresensi,
            'totalHadir' => $totalHadirLengkap,
            'totalProses' => $totalSedangProses,
            'totalJamKerja' => round($totalMenitKerja / 60, 1),
        ];
    }

    /**
     * Dapatkan data rekapitulasi perizinan dan pengajuan lupa lapor.
     */
    public function getLaporanPerizinanData(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($filters['end_date'] ?? Carbon::now('Asia/Jakarta')->endOfDay()->toDateString())->endOfDay();

        $tutorId = $filters['tutor_id'] ?? null;
        $status = $filters['status'] ?? null;

        // 1. Pengajuan Izin & Sakit
        $izinQuery = PengajuanIzinSakit::with(['tutor.user', 'verifikator'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tgl_mulai', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('tgl_selesai', [$startDate->toDateString(), $endDate->toDateString()]);
            });

        if ($tutorId) {
            $izinQuery->where('tutor_id', $tutorId);
        }
        if ($status) {
            $izinQuery->where('status', $status);
        }

        $izinRecords = $izinQuery->orderByDesc('tgl_mulai')->get();

        // 2. Pengajuan Lupa Lapor
        $lupaQuery = PengajuanLupaLapor::with(['tutor.user', 'siswa'])
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($tutorId) {
            $lupaQuery->where('tutor_id', $tutorId);
        }
        if ($status) {
            $lupaQuery->where('status', $status);
        }

        $lupaRecords = $lupaQuery->orderByDesc('tanggal')->get();

        $totalIzinSakit = $izinRecords->count();
        $totalLupaLapor = $lupaRecords->count();

        $totalPending = $izinRecords->where('status', 'pending')->count() + $lupaRecords->where('status', 'pending')->count();
        $totalDisetujui = $izinRecords->where('status', 'disetujui')->count() + $lupaRecords->where('status', 'disetujui')->count();
        $totalDitolak = $izinRecords->where('status', 'ditolak')->count() + $lupaRecords->where('status', 'ditolak')->count();

        return [
            'izinRecords' => $izinRecords,
            'lupaRecords' => $lupaRecords,
            'totalIzinSakit' => $totalIzinSakit,
            'totalLupaLapor' => $totalLupaLapor,
            'totalPending' => $totalPending,
            'totalDisetujui' => $totalDisetujui,
            'totalDitolak' => $totalDitolak,
        ];
    }

    /**
     * Dapatkan data matriks presensi bulanan (Buku Induk Ledger Tanggal 1-31).
     */
    public function getMatriksBulananSiswa(int $bulan, int $tahun, ?int $kelasId = null, ?int $jenjangId = null): array
    {
        $start = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();
        $daysInMonth = $start->daysInMonth;

        $siswaQuery = Siswa::with(['relKelas.masterJenjang'])
            ->where('status_siswa', 'aktif')
            ->orderBy('nama_siswa');

        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }
        if ($jenjangId) {
            $siswaQuery->whereHas('relKelas', fn ($q) => $q->where('jenjang_paket_id', $jenjangId));
        }

        $siswas = $siswaQuery->get();

        // Ambil data presensi mandiri dalam bulan ini
        $presensis = PresensiMandiriSiswa::whereBetween('tgl_presensi', [$start->toDateString(), $end->toDateString()])
            ->get();

        // Ambil juga data presensi tutor (kegiatan KBM) sebagai fallback jika presensi mandiri belum ada
        $presensiTutor = Presensi::whereBetween('tgl_presensi', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'hadir')
            ->get();

        $matrix = [];

        foreach ($siswas as $siswa) {
            $rowDays = [];
            $hadirCount = 0;
            $izinCount = 0;
            $sakitCount = 0;
            $alphaCount = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
                $dayCarbon = Carbon::parse($dateStr);
                $isSunday = ($dayCarbon->dayOfWeek === Carbon::SUNDAY);

                // Cek presensi mandiri
                $pRecord = $presensis->first(fn ($p) => $p->siswa_id === $siswa->id && $p->tgl_presensi === $dateStr);

                $status = '-';
                if ($pRecord) {
                    if ($pRecord->status === 'hadir') {
                        $status = 'H';
                        $hadirCount++;
                    } elseif ($pRecord->status === 'izin') {
                        $status = 'I';
                        $izinCount++;
                    } elseif ($pRecord->status === 'sakit') {
                        $status = 'S';
                        $sakitCount++;
                    } elseif ($pRecord->status === 'alpha') {
                        $status = 'A';
                        $alphaCount++;
                    }
                } else {
                    // Cek presensi KBM dari tutor
                    $pTutorRecord = $presensiTutor->first(fn ($p) => $p->siswa_id === $siswa->id && $p->tgl_presensi === $dateStr);
                    if ($pTutorRecord) {
                        $status = 'H';
                        $hadirCount++;
                    } elseif ($isSunday) {
                        $status = 'L'; // Libur Minggu
                    }
                }

                $rowDays[$d] = [
                    'date' => $dateStr,
                    'dayName' => $dayCarbon->isoFormat('dd'),
                    'isSunday' => $isSunday,
                    'status' => $status,
                ];
            }

            $totalSesi = $hadirCount + $izinCount + $sakitCount + $alphaCount;
            $keaktifan = $totalSesi > 0 ? round(($hadirCount / $totalSesi) * 100) : 0;

            $matrix[] = [
                'siswa' => $siswa,
                'days' => $rowDays,
                'hadir' => $hadirCount,
                'izin' => $izinCount,
                'sakit' => $sakitCount,
                'alpha' => $alphaCount,
                'totalSesi' => $totalSesi,
                'keaktifan' => $keaktifan,
            ];
        }

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'daysInMonth' => $daysInMonth,
            'matrix' => $matrix,
            'totalSiswa' => $siswas->count(),
        ];
    }

    /**
     * Dapatkan visual analytics data untuk chart.js.
     */
    public function getVisualChartData(Carbon $startDate, Carbon $endDate): array
    {
        // 1. Tren Harian Presensi
        $dailyStats = Presensi::selectRaw('DATE(tgl_presensi) as date, 
                SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status IN ("izin", "sakit") THEN 1 ELSE 0 END) as izin')
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $dailyStats->map(fn ($d) => Carbon::parse($d->date)->format('d M'))->toArray();
        $dataHadir = $dailyStats->pluck('hadir')->toArray();
        $dataIzin = $dailyStats->pluck('izin')->toArray();

        // 2. Komposisi Moda Pembelajaran
        $modaStats = Presensi::selectRaw('moda_pembelajaran, COUNT(*) as count')
            ->whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('moda_pembelajaran')
            ->pluck('count', 'moda_pembelajaran')
            ->toArray();

        $modaLabels = ['Tatap Muka di Sekolah', 'Kunjungan Rumah (Home Visit)', 'Distance Learning / Online'];
        $modaData = [
            $modaStats['sekolah'] ?? 0,
            $modaStats['kunjungan_rumah'] ?? 0,
            $modaStats['online'] ?? 0,
        ];

        // 3. Distribusi Kehadiran per Jenjang Paket Siswa
        $jenjangs = JenjangPaket::aktif()->orderBy('urutan')->get();
        $jenjangLabels = [];
        $jenjangData = [];

        foreach ($jenjangs as $j) {
            $count = PresensiMandiriSiswa::whereBetween('tgl_presensi', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'hadir')
                ->whereHas('siswa.relKelas', fn ($q) => $q->where('jenjang_paket_id', $j->id))
                ->count();

            $jenjangLabels[] = $j->nama_jenjang;
            $jenjangData[] = $count;
        }

        return [
            'chartLabels' => $labels,
            'chartDataHadir' => $dataHadir,
            'chartDataIzin' => $dataIzin,
            'modaLabels' => $modaLabels,
            'modaData' => $modaData,
            'jenjangLabels' => $jenjangLabels,
            'jenjangData' => $jenjangData,
        ];
    }

    /**
     * Dapatkan rekapitulasi presensi tutor per periode bulan dan tahun (Legacy compatibility).
     */
    public function getRekapTutorBulanan(int $bulan, int $tahun, ?int $tutorId = null, ?int $siswaId = null): array
    {
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $hasJamMulai = Schema::hasColumn((new Presensi)->getTable(), 'jam_mulai');

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

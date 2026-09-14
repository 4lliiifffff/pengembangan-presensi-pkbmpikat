<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;

class PresensiService
{
    /**
     * Sinkronisasi data presensi terverifikasi hasil persetujuan (Lupa Lapor, Izin/Sakit, atau Admin).
     */
    public function syncApprovedAttendance(
        int $tutorId,
        int|array $siswaIds,
        string $startDateStr,
        ?string $endDateStr = null,
        string $status = 'hadir',
        ?string $jamMulai = null,
        ?string $jamSelesai = null,
        ?string $materi = null
    ): int {
        $startDate = Carbon::parse($startDateStr);
        $endDate = $endDateStr ? Carbon::parse($endDateStr) : $startDate->copy();

        $targetSiswaIds = is_array($siswaIds) ? $siswaIds : [$siswaIds];

        if (empty($targetSiswaIds)) {
            $targetSiswaIds = Presensi::where('tutor_id', $tutorId)->distinct()->pluck('siswa_id')->toArray();
            if (empty($targetSiswaIds)) {
                $targetSiswaIds = Siswa::pluck('id')->take(1)->toArray();
            }
        }

        $count = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($targetSiswaIds as $siswaId) {
                Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutorId,
                        'siswa_id' => $siswaId,
                        'tgl_presensi' => $date->toDateString(),
                    ],
                    [
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'status' => $status,
                        'materi' => $materi,
                    ]
                );
                $count++;
            }
        }

        return $count;
    }
}

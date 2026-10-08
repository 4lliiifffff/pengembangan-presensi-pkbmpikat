<?php

namespace App\Services;

use App\Models\JadwalSesi;
use App\Models\Murid;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiService
{
    /**
     * Sinkronisasi data presensi terverifikasi hasil persetujuan (Lupa Lapor, Izin/Sakit, atau Admin).
     */
    public function syncApprovedAttendance(
        int $tutorId,
        int|array $muridIds = [],
        string $startDateStr = '',
        ?string $endDateStr = null,
        string $status = 'hadir',
        ?string $jamMulai = null,
        ?string $jamSelesai = null,
        ?string $materi = null,
        int|array $siswaIds = []
    ): int {
        if (empty($muridIds) && ! empty($siswaIds)) {
            $muridIds = $siswaIds;
        }

        $startDate = Carbon::parse($startDateStr ?: now()->toDateString());
        $endDate = $endDateStr ? Carbon::parse($endDateStr) : $startDate->copy();

        $targetMuridIds = is_array($muridIds) ? $muridIds : [$muridIds];

        if (empty($targetMuridIds)) {
            $targetMuridIds = Presensi::where('tutor_id', $tutorId)->distinct()->pluck('murid_id')->filter()->toArray();
            if (empty($targetMuridIds)) {
                $targetMuridIds = Murid::pluck('id')->take(1)->toArray();
            }
        }

        $count = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($targetMuridIds as $muridId) {
                $presensi = Presensi::updateOrCreate(
                    [
                        'tutor_id' => $tutorId,
                        'murid_id' => $muridId,
                        'tgl_presensi' => $date->toDateString(),
                    ],
                    [
                        'siswa_id' => $muridId,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'status' => $status,
                        'materi' => $materi,
                    ]
                );

                // Sinkronisasi status sesi KBM terkait jika ada
                JadwalSesi::where('tutor_id', $tutorId)
                    ->where('murid_id', $muridId)
                    ->whereDate('tanggal_rencana', $date->toDateString())
                    ->where('status', '!=', 'dibatalkan')
                    ->update([
                        'status' => 'selesai',
                        'presensi_id' => $presensi->id,
                    ]);

                $count++;
            }
        }

        return $count;
    }
}

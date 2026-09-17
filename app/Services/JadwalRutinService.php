<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class JadwalRutinService
{
    /**
     * Generate instance Jadwal Sesi untuk rentang tanggal tertentu berdasarkan Master Jadwal Rutin aktif.
     *
     * @return array{created: int, skipped: int, holidays: int, total_processed: int}
     */
    public function generateSesiForPeriod(
        string $startDate,
        string $endDate,
        ?int $siswaId = null,
        ?int $tutorId = null,
        bool $skipHolidays = false
    ): array {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $period = CarbonPeriod::create($start, $end);

        $created = 0;
        $skipped = 0;
        $holidaysCount = 0;

        // Ambil hari libur / agenda khusus dalam periode ini
        $holidayAgendas = Jadwal::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($item) => is_object($item->tanggal) ? $item->tanggal->format('Y-m-d') : substr((string) $item->tanggal, 0, 10));

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $dayName = JadwalRutin::isoToHariName((int) $date->format('N'));

            // Cek apakah tanggal ini terdata sebagai agenda libur / cuti
            $agendasHariIni = $holidayAgendas->get($dateStr, collect());
            $holidayAgenda = $agendasHariIni->first(function ($agenda) {
                $judul = strtolower((string) $agenda->judul);
                $desk = strtolower((string) $agenda->deskripsi);

                return str_contains($judul, 'libur') || str_contains($judul, 'cuti') ||
                    str_contains($desk, 'libur') || str_contains($desk, 'cuti');
            });

            $isHoliday = (bool) $holidayAgenda;
            if ($isHoliday) {
                $holidaysCount++;
                if ($skipHolidays) {
                    continue;
                }
            }

            // Ambil master jadwal rutin yang aktif pada hari ini
            $rutins = JadwalRutin::active()
                ->byHari($dayName)
                ->when($siswaId, fn ($q) => $q->where('siswa_id', $siswaId))
                ->when($tutorId, fn ($q) => $q->where('tutor_id', $tutorId))
                ->where(function ($q) use ($dateStr) {
                    $q->whereNull('berlaku_mulai')->orWhere('berlaku_mulai', '<=', $dateStr);
                })
                ->where(function ($q) use ($dateStr) {
                    $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $dateStr);
                })
                ->get();

            foreach ($rutins as $rutin) {
                // Cek anti-duplikasi: apakah siswa sudah memiliki sesi pada tanggal & jam tersebut atau dari jadwal_rutin_id ini
                $exists = JadwalSesi::where('siswa_id', $rutin->siswa_id)
                    ->whereDate('tanggal_rencana', $dateStr)
                    ->where(function ($q) use ($rutin) {
                        $q->where('jadwal_rutin_id', $rutin->id)
                            ->orWhere('jam_masuk_rencana', $rutin->jam_masuk);
                    })
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                $status = $isHoliday ? 'dibatalkan' : 'terjadwal';
                $catatan = $isHoliday
                    ? "Diliburkan otomatis karena agenda: {$holidayAgenda->judul}"
                    : 'Generated otomatis dari Master Jadwal Rutin Mingguan';

                JadwalSesi::create([
                    'jadwal_rutin_id' => $rutin->id,
                    'tutor_id' => $rutin->tutor_id,
                    'siswa_id' => $rutin->siswa_id,
                    'kategori_tutorial_id' => $rutin->kategori_tutorial_id,
                    'jadwal_kerja_id' => $rutin->jadwal_kerja_id,
                    'tanggal_rencana' => $dateStr,
                    'jam_masuk_rencana' => $rutin->jam_masuk,
                    'jam_pulang_rencana' => $rutin->jam_pulang,
                    'durasi_jam' => $rutin->durasi_jam,
                    'jenis_sesi' => 'reguler',
                    'status' => $status,
                    'status_kehadiran_siswa' => 'belum_presensi',
                    'catatan' => $catatan,
                ]);

                $created++;
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'holidays' => $holidaysCount,
            'total_processed' => $created + $skipped,
        ];
    }

    /**
     * Generate sesi untuk N minggu ke depan mulai dari hari ini.
     *
     * @return array{created: int, skipped: int, holidays: int, total_processed: int}
     */
    public function generateForNextWeeks(
        int $weeks = 4,
        ?int $siswaId = null,
        ?int $tutorId = null,
        bool $skipHolidays = false
    ): array {
        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();
        $endDate = Carbon::now($tz)->addWeeks(max(1, $weeks))->toDateString();

        return $this->generateSesiForPeriod($today, $endDate, $siswaId, $tutorId, $skipHolidays);
    }

    /**
     * Sinkronisasi sesi masa depan jika master jadwal rutin diubah.
     */
    public function syncOnMasterUpdate(JadwalRutin $jadwalRutin): int
    {
        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();

        // Cari sesi reguler masa depan yang belum selesai / belum presensi
        $futureSesis = JadwalSesi::where('jadwal_rutin_id', $jadwalRutin->id)
            ->whereDate('tanggal_rencana', '>=', $today)
            ->where('status', 'terjadwal')
            ->whereNull('presensi_id')
            ->whereNull('presensi_siswa_id')
            ->get();

        $updatedCount = 0;
        foreach ($futureSesis as $sesi) {
            $sesi->update([
                'tutor_id' => $jadwalRutin->tutor_id,
                'siswa_id' => $jadwalRutin->siswa_id,
                'kategori_tutorial_id' => $jadwalRutin->kategori_tutorial_id,
                'jadwal_kerja_id' => $jadwalRutin->jadwal_kerja_id,
                'jam_masuk_rencana' => $jadwalRutin->jam_masuk,
                'jam_pulang_rencana' => $jadwalRutin->jam_pulang,
                'durasi_jam' => $jadwalRutin->durasi_jam,
            ]);
            $updatedCount++;
        }

        return $updatedCount;
    }
}

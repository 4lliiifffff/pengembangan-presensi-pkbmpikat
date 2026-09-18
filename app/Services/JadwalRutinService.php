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

    /**
     * Buat master jadwal rutin baru langsung oleh Tutor untuk siswa bimbingannya.
     *
     * @param  array<string, mixed>  $data
     * @return array{rutin: JadwalRutin, generated: array{created: int, skipped: int, holidays: int, total_processed: int}}
     */
    public function createRutinFromTutor(array $data, int $tutorId): array
    {
        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString();

        $data['tutor_id'] = $tutorId;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['berlaku_mulai'] = $data['berlaku_mulai'] ?? $today;

        $rutin = JadwalRutin::create($data);

        $generatedStats = ['created' => 0, 'skipped' => 0, 'holidays' => 0, 'total_processed' => 0];

        $autoGenerate = filter_var($data['auto_generate'] ?? true, FILTER_VALIDATE_BOOLEAN);
        if ($autoGenerate) {
            $startDate = $rutin->berlaku_mulai ? $rutin->berlaku_mulai->toDateString() : $today;

            // Jika berlaku_sampai ditentukan, generate hingga tanggal tersebut.
            // Jika kosong (terbuka), generate untuk 4 minggu ke depan secara berkala.
            if (! empty($rutin->berlaku_sampai)) {
                $endDate = $rutin->berlaku_sampai->toDateString();
            } else {
                $endDate = Carbon::parse($startDate)->addWeeks(4)->toDateString();
            }

            $generatedStats = $this->generateSesiForPeriod(
                $startDate,
                $endDate,
                $rutin->siswa_id,
                $rutin->tutor_id
            );
        }

        return [
            'rutin' => $rutin,
            'generated' => $generatedStats,
        ];
    }

    /**
     * Reschedule sesi KBM (Opsi 2: Sesi lama dibatalkan dengan riwayat alasan, sesi baru dibuat sebagai pengganti).
     */
    public function rescheduleSesi(
        JadwalSesi $oldSesi,
        string $newDate,
        string $newJamMasuk,
        string $newJamPulang,
        string $alasan,
        ?float $durasiJam = null
    ): JadwalSesi {
        if ($oldSesi->status === 'selesai' || $oldSesi->presensi_id) {
            throw new \InvalidArgumentException('Sesi yang sudah selesai presensi tidak dapat di-reschedule.');
        }

        $formattedOldDate = $oldSesi->tanggal_rencana ? $oldSesi->tanggal_rencana->format('d/m/Y') : '-';
        $oldCatatan = $oldSesi->catatan ? $oldSesi->catatan.' | ' : '';

        // 1. Tandai sesi lama sebagai dibatalkan karena reschedule
        $oldSesi->update([
            'status' => 'dibatalkan',
            'alasan_penggantian' => $alasan,
            'catatan' => $oldCatatan."Direschedule ke {$newDate} pukul {$newJamMasuk} WIB. Alasan: {$alasan}",
        ]);

        // 2. Hitung durasi jam jika tidak diberikan
        if ($durasiJam === null) {
            $t1 = Carbon::createFromFormat('H:i', substr($newJamMasuk, 0, 5));
            $t2 = Carbon::createFromFormat('H:i', substr($newJamPulang, 0, 5));
            $diffMin = (int) $t1->diffInMinutes($t2, false);
            if ($diffMin > 0) {
                $durasiJam = round($diffMin / 60, 2);
            } else {
                $durasiJam = (float) $oldSesi->durasi_jam;
            }
        }

        // 3. Buat sesi pengganti baru
        return JadwalSesi::create([
            'jadwal_rutin_id' => $oldSesi->jadwal_rutin_id,
            'tutor_id' => $oldSesi->tutor_id,
            'siswa_id' => $oldSesi->siswa_id,
            'kategori_tutorial_id' => $oldSesi->kategori_tutorial_id,
            'jadwal_kerja_id' => $oldSesi->jadwal_kerja_id,
            'tanggal_rencana' => $newDate,
            'jam_masuk_rencana' => $newJamMasuk,
            'jam_pulang_rencana' => $newJamPulang,
            'durasi_jam' => $durasiJam,
            'jenis_sesi' => 'pengganti',
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'belum_presensi',
            'tanggal_asli' => $oldSesi->tanggal_rencana,
            'alasan_penggantian' => $alasan,
            'catatan' => "Sesi pengganti untuk pertemuan tanggal {$formattedOldDate} ({$alasan})",
        ]);
    }
}

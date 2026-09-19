<?php

namespace App\Services;

use App\Models\JadwalKerja;
use App\Models\JadwalSesi;
use App\Models\Presensi;
use Carbon\Carbon;
use Throwable;

class ShiftPresensiService
{
    /**
     * Ambil semua data shift yang aktif dari database (dengan fallback ke config jika DB kosong/belum migrate).
     *
     * @return array<string, array{
     *     id: string|int,
     *     jadwal_kerja_id: int|null,
     *     nama: string,
     *     jam_masuk: string,
     *     jam_pulang: string,
     *     durasi_jam: float,
     *     earliest_minutes: int,
     *     tolerance_minutes: int,
     *     kategori_tutorial_id: int|null
     * }>
     */
    public function getShifts(): array
    {
        try {
            $dbShifts = JadwalKerja::with('kategoriTutorial')
                ->active()
                ->orderBy('urutan')
                ->orderBy('jam_masuk')
                ->get();

            if ($dbShifts->isNotEmpty()) {
                $result = [];
                foreach ($dbShifts as $s) {
                    $key = (string) ($s->kode_shift ?: $s->id);
                    $result[$key] = [
                        'id' => $key,
                        'jadwal_kerja_id' => $s->id,
                        'nama' => $s->nama_shift,
                        'jam_masuk' => substr((string) $s->jam_masuk, 0, 5),
                        'jam_pulang' => substr((string) $s->jam_pulang, 0, 5),
                        'durasi_jam' => (float) $s->durasi_jam,
                        'earliest_minutes' => (int) $s->earliest_minutes,
                        'tolerance_minutes' => (int) $s->tolerance_minutes,
                        'kategori_tutorial_id' => $s->kategori_tutorial_id,
                    ];
                }

                return $result;
            }
        } catch (Throwable) {
            // Fallback ke config jika query DB gagal saat bootstrap/testing awal
        }

        return config('presensi_sk.shifts', [
            'pagi' => [
                'id' => 'pagi',
                'jadwal_kerja_id' => null,
                'nama' => 'Shift Pagi',
                'jam_masuk' => '08:00',
                'jam_pulang' => '16:00',
                'durasi_jam' => 8.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'kategori_tutorial_id' => null,
            ],
            'siang' => [
                'id' => 'siang',
                'jadwal_kerja_id' => null,
                'nama' => 'Shift Siang',
                'jam_masuk' => '13:00',
                'jam_pulang' => '17:00',
                'durasi_jam' => 4.00,
                'earliest_minutes' => 30,
                'tolerance_minutes' => 30,
                'kategori_tutorial_id' => null,
            ],
        ]);
    }

    /**
     * Ambil data shift berdasarkan key id atau kode shift.
     *
     * @return array{
     *     id: string|int,
     *     jadwal_kerja_id: int|null,
     *     nama: string,
     *     jam_masuk: string,
     *     jam_pulang: string,
     *     durasi_jam: float,
     *     earliest_minutes: int,
     *     tolerance_minutes: int,
     *     kategori_tutorial_id: int|null
     * }|null
     */
    public function getShiftByKey(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        $shifts = $this->getShifts();

        return $shifts[$key] ?? null;
    }

    /**
     * Deteksi shift yang paling sesuai berdasarkan waktu saat ini atau kategori tutorial.
     *
     * @return array{
     *     id: string|int,
     *     jadwal_kerja_id: int|null,
     *     nama: string,
     *     jam_masuk: string,
     *     jam_pulang: string,
     *     durasi_jam: float,
     *     earliest_minutes: int,
     *     tolerance_minutes: int,
     *     kategori_tutorial_id: int|null
     * }
     */
    public function detectShift(?Carbon $time = null, ?string $preferredKey = null, ?int $kategoriTutorialId = null): array
    {
        $shifts = $this->getShifts();

        // 1. Cek preferred key / kode shift
        if ($preferredKey && isset($shifts[$preferredKey])) {
            return $shifts[$preferredKey];
        }

        // 2. Cek apakah ada shift yang terhubung dengan Kategori Tutorial SK tertentu
        if ($kategoriTutorialId) {
            foreach ($shifts as $s) {
                if (($s['kategori_tutorial_id'] ?? null) == $kategoriTutorialId) {
                    return $s;
                }
            }
        }

        $now = $time ?? Carbon::now('Asia/Jakarta');
        $currentTimeStr = $now->format('H:i:s');

        // 3. Jika lewat pukul 12:00 dan ada shift siang, pilih shift siang
        if ($currentTimeStr >= '12:00:00' && isset($shifts['siang'])) {
            return $shifts['siang'];
        }

        $defaultKey = config('presensi_sk.default_shift', 'pagi');

        return $shifts[$defaultKey] ?? reset($shifts);
    }

    /**
     * Evaluasi waktu check-in presensi masuk berdasarkan aturan SK atau Jadwal Sesi Rencana.
     *
     * @param  Carbon|null  $time  Waktu presensi
     * @param  string|null  $shiftKey  Key shift (opsional)
     * @param  int|null  $kategoriTutorialId  ID Kategori Tutorial SK (opsional)
     * @param  JadwalSesi|null  $jadwalSesi  Objek Jadwal Sesi Belajar / Pengganti (opsional)
     * @return array{
     *     shift_id: string|int,
     *     jadwal_kerja_id: int|null,
     *     jadwal_sesi_id: int|null,
     *     shift_nama: string,
     *     durasi_jam: float,
     *     jam_masuk_target: string,
     *     jam_pulang_target: string,
     *     batas_awal: string,
     *     batas_toleransi: string,
     *     status_kehadiran: string,
     *     menit_keterlambatan: int,
     *     pesan: string,
     *     is_terlambat: bool
     * }
     */
    public function evaluateCheckIn(
        ?Carbon $time = null,
        ?string $shiftKey = null,
        ?int $kategoriTutorialId = null,
        ?JadwalSesi $jadwalSesi = null
    ): array {
        $now = $time ? $time->copy()->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $todayDate = $now->toDateString();

        if ($jadwalSesi) {
            $shiftId = 'sesi_'.$jadwalSesi->id;
            $jadwalKerjaId = $jadwalSesi->jadwal_kerja_id;
            $jadwalSesiId = $jadwalSesi->id;
            $shiftNama = $jadwalSesi->jenis_label.($jadwalSesi->siswa ? ' - '.$jadwalSesi->siswa->nama_siswa : '');
            $durasiJam = (float) $jadwalSesi->durasi_jam;
            $jamMasuk = $jadwalSesi->jam_masuk_formatted;
            $jamPulang = $jadwalSesi->jam_pulang_formatted;

            $earliestMinutes = (int) ($jadwalSesi->jadwalKerja->earliest_minutes ?? config('presensi_sk.earliest_minutes', 30));
            $toleranceMinutes = (int) ($jadwalSesi->jadwalKerja->tolerance_minutes ?? config('presensi_sk.tolerance_minutes', 30));
        } else {
            $shift = $this->detectShift($now, $shiftKey, $kategoriTutorialId);
            $shiftId = $shift['id'];
            $jadwalKerjaId = $shift['jadwal_kerja_id'] ?? null;
            $jadwalSesiId = null;
            $shiftNama = $shift['nama'];
            $durasiJam = (float) ($shift['durasi_jam'] ?? 8.00);
            $jamMasuk = $shift['jam_masuk'];
            $jamPulang = $shift['jam_pulang'];

            $earliestMinutes = (int) ($shift['earliest_minutes'] ?? config('presensi_sk.earliest_minutes', 30));
            $toleranceMinutes = (int) ($shift['tolerance_minutes'] ?? config('presensi_sk.tolerance_minutes', 30));
        }

        $targetMasuk = Carbon::parse($todayDate.' '.$jamMasuk.':00', 'Asia/Jakarta');
        $batasAwal = $targetMasuk->copy()->subMinutes($earliestMinutes);
        $batasToleransi = $targetMasuk->copy()->addMinutes($toleranceMinutes);

        $menitKeterlambatan = 0;
        $statusKehadiran = 'tepat_waktu';
        $isTerlambat = false;
        $pesan = "Presensi masuk tepat waktu ({$shiftNama} {$jamMasuk} WIB).";

        if ($now->lt($batasAwal)) {
            $statusKehadiran = 'lebih_awal';
            $pesan = "Presensi masuk lebih awal dari jadwal {$shiftNama} ({$jamMasuk} WIB).";
        } elseif ($now->gt($batasToleransi)) {
            $statusKehadiran = 'terlambat';
            $isTerlambat = true;
            $menitKeterlambatan = (int) $targetMasuk->diffInMinutes($now, false);
            if ($menitKeterlambatan < 0) {
                $menitKeterlambatan = 0;
            }
            $pesan = "Presensi masuk tercatat terlambat {$menitKeterlambatan} menit dari jadwal {$shiftNama} ({$jamMasuk} WIB).";
        }

        return [
            'shift_id' => $shiftId,
            'jadwal_kerja_id' => $jadwalKerjaId,
            'jadwal_sesi_id' => $jadwalSesiId,
            'shift_nama' => $shiftNama,
            'durasi_jam' => $durasiJam,
            'jam_masuk_target' => $jamMasuk,
            'jam_pulang_target' => $jamPulang,
            'batas_awal' => $batasAwal->format('H:i'),
            'batas_toleransi' => $batasToleransi->format('H:i'),
            'status_kehadiran' => $statusKehadiran,
            'menit_keterlambatan' => $menitKeterlambatan,
            'pesan' => $pesan,
            'is_terlambat' => $isTerlambat,
        ];
    }

    /**
     * Evaluasi kelayakan presensi pulang (Clock-Out) Tutor berdasarkan Model Hibrida Cerdas (Opsi 3):
     * Kondisi A: Jam server mencapai (jam_pulang_rencana - 10 menit toleransi kepulangan wajar).
     * ATAU
     * Kondisi B: Tutor telah mengajar minimal 80% dari durasi rencana sesi KBM sejak jam masuk aktual.
     *
     * @return array{
     *     bisa_pulang: bool,
     *     sisa_detik: int,
     *     sisa_menit: int,
     *     durasi_rencana_jam: float,
     *     durasi_rencana_menit: int,
     *     menit_efektif_wajib: int,
     *     jam_mulai_aktual: string,
     *     jam_pulang_rencana: string|null,
     *     target_waktu_buka: string,
     *     alasan_buka: string,
     *     pesan: string
     * }
     */
    public function calculateCheckOutEligibility(Presensi $presensi, ?Carbon $now = null): array
    {
        $tz = 'Asia/Jakarta';
        $currentTime = $now ? $now->copy()->setTimezone($tz) : Carbon::now($tz);
        $today = $currentTime->toDateString();

        $tglPresensi = $presensi->tgl_presensi ? Carbon::parse($presensi->tgl_presensi)->toDateString() : $today;
        $jamMulaiStr = (string) ($presensi->jam_mulai ?: $currentTime->format('H:i:s'));
        $jamMulaiAktual = Carbon::parse($tglPresensi.' '.$jamMulaiStr, $tz);

        if ($jamMulaiAktual->greaterThan($currentTime)) {
            $jamMulaiAktual->subDay();
        }

        // Ambil JadwalSesi terkait
        $jadwalSesi = $presensi->jadwalSesi;
        if (! $jadwalSesi && $presensi->jadwal_sesi_id) {
            $jadwalSesi = JadwalSesi::find($presensi->jadwal_sesi_id);
        }
        if (! $jadwalSesi && $presensi->tutor_id && $presensi->siswa_id) {
            $jadwalSesi = JadwalSesi::where('tutor_id', $presensi->tutor_id)
                ->where('siswa_id', $presensi->siswa_id)
                ->whereDate('tanggal_rencana', $tglPresensi)
                ->first();
        }

        // Tentukan durasi rencana dalam jam
        $durasiRencanaJam = (float) (
            $jadwalSesi?->durasi_jam
            ?: ($presensi->durasi_pilihan
            ?: ($presensi->kategoriTutorial?->durasi_jam ?: 2.0))
        );
        $durasiRencanaMenit = (int) max(15, round($durasiRencanaJam * 60));

        // Kondisi B: Target Durasi Efektif (80% durasi, minimal 15 menit)
        $menitEfektifWajib = (int) max(15, round($durasiRencanaMenit * 0.80));
        $waktuBukaBerdasarkanDurasi = $jamMulaiAktual->copy()->addMinutes($menitEfektifWajib);

        // Kondisi A: Target Jam Selesai Jadwal (jika ada jam_pulang_rencana, dengan toleransi 10 menit sebelum jadwal usai)
        $waktuBukaBerdasarkanJadwal = null;
        $jamPulangRencana = null;
        if ($jadwalSesi && $jadwalSesi->jam_pulang_rencana) {
            $jamPulangRencana = substr((string) $jadwalSesi->jam_pulang_rencana, 0, 5);
            $waktuSelesaiJadwal = Carbon::parse($tglPresensi.' '.$jamPulangRencana.':00', $tz);
            $waktuBukaBerdasarkanJadwal = $waktuSelesaiJadwal->copy()->subMinutes(10);
        }

        // Tentukan waktu tercepat (earlier of the two)
        if ($waktuBukaBerdasarkanJadwal !== null) {
            if ($waktuBukaBerdasarkanJadwal->lt($waktuBukaBerdasarkanDurasi)) {
                $earliestUnlock = $waktuBukaBerdasarkanJadwal;
                $alasanKey = 'jadwal_selesai';
            } else {
                $earliestUnlock = $waktuBukaBerdasarkanDurasi;
                $alasanKey = 'durasi_terpenuhi';
            }
        } else {
            $earliestUnlock = $waktuBukaBerdasarkanDurasi;
            $alasanKey = 'durasi_terpenuhi';
        }

        $bisaPulang = $currentTime->gte($earliestUnlock);
        $sisaDetik = $bisaPulang ? 0 : (int) $currentTime->diffInSeconds($earliestUnlock, false);
        if ($sisaDetik < 0) {
            $sisaDetik = 0;
        }
        $sisaMenit = (int) ceil($sisaDetik / 60);

        if ($bisaPulang) {
            $pesan = 'Presensi pulang telah dibuka dan dapat dikirimkan sekarang.';
        } else {
            $targetFormat = $earliestUnlock->format('H:i');
            $detailAlasan = $alasanKey === 'jadwal_selesai'
                ? "mendekati jam selesai jadwal ({$jamPulangRencana} WIB)"
                : "memenuhi 80% durasi mengajar ({$menitEfektifWajib} menit)";
            $pesan = "Tunggu {$sisaMenit} menit lagi (hingga pukul {$targetFormat} WIB). Presensi pulang dibuka saat {$detailAlasan}.";
        }

        return [
            'bisa_pulang' => $bisaPulang,
            'sisa_detik' => $sisaDetik,
            'sisa_menit' => $sisaMenit,
            'durasi_rencana_jam' => $durasiRencanaJam,
            'durasi_rencana_menit' => $durasiRencanaMenit,
            'menit_efektif_wajib' => $menitEfektifWajib,
            'jam_mulai_aktual' => $jamMulaiAktual->format('H:i'),
            'jam_pulang_rencana' => $jamPulangRencana,
            'target_waktu_buka' => $earliestUnlock->format('H:i'),
            'alasan_buka' => $alasanKey,
            'pesan' => $pesan,
        ];
    }
}

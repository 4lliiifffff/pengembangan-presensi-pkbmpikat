<?php

namespace App\Services;

use App\Models\JadwalKerja;
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
     * Evaluasi waktu check-in presensi masuk berdasarkan aturan SK.
     *
     * @param  Carbon|null  $time  Waktu presensi
     * @param  string|null  $shiftKey  Key shift (opsional)
     * @param  int|null  $kategoriTutorialId  ID Kategori Tutorial SK (opsional)
     * @return array{
     *     shift_id: string|int,
     *     jadwal_kerja_id: int|null,
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
    public function evaluateCheckIn(?Carbon $time = null, ?string $shiftKey = null, ?int $kategoriTutorialId = null): array
    {
        $now = $time ? $time->copy()->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $shift = $this->detectShift($now, $shiftKey, $kategoriTutorialId);

        $earliestMinutes = (int) ($shift['earliest_minutes'] ?? config('presensi_sk.earliest_minutes', 30));
        $toleranceMinutes = (int) ($shift['tolerance_minutes'] ?? config('presensi_sk.tolerance_minutes', 30));

        $todayDate = $now->toDateString();

        $targetMasuk = Carbon::parse($todayDate.' '.$shift['jam_masuk'].':00', 'Asia/Jakarta');
        $batasAwal = $targetMasuk->copy()->subMinutes($earliestMinutes);
        $batasToleransi = $targetMasuk->copy()->addMinutes($toleranceMinutes);

        $menitKeterlambatan = 0;
        $statusKehadiran = 'tepat_waktu';
        $isTerlambat = false;
        $pesan = "Presensi masuk tepat waktu ({$shift['nama']} {$shift['jam_masuk']} WIB).";

        if ($now->lt($batasAwal)) {
            $statusKehadiran = 'lebih_awal';
            $pesan = "Presensi masuk lebih awal dari jadwal {$shift['nama']} ({$shift['jam_masuk']} WIB).";
        } elseif ($now->gt($batasToleransi)) {
            $statusKehadiran = 'terlambat';
            $isTerlambat = true;
            $menitKeterlambatan = (int) $targetMasuk->diffInMinutes($now, false);
            if ($menitKeterlambatan < 0) {
                $menitKeterlambatan = 0;
            }
            $pesan = "Presensi masuk tercatat terlambat {$menitKeterlambatan} menit dari jadwal {$shift['nama']} ({$shift['jam_masuk']} WIB).";
        }

        return [
            'shift_id' => $shift['id'],
            'jadwal_kerja_id' => $shift['jadwal_kerja_id'] ?? null,
            'shift_nama' => $shift['nama'],
            'durasi_jam' => (float) ($shift['durasi_jam'] ?? 8.00),
            'jam_masuk_target' => $shift['jam_masuk'],
            'jam_pulang_target' => $shift['jam_pulang'],
            'batas_awal' => $batasAwal->format('H:i'),
            'batas_toleransi' => $batasToleransi->format('H:i'),
            'status_kehadiran' => $statusKehadiran,
            'menit_keterlambatan' => $menitKeterlambatan,
            'pesan' => $pesan,
            'is_terlambat' => $isTerlambat,
        ];
    }
}

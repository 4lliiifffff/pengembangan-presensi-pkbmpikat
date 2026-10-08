<?php

namespace App\Imports;

use App\Models\JadwalSesi;
use App\Models\KategoriTutorial;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class JadwalSesiImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $importedCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    /**
     * @var array<int, string>
     */
    public array $importErrors = [];

    public function __construct(
        public ?int $defaultTutorId = null
    ) {}

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            $rowNumber = 1; // Baris data setelah heading

            foreach ($rows as $row) {
                $rowNumber++;

                // 1. Ambil nilai kolom dengan berbagai variasi alias nama kolom menggunakan helper getColumnValue
                $rawTanggal = $this->getColumnValue($row, [
                    'tanggal_rencana', 'tanggal_kbm', 'tanggal_sesi', 'tanggal', 'tgl', 'date',
                ]);

                $rawJamMasuk = $this->getColumnValue($row, [
                    'jam_mulai', 'jam_masuk', 'jam_masuk_rencana', 'jam_mulai_kbm', 'start_time',
                ]);

                $rawJamPulang = $this->getColumnValue($row, [
                    'jam_selesai', 'jam_pulang', 'jam_pulang_rencana', 'jam_selesai_kbm', 'end_time',
                ]);

                $rawSiswa = $this->getColumnValue($row, [
                    'nomor_absen_murid', 'nomor_absen_siswa', 'nomor_absen', 'no_absen', 'nis', 'nama_murid', 'murid', 'nama_siswa', 'siswa',
                ]);

                $rawKategori = $this->getColumnValue($row, [
                    'kategori_tutorial', 'kategori', 'program', 'tipe',
                ]);

                $rawCatatan = $this->getColumnValue($row, [
                    'catatan', 'materi', 'topik', 'keterangan', 'deskripsi',
                ]);

                $rawNikTutor = $this->getColumnValue($row, [
                    'nik_tutor', 'nik', 'tutor_nik',
                ]);

                // Abaikan baris kosong
                if (! $rawTanggal && ! $rawSiswa && ! $rawJamMasuk) {
                    continue;
                }

                // Validasi data minimum
                if (! $rawTanggal || ! $rawSiswa || ! $rawJamMasuk) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Baris {$rowNumber}: Kolom tanggal, siswa, atau jam mulai tidak boleh kosong.";

                    continue;
                }

                // 2. Parse Tanggal
                $parsedTanggal = $this->parseDate($rawTanggal);
                if (! $parsedTanggal) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Baris {$rowNumber}: Format tanggal '{$rawTanggal}' tidak valid. Gunakan format YYYY-MM-DD.";

                    continue;
                }

                // 3. Parse Jam Masuk & Jam Pulang
                $parsedJamMasuk = $this->parseTime($rawJamMasuk);
                if (! $parsedJamMasuk) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Baris {$rowNumber}: Format jam mulai '{$rawJamMasuk}' tidak valid. Gunakan format HH:MM.";

                    continue;
                }

                $parsedJamPulang = $this->parseTime($rawJamPulang);
                if (! $parsedJamPulang) {
                    // Default 2 jam setelah jam masuk jika jam pulang kosong
                    try {
                        $parsedJamPulang = Carbon::createFromFormat('H:i', $parsedJamMasuk)->addHours(2)->format('H:i');
                    } catch (\Throwable) {
                        $parsedJamPulang = '10:00';
                    }
                }

                // Hitung durasi jam
                try {
                    $t1 = Carbon::createFromFormat('H:i', $parsedJamMasuk);
                    $t2 = Carbon::createFromFormat('H:i', $parsedJamPulang);
                    $diffMin = (int) $t1->diffInMinutes($t2, false);
                    $durasiJam = $diffMin > 0 ? round($diffMin / 60, 2) : 2.00;
                } catch (\Throwable) {
                    $durasiJam = 2.00;
                }

                // 4. Resolusi Tutor
                $tutorId = $this->defaultTutorId;
                if ($rawNikTutor) {
                    $foundTutor = Tutor::where('nik', $rawNikTutor)->first();
                    if ($foundTutor) {
                        // Jika tutor login, verifikasi integritas kepemilikan
                        if ($this->defaultTutorId && $foundTutor->id !== $this->defaultTutorId) {
                            $this->skippedCount++;
                            $this->importErrors[] = "Baris {$rowNumber}: NIK Tutor ({$rawNikTutor}) tidak sesuai dengan akun Anda.";

                            continue;
                        }
                        $tutorId = $foundTutor->id;
                    }
                }

                if (! $tutorId) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Baris {$rowNumber}: Data Tutor pengampu tidak dapat diidentifikasi.";

                    continue;
                }

                // 5. Resolusi Murid (cek no_absen / nis, fallback ke nama_murid)
                $siswa = Siswa::where('no_absen', $rawSiswa)->first();
                if (! $siswa) {
                    $siswa = Siswa::whereRaw('LOWER(nama_murid) = ?', [strtolower($rawSiswa)])->first();
                }
                if (! $siswa) {
                    $siswa = Siswa::where('nama_murid', 'like', '%'.$rawSiswa.'%')->first();
                }

                if (! $siswa) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Baris {$rowNumber}: Murid dengan identitas '{$rawSiswa}' tidak ditemukan di sistem.";

                    continue;
                }

                // 6. Resolusi Kategori Tutorial (opsional)
                $kategoriId = null;
                if ($rawKategori) {
                    $kat = KategoriTutorial::where('nama_kategori', 'like', '%'.$rawKategori.'%')
                        ->first();
                    if ($kat) {
                        $kategoriId = $kat->id;
                    }
                }

                // 7. Cek Sesi Duplikat (Idempotensi)
                $existing = JadwalSesi::where('tutor_id', $tutorId)
                    ->where('murid_id', $siswa->id)
                    ->whereDate('tanggal_rencana', $parsedTanggal)
                    ->where('jam_masuk_rencana', $parsedJamMasuk)
                    ->first();

                if ($existing) {
                    // Update jika sesi belum selesai / belum presensi
                    if ($existing->status !== 'selesai' && ! $existing->presensi_id) {
                        $existing->update([
                            'jam_pulang_rencana' => $parsedJamPulang,
                            'durasi_jam' => $durasiJam,
                            'kategori_tutorial_id' => $kategoriId ?? $existing->kategori_tutorial_id,
                            'catatan' => $rawCatatan ?: $existing->catatan,
                        ]);
                        $this->updatedCount++;
                    } else {
                        $this->skippedCount++;
                    }

                    continue;
                }

                // 8. Buat Jadwal Sesi Baru
                JadwalSesi::create([
                    'tutor_id' => $tutorId,
                    'murid_id' => $siswa->id,
                    'siswa_id' => $siswa->id,
                    'kategori_tutorial_id' => $kategoriId,
                    'tanggal_rencana' => $parsedTanggal,
                    'jam_masuk_rencana' => $parsedJamMasuk,
                    'jam_pulang_rencana' => $parsedJamPulang,
                    'durasi_jam' => $durasiJam,
                    'jenis_sesi' => 'reguler',
                    'status' => 'terjadwal',
                    'status_kehadiran_murid' => 'belum_presensi',
                    'status_kehadiran_siswa' => 'belum_presensi',
                    'catatan' => $rawCatatan ?: 'Diimpor dari berkas Excel jadwal mengajar',
                ]);

                $this->importedCount++;
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.tanggal_rencana_yyyy_mm_dd_wajib' => ['nullable'],
            '*.nomor_absen_siswa_nis_nama_wajib' => ['nullable'],
        ];
    }

    /**
     * Parse tanggal dari berbagai format string maupun serial number Excel.
     */
    private function parseDate(string $value): ?string
    {
        if (is_numeric($value) && (float) $value > 1000 && (float) $value < 100000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                // Lanjut ke parser string
            }
        }

        $cleaned = trim($value);

        // Format standar Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $cleaned)) {
            return $cleaned;
        }

        // Format d/m/Y atau d-m-Y
        try {
            return Carbon::parse($cleaned)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse jam dari string maupun fractional day Excel.
     */
    private function parseTime(string $value): ?string
    {
        $cleaned = trim($value);

        // Fractional day Excel (misal 0.375 = 09:00)
        if (is_numeric($cleaned) && (float) $cleaned >= 0 && (float) $cleaned < 1) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $cleaned)->format('H:i');
            } catch (\Throwable) {
                // Lanjut ke parser string
            }
        }

        // Ganti titik dengan titik dua jika ada format jam seperti 08.00
        $normalized = str_replace('.', ':', $cleaned);

        // Format H:i atau H:i:s
        if (preg_match('/^(\d{1,2}):(\d{2})/', $normalized, $matches)) {
            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $min = $matches[2];

            return "{$hour}:{$min}";
        }

        try {
            return Carbon::parse($normalized)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolusi nilai kolom dari row secara fleksibel berdasarkan kumpulan keyword.
     *
     * @param  array<string>  $keywords
     */
    private function getColumnValue(mixed $row, array $keywords): string
    {
        $rowArray = is_array($row) ? $row : (method_exists($row, 'toArray') ? $row->toArray() : (array) $row);

        // 1. Coba exact normalized match dulu
        foreach ($rowArray as $key => $val) {
            $cleanKey = strtolower((string) preg_replace('/[^a-z0-9]/', '', (string) $key));
            foreach ($keywords as $kw) {
                $cleanKw = strtolower((string) preg_replace('/[^a-z0-9]/', '', $kw));
                if ($cleanKey === $cleanKw) {
                    return trim((string) $val);
                }
            }
        }

        // 2. Coba partial contains match
        foreach ($rowArray as $key => $val) {
            $cleanKey = strtolower((string) preg_replace('/[^a-z0-9]/', '', (string) $key));
            foreach ($keywords as $kw) {
                $cleanKw = strtolower((string) preg_replace('/[^a-z0-9]/', '', $kw));
                if (str_contains($cleanKey, $cleanKw)) {
                    return trim((string) $val);
                }
            }
        }

        return '';
    }
}

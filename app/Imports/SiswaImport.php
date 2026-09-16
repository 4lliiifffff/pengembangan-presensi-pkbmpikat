<?php

namespace App\Imports;

use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $importedCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $noAbsen = trim((string) ($row['nomor_absen_nis_wajib'] ?? ($row['nomor_absen_nis'] ?? ($row['no_absen'] ?? ''))));
                $namaSiswa = trim((string) ($row['nama_siswa_wajib'] ?? ($row['nama_siswa'] ?? '')));
                $namaWali = trim((string) ($row['nama_wali_murid_wajib'] ?? ($row['nama_wali_murid'] ?? ($row['nama_wali'] ?? ''))));
                $noHp = trim((string) ($row['no_whatsapp_wali_wajib'] ?? ($row['no_whatsapp_wali'] ?? ($row['no_hp'] ?? ''))));
                $namaKelas = trim((string) ($row['nama_kelas_rombel_wajib'] ?? ($row['nama_kelas_rombel'] ?? ($row['nama_kelas'] ?? ''))));
                $nikTutor = trim((string) ($row['nik_tutor_pembimbing_opsional'] ?? ($row['nik_tutor_pembimbing'] ?? ($row['nik_tutor'] ?? ''))));
                $rawAbk = strtolower(trim((string) ($row['status_abk_opsional'] ?? ($row['status_abk'] ?? ($row['is_abk'] ?? '')))));

                if (! $noAbsen || ! $namaSiswa) {
                    $this->skippedCount++;

                    continue;
                }

                // Cari atau buat Kelas
                $kelasId = 1;
                if ($namaKelas) {
                    $kelas = Kelas::firstOrCreate(['nama_kelas' => $namaKelas]);
                    $kelasId = $kelas->id;
                } else {
                    $firstKelas = Kelas::first();
                    if ($firstKelas) {
                        $kelasId = $firstKelas->id;
                    }
                }

                // Cari Tutor jika diisi
                $tutorId = null;
                if ($nikTutor) {
                    $tutor = Tutor::where('nik', $nikTutor)->first();
                    if ($tutor) {
                        $tutorId = $tutor->id;
                    }
                }

                $isAbk = str_contains($rawAbk, 'abk') || in_array($rawAbk, ['1', 'true', 'ya', 'yes']);

                $existingSiswa = Siswa::where('no_absen', $noAbsen)->first();
                if ($existingSiswa) {
                    $existingSiswa->update([
                        'nama_siswa' => $namaSiswa,
                        'is_abk' => $isAbk,
                        'nama_wali' => $namaWali ?: $existingSiswa->nama_wali,
                        'no_hp' => $noHp ?: $existingSiswa->no_hp,
                        'kelas_id' => $kelasId,
                        'tutor_id' => $tutorId ?: $existingSiswa->tutor_id,
                    ]);
                    $this->updatedCount++;
                } else {
                    Siswa::create([
                        'no_absen' => $noAbsen,
                        'nama_siswa' => $namaSiswa,
                        'is_abk' => $isAbk,
                        'nama_wali' => $namaWali ?: '-',
                        'no_hp' => $noHp ?: '-',
                        'kelas_id' => $kelasId,
                        'tutor_id' => $tutorId,
                    ]);
                    $this->importedCount++;
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.nomor_absen_nis_wajib' => ['nullable'],
            '*.nama_siswa_wajib' => ['nullable'],
        ];
    }
}

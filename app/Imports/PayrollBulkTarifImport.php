<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PayrollBulkTarifImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Ambil nomor absen / NIS
                $noAbsen = trim((string) ($row['nomor_absen_nis'] ?? ($row['no_absen'] ?? ($row['nis'] ?? ''))));
                $rawAbk = strtolower(trim((string) ($row['status_abk_abk_reguler'] ?? ($row['status_abk'] ?? ($row['is_abk'] ?? '')))));

                if (! $noAbsen) {
                    $this->skippedCount++;

                    continue;
                }

                $siswa = Siswa::where('no_absen', $noAbsen)->first();
                if ($siswa) {
                    $isAbk = str_contains($rawAbk, 'abk') || in_array($rawAbk, ['1', 'true', 'ya', 'yes']);
                    $siswa->update(['is_abk' => $isAbk]);
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.nomor_absen_nis' => ['nullable'],
        ];
    }
}

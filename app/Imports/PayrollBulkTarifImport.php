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
                $rawTarif = $row['tarif_honor_per_jam_rp'] ?? ($row['tarif_per_jam'] ?? ($row['tarif'] ?? null));

                if (! $noAbsen || $rawTarif === null) {
                    $this->skippedCount++;

                    continue;
                }

                // Bersihkan format nominal misal Rp 50.000 atau 50000
                $cleanTarif = (float) preg_replace('/[^\d.]/', '', str_replace(',', '.', (string) $rawTarif));

                $siswa = Siswa::where('no_absen', $noAbsen)->first();
                if ($siswa) {
                    $siswa->update(['tarif_per_jam' => $cleanTarif]);
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
            '*.tarif_honor_per_jam_rp' => ['nullable'],
        ];
    }
}

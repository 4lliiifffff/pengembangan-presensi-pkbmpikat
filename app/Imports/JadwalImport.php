<?php

namespace App\Imports;

use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class JadwalImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $importedCount = 0;

    public int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $judul = trim((string) ($row['judul_agenda_kegiatan_wajib'] ?? ($row['judul_agenda_kegiatan'] ?? ($row['judul'] ?? ''))));
                $deskripsi = trim((string) ($row['deskripsi_keterangan_opsional'] ?? ($row['deskripsi'] ?? '')));
                $rawTanggal = trim((string) ($row['tanggal_kegiatan_format_yyyy_mm_dd_wajib'] ?? ($row['tanggal_kegiatan'] ?? ($row['tanggal'] ?? ''))));
                $lokasi = trim((string) ($row['lokasi_kegiatan_opsional'] ?? ($row['lokasi'] ?? '')));

                if (! $judul || ! $rawTanggal) {
                    $this->skippedCount++;

                    continue;
                }

                try {
                    $parsedTanggal = Carbon::parse($rawTanggal)->toDateString();
                } catch (\Throwable) {
                    $this->skippedCount++;

                    continue;
                }

                Jadwal::create([
                    'judul' => $judul,
                    'deskripsi' => $deskripsi ?: null,
                    'tanggal' => $parsedTanggal,
                    'lokasi' => $lokasi ?: null,
                ]);

                $this->importedCount++;
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.judul_agenda_kegiatan_wajib' => ['nullable'],
            '*.tanggal_kegiatan_format_yyyy_mm_dd_wajib' => ['nullable'],
        ];
    }
}

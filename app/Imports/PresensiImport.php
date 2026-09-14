<?php

namespace App\Imports;

use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PresensiImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $importedCount = 0;

    public int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $nikTutor = trim((string) ($row['nik_tutor_wajib'] ?? ($row['nik_tutor'] ?? ($row['nik'] ?? ''))));
                $noAbsenSiswa = trim((string) ($row['nomor_absen_siswa_nis_wajib'] ?? ($row['nomor_absen_siswa'] ?? ($row['no_absen'] ?? ''))));
                $rawTanggal = trim((string) ($row['tanggal_presensi_yyyy_mm_dd_wajib'] ?? ($row['tanggal_presensi'] ?? ($row['tanggal'] ?? ''))));
                $jamMulai = trim((string) ($row['jam_mulai_hh_mm_wajib'] ?? ($row['jam_mulai'] ?? '')));
                $jamSelesai = trim((string) ($row['jam_selesai_hh_mm_wajib'] ?? ($row['jam_selesai'] ?? '')));
                $moda = strtolower(trim((string) ($row['moda_pembelajaran_tatap_muka_home_visit_online_wajib'] ?? ($row['moda_pembelajaran'] ?? 'tatap_muka'))));
                $status = strtolower(trim((string) ($row['status_hadir_izin_sakit_alpha_wajib'] ?? ($row['status'] ?? 'hadir'))));
                $lokasiMulai = trim((string) ($row['lokasi_mulai_opsional'] ?? ($row['lokasi_mulai'] ?? '')));
                $lokasiSelesai = trim((string) ($row['lokasi_selesai_opsional'] ?? ($row['lokasi_selesai'] ?? '')));
                $keterangan = trim((string) ($row['keterangan_opsional'] ?? ($row['keterangan'] ?? '')));

                if (! $nikTutor || ! $noAbsenSiswa || ! $rawTanggal) {
                    $this->skippedCount++;

                    continue;
                }

                $tutor = Tutor::where('nik', $nikTutor)->first();
                $siswa = Siswa::where('no_absen', $noAbsenSiswa)->first();

                if (! $tutor || ! $siswa) {
                    $this->skippedCount++;

                    continue;
                }

                try {
                    $tglPresensi = Carbon::parse($rawTanggal)->toDateString();
                } catch (\Throwable) {
                    $this->skippedCount++;

                    continue;
                }

                if (in_array($moda, ['home_visit', 'kunjungan_rumah', 'kunjungan', 'home visit', 'kunjungan rumah'])) {
                    $moda = 'kunjungan_rumah';
                } elseif (in_array($moda, ['online', 'daring'])) {
                    $moda = 'online';
                } else {
                    $moda = 'sekolah';
                }

                if (! in_array($status, ['hadir', 'izin', 'sakit', 'alpha'])) {
                    $status = 'hadir';
                }

                Presensi::create([
                    'tutor_id' => $tutor->id,
                    'siswa_id' => $siswa->id,
                    'tgl_presensi' => $tglPresensi,
                    'jam_mulai' => $jamMulai ?: '08:00',
                    'jam_selesai' => $jamSelesai ?: '10:00',
                    'moda_pembelajaran' => $moda,
                    'status' => $status,
                    'lokasi_mulai' => $lokasiMulai ?: 'PKBM Pikat',
                    'lokasi_selesai' => $lokasiSelesai ?: 'PKBM Pikat',
                ]);

                $this->importedCount++;
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.nik_tutor_wajib' => ['nullable'],
            '*.nomor_absen_siswa_nis_wajib' => ['nullable'],
        ];
    }
}

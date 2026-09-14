<?php

namespace App\Imports;

use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TutorImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $importedCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $nik = trim((string) ($row['nik_nomor_induk_kependudukan_wajib'] ?? ($row['nik'] ?? '')));
                $namaLengkap = trim((string) ($row['nama_lengkap_beserta_gelar_wajib'] ?? ($row['nama_lengkap'] ?? ($row['nama'] ?? ''))));
                $email = trim((string) ($row['email_aktif_opsional'] ?? ($row['email'] ?? '')));
                $noHp = trim((string) ($row['nomor_whatsapp_hp_wajib'] ?? ($row['nomor_whatsapp_hp'] ?? ($row['no_hp'] ?? ''))));
                $role = strtolower(trim((string) ($row['peran_role_tutor_admin_kepala_sekolah_wajib'] ?? ($row['role'] ?? 'tutor'))));
                $rawPassword = trim((string) ($row['password_akun_kosongkan_jika_default_nik'] ?? ($row['password'] ?? '')));

                if (! $nik || ! $namaLengkap) {
                    $this->skippedCount++;

                    continue;
                }

                if (! in_array($role, ['tutor', 'admin', 'kepala_sekolah'])) {
                    $role = 'tutor';
                }

                $passwordHash = Hash::make($rawPassword ?: $nik);

                $user = User::where('nik', $nik)->first();
                if ($user) {
                    $user->update([
                        'nama_lengkap' => $namaLengkap,
                        'name' => $namaLengkap,
                        'email' => $email ?: $user->email,
                        'no_hp' => $noHp ?: $user->no_hp,
                        'role' => $role,
                    ]);
                    $this->updatedCount++;
                } else {
                    $user = User::create([
                        'nik' => $nik,
                        'nama_lengkap' => $namaLengkap,
                        'name' => $namaLengkap,
                        'email' => $email ?: ($nik.'@pkbmpikat.sch.id'),
                        'no_hp' => $noHp ?: '-',
                        'role' => $role,
                        'password' => $passwordHash,
                        'is_active' => true,
                    ]);
                    $this->importedCount++;
                }

                // Sinkronkan ke tabel tutors jika role = tutor
                if ($role === 'tutor') {
                    Tutor::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'nik' => $nik,
                            'nama_lengkap' => $namaLengkap,
                            'email' => $email ?: $user->email,
                            'no_hp' => $noHp ?: '-',
                        ]
                    );
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.nik_nomor_induk_kependudukan_wajib' => ['nullable'],
            '*.nama_lengkap_beserta_gelar_wajib' => ['nullable'],
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Kepala_Sekolah;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TutorService
{
    /**
     * Dapatkan semua tutor yang dapat diberi tugas/siswa/jadwal (termasuk sinkronisasi Kepala Sekolah).
     */
    public function getAssignableTutors(): Collection
    {
        $this->syncKepsekIntoTutors();

        return Tutor::orderBy('nama_lengkap')->get();
    }

    /**
     * Sinkronisasi data Kepala Sekolah ke dalam tabel tutors agar tersedia di dropdown penugasan.
     */
    public function syncKepsekIntoTutors(): void
    {
        $hasJabatan = Schema::hasColumn('tutors', 'jabatan');

        $ensureTutor = function (array $attrs) use ($hasJabatan): void {
            if (! empty($attrs['user_id']) && Tutor::where('user_id', $attrs['user_id'])->exists()) {
                return;
            }
            if (! empty($attrs['nik']) && Tutor::where('nik', $attrs['nik'])->exists()) {
                return;
            }
            if (! empty($attrs['email']) && Schema::hasColumn('tutors', 'email')
                && Tutor::where('email', $attrs['email'])->exists()) {
                return;
            }

            if ($hasJabatan) {
                $attrs['jabatan'] = $attrs['jabatan'] ?? 'Kepala Sekolah';
            }

            Tutor::create($attrs);
        };

        Kepala_Sekolah::query()->orderBy('nama_lengkap')->each(function (Kepala_Sekolah $kepsek) use ($ensureTutor) {
            if (! $kepsek->user_id) {
                return;
            }

            $ensureTutor([
                'user_id' => $kepsek->user_id,
                'nik' => $kepsek->nik,
                'nama_lengkap' => $kepsek->nama_lengkap,
                'email' => $kepsek->email,
                'no_hp' => $kepsek->no_hp,
                'foto' => $kepsek->foto,
                'jabatan' => 'Kepala Sekolah',
            ]);
        });

        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        User::query()
            ->where('role', 'kepala_sekolah')
            ->each(function (User $user) use ($ensureTutor) {
                if (Tutor::where('user_id', $user->id)->exists()) {
                    return;
                }

                $nik = (string) ($user->nik ?? '');
                $email = (string) ($user->email ?? '');
                if ($email === '' && $nik !== '') {
                    $email = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
                }
                if ($nik === '') {
                    $nik = 'KEPSEK-'.$user->id;
                }

                $ensureTutor([
                    'user_id' => $user->id,
                    'nik' => $nik,
                    'nama_lengkap' => (string) (($user->nama_lengkap ?? $user->name) ?? 'Kepala Sekolah'),
                    'email' => $email,
                    'no_hp' => $user->no_hp ?? null,
                    'foto' => $user->foto ?? null,
                    'jabatan' => 'Kepala Sekolah',
                ]);
            });
    }
}

<?php

namespace App\Observers;

use App\Models\Murid;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MuridObserver
{
    /**
     * Handle the Murid "created" event.
     * Otomatis membuatkan akun User dengan role 'murid' dan menghubungkan user_id.
     */
    public function created(Murid $murid): void
    {
        $this->syncUserAccount($murid);
    }

    /**
     * Handle the Murid "updated" event.
     * Otomatis menyinkronkan nama, kontak, dan status aktif akun User.
     */
    public function updated(Murid $murid): void
    {
        $this->syncUserAccount($murid);
    }

    /**
     * Handle the Murid "deleted" event.
     * Saat murid diarsipkan / soft delete, nonaktifkan akun User agar tidak bisa login.
     */
    public function deleted(Murid $murid): void
    {
        if ($murid->user) {
            $murid->user->updateQuietly([
                'is_active' => 0,
            ]);
        }
    }

    /**
     * Handle the Murid "restored" event.
     * Saat murid dipulihkan, aktifkan kembali akun jika status_murid adalah aktif.
     */
    public function restored(Murid $murid): void
    {
        if ($murid->user && $murid->status_murid === 'aktif') {
            $murid->user->updateQuietly([
                'is_active' => 1,
            ]);
        }
    }

    /**
     * Sinkronisasi atau buat akun User untuk model Murid.
     */
    protected function syncUserAccount(Murid $murid): void
    {
        $isActive = (! $murid->status_murid || $murid->status_murid === 'aktif') ? 1 : 0;

        // 1. Jika sudah punya user_id dan user ditemukan, update data user
        $cleanNo = preg_replace('/[^a-zA-Z0-9]/', '', (string) $murid->no_absen) ?: (string) $murid->id;
        $mrdNik = 'MRD'.str_pad($cleanNo, 4, '0', STR_PAD_LEFT);
        $preferredNik = $murid->nis ?: ($murid->nisn ?: $mrdNik);
        $email = 'murid'.strtolower($cleanNo).'@pkbmpikat.com';

        if ($murid->user_id) {
            $user = User::find($murid->user_id);
            if ($user) {
                $userUpdates = [
                    'nama_lengkap' => $murid->nama_murid,
                    'no_hp' => $murid->no_hp,
                    'is_active' => $isActive,
                ];

                $targetNik = $murid->nis ?: ($murid->nisn ?: (empty($user->nik) ? $mrdNik : null));
                if ($targetNik && $targetNik !== $user->nik) {
                    $collision = User::where('nik', $targetNik)->where('id', '!=', $user->id)->exists();
                    if (! $collision) {
                        $userUpdates['nik'] = $targetNik;
                    }
                }

                $user->updateQuietly($userUpdates);

                return;
            }
        }

        // 2. Cek apakah NIK atau Email sudah terpakai oleh user lain
        $existingUserByEmail = User::where('email', $email)->first();
        $existingUserByNik = User::where('nik', $preferredNik)
            ->orWhere('nik', $mrdNik)
            ->first();

        if ($existingUserByEmail && in_array($existingUserByEmail->role, ['murid', 'siswa'])) {
            $user = $existingUserByEmail;
            $userUpdates = [
                'nama_lengkap' => $murid->nama_murid,
                'no_hp' => $murid->no_hp,
                'role' => 'murid',
                'is_active' => $isActive,
            ];
            if (! User::where('nik', $preferredNik)->where('id', '!=', $user->id)->exists()) {
                $userUpdates['nik'] = $preferredNik;
            }
            $user->updateQuietly($userUpdates);
        } elseif ($existingUserByNik && in_array($existingUserByNik->role, ['murid', 'siswa'])) {
            $user = $existingUserByNik;
            $userUpdates = [
                'nama_lengkap' => $murid->nama_murid,
                'no_hp' => $murid->no_hp,
                'role' => 'murid',
                'is_active' => $isActive,
            ];
            if (! User::where('nik', $preferredNik)->where('id', '!=', $user->id)->exists()) {
                $userUpdates['nik'] = $preferredNik;
            }
            $user->updateQuietly($userUpdates);
        } else {
            // Generate fallback email/nik jika terjadi collision
            $nik = $preferredNik;
            if (User::where('nik', $nik)->exists()) {
                $nik = $mrdNik;
            }
            if (User::where('nik', $nik)->exists()) {
                $uniqueSuffix = $murid->id ? $murid->id : uniqid();
                $nik = 'MRD'.$cleanNo.'_'.$uniqueSuffix;
            }

            if (User::where('email', $email)->exists()) {
                $uniqueSuffix = $murid->id ? $murid->id : uniqid();
                $email = 'murid'.$cleanNo.'_'.$uniqueSuffix.'@pkbmpikat.com';
            }

            $user = User::create([
                'nik' => $nik,
                'nama_lengkap' => $murid->nama_murid,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'murid',
                'no_hp' => $murid->no_hp,
                'is_active' => $isActive,
            ]);
        }

        // 3. Hubungkan foreign key user_id ke model Murid tanpa mentrigger infinite loop
        $murid->user_id = $user->id;
        $murid->saveQuietly();
    }
}

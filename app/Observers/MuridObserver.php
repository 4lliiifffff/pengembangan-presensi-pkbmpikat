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
        if ($murid->user_id) {
            $user = User::find($murid->user_id);
            if ($user) {
                $user->updateQuietly([
                    'nama_lengkap' => $murid->nama_murid,
                    'no_hp' => $murid->no_hp,
                    'is_active' => $isActive,
                ]);

                return;
            }
        }

        // 2. Buat NIK dan Email yang unik dan ramah pengguna
        $cleanNo = preg_replace('/[^a-zA-Z0-9]/', '', (string) $murid->no_absen) ?: (string) $murid->id;
        $nik = 'MRD'.str_pad($cleanNo, 4, '0', STR_PAD_LEFT);
        $email = 'murid'.strtolower($cleanNo).'@pkbmpikat.com';

        // Cek apakah NIK atau Email sudah terpakai oleh user lain
        $existingUserByEmail = User::where('email', $email)->first();
        $existingUserByNik = User::where('nik', $nik)->first();

        if ($existingUserByEmail && in_array($existingUserByEmail->role, ['murid', 'siswa'])) {
            $user = $existingUserByEmail;
            $user->updateQuietly([
                'nama_lengkap' => $murid->nama_murid,
                'no_hp' => $murid->no_hp,
                'role' => 'murid',
                'is_active' => $isActive,
            ]);
        } elseif ($existingUserByNik && in_array($existingUserByNik->role, ['murid', 'siswa'])) {
            $user = $existingUserByNik;
            $user->updateQuietly([
                'nama_lengkap' => $murid->nama_murid,
                'no_hp' => $murid->no_hp,
                'role' => 'murid',
                'is_active' => $isActive,
            ]);
        } else {
            // Generate fallback email/nik jika terjadi collision
            if ($existingUserByEmail || $existingUserByNik) {
                $uniqueSuffix = $murid->id ? $murid->id : uniqid();
                $email = 'murid'.$cleanNo.'_'.$uniqueSuffix.'@pkbmpikat.com';
                $nik = 'MRD'.$cleanNo.'_'.$uniqueSuffix;
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

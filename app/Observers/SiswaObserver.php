<?php

namespace App\Observers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SiswaObserver
{
    /**
     * Handle the Siswa "created" event.
     * Otomatis membuatkan akun User dengan role 'siswa' dan menghubungkan user_id.
     */
    public function created(Siswa $siswa): void
    {
        $this->syncUserAccount($siswa);
    }

    /**
     * Handle the Siswa "updated" event.
     * Otomatis menyinkronkan nama, kontak, dan status aktif akun User.
     */
    public function updated(Siswa $siswa): void
    {
        $this->syncUserAccount($siswa);
    }

    /**
     * Handle the Siswa "deleted" event.
     * Saat siswa diarsipkan / soft delete, nonaktifkan akun User agar tidak bisa login.
     */
    public function deleted(Siswa $siswa): void
    {
        if ($siswa->user) {
            $siswa->user->updateQuietly([
                'is_active' => 0,
            ]);
        }
    }

    /**
     * Handle the Siswa "restored" event.
     * Saat siswa dipulihkan, aktifkan kembali akun jika status_siswa adalah aktif.
     */
    public function restored(Siswa $siswa): void
    {
        if ($siswa->user && $siswa->status_siswa === 'aktif') {
            $siswa->user->updateQuietly([
                'is_active' => 1,
            ]);
        }
    }

    /**
     * Sinkronisasi atau buat akun User untuk model Siswa.
     */
    protected function syncUserAccount(Siswa $siswa): void
    {
        $isActive = (! $siswa->status_siswa || $siswa->status_siswa === 'aktif') ? 1 : 0;

        // 1. Jika sudah punya user_id dan user ditemukan, update data user
        if ($siswa->user_id) {
            $user = User::find($siswa->user_id);
            if ($user) {
                $user->updateQuietly([
                    'nama_lengkap' => $siswa->nama_siswa,
                    'no_hp' => $siswa->no_hp,
                    'is_active' => $isActive,
                ]);

                return;
            }
        }

        // 2. Buat NIK dan Email yang unik dan ramah pengguna
        $cleanNo = preg_replace('/[^a-zA-Z0-9]/', '', (string) $siswa->no_absen) ?: (string) $siswa->id;
        $nik = 'SW'.str_pad($cleanNo, 4, '0', STR_PAD_LEFT);
        $email = 'siswa'.strtolower($cleanNo).'@pkbmpikat.com';

        // Cek apakah NIK atau Email sudah terpakai oleh user lain
        $existingUserByEmail = User::where('email', $email)->first();
        $existingUserByNik = User::where('nik', $nik)->first();

        if ($existingUserByEmail && $existingUserByEmail->role === 'siswa') {
            $user = $existingUserByEmail;
            $user->updateQuietly([
                'nama_lengkap' => $siswa->nama_siswa,
                'no_hp' => $siswa->no_hp,
                'is_active' => $isActive,
            ]);
        } elseif ($existingUserByNik && $existingUserByNik->role === 'siswa') {
            $user = $existingUserByNik;
            $user->updateQuietly([
                'nama_lengkap' => $siswa->nama_siswa,
                'no_hp' => $siswa->no_hp,
                'is_active' => $isActive,
            ]);
        } else {
            // Generate fallback email/nik jika terjadi collision
            if ($existingUserByEmail || $existingUserByNik) {
                $uniqueSuffix = $siswa->id ? $siswa->id : uniqid();
                $email = 'siswa'.$cleanNo.'_'.$uniqueSuffix.'@pkbmpikat.com';
                $nik = 'SW'.$cleanNo.'_'.$uniqueSuffix;
            }

            $user = User::create([
                'nik' => $nik,
                'nama_lengkap' => $siswa->nama_siswa,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'no_hp' => $siswa->no_hp,
                'is_active' => $isActive,
            ]);
        }

        // 3. Hubungkan foreign key user_id ke model Siswa tanpa mentrigger infinite loop
        $siswa->user_id = $user->id;
        $siswa->saveQuietly();
    }
}

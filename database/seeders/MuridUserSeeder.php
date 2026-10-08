<?php

namespace Database\Seeders;

use App\Models\kelas;
use App\Models\Murid;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MuridUserSeeder extends Seeder
{
    /**
     * Run the database seeds for Murid user accounts.
     */
    public function run(): void
    {
        $passwordHash = Hash::make('password123');

        // Pastikan ada kelas default jika tabel kelas masih kosong
        $defaultKelasId = kelas::first()?->id;
        if (! $defaultKelasId) {
            $defaultKelas = kelas::create([
                'nama_kelas' => 'Paket C - Kelas 10',
                'tingkat' => '10',
            ]);
            $defaultKelasId = $defaultKelas->id;
        }

        // 1. Akun Murid 1: Zeldi (no_absen 001)
        $murid1 = Murid::where('no_absen', '001')->first() ?? Murid::first();
        if (! $murid1) {
            $murid1 = Murid::create([
                'no_absen' => '001',
                'nama_murid' => 'Zeldi',
                'no_hp' => '081234567001',
                'nama_wali' => 'Bambang Pratama',
                'kelas_id' => $defaultKelasId,
                'status_murid' => 'aktif',
            ]);
        } else {
            $murid1->update([
                'nama_murid' => 'Zeldi',
            ]);
        }

        $user1 = $murid1->user ?? User::where('email', 'murid001@pkbmpikat.com')->orWhere('nik', 'MRD0001')->orWhere('email', 'siswa001@pkbmpikat.com')->first();
        if ($user1) {
            $user1->update([
                'nik' => 'MRD0001',
                'email' => 'murid001@pkbmpikat.com',
                'nama_lengkap' => $murid1->nama_murid,
                'password' => $passwordHash,
                'role' => 'murid',
                'no_hp' => $murid1->no_hp ?? '081234567001',
                'is_active' => 1,
            ]);
        } else {
            $user1 = User::create([
                'nik' => 'MRD0001',
                'email' => 'murid001@pkbmpikat.com',
                'nama_lengkap' => $murid1->nama_murid,
                'password' => $passwordHash,
                'role' => 'murid',
                'no_hp' => $murid1->no_hp ?? '081234567001',
                'is_active' => 1,
            ]);
        }
        $murid1->update(['user_id' => $user1->id]);

        // 2. Akun Murid 2: Siti Nurhaliza (no_absen 002)
        $murid2 = Murid::where('no_absen', '002')->first();
        if (! $murid2) {
            $murid2 = Murid::create([
                'no_absen' => '002',
                'nama_murid' => 'Siti Nurhaliza',
                'no_hp' => '081234567002',
                'nama_wali' => 'Suryani',
                'kelas_id' => $defaultKelasId,
                'status_murid' => 'aktif',
            ]);
        }

        $user2 = $murid2->user ?? User::where('email', 'murid002@pkbmpikat.com')->orWhere('nik', 'MRD0002')->orWhere('email', 'siswa002@pkbmpikat.com')->first();
        if ($user2) {
            $user2->update([
                'nik' => 'MRD0002',
                'email' => 'murid002@pkbmpikat.com',
                'nama_lengkap' => $murid2->nama_murid,
                'password' => $passwordHash,
                'role' => 'murid',
                'no_hp' => $murid2->no_hp ?? '081234567002',
                'is_active' => 1,
            ]);
        } else {
            $user2 = User::create([
                'nik' => 'MRD0002',
                'email' => 'murid002@pkbmpikat.com',
                'nama_lengkap' => $murid2->nama_murid,
                'password' => $passwordHash,
                'role' => 'murid',
                'no_hp' => $murid2->no_hp ?? '081234567002',
                'is_active' => 1,
            ]);
        }
        $murid2->update(['user_id' => $user2->id]);

        // 3. Hubungkan murid lainnya yang belum memiliki akun jika ada
        $unlinkedMurids = Murid::whereNull('user_id')->limit(10)->get();
        foreach ($unlinkedMurids as $sw) {
            $email = 'murid'.$sw->id.'@pkbmpikat.com';
            $nik = 'MRD'.str_pad($sw->id, 5, '0', STR_PAD_LEFT);
            $nama = $sw->nama_murid ?: ('Murid PKBM '.$sw->id);

            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'nik' => $nik,
                    'nama_lengkap' => $nama,
                    'password' => Hash::make('password123'),
                    'role' => 'murid',
                    'no_hp' => $sw->no_hp,
                    'is_active' => 1,
                ]
            );

            $sw->update(['user_id' => $u->id]);
        }
    }
}

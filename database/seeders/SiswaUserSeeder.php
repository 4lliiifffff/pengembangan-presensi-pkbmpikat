<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaUserSeeder extends Seeder
{
    /**
     * Run the database seeds for Siswa user accounts.
     */
    public function run(): void
    {
        // 1. Akun Siswa 1: Ahmad Rizky Pratama (no_absen 001)
        $siswa1 = Siswa::where('no_absen', '001')->first() ?? Siswa::first();
        if ($siswa1) {
            $user1 = User::updateOrCreate(
                ['email' => 'siswa@pkbmpikat.com'],
                [
                    'nik' => 'SW202601',
                    'nama_lengkap' => $siswa1->nama_siswa,
                    'password' => Hash::make('password123'),
                    'role' => 'siswa',
                    'no_hp' => $siswa1->no_hp ?? '081234567001',
                    'is_active' => 1,
                ]
            );
            $siswa1->update(['user_id' => $user1->id]);
        }

        // 2. Akun Siswa 2: Siti Nurhaliza (no_absen 002)
        $siswa2 = Siswa::where('no_absen', '002')->first();
        if ($siswa2) {
            $user2 = User::updateOrCreate(
                ['email' => 'siswa2@pkbmpikat.com'],
                [
                    'nik' => 'SW202602',
                    'nama_lengkap' => $siswa2->nama_siswa,
                    'password' => Hash::make('password123'),
                    'role' => 'siswa',
                    'no_hp' => $siswa2->no_hp ?? '081234567002',
                    'is_active' => 1,
                ]
            );
            $siswa2->update(['user_id' => $user2->id]);
        }

        // 3. Hubungkan siswa lainnya yang belum memiliki akun jika ada
        $unlinkedSiswas = Siswa::whereNull('user_id')->limit(10)->get();
        foreach ($unlinkedSiswas as $sw) {
            $email = 'siswa'.$sw->id.'@pkbmpikat.com';
            $nik = 'SW'.str_pad($sw->id, 5, '0', STR_PAD_LEFT);
            $nama = $sw->nama_siswa ?: ('Siswa PKBM '.$sw->id);

            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'nik' => $nik,
                    'nama_lengkap' => $nama,
                    'password' => Hash::make('password123'),
                    'role' => 'siswa',
                    'no_hp' => $sw->no_hp,
                    'is_active' => 1,
                ]
            );

            $sw->update(['user_id' => $u->id]);
        }
    }
}

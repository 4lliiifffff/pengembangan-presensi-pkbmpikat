<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Kepala_Sekolah;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('password123');

        // 1. Akun Admin Default
        $adminUser = User::updateOrCreate(
            ['nik' => '12345'],
            [
                'nama_lengkap' => 'Admin Presensi',
                'email' => 'admin@pkbmpikat.com',
                'password' => $passwordHash,
                'role' => 'admin',
                'no_hp' => '081234567890',
                'is_active' => 1,
            ]
        );

        if (Schema::hasTable('admins')) {
            Admin::updateOrCreate(
                ['user_id' => $adminUser->id],
                [
                    'nik' => $adminUser->nik,
                    'nama_lengkap' => $adminUser->nama_lengkap,
                    'email' => $adminUser->email,
                    'no_hp' => $adminUser->no_hp,
                ]
            );
        }

        // 2. Akun Tutor Default
        $tutorUser = User::updateOrCreate(
            ['nik' => '10001'],
            [
                'nama_lengkap' => 'Budi Santoso, S.Pd (Tutor)',
                'email' => 'tutor@pkbmpikat.com',
                'password' => $passwordHash,
                'role' => 'tutor',
                'no_hp' => '082345678901',
                'is_active' => 1,
            ]
        );

        if (Schema::hasTable('tutors')) {
            Tutor::updateOrCreate(
                ['user_id' => $tutorUser->id],
                [
                    'nik' => $tutorUser->nik,
                    'nama_lengkap' => $tutorUser->nama_lengkap,
                    'jabatan' => 'Tutor Pengajar',
                    'email' => $tutorUser->email,
                    'alamat' => 'Jl. Pikat No. 123, Kota',
                    'no_hp' => $tutorUser->no_hp,
                ]
            );
        }

        // 3. Akun Kepala Sekolah Default
        $kepsekUser = User::updateOrCreate(
            ['nik' => '99001'],
            [
                'nama_lengkap' => 'Dr. H. Ahmad Dahlan, M.Pd (Kepsek)',
                'email' => 'kepsek@pkbmpikat.com',
                'password' => $passwordHash,
                'role' => 'kepala_sekolah',
                'no_hp' => '083456789012',
                'is_active' => 1,
            ]
        );

        if (Schema::hasTable('kepala__sekolahs')) {
            Kepala_Sekolah::updateOrCreate(
                ['user_id' => $kepsekUser->id],
                [
                    'nik' => $kepsekUser->nik,
                    'nama_lengkap' => $kepsekUser->nama_lengkap,
                    'email' => $kepsekUser->email,
                    'no_hp' => $kepsekUser->no_hp,
                ]
            );
        }
    }
}

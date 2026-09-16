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

        // 1. Akun Admin
        $admins = [
            [
                'nik' => '12345',
                'nama_lengkap' => 'Admin Presensi PKBM',
                'email' => 'admin@pkbmpikat.com',
                'no_hp' => '081234567890',
            ],
            [
                'nik' => '12346',
                'nama_lengkap' => 'Admin Operasional',
                'email' => 'admin2@pkbmpikat.com',
                'no_hp' => '081234567891',
            ],
        ];

        foreach ($admins as $adm) {
            $user = User::updateOrCreate(
                ['nik' => $adm['nik']],
                [
                    'nama_lengkap' => $adm['nama_lengkap'],
                    'email' => $adm['email'],
                    'password' => $passwordHash,
                    'role' => 'admin',
                    'no_hp' => $adm['no_hp'],
                    'is_active' => 1,
                ]
            );

            if (Schema::hasTable('admins')) {
                Admin::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nik' => $user->nik,
                        'nama_lengkap' => $user->nama_lengkap,
                        'email' => $user->email,
                        'no_hp' => $user->no_hp,
                    ]
                );
            }
        }

        // 2. Akun Tutor
        $tutors = [
            [
                'nik' => '10001',
                'nama_lengkap' => 'Budi Santoso, S.Pd',
                'email' => 'tutor@pkbmpikat.com',
                'jabatan' => 'Tutor Matematika & IPA',
                'alamat' => 'Jl. Pikat No. 123, Surabaya',
                'no_hp' => '082345678901',
            ],
            [
                'nik' => '10002',
                'nama_lengkap' => 'Siti Aminah, M.Pd',
                'email' => 'tutor2@pkbmpikat.com',
                'jabatan' => 'Tutor Bahasa & IPS',
                'alamat' => 'Jl. Dharmahusada No. 45, Surabaya',
                'no_hp' => '082345678902',
            ],
            [
                'nik' => '10003',
                'nama_lengkap' => 'Agus Prasetyo, S.Si',
                'email' => 'tutor3@pkbmpikat.com',
                'jabatan' => 'Tutor Vokasi & Komputer',
                'alamat' => 'Jl. Rungkut Madya No. 88, Surabaya',
                'no_hp' => '082345678903',
            ],
        ];

        foreach ($tutors as $tut) {
            $tutorUser = User::updateOrCreate(
                ['nik' => $tut['nik']],
                [
                    'nama_lengkap' => $tut['nama_lengkap'],
                    'email' => $tut['email'],
                    'password' => $passwordHash,
                    'role' => 'tutor',
                    'no_hp' => $tut['no_hp'],
                    'is_active' => 1,
                ]
            );

            if (Schema::hasTable('tutors')) {
                Tutor::updateOrCreate(
                    ['user_id' => $tutorUser->id],
                    [
                        'nik' => $tutorUser->nik,
                        'nama_lengkap' => $tutorUser->nama_lengkap,
                        'jabatan' => $tut['jabatan'],
                        'email' => $tutorUser->email,
                        'alamat' => $tut['alamat'],
                        'no_hp' => $tutorUser->no_hp,
                    ]
                );
            }
        }

        // 3. Akun Kepala Sekolah
        $kepsekUser = User::updateOrCreate(
            ['nik' => '99001'],
            [
                'nama_lengkap' => 'Dr. H. Ahmad Dahlan, M.Pd',
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

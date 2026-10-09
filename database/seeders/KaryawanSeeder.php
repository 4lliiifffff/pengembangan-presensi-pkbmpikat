<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\KepalaSekolah;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds for 9 Karyawan/Tutor + Tutor Dani pendamping.
     */
    public function run(): void
    {
        $karyawanList = [
            // 1. Dara Agstiana, M.Pd. (Kepala Sekolah / Pegawai Tetap >6 Tahun)
            [
                'nik' => 'KRY001',
                'nama_lengkap' => 'Dara Agstiana, M.Pd.',
                'email' => 'dara.agstiana@pkbmpikat.com',
                'role' => 'kepala_sekolah',
                'no_hp' => '081234567801',
                'jabatan' => 'Kepala PKBM / Pegawai Tetap',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 2. Natasya Dwi Putri (Admin / Pegawai Tetap >2 Tahun)
            [
                'nik' => 'KRY002',
                'nama_lengkap' => 'Natasya Dwi Putri',
                'email' => 'natasya.putri@pkbmpikat.com',
                'role' => 'admin',
                'no_hp' => '081234567802',
                'jabatan' => 'Staf Administrasi / Pegawai Tetap',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 3. Sendi Lestari, S.Pd. (Kak Sendi - Tutor Tetap >6 Tahun)
            [
                'nik' => 'TTR001',
                'nama_lengkap' => 'Sendi Lestari, S.Pd.',
                'email' => 'sendi.lestari@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567803',
                'jabatan' => 'Tutor / Pegawai Tetap',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 4. Muhammad Nailul Fathul Wafiq, S.Hub.Int. (Kak Nailul - Tutor Kontrak >1 Tahun)
            [
                'nik' => 'TTR002',
                'nama_lengkap' => 'Muhammad Nailul Fathul Wafiq, S.Hub.Int.',
                'email' => 'nailul.wafiq@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567804',
                'jabatan' => 'Tutor / Pegawai Kontrak',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 5. Wina Karlina, S.Pd. (Kak Wina - Tutor Kontrak >1 Tahun)
            [
                'nik' => 'TTR003',
                'nama_lengkap' => 'Wina Karlina, S.Pd.',
                'email' => 'wina.karlina@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567805',
                'jabatan' => 'Tutor / Pegawai Kontrak',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 6. Inka Purnamasari, S.Hum. (Kak Inka - Tutor Kontrak >1 Tahun)
            [
                'nik' => 'TTR004',
                'nama_lengkap' => 'Inka Purnamasari, S.Hum.',
                'email' => 'inka.purnamasari@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567806',
                'jabatan' => 'Tutor / Pegawai Kontrak',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 7. Lestary Jungjunan Effendy, S.Li. (Kak Tary - Tutor Kontrak >1 Tahun)
            [
                'nik' => 'TTR005',
                'nama_lengkap' => 'Lestary Jungjunan Effendy, S.Li.',
                'email' => 'lestary.effendy@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567807',
                'jabatan' => 'Tutor / Pegawai Kontrak',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 8. Maura Husnu Salma, S.Sos. (Kak Maura - Tutor Kontrak 1 Bulan)
            [
                'nik' => 'TTR006',
                'nama_lengkap' => 'Maura Husnu Salma, S.Sos.',
                'email' => 'maura.salma@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567808',
                'jabatan' => 'Tutor / Pegawai Kontrak',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 9. Nova Arzazi, S.Pd. (Kak Nova - Tutor Kontrak 1 Bulan)
            [
                'nik' => 'TTR007',
                'nama_lengkap' => 'Nova Arzazi, S.Pd.',
                'email' => 'nova.arzazi@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567809',
                'jabatan' => 'Tutor / Pegawai Kontrak',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            // 10. Kak Dani (Tutor Pendamping Peserta Didik)
            [
                'nik' => 'TTR008',
                'nama_lengkap' => 'Dani Pratama, S.Pd.',
                'email' => 'dani.pratama@pkbmpikat.com',
                'role' => 'tutor',
                'no_hp' => '081234567810',
                'jabatan' => 'Tutor / Pendamping',
                'alamat' => 'Bandung, Jawa Barat',
            ],
        ];

        foreach ($karyawanList as $item) {
            // 1. Buat / Perbarui Akun User Login
            $user = User::updateOrCreate(
                ['nik' => $item['nik']],
                [
                    'nama_lengkap' => $item['nama_lengkap'],
                    'email' => $item['email'],
                    'password' => Hash::make('password123'),
                    'role' => $item['role'],
                    'no_hp' => $item['no_hp'],
                    'is_active' => 1,
                ]
            );

            // 2. Sinkronkan ke Profil Tabel Terkait sesuai Role
            if ($item['role'] === 'tutor') {
                Tutor::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nik' => $item['nik'],
                        'nama_lengkap' => $item['nama_lengkap'],
                        'email' => $item['email'],
                        'jabatan' => $item['jabatan'],
                        'no_hp' => $item['no_hp'],
                        'alamat' => $item['alamat'],
                    ]
                );
            } elseif ($item['role'] === 'kepala_sekolah' && (Schema::hasTable('kepala_sekolah') || Schema::hasTable('kepala__sekolahs'))) {
                KepalaSekolah::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nik' => $item['nik'],
                        'nama_lengkap' => $item['nama_lengkap'],
                        'email' => $item['email'],
                        'no_hp' => $item['no_hp'],
                    ]
                );
            } elseif ($item['role'] === 'admin' && (Schema::hasTable('admin') || Schema::hasTable('admins'))) {
                Admin::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nik' => $item['nik'],
                        'nama_lengkap' => $item['nama_lengkap'],
                        'email' => $item['email'],
                        'no_hp' => $item['no_hp'],
                    ]
                );
            }
        }
    }
}

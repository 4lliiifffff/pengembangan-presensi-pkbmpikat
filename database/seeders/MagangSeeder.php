<?php

namespace Database\Seeders;

use App\Models\Magang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $magangs = [
            [
                'email' => 'magang@pkbmpikat.com',
                'nik' => 'MG202601',
                'nama_lengkap' => 'Rizky Pratama (Mahasiswa PKL)',
                'nim_nisn' => '22050974001',
                'asal_instansi' => 'Universitas Negeri Surabaya (UNESA)',
                'jurusan_prodi' => 'S1 Pendidikan Luar Sekolah',
                'no_hp' => '085712345678',
                'alamat' => 'Jl. Ketintang No. 12, Surabaya',
            ],
            [
                'email' => 'magang2@pkbmpikat.com',
                'nik' => 'MG202602',
                'nama_lengkap' => 'Nabila Putri Azzahra (Mahasiswa Magang)',
                'nim_nisn' => '22050974002',
                'asal_instansi' => 'Universitas Airlangga (UNAIR)',
                'jurusan_prodi' => 'D4 Manajemen Perkantoran Digital',
                'no_hp' => '085712345679',
                'alamat' => 'Jl. Airlangga No. 4, Surabaya',
            ],
        ];

        foreach ($magangs as $m) {
            $magangUser = User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'nik' => $m['nik'],
                    'nama_lengkap' => $m['nama_lengkap'],
                    'password' => Hash::make('password123'),
                    'role' => 'magang',
                    'no_hp' => $m['no_hp'],
                    'is_active' => 1,
                ]
            );

            Magang::updateOrCreate(
                ['user_id' => $magangUser->id],
                [
                    'nim_nisn' => $m['nim_nisn'],
                    'nama_lengkap' => $m['nama_lengkap'],
                    'asal_instansi' => $m['asal_instansi'],
                    'jurusan_prodi' => $m['jurusan_prodi'],
                    'tgl_mulai_magang' => Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString(),
                    'tgl_selesai_magang' => Carbon::now('Asia/Jakarta')->addMonths(3)->endOfMonth()->toDateString(),
                    'no_hp' => $m['no_hp'],
                    'alamat' => $m['alamat'],
                ]
            );
        }
    }
}

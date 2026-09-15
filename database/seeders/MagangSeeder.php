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
        $magangUser = User::updateOrCreate(
            ['email' => 'magang@pkbmpikat.com'],
            [
                'nik' => 'MG202601',
                'nama_lengkap' => 'Rizky Pratama (Mahasiswa PKL)',
                'password' => Hash::make('password123'),
                'role' => 'magang',
                'no_hp' => '085712345678',
                'is_active' => 1,
            ]
        );

        Magang::updateOrCreate(
            ['user_id' => $magangUser->id],
            [
                'nim_nisn' => '22050974001',
                'nama_lengkap' => 'Rizky Pratama (Mahasiswa PKL)',
                'asal_instansi' => 'Universitas Negeri Surabaya (UNESA)',
                'jurusan_prodi' => 'S1 Pendidikan Luar Sekolah',
                'tgl_mulai_magang' => Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString(),
                'tgl_selesai_magang' => Carbon::now('Asia/Jakarta')->addMonths(3)->endOfMonth()->toDateString(),
                'no_hp' => '085712345678',
                'alamat' => 'Jl. Ketintang No. 12, Surabaya',
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['nik' => '12345'],
            [
                'nama_lengkap' => 'Kak Tasya',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'no_hp' => '-',
                'is_active' => 1,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['nik' => '12345'],
            [
                'nama_lengkap' => 'Admin PKBM',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'no_hp' => '081511112222',
                'foto' => 'foto_karyawan/foto_1779208529_6a0c9151299c1.jpg',
                'is_active' => 1,
            ]
        );

        if (Schema::hasTable('admin') || Schema::hasTable('admins')) {
            Admin::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $user->nik,
                    'nama_lengkap' => $user->nama_lengkap,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'foto' => $user->foto,
                ]
            );
        }
    }
}

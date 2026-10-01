<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SimpikatIntegrationAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_simpikat_tutor_login_success_and_jit_provisions_tutor_profile(): void
    {
        $response = $this->post(route('login.process'), [
            'username' => 'tutor',
            'password' => '12345678',
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertNotNull($user);
        $this->assertSame('tutor', $user->role);

        // Verifikasi profil Tutor di-provisioning otomatis
        $tutorProfile = Tutor::where('user_id', $user->id)->first();
        $this->assertNotNull($tutorProfile);
        $this->assertSame($user->nama_lengkap, $tutorProfile->nama_lengkap);
    }

    public function test_simpikat_admin_login_success_and_jit_provisions_admin_profile(): void
    {
        $response = $this->post(route('login.process'), [
            'username' => 'admin',
            'password' => '12345678',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertNotNull($user);
        $this->assertSame('admin', $user->role);

        // Verifikasi profil Admin di-provisioning otomatis
        $adminProfile = Admin::where('user_id', $user->id)->first();
        $this->assertNotNull($adminProfile);
        $this->assertSame($user->nama_lengkap, $adminProfile->nama_lengkap);
    }

    public function test_simpikat_unsupported_role_is_rejected_with_warning(): void
    {
        $response = $this->post(route('login.process'), [
            'username' => 'orangtua',
            'password' => '12345678',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning', function ($warning) {
            return str_contains($warning, 'Orang Tua') && str_contains($warning, 'tidak memiliki akses');
        });

        $this->assertGuest();
    }

    public function test_simpikat_wrong_password_is_rejected(): void
    {
        $response = $this->post(route('login.process'), [
            'username' => 'tutor',
            'password' => 'password-yang-salah-123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning', 'Username/NIK atau password salah.');
        $this->assertGuest();
    }

    public function test_simpikat_siswa_login_accurately_provisions_jenjang_and_kelas(): void
    {
        // budisantoso terdaftar di SIMPIKAT sebagai siswa Paket C Tingkat 12
        $response = $this->post(route('login.process'), [
            'username' => 'budisantoso',
            'password' => '12345678',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertNotNull($user);
        $this->assertSame('siswa', $user->role);

        // Verifikasi profil Siswa di-provisioning dan kelasnya sesuai (Paket C - Kelas 12)
        $siswaProfile = Siswa::where('user_id', $user->id)->first();
        $this->assertNotNull($siswaProfile);
        $this->assertNotNull($siswaProfile->kelas);
        $this->assertSame('Paket C - Kelas 12', $siswaProfile->kelas->nama_kelas);
        $this->assertSame('12', $siswaProfile->kelas->tingkat);
        $this->assertSame('paket_c', $siswaProfile->kelas->jenjang_paket);

        // Verifikasi relasi Tutor pembimbing dari rombel SIMPIKAT terhubung
        $this->assertNotNull($siswaProfile->tutor_id);
        $this->assertNotNull($siswaProfile->tutor);
        $this->assertSame('Tutor Pengajar Vokasi', $siswaProfile->tutor->nama_lengkap);

        // Verifikasi status non-ABK
        $this->assertFalse((bool) $siswaProfile->is_abk);
    }

    public function test_simpikat_abk_siswa_login_accurately_detects_abk_condition(): void
    {
        // Simulasikan data siswa ABK di SIMPIKAT
        $simpikatDb = DB::connection('simpikat');
        $originalPeserta = $simpikatDb->table('peserta_didik')->where('pengguna_id', 12)->first();

        $simpikatDb->table('peserta_didik')->where('pengguna_id', 12)->update([
            'kelainan_jasmani' => 'Autisme Spektrum Ringan (ABK)',
            'diterima_di_kelompok' => 'Kelas Inklusi Khusus',
        ]);

        try {
            $response = $this->post(route('login.process'), [
                'username' => 'sitinurhaliza',
                'password' => '12345678',
            ]);

            $response->assertRedirect(route('siswa.dashboard'));
            $this->assertAuthenticated();

            $user = auth()->user();
            $this->assertNotNull($user);

            $siswaProfile = Siswa::where('user_id', $user->id)->first();
            $this->assertNotNull($siswaProfile);
            $this->assertTrue((bool) $siswaProfile->is_abk);
        } finally {
            if ($originalPeserta) {
                $simpikatDb->table('peserta_didik')->where('pengguna_id', 12)->update([
                    'kelainan_jasmani' => $originalPeserta->kelainan_jasmani,
                    'diterima_di_kelompok' => $originalPeserta->diterima_di_kelompok,
                ]);
            }
        }
    }

    public function test_local_presensi_user_login_still_works_seamlessly(): void
    {
        $user = User::factory()->create([
            'nik' => '987654321',
            'email' => 'lokal_tutor@pkbmpikat.com',
            'role' => 'tutor',
            'password' => 'password123',
            'is_active' => 1,
        ]);

        Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'jabatan' => 'Tutor Tetap',
            'no_hp' => '081234567890',
        ]);

        $response = $this->post(route('login.process'), [
            'username' => '987654321',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}

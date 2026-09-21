<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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

<?php

namespace Tests\Feature;

use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleFlowAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_are_redirected_to_correct_dashboards_on_login(): void
    {
        // 1. Admin login
        $admin = User::factory()->create([
            'role' => 'admin',
            'nik' => '11111',
            'password' => bcrypt('password123'),
        ]);

        $responseAdmin = $this->post(route('login.process'), [
            'username' => '11111',
            'password' => 'password123',
        ]);
        $responseAdmin->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        // 2. Kepsek login
        $kepsek = User::factory()->create([
            'role' => 'kepala_sekolah',
            'nik' => '22222',
            'password' => bcrypt('password123'),
        ]);

        $responseKepsek = $this->post(route('login.process'), [
            'username' => '22222',
            'password' => 'password123',
        ]);
        $responseKepsek->assertRedirect(route('kepsek.dashboard'));

        $this->post(route('logout'));

        // 3. Tutor login
        $tutor = User::factory()->create([
            'role' => 'tutor',
            'nik' => '33333',
            'password' => bcrypt('password123'),
        ]);

        $responseTutor = $this->post(route('login.process'), [
            'username' => '33333',
            'password' => 'password123',
        ]);
        $responseTutor->assertRedirect(route('tutor.dashboard'));
    }

    public function test_unauthorized_role_access_redirects_gracefully_to_user_own_dashboard(): void
    {
        // Kepsek accessing Admin dashboard
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $responseKepsek = $this->actingAs($kepsek)->get(route('admin.dashboard'));
        $responseKepsek->assertRedirect(route('kepsek.dashboard'));
        $responseKepsek->assertSessionHas('warning', 'Anda tidak memiliki hak akses ke halaman tersebut.');

        // Tutor accessing Kepsek dashboard
        $tutor = User::factory()->create(['role' => 'tutor']);
        $responseTutor = $this->actingAs($tutor)->get(route('kepsek.dashboard'));
        $responseTutor->assertRedirect(route('tutor.dashboard'));
        $responseTutor->assertSessionHas('warning', 'Anda tidak memiliki hak akses ke halaman tersebut.');

        // Admin accessing Tutor dashboard
        $admin = User::factory()->create(['role' => 'admin']);
        $responseAdmin = $this->actingAs($admin)->get(route('tutor.dashboard'));
        $responseAdmin->assertRedirect(route('admin.dashboard'));
    }

    public function test_presensi_pages_render_correct_role_specific_bottom_navigation(): void
    {
        // 1. Kepsek accessing /kepsek/presensi
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $resKepsek = $this->actingAs($kepsek)->get(route('kepsek.presensi'));
        $resKepsek->assertStatus(200);
        $resKepsek->assertSee(route('kepsek.dashboard'));
        $resKepsek->assertDontSee(route('tutor.riwayat'));

        // 2. Admin accessing /admin/presensi
        $admin = User::factory()->create(['role' => 'admin']);
        $resAdmin = $this->actingAs($admin)->get(route('admin.presensi'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee(route('admin.dashboard'));

        // 3. Tutor accessing /tutor/presensi
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => $tutorUser->nik,
            'nama_tutor' => 'Tutor Nav Test',
            'nama_lengkap' => $tutorUser->nama_lengkap,
            'email' => $tutorUser->email,
            'no_hp' => '081234567899',
        ]);
        $resTutor = $this->actingAs($tutorUser)->get(route('tutor.presensi'));
        $resTutor->assertStatus(200);
        $resTutor->assertSee(route('tutor.dashboard'));
    }

    public function test_authenticated_users_accessing_root_or_login_are_forwarded_to_their_dashboard(): void
    {
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $response = $this->actingAs($kepsek)->get(route('login'));
        $response->assertRedirect(route('kepsek.dashboard'));
    }
}

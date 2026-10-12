<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_login_via_post_for_each_role_authenticates_and_redirects(): void
    {
        $roles = [
            'admin' => route('admin.dashboard'),
            'kepala_sekolah' => route('kepsek.dashboard'),
            'tutor' => route('tutor.dashboard'),
            'magang' => route('magang.dashboard'),
            'siswa' => route('murid.dashboard'),
        ];

        foreach ($roles as $role => $expectedRoute) {
            // Seed a user for this role
            User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->post(route('login.quick'), [
                'role' => $role,
            ]);

            $response->assertRedirect($expectedRoute);
            $this->assertAuthenticated();
            $this->assertEquals($role, auth()->user()->role);

            // Log out before next iteration
            $this->post(route('logout'));
            $this->assertGuest();
        }
    }

    public function test_quick_login_via_get_for_each_role_authenticates_and_redirects(): void
    {
        $roles = [
            'admin' => route('admin.dashboard'),
            'kepala_sekolah' => route('kepsek.dashboard'),
            'tutor' => route('tutor.dashboard'),
            'magang' => route('magang.dashboard'),
            'siswa' => route('murid.dashboard'),
        ];

        foreach ($roles as $role => $expectedRoute) {
            User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->get(route('login.quick.get', ['role' => $role]));

            $response->assertRedirect($expectedRoute);
            $this->assertAuthenticated();
            $this->assertEquals($role, auth()->user()->role);

            $this->post(route('logout'));
            $this->assertGuest();
        }
    }

    public function test_quick_login_switches_role_while_already_authenticated(): void
    {
        User::whereIn('role', ['admin', 'tutor'])->delete();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $tutor = User::factory()->create(['role' => 'tutor', 'is_active' => true]);

        // Login as Admin
        $this->actingAs($admin);
        $this->assertEquals('admin', auth()->user()->role);

        // Quick switch to Tutor
        $response = $this->post(route('login.quick'), ['role' => 'tutor']);
        $response->assertRedirect(route('tutor.dashboard'));
        $this->assertEquals('tutor', auth()->user()->role);
        $this->assertEquals($tutor->id, auth()->id());

        // Quick switch via GET to Admin
        $response = $this->get(route('login.quick.get', ['role' => 'admin']));
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertEquals('admin', auth()->user()->role);
        $this->assertEquals($admin->id, auth()->id());
    }

    public function test_quick_login_creates_fallback_user_if_database_empty_for_role(): void
    {
        User::whereIn('role', ['murid', 'siswa'])->delete();
        $this->assertEquals(0, User::whereIn('role', ['murid', 'siswa'])->count());

        $response = $this->post(route('login.quick'), ['role' => 'murid']);

        $response->assertRedirect(route('murid.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('murid', auth()->user()->role);
        $this->assertEquals(1, User::whereIn('role', ['murid', 'siswa'])->count());
    }

    public function test_quick_login_validates_invalid_role(): void
    {
        $response = $this->post(route('login.quick'), ['role' => 'hacker_role']);
        $response->assertSessionHasErrors(['role']);

        $responseGet = $this->get(route('login.quick.get', ['role' => 'invalid_role']));
        $responseGet->assertRedirect(route('login'));
        $responseGet->assertSessionHas('warning');
    }

    public function test_login_page_displays_quick_login_testing_panel(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Mode Pengujian');
        $response->assertSee('quickLoginSection');
        $response->assertSee('AKSES PENGUJIAN CEPAT (DEMO ROLE)');
    }

    public function test_quick_login_is_blocked_and_hidden_when_disabled(): void
    {
        config(['auth.quick_login' => false]);

        $this->assertFalse(User::isQuickLoginEnabled());

        // Halaman login tidak menampilkan panel pengujian
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertDontSee('AKSES PENGUJIAN CEPAT (DEMO ROLE)');
        $response->assertDontSee('fillLoginForm');

        // Request POST quick login ditolak dengan 403
        $postResponse = $this->post(route('login.quick'), ['role' => 'admin']);
        $postResponse->assertStatus(403);

        // Request GET quick login dialihkan dengan pesan peringatan
        $getResponse = $this->get(route('login.quick.get', ['role' => 'admin']));
        $getResponse->assertRedirect(route('login'));
        $getResponse->assertSessionHas('warning');
    }

    public function test_quick_login_artisan_commands_execute_successfully(): void
    {
        $this->artisan('quick-login:status')->assertExitCode(0);
        $this->artisan('quick-login', ['action' => 'status'])->assertExitCode(0);
    }
}

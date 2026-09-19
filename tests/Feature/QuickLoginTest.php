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
            'siswa' => route('siswa.dashboard'),
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
            'siswa' => route('siswa.dashboard'),
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
        $this->assertEquals(0, User::where('role', 'siswa')->count());

        $response = $this->post(route('login.quick'), ['role' => 'siswa']);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('siswa', auth()->user()->role);
        $this->assertEquals(1, User::where('role', 'siswa')->count());
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
        $response->assertSee('Kak Tasya');
        $response->assertSee('Bu Dara');
        $response->assertSee('Kak Tari');
        $response->assertSee('Alif');
        $response->assertSee('Zeldi (001)');
    }
}

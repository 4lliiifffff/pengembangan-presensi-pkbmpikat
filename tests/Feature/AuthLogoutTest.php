<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout_via_post_request(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_can_logout_via_get_request(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}

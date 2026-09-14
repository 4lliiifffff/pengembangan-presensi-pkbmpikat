<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('1234567890|127.0.0.1');
        RateLimiter::clear('apiuser@example.com|127.0.0.1');
    }

    public function test_web_login_throttles_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'nik' => '1234567890',
            'email' => 'tutor@pikat.org',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'username' => '1234567890',
                'password' => 'wrong-password',
            ]);
            $response->assertStatus(302);
            $response->assertSessionHas('warning', 'Username/NIK atau password salah.');
        }

        $response = $this->post('/login', [
            'username' => '1234567890',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('warning', 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.');
    }

    public function test_api_login_throttles_and_returns_json_429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'apiuser@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'status' => false,
            'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.',
        ]);
    }
}

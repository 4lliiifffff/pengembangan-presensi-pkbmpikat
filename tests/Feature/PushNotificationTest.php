<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_vapid_public_key(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);

        $response = $this->actingAs($user)->getJson(route('push.key'));

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'publicKey',
            ]);
    }

    public function test_user_can_subscribe_push_notification(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);

        $payload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-test-endpoint-12345',
            'keys' => [
                'p256dh' => 'BNcRdreALRF8M+CvOUK6K0ZODS3wK9wxU2A86bbGKUKeNPQgOsqf3ZNy1ldACK2xDwpnvJJ10Y5hUzRvvBug/zs=',
                'auth' => 'tBHItJI5svbpez7KI4CCXg==',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $response = $this->actingAs($user)->postJson(route('push.subscribe'), $payload);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-test-endpoint-12345',
        ]);
    }

    public function test_user_can_unsubscribe_push_notification(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-test-endpoint-to-remove',
            'public_key' => 'fake-key',
            'auth_token' => 'fake-auth',
        ]);

        $response = $this->actingAs($user)->postJson(route('push.unsubscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-test-endpoint-to-remove',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-test-endpoint-to-remove',
        ]);
    }

    public function test_send_absen_reminder_command_runs_successfully(): void
    {
        $tutor = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);

        $this->artisan('presensi:send-reminder', ['--tutor_id' => $tutor->id])
            ->assertSuccessful();
    }
}

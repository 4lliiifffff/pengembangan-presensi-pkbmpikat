<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePasswordValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_password_submission_returns_clear_distinct_attribute_errors(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->actingAs($user)->patch(route('profil.password'), [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'current_password' => 'kata sandi saat ini wajib diisi.',
            'password' => 'kata sandi baru wajib diisi.',
        ]);
    }

    public function test_incorrect_current_password_returns_current_password_rule_error(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->actingAs($user)->patch(route('profil.password'), [
            'current_password' => 'WrongOldPassword',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ]);

        $response->assertSessionHasErrors([
            'current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);
    }

    public function test_unconfirmed_new_password_returns_confirmation_error(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->actingAs($user)->patch(route('profil.password'), [
            'current_password' => 'Secret123!',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'DifferentSecret123!',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);
    }

    public function test_valid_password_update_succeeds(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->actingAs($user)->patch(route('profil.password'), [
            'current_password' => 'Secret123!',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Kata sandi berhasil diperbarui!');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecret123!', $user->password));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Tutor;
use App\Models\User;
use App\Services\SimpikatAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SimpikatSsoAuthTest extends TestCase
{
    use DatabaseTransactions;

    protected SimpikatAuthService $ssoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ssoService = app(SimpikatAuthService::class);
    }

    /**
     * Pastikan token SSO yang sah untuk akun Tutor SIMPIKAT berhasil login otomatis
     * dan mengarahkan ke dashboard Tutor.
     */
    public function test_valid_sso_token_authenticates_simpikat_tutor_and_redirects_to_tutor_dashboard(): void
    {
        $token = $this->ssoService->generateSsoToken('tutor', 'presensi', 120);

        $response = $this->get(route('sso.simpikat', ['token' => $token]));

        $response->assertRedirect(route('tutor.dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertNotNull($user);
        $this->assertSame('tutor', $user->role);

        // Verifikasi profil Tutor otomatis dibuat / disinkronkan
        $tutorProfile = Tutor::where('user_id', $user->id)->first();
        $this->assertNotNull($tutorProfile);
        $this->assertSame($user->nama_lengkap, $tutorProfile->nama_lengkap);
    }

    /**
     * Pastikan token SSO yang sah untuk akun Admin SIMPIKAT berhasil login otomatis
     * dan mengarahkan ke dashboard Admin.
     */
    public function test_valid_sso_token_authenticates_simpikat_admin_and_redirects_to_admin_dashboard(): void
    {
        $token = $this->ssoService->generateSsoToken('admin', 'presensi', 120);

        $response = $this->get(route('sso.simpikat', ['token' => $token]));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertNotNull($user);
        $this->assertSame('admin', $user->role);

        $adminProfile = Admin::where('user_id', $user->id)->first();
        $this->assertNotNull($adminProfile);
        $this->assertSame($user->nama_lengkap, $adminProfile->nama_lengkap);
    }

    /**
     * Pastikan token SSO yang telah kedaluwarsa (expired) ditolak.
     */
    public function test_expired_sso_token_is_rejected_and_redirects_to_login(): void
    {
        // Token dengan masa berlaku negatif (sudah lampau)
        $token = $this->ssoService->generateSsoToken('tutor', 'presensi', -30);

        $response = $this->get(route('sso.simpikat', ['token' => $token]));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHas('warning', function ($msg) {
            return str_contains($msg, 'tidak valid atau telah kedaluwarsa');
        });
    }

    /**
     * Pastikan token SSO dengan signature yang dimanipulasi (tampered) ditolak.
     */
    public function test_tampered_signature_sso_token_is_rejected(): void
    {
        $token = $this->ssoService->generateSsoToken('tutor', 'presensi', 120);
        $tamperedToken = $token.'invalid';

        $response = $this->get(route('sso.simpikat', ['token' => $tamperedToken]));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHas('warning');
    }

    /**
     * Pastikan token SSO dengan target aplikasi yang salah (misal: 'lapor') ditolak di Presensi.
     */
    public function test_wrong_target_app_token_is_rejected(): void
    {
        $token = $this->ssoService->generateSsoToken('tutor', 'lapor', 120);

        $response = $this->get(route('sso.simpikat', ['token' => $token]));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHas('warning');
    }

    /**
     * Pastikan peran yang tidak didukung (misal: orang tua) ditolak dengan pesan peringatan.
     */
    public function test_unsupported_role_sso_is_rejected(): void
    {
        $token = $this->ssoService->generateSsoToken('orangtua', 'presensi', 120);

        $response = $this->get(route('sso.simpikat', ['token' => $token]));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHas('warning', function ($warning) {
            return str_contains($warning, 'Orang Tua') && str_contains($warning, 'tidak memiliki akses');
        });
    }

    /**
     * Pastikan pengguna yang sudah login dapat berpindah keluar ke SIMPIKAT dengan URL signed.
     */
    public function test_authenticated_user_can_redirect_outbound_to_simpikat(): void
    {
        $user = User::factory()->create([
            'role' => 'tutor',
            'nik' => 'TUTOR9999',
            'nama_lengkap' => 'Tutor Outbound Test',
        ]);

        $response = $this->actingAs($user)->get(route('sso.ke-simpikat'));

        $response->assertRedirect();
        $targetUrl = (string) $response->headers->get('Location');

        $this->assertStringContainsString('https://pkbmpikat.com/sso/presensi?token=', $targetUrl);

        // Ekstrak token dari URL dan verifikasi
        $parsedUrl = parse_url($targetUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $this->assertNotEmpty($queryParams['token'] ?? null);
        $extractedIdentifier = $this->ssoService->verifySsoToken($queryParams['token'], 'sim');
        $this->assertSame('TUTOR9999', $extractedIdentifier);
    }
}

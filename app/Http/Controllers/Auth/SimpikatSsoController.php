<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SimpikatAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * SimpikatSsoController - Pengendali Single Sign-On (SSO) Terpadu SIMPIKAT & Presensi
 *
 * Mengelola alur login otomatis masuk (inbound) dari portal SIMPIKAT menggunakan
 * HMAC-SHA256 One-Time Signed Token, serta menyediakan generator alur keluar (outbound)
 * untuk berpindah dari sistem Presensi kembali ke portal SIMPIKAT tanpa login ulang.
 */
class SimpikatSsoController extends Controller
{
    public function __construct(
        protected SimpikatAuthService $simpikatAuthService
    ) {}

    /**
     * Menangani permintaan masuk Single Sign-On (SSO) dari portal SIMPIKAT.
     *
     * Alur:
     * 1. Tangkap token dari query param 'token' (atau pasangan 'payload' dan 'signature')
     * 2. Verifikasi tanda tangan kriptografi HMAC-SHA256, target aplikasi, dan kedaluwarsa
     * 3. Ambil dan sinkronkan data pengguna SIMPIKAT (Just-In-Time Provisioning)
     * 4. Login sesi web Laravel dan regenerasi session ID (mencegah Session Fixation)
     * 5. Arahkan pengguna langsung ke dashboard yang sesuai dengan perannya
     */
    public function handle(Request $request): RedirectResponse
    {
        $token = (string) ($request->query('token') ?? $request->input('token') ?? '');

        // Dukungan jika token dikirim terpisah sebagai payload & signature
        if ($token === '' && $request->filled('payload') && $request->filled('signature')) {
            $token = $request->query('payload').'.'.$request->query('signature');
        }

        if ($token === '') {
            return redirect()->route('login')->with('warning', 'Token login SSO SIMPIKAT tidak ditemukan. Silakan akses kembali melalui portal SIMPIKAT.');
        }

        $expectedApp = (string) config('auth.simpikat_sso.app_name', 'presensi');
        $identifier = $this->simpikatAuthService->verifySsoToken($token, $expectedApp);

        if (! $identifier) {
            return redirect()->route('login')->with('warning', 'Tautan login otomatis dari SIMPIKAT tidak valid atau telah kedaluwarsa. Silakan ulangi dari portal SIMPIKAT.');
        }

        try {
            $user = $this->simpikatAuthService->authenticateBySimpikatIdentifier($identifier);
        } catch (ValidationException $e) {
            $messages = $e->validator->errors()->all();

            return redirect()->route('login')->with('warning', $messages[0] ?? 'Akun SIMPIKAT Anda belum memenuhi syarat akses presensi.');
        } catch (\Throwable $e) {
            Log::error('SimpikatSsoController Error: '.$e->getMessage(), [
                'identifier' => $identifier,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->with('warning', 'Terjadi kendala saat memproses login otomatis dari SIMPIKAT.');
        }

        if (! $user) {
            return redirect()->route('login')->with('warning', 'Pengguna SIMPIKAT tidak ditemukan.');
        }

        if (! $user->is_active) {
            return redirect()->route('login')->with('warning', 'Akun Anda dinonaktifkan. Silakan hubungi administrator.');
        }

        // Login ke sesi Laravel
        Auth::login($user);

        // Regenerasi sesi untuk mencegah serangan Session Fixation (keamanan standar OWASP)
        $request->session()->regenerate();

        // Arahkan ke dashboard berdasarkan peran pengguna
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard')->with('success', 'Login otomatis berhasil dari SIMPIKAT. Selamat datang!'),
            'tutor' => redirect()->route('tutor.dashboard')->with('success', 'Login otomatis berhasil dari SIMPIKAT. Selamat datang!'),
            'kepala_sekolah' => redirect()->route('kepsek.dashboard')->with('success', 'Login otomatis berhasil dari SIMPIKAT. Selamat datang!'),
            'magang' => redirect()->route('magang.dashboard')->with('success', 'Login otomatis berhasil dari SIMPIKAT. Selamat datang!'),
            'siswa' => redirect()->route('siswa.dashboard')->with('success', 'Login otomatis berhasil dari SIMPIKAT. Selamat datang!'),
            default => redirect()->route('login')->with('warning', 'Peran akun tidak dikenali untuk portal presensi.'),
        };
    }

    /**
     * Menghasilkan token SSO dan mengarahkan pengguna saat ini ke portal SIMPIKAT tanpa login ulang.
     */
    public function redirectToSimpikat(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Gunakan NIK atau Email atau ID pengguna sebagai identifier
        $identifier = ! empty($user->nik) ? $user->nik : (! empty($user->email) ? $user->email : (string) $user->id);

        $token = $this->simpikatAuthService->generateSsoToken($identifier, 'sim');
        $simpikatBaseUrl = rtrim((string) config('auth.simpikat_sso.simpikat_base_url', 'https://pkbmpikat.com'), '/');

        $targetUrl = $simpikatBaseUrl.'/sso/presensi?token='.urlencode($token);

        return redirect()->away($targetUrl);
    }
}

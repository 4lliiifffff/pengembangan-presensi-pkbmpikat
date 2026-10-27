<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Autentikasi Pengguna API (Mendukung Email, NIK, dan Username).
     */
    public function login(Request $request): JsonResponse
    {
        $loginId = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('nik') ?? ''));
        $password = (string) $request->input('password', '');

        if ($loginId === '' || $password === '') {
            return response()->json([
                'status' => false,
                'message' => 'login gagal',
            ], 401);
        }

        // Coba autentikasi via Email terlebih dahulu, kemudian via NIK
        $attempt = Auth::attempt(['email' => $loginId, 'password' => $password])
            || Auth::attempt(['nik' => $loginId, 'password' => $password]);

        if (! $attempt) {
            return response()->json([
                'status' => false,
                'message' => 'login gagal',
            ], 401);
        }

        $user = Auth::user();

        // Validasi status keaktifan akun pengguna
        if ($user && ! $user->is_active) {
            Auth::logout();

            return response()->json([
                'status' => false,
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ], 403);
        }

        $username = strtolower($loginId);
        $throttleKey = $username.'|'.$request->ip();
        RateLimiter::clear($throttleKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'login berhasil',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Dapatkan Profil Pengguna Terautentikasi (Sanctum).
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Cabut Token Akses Saat Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'logout berhasil',
        ]);
    }
}

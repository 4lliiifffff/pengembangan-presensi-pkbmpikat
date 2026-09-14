<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware — Middleware Otorisasi Berbasis Peran (Role-Based Access Control / RBAC)
 *
 * Middleware ini berfungsi sebagai "penjaga gerbang" (gate keeper) yang memastikan
 * bahwa hanya pengguna dengan peran (role) tertentu yang dapat mengakses route tertentu.
 *
 * ALUR KERJA:
 *   1. Setiap request yang masuk akan dicek apakah pengguna sudah login (terautentikasi).
 *   2. Jika belum login → redirect ke halaman login.
 *   3. Jika sudah login, cek apakah role pengguna ada dalam daftar role yang diizinkan.
 *   4. Jika role tidak cocok → tolak dengan HTTP 403 (Forbidden).
 *   5. Jika lolos semua pengecekan → lanjutkan request ke controller.
 *
 * PENGGUNAAN DI ROUTE (routes/web.php):
 *   Route::middleware(['auth', 'role:admin'])->...
 *   Route::middleware(['auth', 'role:tutor,kepala_sekolah'])->...
 *
 * SIDANG FAQ:
 *   Q: Apa perbedaan middleware 'auth' dan 'role'?
 *   A: 'auth' memverifikasi apakah pengguna sudah login (autentikasi).
 *      'role' memverifikasi apakah pengguna yang sudah login memiliki hak akses (otorisasi).
 *
 *   Q: Bagaimana cara mendaftarkan middleware ini?
 *   A: Didaftarkan di app/Http/Kernel.php atau bootstrap/app.php sebagai route middleware
 *      dengan alias 'role'.
 */
class RoleMiddleware
{
    /**
     * Menangani request yang masuk dan memvalidasi role pengguna.
     *
     * @param  Request  $request  Objek HTTP request dari browser pengguna
     * @param  Closure  $next  Fungsi untuk meneruskan request ke handler berikutnya
     * @param  string[]  ...$roles  Daftar role yang diizinkan (variadic: bisa lebih dari satu)
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Langkah 1: Cek apakah pengguna sudah login (terautentikasi)
        // Jika belum login, redirect ke halaman login
        if (! auth()->check()) {
            return redirect('/login');
        }

        // Langkah 2: Cek apakah role pengguna saat ini ada dalam daftar role yang diizinkan
        // Jika role tidak cocok, tolak akses dengan HTTP 403 Forbidden
        if (! in_array(auth()->user()->role, $roles)) {
            abort(403);
        }

        // Langkah 3: Semua pengecekan lolos, lanjutkan request ke controller tujuan
        return $next($request);
    }
}

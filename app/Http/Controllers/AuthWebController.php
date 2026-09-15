<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * AuthWebController — Controller untuk Proses Autentikasi (Login & Logout)
 *
 * Controller ini menangani seluruh proses autentikasi pengguna berbasis web,
 * termasuk validasi kredensial, penetapan sesi, dan pengalihan berdasarkan peran (role).
 *
 * FITUR UTAMA:
 *   - Login menggunakan NIK (Nomor Induk Karyawan) ATAU Email sebagai username
 *   - Pengalihan otomatis ke dashboard berdasarkan peran pengguna (admin/tutor/kepala_sekolah)
 *   - Fitur "Remember Me" untuk memperpanjang sesi login
 *   - Logout aman dengan invalidasi sesi dan regenerasi CSRF token
 *
 * SIDANG FAQ:
 *   Q: Mengapa login bisa menggunakan NIK dan email sekaligus?
 *   A: Karena sistem memiliki dua tipe pengguna: tutor lapangan yang lebih familiar
 *      dengan NIK, dan admin/kepala sekolah yang terbiasa dengan email. Fleksibilitas ini
 *      meningkatkan kemudahan penggunaan (usability) tanpa mengorbankan keamanan.
 *
 *   Q: Mengapa session di-regenerate setelah login berhasil?
 *   A: Untuk mencegah serangan Session Fixation, yaitu serangan di mana penyerang
 *      menggunakan session ID yang sama sebelum dan sesudah login. Regenerasi session
 *      memastikan session ID baru dibuat setelah autentikasi berhasil.
 *
 *   Q: Mengapa password tidak disimpan dalam bentuk teks biasa (plaintext)?
 *   A: Password di-hash menggunakan bcrypt (melalui Laravel Auth). Ini memastikan bahwa
 *      meskipun database bocor, password asli pengguna tetap tidak bisa diketahui.
 */
class AuthWebController extends Controller
{
    /**
     * Memproses request login dari form halaman login.
     *
     * Alur proses:
     *  1. Validasi input (username & password wajib diisi)
     *  2. Coba login dengan NIK terlebih dahulu
     *  3. Jika gagal dengan NIK, coba dengan email (fallback)
     *  4. Jika semua gagal, kembalikan pesan error
     *  5. Jika berhasil, regenerasi session dan redirect ke dashboard sesuai role
     *
     * @param  Request  $request  Data form yang dikirim (username, password, remember)
     * @return RedirectResponse
     */
    public function process(Request $request)
    {
        // Validasi input: username dan password wajib ada dan berupa string
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Percobaan login: coba dengan NIK terlebih dahulu,
        // jika gagal, fallback ke email (menggunakan operator OR / ||)
        // Parameter ketiga adalah "remember me" (dari checkbox di form)
        $attempt =
            Auth::attempt([
                'nik' => $credentials['username'],
                'password' => $credentials['password'],
            ], $request->boolean('remember')) ||
            Auth::attempt([
                'email' => $credentials['username'],
                'password' => $credentials['password'],
            ], $request->boolean('remember'));

        // Jika login gagal (NIK dan email keduanya tidak cocok)
        if (! $attempt) {
            return back()
                ->withInput($request->only('username')) // Pertahankan username di form agar tidak perlu mengetik ulang
                ->with('warning', 'Username/NIK atau password salah.');
        }

        // Bersihkan counter RateLimiter karena login berhasil
        $throttleKey = strtolower((string) $credentials['username']).'|'.$request->ip();
        RateLimiter::clear($throttleKey);

        // Regenerasi session ID untuk mencegah Session Fixation Attack
        // Ini adalah praktik keamanan standar (OWASP recommendation)
        $request->session()->regenerate();

        // Ambil role pengguna yang baru saja login
        $role = Auth::user()?->role;

        // Redirect ke dashboard yang sesuai berdasarkan role
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Login berhasil. Selamat datang!');
        }

        if ($role === 'tutor') {
            return redirect()->route('tutor.dashboard')
                ->with('success', 'Login berhasil. Selamat datang!');
        }

        if ($role === 'kepala_sekolah') {
            return redirect()->route('kepsek.dashboard')
                ->with('success', 'Login berhasil. Selamat datang!');
        }

        if ($role === 'magang') {
            return redirect()->route('magang.dashboard')
                ->with('success', 'Login berhasil. Selamat datang!');
        }

        // Jika role tidak dikenali (misal: role baru yang belum di-handle),
        // logout paksa pengguna agar tidak terjebak dalam kondisi login tanpa akses
        Auth::logout();
        throw ValidationException::withMessages([
            'username' => 'Akun ini tidak memiliki akses.',
        ]);
    }

    /**
     * Memproses request logout dan mengakhiri sesi pengguna.
     *
     * Proses logout yang benar mencakup 3 langkah:
     *  1. Logout dari Auth Guard (hapus data user dari session)
     *  2. Invalidasi session saat ini (hapus semua data session)
     *  3. Regenerasi CSRF token (mencegah penyalahgunaan token lama)
     *
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        // Langkah 1: Hapus autentikasi pengguna dari session
        Auth::logout();

        // Langkah 2: Batalkan/hapus semua data session yang ada
        $request->session()->invalidate();

        // Langkah 3: Generate ulang CSRF token untuk keamanan
        // Ini mencegah serangan CSRF menggunakan token sesi yang sudah tidak valid
        $request->session()->regenerateToken();

        // Redirect ke halaman utama (halaman login)
        return redirect('/');
    }
}

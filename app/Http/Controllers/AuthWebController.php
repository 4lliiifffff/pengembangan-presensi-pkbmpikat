<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\kelas;
use App\Models\Kepala_Sekolah;
use App\Models\Magang;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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

        $username = trim((string) $credentials['username']);
        $password = (string) $credentials['password'];
        $remember = $request->boolean('remember');

        // 1. Percobaan login langsung dengan NIK (case-insensitive)
        $attempt = Auth::attempt([
            'nik' => $username,
            'password' => $password,
        ], $remember);

        // 2. Percobaan login dengan Email
        if (! $attempt) {
            $attempt = Auth::attempt([
                'email' => $username,
                'password' => $password,
            ], $remember);
        }

        // 3. Percobaan login variasi NIK / Nomor Absen Siswa (e.g. SW001, sw001, 001, SW0001, dll.)
        if (! $attempt) {
            $cleanNo = preg_replace('/^sw/i', '', $username);
            $cleanNo = ltrim($cleanNo, '0') ?: $cleanNo;
            $padded3 = str_pad($cleanNo, 3, '0', STR_PAD_LEFT);
            $padded4 = str_pad($cleanNo, 4, '0', STR_PAD_LEFT);

            // Cari di tabel Siswa
            $siswa = Siswa::where('no_absen', $username)
                ->orWhere('no_absen', $cleanNo)
                ->orWhere('no_absen', $padded3)
                ->orWhere('no_absen', $padded4)
                ->first();

            if ($siswa && $siswa->user_id) {
                $attempt = Auth::attempt([
                    'id' => $siswa->user_id,
                    'password' => $password,
                ], $remember);
            }

            // Jika belum ketemu, cari di tabel Users dengan variasi NIK
            if (! $attempt) {
                $userSiswa = User::where('role', 'siswa')
                    ->where(function ($q) use ($username, $cleanNo, $padded3, $padded4) {
                        $q->where('nik', 'SW'.$padded3)
                            ->orWhere('nik', 'SW'.$padded4)
                            ->orWhere('nik', 'SW'.$cleanNo)
                            ->orWhere('nik', $username)
                            ->orWhere('email', 'siswa'.$cleanNo.'@pkbmpikat.com')
                            ->orWhere('email', 'siswa'.$padded3.'@pkbmpikat.com');
                    })->first();

                if ($userSiswa) {
                    $attempt = Auth::attempt([
                        'id' => $userSiswa->id,
                        'password' => $password,
                    ], $remember);
                }
            }
        }

        // Jika login gagal (tidak ditemukan atau password salah)
        if (! $attempt) {
            return back()
                ->withInput($request->only('username'))
                ->with('warning', 'Username/NIK atau password salah.');
        }

        // Cek status keaktifan akun pengguna
        if (Auth::check() && ! Auth::user()->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('username'))
                ->with('warning', 'Akun Anda dinonaktifkan. Silakan hubungi administrator.');
        }

        // Bersihkan counter RateLimiter karena login berhasil
        $throttleKey = strtolower($username).'|'.$request->ip();
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

        if ($role === 'siswa') {
            return redirect()->route('siswa.dashboard')
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

    /**
     * Quick Login untuk keperluan pengujian dan demonstrasi (Local / Testing / Debug mode).
     * Memungkinkan developer dan tester masuk instan ke akun peran tertentu.
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        if (! app()->environment('local', 'testing') && ! config('app.debug') && ! env('APP_QUICK_LOGIN', false)) {
            abort(403, 'Akses Quick Login hanya diperbolehkan pada lingkungan pengembangan atau pengujian.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,kepala_sekolah,tutor,magang,siswa'],
        ]);

        $role = (string) $validated['role'];
        $user = $this->resolveQuickLoginUser($role);

        Auth::login($user, true);
        $request->session()->regenerate();

        RateLimiter::clear(strtolower((string) $user->nik).'|'.$request->ip());
        RateLimiter::clear(strtolower((string) $user->email).'|'.$request->ip());

        return $this->getDashboardRedirectForRole($role, $user);
    }

    /**
     * Quick Login via GET rute langsung (/quick-login/{role}) untuk kenyamanan pengujian URL cepat.
     */
    public function quickLoginGet(Request $request, string $role): RedirectResponse
    {
        if (! app()->environment('local', 'testing') && ! config('app.debug') && ! env('APP_QUICK_LOGIN', false)) {
            abort(403, 'Akses Quick Login hanya diperbolehkan pada lingkungan pengembangan atau pengujian.');
        }

        if (! in_array($role, ['admin', 'kepala_sekolah', 'tutor', 'magang', 'siswa'], true)) {
            return redirect()->route('login')->with('warning', "Role pengujian '{$role}' tidak valid.");
        }

        $user = $this->resolveQuickLoginUser($role);

        Auth::login($user, true);
        $request->session()->regenerate();

        RateLimiter::clear(strtolower((string) $user->nik).'|'.$request->ip());
        RateLimiter::clear(strtolower((string) $user->email).'|'.$request->ip());

        return $this->getDashboardRedirectForRole($role, $user);
    }

    /**
     * Mencari akun pengguna representatif untuk role tertentu, dengan fallback otomatis jika database kosong.
     */
    protected function resolveQuickLoginUser(string $role): User
    {
        $user = User::where('role', $role)
            ->where('is_active', 1)
            ->oldest('id')
            ->first();

        if (! $user) {
            $user = User::where('role', $role)->oldest('id')->first();
        }

        if (! $user) {
            $user = $this->createFallbackQuickLoginUser($role);
        }

        if (! $user->is_active) {
            $user->update(['is_active' => 1]);
        }

        // Khusus role siswa, pastikan data siswa terhubung agar dashboard siswa tidak mengalihkan ke logout
        if ($role === 'siswa' && class_exists(Siswa::class) && Schema::hasTable('siswas')) {
            if (! $user->siswa) {
                $kelasId = class_exists(kelas::class) ? kelas::first()?->id : null;
                if (! $kelasId && class_exists(kelas::class) && Schema::hasTable('kelas')) {
                    $k = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'tingkat' => '10']);
                    $kelasId = $k->id;
                }
                Siswa::firstOrCreate(['user_id' => $user->id], [
                    'no_absen' => '001',
                    'nama_siswa' => $user->nama_lengkap,
                    'kelas_id' => $kelasId,
                    'status_siswa' => 'aktif',
                    'no_hp' => $user->no_hp ?? '081234567001',
                    'nama_wali' => 'Bambang Pratama',
                ]);
            }
        }

        return $user;
    }

    /**
     * Membuat akun pengujian darurat jika seeder belum dijalankan sama sekali.
     */
    protected function createFallbackQuickLoginUser(string $role): User
    {
        $passwordHash = Hash::make('password123');

        switch ($role) {
            case 'admin':
                $user = User::create([
                    'nik' => '12345',
                    'nama_lengkap' => 'Kak Tasya',
                    'email' => 'admin@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'admin',
                    'is_active' => 1,
                ]);
                if (class_exists(Admin::class) && Schema::hasTable('admins')) {
                    Admin::firstOrCreate(['user_id' => $user->id], [
                        'nik' => $user->nik,
                        'nama_lengkap' => $user->nama_lengkap,
                        'email' => $user->email,
                    ]);
                }

                return $user;

            case 'kepala_sekolah':
                $user = User::create([
                    'nik' => '99001',
                    'nama_lengkap' => 'Bu Dara',
                    'email' => 'kepsek@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'kepala_sekolah',
                    'is_active' => 1,
                ]);
                if (class_exists(Kepala_Sekolah::class) && Schema::hasTable('kepala__sekolahs')) {
                    Kepala_Sekolah::firstOrCreate(['user_id' => $user->id], [
                        'nik' => $user->nik,
                        'nama_lengkap' => $user->nama_lengkap,
                        'email' => $user->email,
                    ]);
                }

                return $user;

            case 'tutor':
                $user = User::create([
                    'nik' => '10001',
                    'nama_lengkap' => 'Kak Tari',
                    'email' => 'tutor@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'tutor',
                    'is_active' => 1,
                ]);
                if (class_exists(Tutor::class) && Schema::hasTable('tutors')) {
                    Tutor::firstOrCreate(['user_id' => $user->id], [
                        'nik' => $user->nik,
                        'nama_lengkap' => $user->nama_lengkap,
                        'jabatan' => 'Tutor Pembelajaran',
                        'email' => $user->email,
                    ]);
                }

                return $user;

            case 'magang':
                $user = User::create([
                    'nik' => 'MG202601',
                    'nama_lengkap' => 'Alif',
                    'email' => 'magang@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'magang',
                    'is_active' => 1,
                ]);
                if (class_exists(Magang::class) && Schema::hasTable('magangs')) {
                    Magang::firstOrCreate(['user_id' => $user->id], [
                        'nim_nisn' => '22050974001',
                        'nama_lengkap' => $user->nama_lengkap,
                        'asal_instansi' => 'UNESA',
                        'jurusan_prodi' => 'Pendidikan Luar Sekolah',
                    ]);
                }

                return $user;

            case 'siswa':
                $user = User::create([
                    'nik' => 'SW0001',
                    'nama_lengkap' => 'Zeldi',
                    'email' => 'siswa001@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'siswa',
                    'no_hp' => '081234567001',
                    'is_active' => 1,
                ]);
                $kelasId = class_exists(kelas::class) ? kelas::first()?->id : null;
                if (! $kelasId && class_exists(kelas::class) && Schema::hasTable('kelas')) {
                    $k = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'tingkat' => '10']);
                    $kelasId = $k->id;
                }
                if (class_exists(Siswa::class) && Schema::hasTable('siswas')) {
                    Siswa::firstOrCreate(['user_id' => $user->id], [
                        'no_absen' => '001',
                        'nama_siswa' => $user->nama_lengkap,
                        'kelas_id' => $kelasId,
                        'status_siswa' => 'aktif',
                        'no_hp' => '081234567001',
                        'nama_wali' => 'Bambang Pratama',
                    ]);
                }

                return $user;

            default:
                abort(404, "Role pengujian {$role} tidak dikenali.");
        }
    }

    /**
     * Menghasilkan pengalihan rute dashboard yang sesuai dengan peran pengguna.
     */
    protected function getDashboardRedirectForRole(string $role, User $user): RedirectResponse
    {
        $roleLabels = [
            'admin' => 'Administrator Presensi',
            'kepala_sekolah' => 'Kepala Sekolah',
            'tutor' => 'Tutor / Pendidik',
            'magang' => 'Mahasiswa Magang (PKL)',
            'siswa' => 'Siswa Homeschooling',
        ];

        $roleLabel = $roleLabels[$role] ?? ucfirst($role);
        $message = "Login Cepat Pengujian: Anda masuk sebagai {$user->nama_lengkap} ({$roleLabel}).";

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard')->with('success', $message),
            'kepala_sekolah' => redirect()->route('kepsek.dashboard')->with('success', $message),
            'tutor' => redirect()->route('tutor.dashboard')->with('success', $message),
            'magang' => redirect()->route('magang.dashboard')->with('success', $message),
            'siswa' => redirect()->route('siswa.dashboard')->with('success', $message),
            default => redirect('/')->with('success', $message),
        };
    }
}

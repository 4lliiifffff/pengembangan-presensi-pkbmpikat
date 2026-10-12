<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\kelas;
use App\Models\Kepala_Sekolah;
use App\Models\Magang;
use App\Models\Murid;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\SimpikatAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * AuthWebController - Controller untuk Proses Autentikasi (Login & Logout)
 *
 * Controller ini menangani seluruh proses autentikasi pengguna berbasis web,
 * termasuk validasi kredensial, penetapan sesi, dan pengalihan berdasarkan peran (role).
 *
 * FITUR UTAMA:
 *   - Login menggunakan NIK (Nomor Induk Karyawan) ATAU Email sebagai username
 *   - Integrasi Akun SIMPIKAT (Cross-Database Authentication & JIT Provisioning)
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
    public function __construct(
        protected SimpikatAuthService $simpikatAuthService
    ) {}

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

        // 3. Percobaan login variasi NIK / Nomor Absen Siswa/Murid (e.g. SW001, sw001, 001, SW0001, MRD001, siswa001@pkbmpikat.com, dll.)
        if (! $attempt) {
            $extracted = preg_replace('/(@pkbmpikat\.com)$/i', '', $username);
            $cleanNo = preg_replace('/^(sw|mrd|siswa|murid)/i', '', $extracted);
            $cleanNo = ltrim($cleanNo, '0') ?: $cleanNo;
            $padded3 = str_pad($cleanNo, 3, '0', STR_PAD_LEFT);
            $padded4 = str_pad($cleanNo, 4, '0', STR_PAD_LEFT);

            // Cari di tabel Murid / Siswa
            $murid = Murid::where('no_absen', $username)
                ->orWhere('no_absen', $extracted)
                ->orWhere('no_absen', $cleanNo)
                ->orWhere('no_absen', $padded3)
                ->orWhere('no_absen', $padded4)
                ->first();

            if ($murid && $murid->user_id) {
                $attempt = Auth::attempt([
                    'id' => $murid->user_id,
                    'password' => $password,
                ], $remember);
            }

            // Jika belum ketemu, cari di tabel Users dengan variasi NIK
            if (! $attempt) {
                $userMurid = User::whereIn('role', ['murid', 'siswa'])
                    ->where(function ($q) use ($username, $cleanNo, $padded3, $padded4) {
                        $q->where('nik', 'MRD'.$padded3)
                            ->orWhere('nik', 'MRD'.$padded4)
                            ->orWhere('nik', 'MRD'.$cleanNo)
                            ->orWhere('nik', 'SW'.$padded3)
                            ->orWhere('nik', 'SW'.$padded4)
                            ->orWhere('nik', 'SW'.$cleanNo)
                            ->orWhere('nik', $username)
                            ->orWhere('email', 'murid'.$cleanNo.'@pkbmpikat.com')
                            ->orWhere('email', 'murid'.$padded3.'@pkbmpikat.com')
                            ->orWhere('email', 'siswa'.$cleanNo.'@pkbmpikat.com')
                            ->orWhere('email', 'siswa'.$padded3.'@pkbmpikat.com');
                    })->first();

                if ($userMurid) {
                    $attempt = Auth::attempt([
                        'id' => $userMurid->id,
                        'password' => $password,
                    ], $remember);
                }
            }
        }

        // 4. Fallback ke Akun Sistem SIMPIKAT (Cross-Database Authentication & JIT Provisioning)
        if (! $attempt) {
            try {
                $simpikatUser = $this->simpikatAuthService->attemptSimpikatLogin($username, $password);
                if ($simpikatUser) {
                    Auth::login($simpikatUser, $remember);
                    $attempt = true;
                }
            } catch (ValidationException $e) {
                $errorMessage = $e->validator->errors()->first('username') ?: $e->getMessage();

                return back()
                    ->withInput($request->only('username'))
                    ->with('warning', $errorMessage);
            } catch (\Throwable $e) {
                // Catat log jika terjadi kendala tak terduga, lalu biarkan alur peringatan login standar berjalan
                Log::error('SIMPIKAT Auth Integration Exception: '.$e->getMessage());
            }
        }

        // Jika seluruh percobaan login gagal
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

        // Sinkronkan data terbaru dari SIMPIKAT jika akun terhubung (Auto-Sync on Login)
        if (Auth::check()) {
            $this->simpikatAuthService->syncUserFromSimpikat(Auth::user());
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

        if ($role === 'murid' || $role === 'siswa') {
            return redirect()->route('murid.dashboard')
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
            'role' => ['required', 'string', 'in:admin,kepala_sekolah,tutor,magang,siswa,murid'],
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

        if (! in_array($role, ['admin', 'kepala_sekolah', 'tutor', 'magang', 'siswa', 'murid'], true)) {
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
     * Mencari akun pengguna representatif untuk role tertentu dari database, dengan fallback otomatis jika kosong.
     */
    protected function resolveQuickLoginUser(string $role): User
    {
        $targetRoles = match ($role) {
            'murid', 'siswa' => ['murid', 'siswa'],
            default => [$role],
        };

        $user = User::whereIn('role', $targetRoles)
            ->where('is_active', 1)
            ->oldest('id')
            ->first();

        if (! $user) {
            $user = User::whereIn('role', $targetRoles)->oldest('id')->first();
        }

        if (! $user) {
            $user = $this->createFallbackQuickLoginUser($role);
        }

        if (! $user->is_active) {
            $user->update(['is_active' => 1]);
        }

        // Pastikan relasi profil murid/siswa tersedia
        if (in_array($role, ['murid', 'siswa'], true)) {
            if (! $user->murid && ! $user->siswa) {
                $kelasId = class_exists(kelas::class) ? kelas::first()?->id : null;
                if (! $kelasId && class_exists(kelas::class) && Schema::hasTable('kelas')) {
                    $k = kelas::create(['nama_kelas' => 'Paket A - Kelas 1', 'tingkat' => '1']);
                    $kelasId = $k->id;
                }
                if (class_exists(Murid::class) && Schema::hasTable((new Murid)->getTable())) {
                    Murid::firstOrCreate(['user_id' => $user->id], [
                        'no_absen' => 1,
                        'nama_murid' => $user->nama_lengkap,
                        'kelas_id' => $kelasId,
                        'status_murid' => 'aktif',
                        'no_hp' => $user->no_hp ?? '081234567001',
                        'nama_wali' => 'Bambang Pratama',
                    ]);
                }
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
                    'nik' => 'KRY002',
                    'nama_lengkap' => 'Natasya Dwi Putri',
                    'email' => 'admin@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'admin',
                    'is_active' => 1,
                ]);
                if (class_exists(Admin::class) && Schema::hasTable((new Admin)->getTable())) {
                    Admin::firstOrCreate(['user_id' => $user->id], [
                        'nik' => $user->nik,
                        'nama_lengkap' => $user->nama_lengkap,
                        'email' => $user->email,
                    ]);
                }

                return $user;

            case 'kepala_sekolah':
                $user = User::create([
                    'nik' => 'KRY001',
                    'nama_lengkap' => 'Dara Agstiana, M.Pd.',
                    'email' => 'dara.agstiana@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'kepala_sekolah',
                    'is_active' => 1,
                ]);
                if (class_exists(Kepala_Sekolah::class) && (Schema::hasTable((new Kepala_Sekolah)->getTable()) || Schema::hasTable('kepala__sekolahs'))) {
                    Kepala_Sekolah::firstOrCreate(['user_id' => $user->id], [
                        'nik' => $user->nik,
                        'nama_lengkap' => $user->nama_lengkap,
                        'email' => $user->email,
                    ]);
                }

                return $user;

            case 'tutor':
                $user = User::create([
                    'nik' => 'TTR001',
                    'nama_lengkap' => 'Sendi Lestari, S.Pd.',
                    'email' => 'sendi.lestari@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'tutor',
                    'is_active' => 1,
                ]);
                if (class_exists(Tutor::class) && Schema::hasTable((new Tutor)->getTable())) {
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
                    'nama_lengkap' => 'Rizky Pratama',
                    'email' => 'magang@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'magang',
                    'is_active' => 1,
                ]);
                if (class_exists(Magang::class) && Schema::hasTable((new Magang)->getTable())) {
                    Magang::firstOrCreate(['user_id' => $user->id], [
                        'nim_nisn' => '22050974001',
                        'nama_lengkap' => $user->nama_lengkap,
                        'asal_instansi' => 'UNESA',
                        'jurusan_prodi' => 'Pendidikan Luar Sekolah',
                    ]);
                }

                return $user;

            case 'siswa':
            case 'murid':
                $user = User::create([
                    'nik' => 'MRD0001',
                    'nama_lengkap' => 'Ari Tri Agusta',
                    'email' => 'murid1@pkbmpikat.com',
                    'password' => $passwordHash,
                    'role' => 'murid',
                    'no_hp' => '081234567001',
                    'is_active' => 1,
                ]);
                $kelasId = class_exists(kelas::class) ? kelas::first()?->id : null;
                if (! $kelasId && class_exists(kelas::class) && Schema::hasTable('kelas')) {
                    $k = kelas::create(['nama_kelas' => 'Paket A - Kelas 1', 'tingkat' => '1']);
                    $kelasId = $k->id;
                }
                if (class_exists(Murid::class) && Schema::hasTable((new Murid)->getTable())) {
                    Murid::firstOrCreate(['user_id' => $user->id], [
                        'no_absen' => 1,
                        'nama_murid' => $user->nama_lengkap,
                        'kelas_id' => $kelasId,
                        'status_murid' => 'aktif',
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
            'siswa' => 'Murid Homeschooling',
            'murid' => 'Murid Homeschooling',
        ];

        $roleLabel = $roleLabels[$role] ?? ucfirst($role);
        $message = "Login Cepat Pengujian: Anda masuk sebagai {$user->nama_lengkap} ({$roleLabel}).";

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard')->with('success', $message),
            'kepala_sekolah' => redirect()->route('kepsek.dashboard')->with('success', $message),
            'tutor' => redirect()->route('tutor.dashboard')->with('success', $message),
            'magang' => redirect()->route('magang.dashboard')->with('success', $message),
            'siswa', 'murid' => redirect()->route('murid.dashboard')->with('success', $message),
            default => redirect('/')->with('success', $message),
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Service SimpikatAuthService
 *
 * Menangani integrasi autentikasi akun dari sistem SIMPIKAT (db_simpikat)
 * ke sistem Presensi PKBM Pikat (pkbc5947_smart) melalui mekanisme:
 * 1. Cross-Database Lookup (pencarian pengguna di tabel pengguna SIMPIKAT)
 * 2. Verifikasi Kredensial via Bcrypt Hash::check()
 * 3. Pemetaan Peran (Role Mapping) & Kebijakan Hak Akses
 * 4. Just-In-Time (JIT) Provisioning (sinkronisasi tabel users dan profil tutor/siswa/admin)
 */
class SimpikatAuthService
{
    /**
     * Nama koneksi database SIMPIKAT di config/database.php.
     */
    protected string $connection = 'simpikat';

    /**
     * Percobaan autentikasi akun pengguna SIMPIKAT.
     *
     * @param  string  $identifier  Username, email, NUPTK, atau NISN/NIK
     * @param  string  $password  Kata sandi plain text
     * @return User|null Mengembalikan User lokal Presensi yang sudah disinkronkan, atau null jika tidak cocok
     *
     * @throws ValidationException Jika akun nonaktif atau perannya tidak diizinkan masuk ke Presensi
     */
    public function attemptSimpikatLogin(string $identifier, string $password): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '' || $password === '') {
            return null;
        }

        try {
            $simpikatUser = $this->findSimpikatPengguna($identifier);
        } catch (\Throwable $e) {
            Log::warning('SimpikatAuthService: Koneksi ke database SIMPIKAT gagal atau tabel belum siap.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $simpikatUser) {
            return null;
        }

        // 1. Verifikasi kecocokan kata sandi menggunakan Bcrypt
        $hashedPassword = (string) ($simpikatUser->kata_sandi ?? '');
        if ($hashedPassword === '' || ! Hash::check($password, $hashedPassword)) {
            return null;
        }

        // 2. Validasi peran akun (Role Validation)
        $peran = (string) ($simpikatUser->peran ?? '');
        if (in_array($peran, ['orang_tua', 'asesor'], true)) {
            $labelPeran = $peran === 'orang_tua' ? 'Orang Tua' : 'Asesor';
            throw ValidationException::withMessages([
                'username' => "Akun {$labelPeran} SIMPIKAT tidak memiliki akses ke portal presensi mandiri.",
            ]);
        }

        // 3. Validasi status akun (harus 'aktif')
        $status = (string) ($simpikatUser->status ?? 'aktif');
        if ($status !== 'aktif') {
            throw ValidationException::withMessages([
                'username' => "Akun SIMPIKAT Anda saat ini berstatus {$status}. Silakan hubungi administrator.",
            ]);
        }

        // 4. Lakukan Just-In-Time (JIT) Provisioning ke sistem Presensi
        return $this->provisionUserToPresensi($simpikatUser, $password);
    }

    /**
     * Mencari pengguna di database SIMPIKAT berdasarkan berbagai variasi identifier:
     * - nama_pengguna
     * - email
     * - nomor_nuptk
     * - nik / nisn / nomor_induk pada tabel peserta_didik (jika siswa)
     */
    protected function findSimpikatPengguna(string $identifier): ?object
    {
        $db = DB::connection($this->connection);

        // 1. Cari langsung di tabel pengguna (nama_pengguna atau email)
        $user = $db->table('pengguna')
            ->where('nama_pengguna', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if ($user) {
            return $user;
        }

        // 2. Cari berdasarkan nomor_nuptk (jika kolom nomor_nuptk ada)
        try {
            $user = $db->table('pengguna')
                ->where('nomor_nuptk', $identifier)
                ->first();

            if ($user) {
                return $user;
            }
        } catch (\Throwable) {
            // Abaikan jika kolom tidak ada
        }

        // 3. Cari di tabel peserta_didik (jika siswa memasukkan NISN/NIK/No Induk)
        try {
            $peserta = $db->table('peserta_didik')
                ->where('nik', $identifier)
                ->orWhere('nisn', $identifier)
                ->orWhere('nomor_induk', $identifier)
                ->first();

            if ($peserta && ! empty($peserta->pengguna_id)) {
                return $db->table('pengguna')
                    ->where('id', $peserta->pengguna_id)
                    ->first();
            }
        } catch (\Throwable) {
            // Abaikan jika tabel peserta_didik tidak tersedia
        }

        return null;
    }

    /**
     * Melakukan sinkronisasi atau pembuatan user di tabel lokal users dan tabel profil pendampingnya.
     */
    protected function provisionUserToPresensi(object $simpikatUser, string $plainPassword): User
    {
        $mappedRole = match ($simpikatUser->peran) {
            'super_admin', 'admin' => 'admin',
            'tutor' => 'tutor',
            'peserta_didik' => 'siswa',
            default => 'siswa',
        };

        $nik = $this->resolveNikForUser($simpikatUser, $mappedRole);

        // Cari user yang sudah ada berdasarkan email atau NIK
        $user = null;
        if (! empty($simpikatUser->email)) {
            $user = User::where('email', $simpikatUser->email)->first();
        }

        if (! $user) {
            $user = User::where('nik', $nik)->first();
        }

        if ($user) {
            // Sinkronisasi data terkini & password
            $user->nama_lengkap = (string) $simpikatUser->nama;
            $user->role = $mappedRole;
            $user->password = $plainPassword; // Akan otomatis di-hash oleh cast 'hashed'
            $user->is_active = 1;
            if (! empty($simpikatUser->email)) {
                $user->email = (string) $simpikatUser->email;
            }
            $user->save();
        } else {
            // Buat akun User baru
            $user = User::create([
                'nik' => $nik,
                'nama_lengkap' => (string) $simpikatUser->nama,
                'email' => ! empty($simpikatUser->email) ? (string) $simpikatUser->email : null,
                'password' => $plainPassword,
                'role' => $mappedRole,
                'is_active' => 1,
            ]);
        }

        // Buat atau sinkronkan profil domain pendukung sesuai role
        $this->provisionDomainProfile($user, $simpikatUser);

        return $user;
    }

    /**
     * Menghasilkan string NIK yang valid (unik, max 20 char) untuk tabel users.
     */
    protected function resolveNikForUser(object $simpikatUser, string $role): string
    {
        // 1. Jika tutor memiliki nomor_nuptk
        if (! empty($simpikatUser->nomor_nuptk)) {
            return substr((string) $simpikatUser->nomor_nuptk, 0, 20);
        }

        // 2. Jika peserta didik, periksa apakah ada NIK / NISN di tabel peserta_didik SIMPIKAT
        if ($role === 'siswa') {
            try {
                $peserta = DB::connection($this->connection)
                    ->table('peserta_didik')
                    ->where('pengguna_id', $simpikatUser->id)
                    ->first();

                if ($peserta) {
                    if (! empty($peserta->nik)) {
                        return substr((string) $peserta->nik, 0, 20);
                    }
                    if (! empty($peserta->nisn)) {
                        return substr('SW'.(string) $peserta->nisn, 0, 20);
                    }
                    if (! empty($peserta->nomor_induk)) {
                        return substr('SW'.(string) $peserta->nomor_induk, 0, 20);
                    }
                }
            } catch (\Throwable) {
                // Abaikan jika tabel tidak ditemukan
            }

            return 'SW'.str_pad((string) $simpikatUser->id, 4, '0', STR_PAD_LEFT);
        }

        if ($role === 'admin') {
            return 'ADM'.str_pad((string) $simpikatUser->id, 4, '0', STR_PAD_LEFT);
        }

        return 'TUTOR'.str_pad((string) $simpikatUser->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Memastikan tabel detail profil (tutor, siswa, admin) memiliki record yang sesuai
     * agar relasi Eloquent di dashboard tidak melempar null reference error.
     */
    protected function provisionDomainProfile(User $user, object $simpikatUser): void
    {
        if ($user->role === 'admin' && Schema::hasTable('admin')) {
            Admin::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $user->nik,
                    'nama_lengkap' => $user->nama_lengkap,
                    'email' => $user->email ?? ($user->nik.'@pkbmpikat.com'),
                    'no_hp' => $user->no_hp ?? '081234567890',
                ]
            );
        } elseif ($user->role === 'tutor' && Schema::hasTable('tutor')) {
            $jabatan = ! empty($simpikatUser->gelar_akademik)
                ? 'Tutor ('.$simpikatUser->gelar_akademik.')'
                : 'Tutor Pengajar SIMPIKAT';

            Tutor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $user->nik,
                    'nama_lengkap' => $user->nama_lengkap,
                    'jabatan' => $jabatan,
                    'email' => $user->email ?? ($user->nik.'@pkbmpikat.com'),
                    'alamat' => $simpikatUser->institusi_pendidikan ?? 'PKBM Pintar Berbakat',
                    'no_hp' => $user->no_hp ?? '081234567890',
                ]
            );
        } elseif ($user->role === 'siswa' && Schema::hasTable('siswa')) {
            // Dapatkan atau buat kelas fallback jika belum ada
            $kelasId = $this->resolveFallbackKelasId();

            // Coba ambil detail dari peserta_didik SIMPIKAT jika ada
            $peserta = null;
            try {
                $peserta = DB::connection($this->connection)
                    ->table('peserta_didik')
                    ->where('pengguna_id', $simpikatUser->id)
                    ->first();
            } catch (\Throwable) {
                // Abaikan jika query gagal
            }

            $noAbsen = 'SW'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
            $namaSiswa = $user->nama_lengkap;
            $namaWali = 'Orang Tua / Wali';
            $noHp = '081234567890';

            if ($peserta) {
                $noAbsen = (string) ($peserta->nomor_induk ?: ($peserta->nisn ?: $noAbsen));
                $namaSiswa = (string) ($peserta->nama_lengkap ?: $namaSiswa);
                $namaWali = (string) ($peserta->nama_wali ?: ($peserta->nama_ayah ?: $namaWali));
                $noHp = (string) ($peserta->nomor_wa ?: ($peserta->telepon_peserta ?: $noHp));
            }

            Siswa::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'no_absen' => substr($noAbsen, 0, 20),
                    'nama_siswa' => $namaSiswa,
                    'status_siswa' => 'aktif',
                    'kelas_id' => $kelasId,
                    'nama_wali' => substr($namaWali, 0, 50),
                    'no_hp' => substr($noHp, 0, 15),
                    'is_abk' => false,
                ]
            );
        }
    }

    /**
     * Memastikan ada ID kelas yang tersedia untuk relasi tabel siswa.
     */
    protected function resolveFallbackKelasId(): int
    {
        $kelas = Kelas::first();
        if ($kelas) {
            return (int) $kelas->id;
        }

        $kelasBaru = Kelas::create([
            'nama_kelas' => 'Kelas Reguler (SIMPIKAT)',
            'tingkat' => 'Umum',
            'keterangan' => 'Kelas otomatis untuk warga belajar sinkronisasi SIMPIKAT',
        ]);

        return (int) $kelasBaru->id;
    }
}

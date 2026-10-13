<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use App\Models\Murid;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Service SimpikatAuthService
 *
 * Menangani integrasi autentikasi akun dari sistem SIMPIKAT (db_simpikat)
 * ke sistem Presensi PKBM Pikat (pkbc5947_smart) melalui mekanisme:
 * 1. Cross-Database Lookup (pencarian pengguna di tabel pengguna SIMPIKAT)
 * 2. Verifikasi Kredensial via Bcrypt Hash::check()
 * 3. Pemetaan Peran (Role Mapping) & Kebijakan Hak Akses
 * 4. Just-In-Time (JIT) Provisioning (sinkronisasi tabel users dan profil tutor/murid/admin)
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
     * Autentikasi dan sinkronisasi akun SIMPIKAT berdasarkan identifier (untuk alur SSO tanpa input password).
     *
     * @param  string  $identifier  Username, email, NUPTK, atau NISN/NIK
     *
     * @throws ValidationException Jika akun nonaktif atau perannya tidak diizinkan masuk ke Presensi
     */
    public function authenticateBySimpikatIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        try {
            $simpikatUser = $this->findSimpikatPengguna($identifier);
        } catch (\Throwable $e) {
            Log::warning('SimpikatAuthService: Gagal mengakses koneksi database SIMPIKAT pada SSO.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $simpikatUser) {
            return null;
        }

        // 1. Validasi peran akun (Role Validation)
        $peran = (string) ($simpikatUser->peran ?? '');
        if (in_array($peran, ['orang_tua', 'asesor'], true)) {
            $labelPeran = $peran === 'orang_tua' ? 'Orang Tua' : 'Asesor';
            throw ValidationException::withMessages([
                'username' => "Akun {$labelPeran} SIMPIKAT tidak memiliki akses ke portal presensi mandiri.",
            ]);
        }

        // 2. Validasi status akun (harus 'aktif')
        $status = (string) ($simpikatUser->status ?? 'aktif');
        if ($status !== 'aktif') {
            throw ValidationException::withMessages([
                'username' => "Akun SIMPIKAT Anda saat ini berstatus {$status}. Silakan hubungi administrator.",
            ]);
        }

        // 3. Lakukan JIT Provisioning (tanpa overwrite password jika user sudah ada)
        return $this->provisionUserToPresensi($simpikatUser, null);
    }

    /**
     * Buat One-Time Signed Token SSO untuk komunikasi antar aplikasi ekosistem PKBM Pikat.
     *
     * @param  string  $identifier  Username, email, atau NIK pengguna
     * @param  string  $targetApp  Kode identifikasi aplikasi tujuan (default: 'sim')
     * @param  int|null  $ttl  Waktu hidup token dalam detik (default dari config atau 120s)
     */
    public function generateSsoToken(string $identifier, string $targetApp = 'sim', ?int $ttl = null): string
    {
        $secret = (string) config('auth.simpikat_sso.secret', config('app.key'));
        $ttl = $ttl ?? (int) config('auth.simpikat_sso.ttl', 120);

        $payloadData = [
            'identifier' => trim($identifier),
            'app' => $targetApp,
            'nonce' => bin2hex(random_bytes(8)),
            'exp' => time() + $ttl,
        ];

        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payloadData, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $payloadEncoded, $secret);

        return $payloadEncoded.'.'.$signature;
    }

    /**
     * Verifikasi One-Time Signed Token SSO dari sistem luar (misal: SIMPIKAT).
     *
     * @param  string  $token  Token bertanda tangan kriptografis format: {payloadEncoded}.{signature}
     * @param  string  $expectedApp  Target aplikasi yang diharapkan (default: 'presensi')
     * @return string|null Mengembalikan identifier pengguna jika valid, atau null jika gagal/kedaluwarsa/tamper
     */
    public function verifySsoToken(string $token, string $expectedApp = 'presensi'): ?string
    {
        $secret = (string) config('auth.simpikat_sso.secret', config('app.key'));

        $parts = explode('.', trim($token));
        if (count($parts) !== 2) {
            return null;
        }

        [$payloadEncoded, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payloadEncoded, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('SimpikatAuthService: SSO signature tidak cocok.');

            return null;
        }

        $decodedJson = base64_decode(strtr($payloadEncoded, '-_', '+/'));
        if (! $decodedJson) {
            return null;
        }

        try {
            $data = json_decode($decodedJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        // Pastikan target aplikasi sesuai
        if (($data['app'] ?? '') !== $expectedApp) {
            Log::warning('SimpikatAuthService: SSO target app tidak sesuai.', [
                'expected' => $expectedApp,
                'actual' => $data['app'] ?? null,
            ]);

            return null;
        }

        // Periksa waktu kedaluwarsa token
        $expiresAt = (int) ($data['exp'] ?? 0);
        if ($expiresAt < time()) {
            Log::info('SimpikatAuthService: SSO token sudah kedaluwarsa.', [
                'expires_at' => $expiresAt,
                'now' => time(),
            ]);

            return null;
        }

        $identifier = trim((string) ($data['identifier'] ?? ''));

        return $identifier !== '' ? $identifier : null;
    }

    /**
     * Mencari pengguna di database SIMPIKAT berdasarkan berbagai variasi identifier:
     * - nama_pengguna
     * - email
     * - nomor_nuptk
     * - nik / nisn / nomor_induk pada tabel peserta_didik (jika murid/siswa)
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

        // 3. Cari di tabel peserta_didik (jika murid memasukkan NISN/NIK/No Induk)
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
    protected function provisionUserToPresensi(object $simpikatUser, ?string $plainPassword = null): User
    {
        $mappedRole = match ($simpikatUser->peran) {
            'super_admin', 'admin', 'kepala_sekolah' => 'admin',
            'tutor' => 'tutor',
            'peserta_didik', 'murid', 'siswa' => 'murid',
            default => 'murid',
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
            // Sinkronisasi data terkini & password (jika plainPassword disediakan)
            $user->nama_lengkap = (string) $simpikatUser->nama;
            $user->role = $mappedRole;
            if ($plainPassword !== null && $plainPassword !== '') {
                $user->password = $plainPassword; // Akan otomatis di-hash oleh cast 'hashed'
            }
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
                'password' => ($plainPassword !== null && $plainPassword !== '') ? $plainPassword : Str::random(32),
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

        // 2. Jika murid / peserta didik, periksa apakah ada NIK / NISN di tabel peserta_didik SIMPIKAT
        if ($role === 'murid' || $role === 'siswa') {
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
                        return substr('MRD'.(string) $peserta->nisn, 0, 20);
                    }
                    if (! empty($peserta->nomor_induk)) {
                        return substr('MRD'.(string) $peserta->nomor_induk, 0, 20);
                    }
                }
            } catch (\Throwable) {
                // Abaikan jika tabel tidak ditemukan
            }

            return 'MRD'.str_pad((string) $simpikatUser->id, 4, '0', STR_PAD_LEFT);
        }

        if ($role === 'admin') {
            return 'ADM'.str_pad((string) $simpikatUser->id, 4, '0', STR_PAD_LEFT);
        }

        return 'TUTOR'.str_pad((string) $simpikatUser->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Memastikan tabel detail profil (tutor, murid, admin) memiliki record yang sesuai
     * agar relasi Eloquent di dashboard tidak melempar null reference error.
     */
    protected function provisionDomainProfile(User $user, object $simpikatUser): void
    {
        if ($user->role === 'admin' && (Schema::hasTable('admins') || Schema::hasTable('admin'))) {
            Admin::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $user->nik,
                    'nama_lengkap' => $user->nama_lengkap,
                    'email' => $user->email ?? ($user->nik.'@pkbmpikat.com'),
                    'no_hp' => $user->no_hp ?? '081234567890',
                ]
            );
        } elseif ($user->role === 'tutor' && (Schema::hasTable('tutors') || Schema::hasTable('tutor'))) {
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
        } elseif (($user->role === 'murid' || $user->role === 'siswa') && (Schema::hasTable('murid') || Schema::hasTable('siswa') || Schema::hasTable('siswas'))) {
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

            // Dapatkan ID kelas yang sesuai berdasarkan data jenjang & tingkat di SIMPIKAT
            $kelasId = $this->resolveKelasForSimpikatPeserta($peserta);

            // Dapatkan ID Tutor pembimbing yang sesuai dari relasi Rombel SIMPIKAT
            $tutorId = $this->resolveTutorForSimpikatPeserta($peserta);

            // Deteksi otomatis status ABK / Inklusi
            $isAbk = $this->resolveIsAbkForSimpikatPeserta($peserta);

            $noAbsen = 'MRD'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
            $namaMurid = $user->nama_lengkap;
            $namaWali = 'Orang Tua / Wali';
            $noHp = '081234567890';

            if ($peserta) {
                $noAbsen = (string) ($peserta->nomor_induk ?: ($peserta->nisn ?: $noAbsen));
                $namaMurid = (string) ($peserta->nama_lengkap ?: $namaMurid);
                $namaWali = (string) ($peserta->nama_wali ?: ($peserta->nama_ayah ?: $namaWali));
                $noHp = (string) ($peserta->nomor_wa ?: ($peserta->telepon_peserta ?: $noHp));
            }

            $muridPayload = [
                'no_absen' => substr($noAbsen, 0, 20),
                'nama_murid' => $namaMurid,
                'nama_siswa' => $namaMurid,
                'status_murid' => 'aktif',
                'status_siswa' => 'aktif',
                'kelas_id' => $kelasId,
                'nama_wali' => substr($namaWali, 0, 50),
                'no_hp' => substr($noHp, 0, 15),
                'is_abk' => $isAbk,
            ];

            if ($tutorId !== null) {
                $muridPayload['tutor_id'] = $tutorId;
            }

            Murid::updateOrCreate(
                ['user_id' => $user->id],
                $muridPayload
            );
        }
    }

    /**
     * Menemukan ID Tutor di Presensi berdasarkan relasi Rombel pembimbing di SIMPIKAT.
     */
    protected function resolveTutorForSimpikatPeserta(?object $peserta): ?int
    {
        if (! $peserta) {
            return null;
        }

        try {
            $program = trim((string) ($peserta->program_pendidikan ?? ''));
            $tingkat = trim((string) ($peserta->tingkat_kelas ?? ''));
            $diterimaTingkat = trim((string) ($peserta->diterima_di_tingkat ?? ''));

            $tingkatNum = null;
            if (preg_match('/\b(1[0-2]|[1-9])\b/', $tingkat, $m)) {
                $tingkatNum = $m[1];
            } elseif (preg_match('/\b(1[0-2]|[1-9])\b/', $diterimaTingkat, $m)) {
                $tingkatNum = $m[1];
            }

            // 1. Cari rombel di SIMPIKAT yang cocok
            $rombelQuery = DB::connection($this->connection)->table('rombel');
            if ($program !== '') {
                $rombelQuery->where('program', 'LIKE', '%'.$program.'%');
            }
            if ($tingkatNum !== null) {
                $rombelQuery->where('tingkat', (string) $tingkatNum);
            }

            $rombel = $rombelQuery->first();
            $simpikatTutorId = $rombel?->tutor_id;

            // 2. Fallback ke rombel_mata_pelajaran jika kolom tutor_id di rombel kosong
            if (! $simpikatTutorId && $rombel) {
                $rombelMapel = DB::connection($this->connection)
                    ->table('rombel_mata_pelajaran')
                    ->where('rombel_id', $rombel->id)
                    ->whereNotNull('tutor_id')
                    ->first();
                $simpikatTutorId = $rombelMapel?->tutor_id;
            }

            if (! $simpikatTutorId) {
                return null;
            }

            // 3. Ambil data pengguna Tutor di database SIMPIKAT
            $simpikatTutor = DB::connection($this->connection)
                ->table('pengguna')
                ->where('id', $simpikatTutorId)
                ->first();

            if (! $simpikatTutor) {
                return null;
            }

            // 4. Sinkronkan / dapatkan user dan profil Tutor di Presensi
            $tutorUser = $this->provisionUserToPresensi($simpikatTutor, null);
            $presensiTutor = Tutor::where('user_id', $tutorUser->id)->first();

            return $presensiTutor ? (int) $presensiTutor->id : null;
        } catch (\Throwable $e) {
            Log::warning('SimpikatAuthService: Gagal memetakan relasi Tutor SIMPIKAT untuk siswa.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Menemukan atau memetakan ID kelas di sistem Presensi berdasarkan data jenjang & tingkat kelas di SIMPIKAT.
     */
    protected function resolveKelasForSimpikatPeserta(?object $peserta): int
    {
        if (! $peserta) {
            return $this->resolveFallbackKelasId();
        }

        $program = trim((string) ($peserta->program_pendidikan ?? ''));
        $tingkat = trim((string) ($peserta->tingkat_kelas ?? ''));
        $diterimaTingkat = trim((string) ($peserta->diterima_di_tingkat ?? ''));

        // Ekstraksi angka tingkat (1 - 12)
        $tingkatNum = null;
        if (preg_match('/\b(1[0-2]|[1-9])\b/', $tingkat, $m)) {
            $tingkatNum = $m[1];
        } elseif (preg_match('/\b(1[0-2]|[1-9])\b/', $diterimaTingkat, $m)) {
            $tingkatNum = $m[1];
        }

        // Tentukan Kode Jenjang (paket_a, paket_b, paket_c)
        $jenjangKode = null;
        if (stripos($program, 'Paket A') !== false || stripos($program, 'Setara SD') !== false) {
            $jenjangKode = 'paket_a';
        } elseif (stripos($program, 'Paket B') !== false || stripos($program, 'Setara SMP') !== false) {
            $jenjangKode = 'paket_b';
        } elseif (stripos($program, 'Paket C') !== false || stripos($program, 'Setara SMA') !== false) {
            $jenjangKode = 'paket_c';
        }

        // Inferensi jenjang jika tidak eksplisit dari nama program
        if (! $jenjangKode && $tingkatNum) {
            $t = (int) $tingkatNum;
            if ($t >= 1 && $t <= 6) {
                $jenjangKode = 'paket_a';
            } elseif ($t >= 7 && $t <= 9) {
                $jenjangKode = 'paket_b';
            } elseif ($t >= 10 && $t <= 12) {
                $jenjangKode = 'paket_c';
            }
        }

        $jenjang = $jenjangKode ? JenjangPaket::where('kode', $jenjangKode)->first() : null;

        // 1. Pencarian presisi: Jenjang Paket + Tingkat
        if ($jenjang && $tingkatNum) {
            $matchedKelas = Kelas::where('jenjang_paket_id', $jenjang->id)
                ->where('tingkat', (string) $tingkatNum)
                ->first();
            if ($matchedKelas) {
                return (int) $matchedKelas->id;
            }

            $namaJenjang = match ($jenjangKode) {
                'paket_a' => 'Paket A',
                'paket_b' => 'Paket B',
                'paket_c' => 'Paket C',
                default => $jenjang->nama_jenjang,
            };

            $kelasBaru = Kelas::create([
                'jenjang_paket_id' => $jenjang->id,
                'nama_kelas' => "{$namaJenjang} - Kelas {$tingkatNum}",
                'tingkat' => (string) $tingkatNum,
                'jenjang_paket' => $jenjangKode,
                'keterangan' => "Kelas {$namaJenjang} Tingkat {$tingkatNum} sinkronisasi SIMPIKAT",
            ]);

            return (int) $kelasBaru->id;
        }

        // 2. Pencarian berdasarkan Tingkat (1 - 12)
        if ($tingkatNum) {
            $matchedKelas = Kelas::where('tingkat', (string) $tingkatNum)->first();
            if ($matchedKelas) {
                return (int) $matchedKelas->id;
            }

            $namaJenjang = match (true) {
                (int) $tingkatNum <= 6 => 'Paket A',
                (int) $tingkatNum <= 9 => 'Paket B',
                default => 'Paket C',
            };

            $kelasBaru = Kelas::create([
                'jenjang_paket_id' => $jenjang?->id,
                'nama_kelas' => "{$namaJenjang} - Kelas {$tingkatNum}",
                'tingkat' => (string) $tingkatNum,
                'jenjang_paket' => $jenjangKode ?? 'paket_c',
                'keterangan' => "Kelas {$namaJenjang} Tingkat {$tingkatNum} sinkronisasi SIMPIKAT",
            ]);

            return (int) $kelasBaru->id;
        }

        // 3. Pencarian kelas pertama pada Jenjang yang sama
        if ($jenjang) {
            $matchedKelas = Kelas::where('jenjang_paket_id', $jenjang->id)->first();
            if ($matchedKelas) {
                return (int) $matchedKelas->id;
            }
        }

        return $this->resolveFallbackKelasId();
    }

    /**
     * Memastikan ada ID kelas yang tersedia untuk relasi tabel siswa (fallback jika tidak ada informasi).
     */
    protected function resolveFallbackKelasId(): int
    {
        $kelas = Kelas::where('nama_kelas', 'like', '%Paket C%')->first() ?? Kelas::first();
        if ($kelas) {
            return (int) $kelas->id;
        }

        $kelasBaru = Kelas::create([
            'nama_kelas' => 'Paket C - Kelas 10',
            'tingkat' => '10',
            'keterangan' => 'Kelas default sistem untuk warga belajar sinkronisasi SIMPIKAT',
        ]);

        return (int) $kelasBaru->id;
    }

    /**
     * Sinkronkan profil pengguna lokal dengan data terbaru di SIMPIKAT jika terhubung.
     */
    public function syncUserFromSimpikat(User $user): void
    {
        try {
            $identifier = $user->email ?: $user->nik;
            if (! $identifier) {
                return;
            }

            $simpikatUser = $this->findSimpikatPengguna($identifier);
            if (! $simpikatUser && $user->role === 'siswa' && $user->siswa) {
                $noAbsen = (string) $user->siswa->no_absen;
                if ($noAbsen !== '') {
                    $simpikatUser = $this->findSimpikatPengguna($noAbsen);
                }
            }

            if ($simpikatUser) {
                $this->provisionDomainProfile($user, $simpikatUser);
            }
        } catch (\Throwable $e) {
            Log::warning('SimpikatAuthService: Sinkronisasi pasca-login SIMPIKAT dilewati.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mendeteksi status Anak Berkebutuhan Khusus (ABK) / Inklusi dari data SIMPIKAT.
     */
    protected function resolveIsAbkForSimpikatPeserta(?object $peserta): bool
    {
        if (! $peserta) {
            return false;
        }

        // 1. Cek jika ada kolom eksplisit is_abk atau kebutuhan_khusus
        if (isset($peserta->is_abk)) {
            return (bool) $peserta->is_abk;
        }

        // 2. Cek kolom kelainan jasmani
        $kelainan = trim((string) ($peserta->kelainan_jasmani ?? ''));
        $nonAbkValues = [
            'tidak ada',
            '-',
            'nihil',
            'normal',
            'tidak',
            'none',
            'sehat',
            'tidak_ada',
            'tidak ada kelainan',
            'tidak ada kelainan jasmani',
            'tidak ada kelainan fisik',
        ];

        if ($kelainan !== '' && ! in_array(strtolower($kelainan), $nonAbkValues, true)) {
            return true;
        }

        // 3. Cek kombinasi teks (kelompok, program, nama, catatan lain) yang menyebut ABK / Inklusi
        $kombinasiTeks = ($peserta->nama_lengkap ?? '').' '
            .($peserta->diterima_di_kelompok ?? '').' '
            .($peserta->program_pendidikan ?? '').' '
            .($peserta->penyakit_diderita ?? '').' '
            .($peserta->lain_lain ?? '');

        $abkKeywords = [
            'abk',
            'inklusi',
            'disabilitas',
            'autis',
            'autisme',
            'adhd',
            'down syndrome',
            'tunarungu',
            'tunagrahita',
            'tunadaksa',
            'tunanetra',
            'tunawicara',
            'tuna rungu',
            'tuna grahita',
            'tuna daksa',
            'tuna netra',
            'tuna wicara',
            'slow learner',
            'lambat belajar',
            'berkebutuhan khusus',
            'cerebral palsy',
            'terapi',
        ];

        foreach ($abkKeywords as $kw) {
            if (stripos($kombinasiTeks, $kw) !== false) {
                return true;
            }
        }

        return false;
    }
}

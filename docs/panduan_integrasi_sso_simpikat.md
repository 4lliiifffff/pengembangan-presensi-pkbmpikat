# Panduan Integrasi Single Sign-On (SSO) Terpadu SIMPIKAT & Presensi Pikat

Dokumen ini adalah panduan teknis dan referensi kode implementasi untuk menghubungkan portal utama **SIMPIKAT** (`pkbmpikat.com`) dengan sistem satelit **Presensi PKBM Pikat** (`kehadiran.pkbmpikat.com`) dan **Lapor-Pikat** (`lapor.pkbmpikat.com`) menggunakan standar keamanan **One-Time HMAC-SHA256 Signed Token**.

---

## 1. Konsep Kerja SSO

1. Pengguna login di SIMPIKAT seperti biasa.
2. Ketika pengguna mengklik menu **"Presensi Online"** di SIMPIKAT, sistem SIMPIKAT menghasilkan token sementara (berlaku 120 detik) bertanda tangan kriptografis rahasia.
3. Browser pengguna dialihkan (*redirect*) ke:
   ```
   https://kehadiran.pkbmpikat.com/sso/simpikat?token={TOKEN}
   ```
4. Sistem Presensi PKBM Pikat memverifikasi keabsahan token dan timestamp-nya, memetakan data pengguna dari database `db_simpikat.pengguna`, melakukan *Just-In-Time (JIT) Provisioning* secara otomatis, lalu langsung mengaktifkan sesi login (`Auth::login()`).
5. Pengguna langsung mendarat di Dashboard perannya (Admin, Tutor, atau Siswa) tanpa melihat form login.
6. Sebaliknya, jika pengguna sedang di Presensi dan ingin kembali ke SIMPIKAT, pengguna dapat mengklik tautan:
   ```
   https://kehadiran.pkbmpikat.com/sso/ke-simpikat
   ```
   yang akan membuat token signed dan mengarahkan kembali ke SIMPIKAT.

---

## 2. Parameter Kunci Bersama (Environment)

Kedua sistem wajib menyepakati satu kunci rahasia yang sama di file `.env`:

```env
# Di SIMPIKAT (.env) dan Presensi Pikat (.env)
SIMPIKAT_SSO_SECRET=kunci_rahasia_bersama_ekosistem_pikat_2026_super_aman
SIMPIKAT_SSO_TTL=120
```

---

## 3. Cuplikan Kode PHP untuk Dipasang di SIMPIKAT

### A. Helper Pembuatan Tautan SSO Masuk ke Presensi

Tambahkan fungsi helper ini di SIMPIKAT (misal di controller atau helper navigasi):

```php
<?php

/**
 * Menghasilkan URL SSO untuk membuka Presensi PKBM Pikat secara otomatis.
 *
 * @param string $identifier Username, Email, atau NIK pengguna yang sedang login di SIMPIKAT
 * @return string URL tujuan lengkap dengan token SSO
 */
function getPresensiSsoUrl(string $identifier): string
{
    $secret = env('SIMPIKAT_SSO_SECRET', env('APP_KEY'));
    $ttl = (int) env('SIMPIKAT_SSO_TTL', 120);

    $payload = [
        'identifier' => trim($identifier),
        'app' => 'presensi', // target aplikasi
        'nonce' => bin2hex(random_bytes(8)),
        'exp' => time() + $ttl,
    ];

    $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', $payloadEncoded, $secret);
    $token = $payloadEncoded . '.' . $signature;

    return 'https://kehadiran.pkbmpikat.com/sso/simpikat?token=' . urlencode($token);
}
```

**Penggunaan di View / Template SIMPIKAT:**
```blade
<a href="{{ getPresensiSsoUrl(auth()->user()->nama_pengguna) }}" class="btn btn-primary" target="_blank">
    <i class="fas fa-calendar-check"></i> Buka Presensi Online
</a>
```

---

### B. Endpoint Penerima SSO Balik di SIMPIKAT (Dari Presensi ke SIMPIKAT)

Jika SIMPIKAT ingin menerima auto-login balik dari Presensi (`/sso/presensi`):

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpikatSsoReceiverController extends Controller
{
    public function handle(Request $request)
    {
        $token = (string) $request->query('token');
        if (empty($token)) {
            return redirect('/login')->with('error', 'Token SSO tidak ditemukan.');
        }

        $secret = env('SIMPIKAT_SSO_SECRET', env('APP_KEY'));
        $parts = explode('.', trim($token));
        if (count($parts) !== 2) {
            return redirect('/login')->with('error', 'Format token SSO tidak valid.');
        }

        [$payloadEncoded, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payloadEncoded, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return redirect('/login')->with('error', 'Tanda tangan token SSO tidak sah.');
        }

        $decodedJson = base64_decode(strtr($payloadEncoded, '-_', '+/'));
        $data = json_decode($decodedJson, true);

        if (!is_array($data) || ($data['app'] ?? '') !== 'sim') {
            return redirect('/login')->with('error', 'Aplikasi tujuan token tidak cocok.');
        }

        if (($data['exp'] ?? 0) < time()) {
            return redirect('/login')->with('error', 'Tautan login SSO sudah kedaluwarsa.');
        }

        $identifier = $data['identifier'] ?? '';
        
        // Cari pengguna di tabel pengguna SIMPIKAT
        $user = \App\Models\Pengguna::where('nama_pengguna', $identifier)
            ->orWhere('email', $identifier)
            ->orWhere('nomor_nuptk', $identifier)
            ->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Login ke SIMPIKAT
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Selamat datang kembali dari Presensi Pikat!');
    }
}
```

---

## 4. Opsi Alternatif: Shared Session Domain (`.pkbmpikat.com`)

Jika kedua sistem (SIMPIKAT dan Presensi) berada di server/subdomain yang sama dan ingin sesi tersambung secara instan tanpa redirect token:

1. Di file `.env` Presensi:
   ```env
   SESSION_DOMAIN=.pkbmpikat.com
   ```
2. Di file `.env` SIMPIKAT:
   ```env
   SESSION_DOMAIN=.pkbmpikat.com
   ```
3. Pastikan `APP_KEY` disamakan jika kedua aplikasi menggunakan enkripsi session cookie Laravel standar.

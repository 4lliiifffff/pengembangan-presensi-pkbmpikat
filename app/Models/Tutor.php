<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Tutor — Data Profil Detail Tutor
 *
 * Model ini merepresentasikan data profil lengkap seorang tutor.
 * Tutor adalah pengguna dengan role 'tutor' yang memiliki record User untuk login
 * dan record Tutor untuk menyimpan informasi profesional/pribadi yang lebih detail.
 *
 * POLA DESAIN (Design Pattern):
 *   Sistem menggunakan pola "User + Profile" (One-to-One Polymorphic Pattern):
 *   - Tabel 'users'  : Data autentikasi (login credentials)
 *   - Tabel 'tutors' : Data profil detail tutor
 *   Ini memisahkan concern antara autentikasi dan data domain.
 *
 * STRUKTUR TABEL 'tutors':
 *   - id           : Primary key
 *   - user_id      : Foreign key ke tabel users (akun login yang terhubung)
 *   - nik          : Nomor Induk Karyawan tutor
 *   - nama_lengkap : Nama lengkap tutor
 *   - jabatan      : Jabatan/posisi tutor di lembaga
 *   - email        : Alamat email tutor
 *   - alamat       : Alamat tempat tinggal
 *   - no_hp        : Nomor handphone
 *   - foto         : Path foto profil
 *
 * RELASI:
 *   - Tutor belongsTo User       (1 tutor punya 1 akun login)
 *   - Tutor hasMany Presensi     (1 tutor punya banyak rekaman kehadiran)
 *   - Tutor hasMany Lapor_Lapor  (1 tutor bisa punya banyak pengajuan lupa lapor)
 *
 * SIDANG FAQ:
 *   Q: Mengapa data tutor dipisah dari tabel users?
 *   A: Separation of Concerns — tabel users hanya mengurus autentikasi, sementara
 *      tabel tutors menyimpan data domain bisnis. Ini mempermudah perubahan schema
 *      di masa depan tanpa mengganggu mekanisme login.
 *
 *   Q: Bisakah seorang User menjadi Tutor sekaligus Admin?
 *   A: Tidak, sistem ini menggunakan Single Role per User. Satu user hanya memiliki
 *      satu role yang menentukan dashboard dan hak aksesnya.
 */
class Tutor extends Model
{
    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     *
     * @var array<string>
     */
    protected $fillable =
        [
            'user_id',      // Foreign key ke akun login (tabel users)
            'nik',          // Nomor Induk Karyawan
            'nama_lengkap', // Nama lengkap tutor
            'jabatan',      // Jabatan/posisi di lembaga
            'email',        // Email tutor
            'alamat',       // Alamat tempat tinggal
            'no_hp',        // Nomor handphone
            'foto',         // Path foto profil (relatif dari public/)
        ];

    /**
     * Relasi: Tutor dimiliki oleh satu User (Many-to-One / BelongsTo).
     * Digunakan untuk mengakses data akun login dari tutor.
     * Contoh: $tutor->user->email
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Tutor memiliki banyak record Presensi (One-to-Many / HasMany).
     * Satu tutor bisa punya banyak sesi mengajar yang terekam sebagai presensi.
     * Contoh: $tutor->presensis()->whereDate('tgl_presensi', today())->get()
     *
     * @return HasMany
     */
    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Relasi: Tutor memiliki banyak pengajuan Lupa Lapor (One-to-Many / HasMany).
     *
     * @return HasMany
     */
    public function pengajuanLupaLapor()
    {
        return $this->hasMany(PengajuanLupaLapor::class);
    }

    /**
     * Relasi: Tutor memiliki banyak Jadwal Sesi Belajar & Sesi Pengganti.
     */
    public function jadwalSesis(): HasMany
    {
        return $this->hasMany(JadwalSesi::class);
    }
}

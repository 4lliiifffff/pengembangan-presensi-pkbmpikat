<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Siswa — Data Murid/Peserta Didik
 *
 * Model ini merepresentasikan data siswa yang menjadi peserta didik di lembaga.
 * Siswa bukan pengguna sistem (tidak bisa login), namun hadir sebagai entitas
 * yang direlasikan dalam setiap sesi presensi mengajar.
 *
 * STRUKTUR TABEL 'siswas':
 *   - id         : Primary key
 *   - nis        : Nomor Induk Siswa
 *   - nama_siswa : Nama lengkap siswa
 *   - alamat     : Alamat tempat tinggal siswa
 *   - no_hp      : Nomor handphone siswa/wali
 *   - nama_wali  : Nama orang tua/wali siswa
 *   - kelas_id   : Foreign key ke tabel kelas (kelas yang diikuti siswa)
 *
 * RELASI:
 *   - Siswa belongsTo Kelas    (1 siswa berada di 1 kelas)
 *   - Siswa hasMany Presensi   (1 siswa bisa punya banyak record kehadiran dari tutor berbeda)
 *
 * SIDANG FAQ:
 *   Q: Mengapa nama tabel 'siswas' (bukan 'siswas' atau 'student')?
 *   A: Laravel secara default menggunakan nama model dalam bentuk plural + snake_case.
 *      'Siswa' → 'siswa' + 's' = 'siswas'. Nama tabel juga di-set eksplisit via
 *      $table untuk memastikan tidak ada ambiguitas.
 *
 *   Q: Mengapa siswa tidak bisa login ke sistem?
 *   A: Sistem ini dirancang sebagai aplikasi presensi untuk tutor/pengajar, bukan
 *      untuk siswa. Siswa hanya sebagai entitas yang direkam dalam data kehadiran.
 *
 *   Q: Apa relasi antara Siswa dan Presensi?
 *   A: Satu Siswa bisa memiliki banyak record Presensi karena ia bisa diajar oleh
 *      tutor yang berbeda pada hari yang berbeda. Setiap sesi mengajar menghasilkan
 *      satu record Presensi yang mengandung siswa_id.
 */
class Siswa extends Model
{
    /**
     * Nama tabel di database (didefinisikan eksplisit karena bentuk plural tidak standar).
     *
     * @var string
     */
    protected $table = 'siswas';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     *
     * @var array<string>
     */
    protected $fillable = [
        'no_absen',   // Nomor Absen Siswa
        'nama_siswa', // Nama lengkap siswa
        'is_abk',     // Status Anak Berkebutuhan Khusus (ABK)
        'no_hp',      // Nomor handphone siswa atau wali
        'nama_wali',  // Nama orang tua/wali yang dapat dihubungi
        'kelas_id',   // ID kelas yang diikuti siswa (foreign key)
        'tutor_id',    // ID tutor yang mengajar siswa ini
    ];

    /**
     * Tipe data casts atribut Siswa.
     */
    protected function casts(): array
    {
        return [
            'is_abk' => 'boolean',
        ];
    }

    /**
     * Accessor: Label status ABK siswa.
     */
    public function getIsAbkLabelAttribute(): string
    {
        return $this->is_abk ? 'ABK' : 'Reguler';
    }

    /**
     * Accessor: Label skema tarif master SK siswa.
     */
    public function getSkemaTarifLabelAttribute(): string
    {
        return $this->is_abk
            ? 'Skema SK ABK (Rp 100rb - 130rb/sesi)'
            : 'Skema SK Reguler (Rp 50rb - 100rb/sesi)';
    }

    /**
     * Accessor: Resolusi kelompok Paket / Jenjang Pendidikan Kesetaraan siswa.
     */
    public function getJenjangPaketAttribute(): string
    {
        // 1. Prioritas utama: kolom terstruktur dari relasi Kelas
        $kelasJenjang = $this->relKelas?->jenjang_paket;
        if ($kelasJenjang && in_array($kelasJenjang, ['paket_a', 'paket_b', 'paket_c', 'vokasi', 'kursus'])) {
            return $kelasJenjang;
        }

        // 2. Fallback: deteksi teks nama kelas (legacy compatibility)
        $namaKelas = strtolower((string) ($this->relKelas?->nama_kelas ?? ''));

        if (str_contains($namaKelas, 'paket a') || str_contains($namaKelas, 'kelas a') || str_contains($namaKelas, 'setara sd') || str_contains($namaKelas, 'sd')) {
            return 'paket_a';
        }
        if (str_contains($namaKelas, 'paket b') || str_contains($namaKelas, 'kelas b') || str_contains($namaKelas, 'setara smp') || str_contains($namaKelas, 'smp')) {
            return 'paket_b';
        }
        if (str_contains($namaKelas, 'paket c') || str_contains($namaKelas, 'kelas c') || str_contains($namaKelas, 'setara sma') || str_contains($namaKelas, 'sma') || str_contains($namaKelas, 'smk')) {
            return 'paket_c';
        }
        if (str_contains($namaKelas, 'vokasi') || str_contains($namaKelas, 'kejuruan') || str_contains($namaKelas, 'keterampilan')) {
            return 'vokasi';
        }

        return $this->kelas_id ? 'kelas_'.$this->kelas_id : 'umum';
    }

    /**
     * Accessor: Label manusiawi untuk Jenjang Paket siswa.
     */
    public function getJenjangPaketLabelAttribute(): string
    {
        if ($this->relKelas?->jenjang_paket && $this->relKelas->jenjang_paket !== 'umum') {
            return $this->relKelas->jenjang_paket_label;
        }

        return match ($this->jenjang_paket) {
            'paket_a' => 'Paket A (Setara SD)',
            'paket_b' => 'Paket B (Setara SMP)',
            'paket_c' => 'Paket C (Setara SMA)',
            'vokasi' => 'Vokasi / Keterampilan Kejuruan',
            'kursus' => 'Kursus & Pelatihan',
            default => $this->relKelas?->nama_kelas ?: 'Umum / Reguler',
        };
    }

    /**
     * Accessor: Nama kelas / rombel lengkap siswa.
     */
    public function getNamaKelasLengkapAttribute(): string
    {
        return $this->relKelas?->nama_kelas ?: 'Belum Ditentukan';
    }

    /**
     * Relasi: Siswa terdaftar di satu Kelas (Many-to-One / BelongsTo).
     * Menggunakan nama relasi 'relKelas' (bukan 'kelas') untuk menghindari
     * konflik nama dengan class lain.
     * Contoh: $siswa->relKelas->nama_kelas
     *
     * @return BelongsTo
     */
    public function relKelas()
    {
        return $this->belongsTo(kelas::class, 'kelas_id');
    }

    /**
     * Relasi: Siswa memiliki banyak record Presensi (One-to-Many / HasMany).
     * Setiap kali seorang tutor mengajar siswa ini, muncul satu record presensi.
     * Contoh: $siswa->presensis()->whereBetween('tgl_presensi', [$awal, $akhir])->get()
     *
     * @return HasMany
     */
    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Relasi: Siswa dibimbing oleh satu Tutor (Many-to-One / BelongsTo).
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }
}

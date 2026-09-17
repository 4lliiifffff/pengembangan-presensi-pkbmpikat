<?php

namespace App\Models;

use App\Observers\SiswaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Siswa — Data Murid/Peserta Didik
 */
#[ObservedBy([SiswaObserver::class])]
class Siswa extends Model
{
    use SoftDeletes;

    /**
     * Nama tabel di database.
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
        'user_id',      // Foreign key ke akun login siswa (tabel users)
        'no_absen',     // Nomor Absen Siswa
        'nama_siswa',   // Nama lengkap siswa
        'is_abk',       // Status Anak Berkebutuhan Khusus (ABK)
        'status_siswa', // Status siklus murid: 'aktif', 'alumni', 'cuti', 'nonaktif'
        'no_hp',        // Nomor handphone siswa atau wali
        'nama_wali',    // Nama orang tua/wali yang dapat dihubungi
        'kelas_id',     // ID kelas yang diikuti siswa (foreign key)
        'tutor_id',     // ID tutor yang mengajar siswa ini
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
     * Scope: Hanya siswa dengan status aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_siswa', 'aktif');
    }

    /**
     * Scope: Hanya siswa dengan status alumni / lulus.
     */
    public function scopeAlumni(Builder $query): Builder
    {
        return $query->where('status_siswa', 'alumni');
    }

    /**
     * Scope: Filter berdasarkan status tertentu.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_siswa', $status);
    }

    /**
     * Accessor: Label manusiawi status siswa.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_siswa) {
            'alumni' => 'Lulus / Alumni',
            'cuti' => 'Cuti Belajar',
            'nonaktif' => 'Nonaktif / Keluar',
            default => 'Aktif Belajar',
        };
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
        if (! empty($kelasJenjang) && $kelasJenjang !== 'umum') {
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

        return $kelasJenjang ?: ($this->kelas_id ? 'kelas_'.$this->kelas_id : 'umum');
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
     */
    public function relKelas(): BelongsTo
    {
        return $this->belongsTo(kelas::class, 'kelas_id');
    }

    /**
     * Alias relasi relKelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->relKelas();
    }

    /**
     * Relasi: Menghubungkan langsung Siswa ke Master Jenjang Paket melalui Kelas (HasOneThrough).
     * Memungkinkan pemanggilan: $siswa->masterJenjang->nama_jenjang atau $siswa->relJenjangPaket
     */
    public function masterJenjang(): HasOneThrough
    {
        return $this->hasOneThrough(
            JenjangPaket::class,
            kelas::class,
            'id',               // Foreign key di tabel kelas (kelas.id)
            'id',               // Foreign key di tabel jenjang_pakets (jenjang_pakets.id)
            'kelas_id',         // Local key di tabel siswas (siswas.kelas_id)
            'jenjang_paket_id'  // Local key di tabel kelas (kelas.jenjang_paket_id)
        );
    }

    /**
     * Alias relasi masterJenjang.
     */
    public function relJenjangPaket(): HasOneThrough
    {
        return $this->masterJenjang();
    }

    /**
     * Relasi: Siswa memiliki banyak record Presensi (One-to-Many / HasMany).
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Relasi: Siswa dibimbing oleh satu Tutor (Many-to-One / BelongsTo).
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    /**
     * Relasi: Siswa memiliki banyak Jadwal Sesi Belajar & Pengganti.
     */
    public function jadwalSesis(): HasMany
    {
        return $this->hasMany(JadwalSesi::class);
    }

    /**
     * Relasi: Siswa terhubung ke satu akun User untuk login (HasOne).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Siswa memiliki banyak record Presensi Mandiri.
     */
    public function presensiMandiri(): HasMany
    {
        return $this->hasMany(PresensiMandiriSiswa::class);
    }
}

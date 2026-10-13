<?php

namespace App\Models;

use App\Observers\MuridObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Murid - Data Murid PKBM PIKAT
 */
#[ObservedBy([MuridObserver::class])]
class Murid extends Model
{
    use SoftDeletes;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'murid';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',      // Foreign key ke akun login murid (tabel users)
        'no_absen',     // Nomor Absen Murid
        'nis',          // Nomor Induk Siswa (NIS)
        'nisn',         // Nomor Induk Siswa Nasional (NISN)
        'nama_murid',   // Nama lengkap murid
        'nama_siswa',   // Alias backward compatibility
        'is_abk',       // Status Anak Berkebutuhan Khusus (ABK)
        'status_murid', // Status siklus murid: 'aktif', 'alumni', 'cuti', 'nonaktif'
        'status_siswa', // Alias backward compatibility
        'no_hp',        // Nomor handphone murid atau wali
        'nama_wali',    // Nama orang tua/wali yang dapat dihubungi
        'kelas_id',     // ID kelas yang diikuti murid (foreign key)
        'tutor_id',     // ID tutor yang membimbing murid ini
    ];

    /**
     * Boot the model with compatibility synchronization.
     */
    protected static function booted(): void
    {
        static::creating(function (Murid $murid) {
            if (empty($murid->nama_murid)) {
                $murid->nama_murid = $murid->nama_siswa ?? (
                    $murid->user_id ? (User::find($murid->user_id)?->nama_lengkap ?? 'Murid PKBM') : 'Murid PKBM'
                );
            }
            if (empty($murid->status_murid)) {
                $murid->status_murid = $murid->status_siswa ?? 'aktif';
            }
        });

        static::updating(function (Murid $murid) {
            if (empty($murid->nama_murid) && ! empty($murid->nama_siswa)) {
                $murid->nama_murid = $murid->nama_siswa;
            }
            if (empty($murid->status_murid) && ! empty($murid->status_siswa)) {
                $murid->status_murid = $murid->status_siswa;
            }
        });
    }

    /**
     * Tipe data casts atribut Murid.
     */
    protected function casts(): array
    {
        return [
            'is_abk' => 'boolean',
        ];
    }

    /**
     * Scope: Hanya murid dengan status aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_murid', 'aktif');
    }

    /**
     * Scope: Hanya murid dengan status alumni / lulus.
     */
    public function scopeAlumni(Builder $query): Builder
    {
        return $query->where('status_murid', 'alumni');
    }

    /**
     * Scope: Filter berdasarkan status tertentu.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_murid', $status);
    }

    /**
     * Accessor: Label manusiawi status murid.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_murid) {
            'alumni' => 'Lulus / Alumni',
            'cuti' => 'Cuti Belajar',
            'nonaktif' => 'Nonaktif / Keluar',
            default => 'Aktif Belajar',
        };
    }

    /**
     * Accessor: Label status ABK murid.
     */
    public function getIsAbkLabelAttribute(): string
    {
        return $this->is_abk ? 'ABK' : 'Reguler';
    }

    /**
     * Accessor: Label skema tarif master SK murid.
     */
    public function getSkemaTarifLabelAttribute(): string
    {
        return $this->is_abk
            ? 'Skema SK ABK (Rp 100rb - 130rb/sesi)'
            : 'Skema SK Reguler (Rp 50rb - 100rb/sesi)';
    }

    /**
     * Accessor: Resolusi kelompok Paket / Jenjang Pendidikan Kesetaraan murid.
     */
    public function getJenjangPaketAttribute(): string
    {
        $kelasJenjang = $this->relKelas?->jenjang_paket;
        if (! empty($kelasJenjang) && $kelasJenjang !== 'umum') {
            return $kelasJenjang;
        }

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
     * Accessor: Label manusiawi untuk Jenjang Paket murid.
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
     * Accessor: Nama kelas / rombel lengkap murid.
     */
    public function getNamaKelasLengkapAttribute(): string
    {
        return $this->relKelas?->nama_kelas ?: 'Belum Ditentukan';
    }

    /**
     * Relasi: Murid terdaftar di satu Kelas (Many-to-One / BelongsTo).
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
     * Relasi: Menghubungkan langsung Murid ke Master Jenjang Paket melalui Kelas (HasOneThrough).
     */
    public function masterJenjang(): HasOneThrough
    {
        return $this->hasOneThrough(
            JenjangPaket::class,
            kelas::class,
            'id',               // Foreign key di tabel kelas (kelas.id)
            'id',               // Foreign key di tabel jenjang_pakets (jenjang_pakets.id)
            'kelas_id',         // Local key di tabel murid (murid.kelas_id)
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
     * Relasi: Murid memiliki banyak record Presensi (One-to-Many / HasMany).
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'murid_id');
    }

    /**
     * Relasi: Murid dibimbing oleh satu Tutor (Many-to-One / BelongsTo).
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    /**
     * Relasi: Murid memiliki banyak Jadwal Sesi Belajar & Pengganti.
     */
    public function jadwalSesis(): HasMany
    {
        return $this->hasMany(JadwalSesi::class, 'murid_id');
    }

    /**
     * Relasi: Murid memiliki Master Jadwal Rutin KBM Mingguan.
     */
    public function jadwalRutins(): HasMany
    {
        return $this->hasMany(JadwalRutin::class, 'murid_id');
    }

    /**
     * Relasi: Murid terhubung ke satu akun User untuk login.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Murid memiliki banyak record Presensi Mandiri.
     */
    public function presensiMandiri(): HasMany
    {
        return $this->hasMany(PresensiMandiriMurid::class, 'murid_id');
    }

    /**
     * Backward compatibility accessors untuk nama_siswa & status_siswa
     */
    public function getNamaSiswaAttribute(): ?string
    {
        return $this->nama_murid;
    }

    public function setNamaSiswaAttribute(?string $value): void
    {
        $this->attributes['nama_murid'] = $value;
    }

    public function getStatusSiswaAttribute(): ?string
    {
        return $this->status_murid;
    }

    public function setStatusSiswaAttribute(?string $value): void
    {
        $this->attributes['status_murid'] = $value;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalRutin extends Model
{
    use HasFactory;

    protected $table = 'jadwal_rutins';

    public const HARI_LABELS = [
        'senin' => 'Senin',
        'selasa' => 'Selasa',
        'rabu' => 'Rabu',
        'kamis' => 'Kamis',
        'jumat' => 'Jumat',
        'sabtu' => 'Sabtu',
        'minggu' => 'Minggu',
    ];

    protected $fillable = [
        'siswa_id',
        'tutor_id',
        'kategori_tutorial_id',
        'jadwal_kerja_id',
        'hari',
        'jam_masuk',
        'jam_pulang',
        'durasi_jam',
        'is_active',
        'berlaku_mulai',
        'berlaku_sampai',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'durasi_jam' => 'decimal:2',
            'is_active' => 'boolean',
            'berlaku_mulai' => 'date',
            'berlaku_sampai' => 'date',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class);
    }

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function jadwalSesis(): HasMany
    {
        return $this->hasMany(JadwalSesi::class, 'jadwal_rutin_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByHari(Builder $query, string $hari): Builder
    {
        return $query->where('hari', strtolower($hari));
    }

    public function scopeForSiswa(Builder $query, int $siswaId): Builder
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopeForTutor(Builder $query, int $tutorId): Builder
    {
        return $query->where('tutor_id', $tutorId);
    }

    // ── Accessors & Helpers ────────────────────────────────────────────────────

    public function getNamaHariLabelAttribute(): string
    {
        return ucfirst(strtolower($this->hari));
    }

    public function getJamMasukFormattedAttribute(): string
    {
        return substr((string) $this->jam_masuk, 0, 5);
    }

    public function getJamPulangFormattedAttribute(): string
    {
        return substr((string) $this->jam_pulang, 0, 5);
    }

    public function getRentangJamLabelAttribute(): string
    {
        return $this->jam_masuk_formatted.' - '.$this->jam_pulang_formatted.' WIB';
    }

    /**
     * Konversi index ISO day of week (1 = Monday ... 7 = Sunday) ke string hari.
     */
    public static function isoToHariName(int $isoDay): string
    {
        return match ($isoDay) {
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            7 => 'minggu',
            default => 'senin',
        };
    }

    /**
     * Konversi string hari ke ISO day of week (1 = Monday ... 7 = Sunday).
     */
    public static function hariNameToIso(string $hari): int
    {
        return match (strtolower(trim($hari))) {
            'senin' => 1,
            'selasa' => 2,
            'rabu' => 3,
            'kamis' => 4,
            'jumat' => 5,
            'sabtu' => 6,
            'minggu' => 7,
            default => 1,
        };
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalKerja extends Model
{
    /**
     * Mass assignment attributes.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_shift',
        'kode_shift',
        'jenis_shift',
        'kategori_tutorial_id',
        'jam_masuk',
        'jam_pulang',
        'durasi_jam',
        'earliest_minutes',
        'tolerance_minutes',
        'is_aktif',
        'urutan',
        'keterangan',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'durasi_jam' => 'float',
            'earliest_minutes' => 'integer',
            'tolerance_minutes' => 'integer',
            'is_aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Scope untuk shift aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Relasi ke Kategori Tutorial SK (opsional untuk shift KBM).
     */
    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class, 'kategori_tutorial_id');
    }

    /**
     * Relasi ke riwayat presensi tutor.
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'jadwal_kerja_id');
    }

    /**
     * Relasi ke riwayat presensi karyawan/magang.
     */
    public function presensiKaryawans(): HasMany
    {
        return $this->hasMany(PresensiKaryawan::class, 'jadwal_kerja_id');
    }

    /**
     * Accessor: Format jam masuk (HH:mm).
     */
    public function getJamMasukFormattedAttribute(): string
    {
        return $this->jam_masuk ? substr((string) $this->jam_masuk, 0, 5) : '-';
    }

    /**
     * Accessor: Format jam pulang (HH:mm).
     */
    public function getJamPulangFormattedAttribute(): string
    {
        return $this->jam_pulang ? substr((string) $this->jam_pulang, 0, 5) : '-';
    }

    /**
     * Accessor: Batas jam paling awal boleh absen masuk (HH:mm).
     */
    public function getBatasAwalMasukAttribute(): string
    {
        if (! $this->jam_masuk) {
            return '-';
        }

        return Carbon::parse('2000-01-01 '.$this->jam_masuk)
            ->subMinutes($this->earliest_minutes)
            ->format('H:i');
    }

    /**
     * Accessor: Batas toleransi keterlambatan (HH:mm).
     */
    public function getBatasToleransiMasukAttribute(): string
    {
        if (! $this->jam_masuk) {
            return '-';
        }

        return Carbon::parse('2000-01-01 '.$this->jam_masuk)
            ->addMinutes($this->tolerance_minutes)
            ->format('H:i');
    }

    /**
     * Accessor: Badge info jenis shift.
     */
    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_shift) {
            'kbm' => 'KBM Tutor',
            default => 'Umum (Karyawan & Magang)',
        };
    }
}

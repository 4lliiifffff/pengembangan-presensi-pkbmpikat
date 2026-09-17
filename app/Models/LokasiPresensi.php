<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiPresensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lokasi',
        'alamat',
        'tipe',
        'latitude',
        'longitude',
        'radius_meter',
        'is_active',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meter' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope query untuk hanya mengambil lokasi presensi yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Relasi ke presensi tutor.
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'lokasi_presensi_id');
    }

    /**
     * Relasi ke presensi karyawan / magang.
     */
    public function presensiKaryawans(): HasMany
    {
        return $this->hasMany(PresensiKaryawan::class, 'lokasi_presensi_id');
    }
}

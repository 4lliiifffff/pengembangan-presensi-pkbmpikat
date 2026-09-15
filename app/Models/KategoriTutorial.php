<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model KategoriTutorial — Master Kategori & Tarif Tutorial Sesuai SK Kepala PKBM
 */
class KategoriTutorial extends Model
{
    protected $table = 'kategori_tutorials';

    protected $fillable = [
        'nama_kategori',
        'jenis_layanan', // 'komunitas', 'dl', 'lainnya'
        'durasi_jam',
        'is_abk',
        'is_gabungan',
        'nominal_honor',
        'is_aktif',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'durasi_jam' => 'float',
            'is_abk' => 'boolean',
            'is_gabungan' => 'boolean',
            'nominal_honor' => 'float',
            'is_aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Scope kategori tutorial yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Relasi ke data presensi.
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'kategori_tutorial_id');
    }

    /**
     * Accessor format nominal rupiah.
     */
    public function getFormattedNominalHonorAttribute(): string
    {
        return 'Rp '.number_format($this->nominal_honor, 0, ',', '.');
    }

    /**
     * Accessor label lengkap untuk dropdown dan pelaporan.
     */
    public function getLabelLengkapAttribute(): string
    {
        $abkLabel = $this->is_abk ? ' (ABK)' : '';
        $durasiLabel = $this->durasi_jam > 0 ? " — {$this->durasi_jam} Jam" : '';

        return "{$this->nama_kategori}{$abkLabel}{$durasiLabel} [{$this->formatted_nominal_honor}]";
    }

    /**
     * Helper resolve kategori berdasarkan parameter sesi.
     */
    public static function resolveKategori(string $jenisLayanan, float $durasiJam, bool $isAbk = false, bool $isGabungan = false): ?self
    {
        return self::active()
            ->where('jenis_layanan', $jenisLayanan)
            ->where('is_abk', $isAbk)
            ->where('is_gabungan', $isGabungan)
            ->where('durasi_jam', $durasiJam)
            ->orderBy('urutan')
            ->first();
    }
}

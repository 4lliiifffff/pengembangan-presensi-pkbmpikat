<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Kelas — Data Kelas & Rombongan Belajar
 */
class kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'jenjang_paket', // 'paket_a', 'paket_b', 'paket_c', 'vokasi', 'kursus', 'umum'
        'tingkat',       // 1 - 12 (null untuk vokasi/kursus non-tingkat)
        'keterangan',
    ];

    protected function casts(): array
    {
        return [];
    }

    /**
     * Accessor: Label manusiawi untuk Jenjang Paket kelas ini.
     */
    public function getJenjangPaketLabelAttribute(): string
    {
        return match ($this->jenjang_paket) {
            'paket_a' => 'Paket A (Setara SD)',
            'paket_b' => 'Paket B (Setara SMP)',
            'paket_c' => 'Paket C (Setara SMA)',
            'vokasi' => 'Vokasi / Keterampilan Kejuruan',
            'kursus' => 'Kursus & Pelatihan',
            default => 'Umum / Reguler',
        };
    }

    /**
     * Scope filter per jenjang paket.
     */
    public function scopeJenjang(Builder $query, string $jenjang): Builder
    {
        return $query->where('jenjang_paket', $jenjang);
    }

    /**
     * Relasi ke data Siswa dalam kelas ini.
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}

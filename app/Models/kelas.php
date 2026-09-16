<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Kelas — Data Kelas & Rombongan Belajar
 */
class kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'jenjang_paket_id', // Foreign key ke tabel jenjang_pakets
        'tingkat',          // 1 - 12 (null untuk vokasi/kursus non-tingkat)
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jenjang_paket_id' => 'integer',
        ];
    }

    /**
     * Relasi ke Master Jenjang Paket (Strict Foreign Key Relation).
     */
    public function jenjangPaket(): BelongsTo
    {
        return $this->belongsTo(JenjangPaket::class, 'jenjang_paket_id');
    }

    /**
     * Alias relasi masterJenjang untuk backward compatibility.
     */
    public function masterJenjang(): BelongsTo
    {
        return $this->jenjangPaket();
    }

    /**
     * Accessor: Mengembalikan kode jenjang paket (misal: 'paket_a', 'vokasi') untuk backward compatibility.
     */
    public function getJenjangPaketAttribute(): string
    {
        $relation = $this->getRelationValue('jenjangPaket');
        if ($relation) {
            return $relation->kode;
        }

        return $this->jenjang_paket_id ? ($this->jenjangPaket()->first()?->kode ?: 'umum') : 'umum';
    }

    /**
     * Accessor: Label resmi manusiawi untuk Jenjang Paket kelas ini.
     */
    public function getJenjangPaketLabelAttribute(): string
    {
        $relation = $this->getRelationValue('jenjangPaket');
        if ($relation) {
            return $relation->nama_jenjang;
        }

        return $this->jenjang_paket_id ? ($this->jenjangPaket()->first()?->nama_jenjang ?: 'Umum / Reguler') : 'Umum / Reguler';
    }

    /**
     * Scope filter per jenjang paket (menerima ID integer atau kode string).
     */
    public function scopeJenjang(Builder $query, int|string $jenjang): Builder
    {
        if (is_numeric($jenjang)) {
            return $query->where('jenjang_paket_id', (int) $jenjang);
        }

        return $query->whereHas('jenjangPaket', function ($q) use ($jenjang) {
            $q->where('kode', $jenjang);
        });
    }

    /**
     * Relasi ke data Siswa dalam kelas ini.
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}

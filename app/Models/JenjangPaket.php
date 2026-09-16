<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model JenjangPaket — Master Data Jenjang / Program Paket Pendidikan Kesetaraan & Vokasi
 */
class JenjangPaket extends Model
{
    protected $table = 'jenjang_pakets';

    protected $fillable = [
        'kode',
        'nama_jenjang',
        'tingkat_label',
        'keterangan',
        'urutan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Scope untuk mengambil jenjang paket yang aktif saja.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Scope untuk mengurutkan berdasarkan kolom urutan lalu nama.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama_jenjang');
    }

    /**
     * Relasi ke Kelas yang menggunakan ID jenjang paket ini (Foreign Key).
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(kelas::class, 'jenjang_paket_id');
    }
}

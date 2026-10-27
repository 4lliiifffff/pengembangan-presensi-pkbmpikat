<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model KepalaSekolah - Data Profil Detail Kepala Sekolah
 *
 * Standarisasi PSR-1 / PSR-12 (PascalCase/StudlyCaps) untuk entitas Kepala Sekolah.
 */
class KepalaSekolah extends Model
{
    protected $table = 'kepala_sekolah';

    protected $fillable = [
        'user_id',
        'nik',
        'nama_lengkap',
        'email',
        'no_hp',
        'foto',
    ];

    /**
     * Relasi ke akun User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

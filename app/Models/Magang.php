<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Magang — Data Profil Mahasiswa Magang / Siswa PKL
 */
class Magang extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim_nisn',
        'nama_lengkap',
        'asal_instansi',
        'jurusan',
        'jurusan_prodi',
        'pembimbing_lapangan',
        'tgl_mulai',
        'tgl_selesai',
        'tgl_mulai_magang',
        'tgl_selesai_magang',
        'status',
        'no_hp',
        'alamat',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'date',
            'tgl_selesai' => 'date',
            'tgl_mulai_magang' => 'date',
            'tgl_selesai_magang' => 'date',
        ];
    }

    /**
     * Relasi: Magang belongs to User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor untuk foto profil.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        return asset('storage/'.ltrim($this->foto, '/'));
    }

    /**
     * Helper cek apakah periode magang masih aktif.
     */
    public function isAktif(): bool
    {
        if (! $this->tgl_mulai_magang || ! $this->tgl_selesai_magang) {
            return true;
        }

        $now = Carbon::now('Asia/Jakarta')->startOfDay();

        return $now->betweenIncluded($this->tgl_mulai_magang, $this->tgl_selesai_magang);
    }
}

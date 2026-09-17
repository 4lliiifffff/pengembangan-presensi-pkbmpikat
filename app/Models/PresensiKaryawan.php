<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiKaryawan extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lokasiPresensi()
    {
        return $this->belongsTo(LokasiPresensi::class, 'lokasi_presensi_id');
    }

    /**
     * Accessor: Mengembalikan URL foto masuk dengan backward compatibility.
     */
    public function getFotoMulaiUrlAttribute(): ?string
    {
        if (! $this->foto_mulai) {
            return null;
        }

        if (str_starts_with($this->foto_mulai, 'http://') || str_starts_with($this->foto_mulai, 'https://')) {
            return $this->foto_mulai;
        }

        if (str_starts_with($this->foto_mulai, 'storage/')) {
            return asset($this->foto_mulai);
        }

        if (file_exists(public_path($this->foto_mulai))) {
            return asset($this->foto_mulai);
        }

        return asset('storage/'.ltrim($this->foto_mulai, '/'));
    }

    /**
     * Accessor: Mengembalikan URL foto pulang dengan backward compatibility.
     */
    public function getFotoSelesaiUrlAttribute(): ?string
    {
        if (! $this->foto_selesai) {
            return null;
        }

        if (str_starts_with($this->foto_selesai, 'http://') || str_starts_with($this->foto_selesai, 'https://')) {
            return $this->foto_selesai;
        }

        if (str_starts_with($this->foto_selesai, 'storage/')) {
            return asset($this->foto_selesai);
        }

        if (file_exists(public_path($this->foto_selesai))) {
            return asset($this->foto_selesai);
        }

        return asset('storage/'.ltrim($this->foto_selesai, '/'));
    }
}

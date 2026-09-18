<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PresensiMandiriSiswa — Kehadiran Harian Mandiri Siswa
 *
 * Mencatat absensi masuk dan pulang siswa di lokasi PKBM / komunitas
 * berdasarkan verifikasi foto selfie dan GPS radius.
 */
class PresensiMandiriSiswa extends Model
{
    protected $fillable = [
        'siswa_id',
        'lokasi_presensi_id',
        'tgl_presensi',
        'jam_masuk',
        'jam_pulang',
        'foto_masuk',
        'foto_pulang',
        'lokasi_masuk',
        'lokasi_pulang',
        'lokasi_akurasi',
        'is_mocked',
        'status',
        'status_kehadiran',
        'menit_keterlambatan',
    ];

    protected function casts(): array
    {
        return [
            'lokasi_akurasi' => 'float',
            'is_mocked' => 'boolean',
            'menit_keterlambatan' => 'integer',
        ];
    }

    /**
     * Cek apakah presensi tercatat terlambat melebihi batas toleransi.
     */
    public function isTerlambat(): bool
    {
        return $this->status_kehadiran === 'terlambat';
    }

    /**
     * Label status kehadiran spesifik (Tepat Waktu / Terlambat XX Menit / Lebih Awal).
     */
    public function getStatusKehadiranLabelAttribute(): string
    {
        return match ($this->status_kehadiran) {
            'terlambat' => 'Terlambat (+'.$this->menit_keterlambatan.' mnt)',
            'lebih_awal' => 'Lebih Awal',
            default => 'Tepat Waktu',
        };
    }

    /** Relasi ke Siswa. */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /** Relasi ke Titik Lokasi Presensi. */
    public function lokasiPresensi(): BelongsTo
    {
        return $this->belongsTo(LokasiPresensi::class, 'lokasi_presensi_id');
    }

    /**
     * Accessor: Label status kehadiran yang ramah.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Tidak Hadir',
            default => 'Hadir',
        };
    }

    /**
     * Accessor: URL foto masuk.
     */
    public function getFotoMasukUrlAttribute(): ?string
    {
        if (! $this->foto_masuk) {
            return null;
        }

        if (str_starts_with($this->foto_masuk, 'storage/')) {
            return asset($this->foto_masuk);
        }

        return asset('storage/'.ltrim($this->foto_masuk, '/'));
    }

    /**
     * Accessor: URL foto pulang.
     */
    public function getFotoPulangUrlAttribute(): ?string
    {
        if (! $this->foto_pulang) {
            return null;
        }

        if (str_starts_with($this->foto_pulang, 'storage/')) {
            return asset($this->foto_pulang);
        }

        return asset('storage/'.ltrim($this->foto_pulang, '/'));
    }
}

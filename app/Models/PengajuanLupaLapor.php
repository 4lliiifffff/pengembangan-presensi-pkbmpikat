<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PengajuanLupaLapor - Pengajuan Lupa Lapor (Retroactive Attendance Request)
 *
 * Model ini merepresentasikan pengajuan presensi susulan/retroaktif oleh Tutor
 * ketika mereka lupa melakukan presensi digital pada hari mengajar.
 *
 * ALUR WORKFLOW:
 *   1. Tutor mengajukan -> status 'pending'
 *   2. Kepala Sekolah meninjau (Setujui / Tolak)
 *   3. Jika 'disetujui' -> Otomatis merekam (upsert) data presensi di tabel 'presensis'
 */
class PengajuanLupaLapor extends Model
{
    /**
     * Nama tabel eksplisit dalam Bahasa Indonesia baku.
     *
     * @var string
     */
    protected $table = 'pengajuan_lupa_lapor';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     *
     * @var array<string>
     */
    protected $fillable = [
        'tutor_id',       // ID tutor yang mengajukan
        'murid_id',       // ID murid yang diajar
        'siswa_id',       // Legacy compatibility
        'tanggal',        // Tanggal mengajar retroaktif
        'jam_mulai',      // Jam mulai mengajar (format HH:MM)
        'jam_selesai',    // Jam selesai mengajar (format HH:MM)
        'alasan',         // Alasan lupa lapor
        'status',         // Status persetujuan: 'pending', 'disetujui', 'ditolak'
        'catatan_kepsek', // Catatan/alasan tanggapan dari Kepala Sekolah
    ];

    protected static function booted(): void
    {
        static::creating(function (PengajuanLupaLapor $pengajuan) {
            if (empty($pengajuan->attributes['murid_id']) && ! empty($pengajuan->attributes['siswa_id'])) {
                $pengajuan->attributes['murid_id'] = $pengajuan->attributes['siswa_id'];
            }
            unset($pengajuan->attributes['siswa_id']);
        });

        static::updating(function (PengajuanLupaLapor $pengajuan) {
            if (isset($pengajuan->attributes['siswa_id'])) {
                if (empty($pengajuan->attributes['murid_id']) && ! empty($pengajuan->attributes['siswa_id'])) {
                    $pengajuan->attributes['murid_id'] = $pengajuan->attributes['siswa_id'];
                }
                unset($pengajuan->attributes['siswa_id']);
            }
        });
    }

    public function getSiswaIdAttribute(): ?int
    {
        return $this->attributes['murid_id'] ?? null;
    }

    public function setSiswaIdAttribute($value): void
    {
        $this->attributes['murid_id'] = $value;
    }

    /**
     * Relasi: Pengajuan dimiliki oleh satu Tutor (BelongsTo).
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Relasi: Pengajuan terkait dengan satu Murid (BelongsTo).
     */
    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    /**
     * Alias relasi murid untuk backward compatibility.
     */
    public function siswa(): BelongsTo
    {
        return $this->murid();
    }
}

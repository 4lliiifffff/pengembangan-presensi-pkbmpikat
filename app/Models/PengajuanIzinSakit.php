<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PengajuanIzinSakit extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_izin_sakit';

    protected $fillable = [
        'tutor_id',
        'jenis',
        'tgl_mulai',
        'tgl_selesai',
        'alasan',
        'dokumen_surat',
        'status',
        'disetujui_oleh',
        'catatan_verifikasi',
    ];

    /**
     * Relasi ke Tutor.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Relasi ke User Verifikator (Kepsek / Admin).
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Accessor untuk URL dokumen pendukung.
     */
    public function getDokumenUrlAttribute(): ?string
    {
        if (! $this->dokumen_surat) {
            return null;
        }

        if (str_starts_with($this->dokumen_surat, 'http://') || str_starts_with($this->dokumen_surat, 'https://')) {
            return $this->dokumen_surat;
        }

        return Storage::url($this->dokumen_surat);
    }

    /**
     * Accessor label jenis.
     */
    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'sakit' => 'Sakit',
            default => 'Izin',
        };
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalSesi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_sesi';

    protected $fillable = [
        'jadwal_rutin_id',
        'tutor_id',
        'murid_id',
        'siswa_id',
        'kategori_tutorial_id',
        'jadwal_kerja_id',
        'lokasi_presensi_id',
        'tanggal_rencana',
        'jam_masuk_rencana',
        'jam_pulang_rencana',
        'durasi_jam',
        'jenis_sesi',
        'tanggal_asli',
        'alasan_penggantian',
        'status',
        'presensi_id',
        'presensi_murid_id',
        'presensi_siswa_id',
        'status_kehadiran_murid',
        'status_kehadiran_siswa',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rencana' => 'date',
            'tanggal_asli' => 'date',
            'durasi_jam' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JadwalSesi $sesi) {
            if (empty($sesi->attributes['murid_id']) && ! empty($sesi->attributes['siswa_id'])) {
                $sesi->attributes['murid_id'] = $sesi->attributes['siswa_id'];
            }
            if (empty($sesi->attributes['status_kehadiran_murid']) && ! empty($sesi->attributes['status_kehadiran_siswa'])) {
                $sesi->attributes['status_kehadiran_murid'] = $sesi->attributes['status_kehadiran_siswa'];
            }
            if (empty($sesi->attributes['presensi_murid_id']) && ! empty($sesi->attributes['presensi_siswa_id'])) {
                $sesi->attributes['presensi_murid_id'] = $sesi->attributes['presensi_siswa_id'];
            }
            unset($sesi->attributes['siswa_id'], $sesi->attributes['status_kehadiran_siswa'], $sesi->attributes['presensi_siswa_id']);
        });

        static::updating(function (JadwalSesi $sesi) {
            if (isset($sesi->attributes['siswa_id'])) {
                if (empty($sesi->attributes['murid_id']) && ! empty($sesi->attributes['siswa_id'])) {
                    $sesi->attributes['murid_id'] = $sesi->attributes['siswa_id'];
                }
                unset($sesi->attributes['siswa_id']);
            }
            if (isset($sesi->attributes['status_kehadiran_siswa'])) {
                if (empty($sesi->attributes['status_kehadiran_murid']) && ! empty($sesi->attributes['status_kehadiran_siswa'])) {
                    $sesi->attributes['status_kehadiran_murid'] = $sesi->attributes['status_kehadiran_siswa'];
                }
                unset($sesi->attributes['status_kehadiran_siswa']);
            }
            if (isset($sesi->attributes['presensi_siswa_id'])) {
                if (empty($sesi->attributes['presensi_murid_id']) && ! empty($sesi->attributes['presensi_siswa_id'])) {
                    $sesi->attributes['presensi_murid_id'] = $sesi->attributes['presensi_siswa_id'];
                }
                unset($sesi->attributes['presensi_siswa_id']);
            }
        });
    }

    public function getSiswaIdAttribute()
    {
        return $this->attributes['murid_id'] ?? null;
    }

    public function setSiswaIdAttribute($value): void
    {
        $this->attributes['murid_id'] = $value;
    }

    public function getStatusKehadiranSiswaAttribute()
    {
        return $this->attributes['status_kehadiran_murid'] ?? null;
    }

    public function setStatusKehadiranSiswaAttribute($value): void
    {
        $this->attributes['status_kehadiran_murid'] = $value;
    }

    public function getPresensiSiswaIdAttribute()
    {
        return $this->attributes['presensi_murid_id'] ?? null;
    }

    public function setPresensiSiswaIdAttribute($value): void
    {
        $this->attributes['presensi_murid_id'] = $value;
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function jadwalRutin(): BelongsTo
    {
        return $this->belongsTo(JadwalRutin::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->murid();
    }

    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class);
    }

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function lokasiPresensi(): BelongsTo
    {
        return $this->belongsTo(LokasiPresensi::class, 'lokasi_presensi_id');
    }

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeHariIni(Builder $query, ?string $date = null): Builder
    {
        $targetDate = $date ?? Carbon::now('Asia/Jakarta')->toDateString();

        return $query->whereDate('tanggal_rencana', $targetDate);
    }

    public function scopeTerjadwal(Builder $query): Builder
    {
        return $query->where('status', 'terjadwal');
    }

    public function scopePengganti(Builder $query): Builder
    {
        return $query->where('jenis_sesi', 'pengganti');
    }

    // ── Accessors & Helpers ────────────────────────────────────────────────────

    public function getJamMasukFormattedAttribute(): string
    {
        return substr((string) $this->jam_masuk_rencana, 0, 5);
    }

    public function getJamPulangFormattedAttribute(): string
    {
        return substr((string) $this->jam_pulang_rencana, 0, 5);
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_sesi) {
            'pengganti' => 'Sesi Pengganti (Reschedule)',
            'tambahan' => 'Sesi Tambahan',
            default => $this->jadwal_rutin_id ? 'Sesi Rutin Mingguan' : 'Sesi Reguler',
        };
    }
}
